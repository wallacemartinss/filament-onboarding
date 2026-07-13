<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\RelationManagers;

use Filament\Actions\{ActionGroup, BulkActionGroup, CreateAction, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Forms\Components\{FileUpload, Hidden, Repeater, Select, TextInput, Textarea, Toggle, ToggleButtons};
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\{Grid, Section, Tabs};
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\{Get, Set};
use Filament\Schemas\Schema;
use Filament\Support\Enums\{FontWeight, Width};
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Wallacemartinss\FilamentOnboarding\Enums\{CompletionMode, MediaSource, MediaType, ModalPosition, StepType};
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Models\OnboardingStep;
use Wallacemartinss\FilamentOnboarding\Support\{IconInput, PanelTargets};

/**
 * Authoring a step used to mean scrolling through four stacked sections, most of
 * them empty for any given step. It is four tabs now — content, behaviour, media,
 * tour — so what you are working on is on screen and the rest is out of the way.
 */
class StepsRelationManager extends RelationManager
{
    protected static string $relationship = 'steps';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament-onboarding::onboarding.resource.steps.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('step')
                ->columnSpanFull()
                ->tabs([
                    Tab::make(__('filament-onboarding::onboarding.resource.sections.content'))
                        ->icon(Heroicon::OutlinedLanguage)
                        ->schema($this->contentFields()),

                    Tab::make(__('filament-onboarding::onboarding.resource.sections.behaviour'))
                        ->icon(Heroicon::OutlinedCog6Tooth)
                        ->schema($this->behaviourFields()),

                    Tab::make(__('filament-onboarding::onboarding.resource.sections.media'))
                        ->icon(Heroicon::OutlinedPhoto)
                        ->badge(fn (?OnboardingStep $record): ?string => $record?->hasMedia()
                            ? $record->media_type->getLabel()
                            : null)
                        ->schema($this->mediaFields()),

                    Tab::make(__('filament-onboarding::onboarding.resource.sections.tour'))
                        ->icon(Heroicon::OutlinedSparkles)
                        ->badge(fn (Get $get): ?int => $get('type') === StepType::Tour->value
                            ? count($get('tour_steps') ?? [])
                            : null)
                        ->visible(fn (Get $get): bool => $get('type') === StepType::Tour->value)
                        ->schema($this->tourFields()),
                ]),
        ]);
    }

    /**
     * @return array<int, mixed>
     */
    private function contentFields(): array
    {
        $locales = Onboarding::locales();

        return [
            Tabs::make('translations')
                ->contained(false)
                ->columnSpanFull()
                ->tabs(
                    collect($locales)
                        ->map(fn (string $locale): Tab => Tab::make($locale)
                            ->label(Str::upper(str_replace('_', '-', $locale)))
                            ->schema([
                                TextInput::make("title.{$locale}")
                                    ->label(__('filament-onboarding::onboarding.resource.fields.title'))
                                    ->prefixIcon(Heroicon::OutlinedFlag)
                                    ->placeholder(__('filament-onboarding::onboarding.resource.placeholders.step_title'))
                                    ->required($locale === $locales[0])
                                    ->maxLength(255),

                                Textarea::make("description.{$locale}")
                                    ->label(__('filament-onboarding::onboarding.resource.fields.description'))
                                    ->placeholder(__('filament-onboarding::onboarding.resource.placeholders.step_description'))
                                    ->rows(2)
                                    ->maxLength(500),

                                TextInput::make("cta_label.{$locale}")
                                    ->label(__('filament-onboarding::onboarding.resource.fields.cta_label'))
                                    ->prefixIcon(Heroicon::OutlinedCursorArrowRays)
                                    ->placeholder(__('filament-onboarding::onboarding.checklist.go'))
                                    ->maxLength(255),
                            ]))
                        ->all()
                ),

            Grid::make(2)->schema([
                IconInput::make('icon')
                    ->label(__('filament-onboarding::onboarding.resource.fields.icon')),

                TextInput::make('key')
                    ->label(__('filament-onboarding::onboarding.resource.fields.key'))
                    ->prefixIcon(Heroicon::OutlinedKey)
                    ->helperText(__('filament-onboarding::onboarding.resource.fields.step_key_helper'))
                    ->placeholder('connect-server')
                    ->required()
                    ->alphaDash()
                    // The database is unique on (flow_id, key); without this
                    // rule a repeated key surfaces as a QueryException instead
                    // of a validation message under the field.
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule): Unique => $rule->where('flow_id', $this->getOwnerRecord()->getKey()),
                    )
                    ->maxLength(255),
            ]),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function behaviourFields(): array
    {
        return [
            Grid::make(2)->schema([
                ToggleButtons::make('type')
                    ->label(__('filament-onboarding::onboarding.resource.fields.type'))
                    ->helperText(__('filament-onboarding::onboarding.resource.fields.type_helper'))
                    ->options(StepType::class)
                    ->default(StepType::Task)
                    ->inline()
                    ->live()
                    ->required(),

                Select::make('completion_mode')
                    ->label(__('filament-onboarding::onboarding.resource.fields.completion_mode'))
                    ->prefixIcon(Heroicon::OutlinedCheckCircle)
                    ->helperText(__('filament-onboarding::onboarding.resource.fields.completion_mode_helper'))
                    ->options(CompletionMode::class)
                    ->default(CompletionMode::Manual)
                    ->native(false)
                    ->live()
                    ->required(),
            ]),

            // Only the field the chosen mode actually needs.
            Select::make('condition_key')
                ->label(__('filament-onboarding::onboarding.resource.fields.condition'))
                ->prefixIcon(Heroicon::OutlinedBolt)
                ->helperText(__('filament-onboarding::onboarding.resource.fields.condition_helper'))
                ->options(fn (): array => Onboarding::conditions()->options())
                ->searchable()
                ->native(false)
                ->columnSpanFull()
                ->required(fn (Get $get): bool => $get('completion_mode') === CompletionMode::Condition->value)
                ->visible(fn (Get $get): bool => $get('completion_mode') === CompletionMode::Condition->value),

            TextInput::make('visit_url')
                ->label(__('filament-onboarding::onboarding.resource.fields.visit_url'))
                ->prefixIcon(Heroicon::OutlinedLink)
                ->helperText(__('filament-onboarding::onboarding.resource.fields.visit_url_helper'))
                ->placeholder('/app/*/servers/create')
                ->columnSpanFull()
                ->required(fn (Get $get): bool => $get('completion_mode') === CompletionMode::Visit->value)
                ->visible(fn (Get $get): bool => $get('completion_mode') === CompletionMode::Visit->value),

            Section::make(__('filament-onboarding::onboarding.resource.sections.destination'))
                ->description(__('filament-onboarding::onboarding.resource.sections.destination_description'))
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->collapsed(fn (?OnboardingStep $record): bool => blank($record?->cta_route) && blank($record?->cta_url))
                ->schema([
                    Select::make('cta_route')
                        ->label(__('filament-onboarding::onboarding.resource.fields.cta_route'))
                        ->prefixIcon(Heroicon::OutlinedWindow)
                        ->helperText(__('filament-onboarding::onboarding.resource.fields.cta_route_helper'))
                        ->options(fn (): array => PanelTargets::pageOptions($this->panelId()))
                        ->searchable()
                        ->native(false),

                    TextInput::make('cta_url')
                        ->label(__('filament-onboarding::onboarding.resource.fields.cta_url'))
                        ->prefixIcon(Heroicon::OutlinedLink)
                        ->helperText(__('filament-onboarding::onboarding.resource.fields.cta_url_helper'))
                        ->placeholder('/app/{tenant}/servers/create')
                        ->maxLength(2048),
                ]),

            Section::make(__('filament-onboarding::onboarding.resource.sections.rules'))
                ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                ->schema([
                    Select::make('visibility_condition')
                        ->label(__('filament-onboarding::onboarding.resource.fields.visibility'))
                        ->prefixIcon(Heroicon::OutlinedFunnel)
                        ->helperText(__('filament-onboarding::onboarding.resource.fields.visibility_helper'))
                        ->options(fn (): array => Onboarding::conditions()->options())
                        ->placeholder(__('filament-onboarding::onboarding.resource.fields.visibility_everyone'))
                        ->searchable()
                        ->native(false)
                        ->columnSpanFull(),

                    Grid::make(3)->schema([
                        Toggle::make('is_required')
                            ->label(__('filament-onboarding::onboarding.resource.fields.is_required'))
                            ->helperText(__('filament-onboarding::onboarding.resource.fields.is_required_helper'))
                            ->default(true),

                        Toggle::make('is_active')
                            ->label(__('filament-onboarding::onboarding.resource.fields.is_active'))
                            ->default(true),

                        TextInput::make('sort_order')
                            ->label(__('filament-onboarding::onboarding.resource.fields.sort_order'))
                            ->prefixIcon(Heroicon::OutlinedBars3BottomLeft)
                            ->numeric()
                            ->default(0),
                    ]),
                ]),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function mediaFields(): array
    {
        $locales = Onboarding::locales();

        return [
            Grid::make(2)->schema([
                Select::make('media_type')
                    ->label(__('filament-onboarding::onboarding.resource.fields.media_type'))
                    ->prefixIcon(Heroicon::OutlinedPhoto)
                    ->options(MediaType::class)
                    ->default(MediaType::None)
                    ->selectablePlaceholder(false)
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(fn (Set $set): mixed => $set('media_source', null)),

                Select::make('media_source')
                    ->label(__('filament-onboarding::onboarding.resource.fields.media_source'))
                    ->prefixIcon(Heroicon::OutlinedCloudArrowUp)
                    ->options(fn (Get $get): array => collect(
                        $get('media_type') === MediaType::Image->value
                            ? MediaSource::forImage()
                            : MediaSource::forVideo()
                    )
                        ->mapWithKeys(fn (MediaSource $source): array => [$source->value => $source->getLabel()])
                        ->all())
                    ->native(false)
                    ->live()
                    ->required(fn (Get $get): bool => static::wantsMedia($get))
                    ->visible(fn (Get $get): bool => static::wantsMedia($get)),
            ]),

            FileUpload::make('media_path')
                ->label(__('filament-onboarding::onboarding.resource.fields.media_file'))
                ->disk(fn (): string => config('filament-onboarding.media.disk', 'public'))
                ->directory(fn (): string => config('filament-onboarding.media.directory', 'onboarding'))
                ->visibility(fn (): string => config('filament-onboarding.media.visibility', 'public'))
                ->acceptedFileTypes(fn (Get $get): array => config(
                    'filament-onboarding.media.accept.' . ($get('media_type') === MediaType::Video->value ? 'video' : 'image'),
                    [],
                ))
                ->maxSize(fn (Get $get): int => (int) config(
                    'filament-onboarding.media.max_size.' . ($get('media_type') === MediaType::Video->value ? 'video' : 'image'),
                    5120,
                ))
                ->image(fn (Get $get): bool => $get('media_type') === MediaType::Image->value)
                ->imageEditor(fn (Get $get): bool => $get('media_type') === MediaType::Image->value)
                ->columnSpanFull()
                ->required(fn (Get $get): bool => $get('media_source') === MediaSource::Upload->value)
                ->visible(fn (Get $get): bool => static::wantsMedia($get) && $get('media_source') === MediaSource::Upload->value)
                ->afterStateUpdated(fn (Set $set): mixed => $set('media_disk', config('filament-onboarding.media.disk', 'public'))),

            Hidden::make('media_disk')
                ->default(fn (): string => config('filament-onboarding.media.disk', 'public')),

            TextInput::make('media_url')
                ->label(__('filament-onboarding::onboarding.resource.fields.media_url'))
                ->prefixIcon(Heroicon::OutlinedLink)
                ->helperText(__('filament-onboarding::onboarding.resource.fields.media_url_helper'))
                ->placeholder('https://youtu.be/dQw4w9WgXcQ')
                ->columnSpanFull()
                ->url(fn (Get $get): bool => $get('media_source') === MediaSource::Url->value)
                ->required(fn (Get $get): bool => static::wantsMedia($get) && filled($get('media_source')) && $get('media_source') !== MediaSource::Upload->value)
                ->visible(fn (Get $get): bool => static::wantsMedia($get) && filled($get('media_source')) && $get('media_source') !== MediaSource::Upload->value)
                ->maxLength(2048),

            Tabs::make('media_translations')
                ->contained(false)
                ->columnSpanFull()
                ->visible(fn (Get $get): bool => static::wantsMedia($get))
                ->tabs(
                    collect($locales)
                        ->map(fn (string $locale): Tab => Tab::make($locale)
                            ->label(Str::upper(str_replace('_', '-', $locale)))
                            ->schema([
                                Textarea::make("media_caption.{$locale}")
                                    ->label(__('filament-onboarding::onboarding.resource.fields.media_caption'))
                                    ->rows(2)
                                    ->maxLength(500),
                            ]))
                        ->all()
                ),

            Grid::make(2)
                ->visible(fn (Get $get): bool => static::wantsMedia($get))
                ->schema([
                    Select::make('modal_position')
                        ->label(__('filament-onboarding::onboarding.resource.fields.modal_position'))
                        ->prefixIcon(Heroicon::OutlinedSquares2x2)
                        ->helperText(__('filament-onboarding::onboarding.resource.fields.modal_position_helper'))
                        ->options(ModalPosition::class)
                        ->placeholder(__('filament-onboarding::onboarding.resource.fields.modal_position_default'))
                        ->native(false),

                    TextInput::make('video_completion_threshold')
                        ->label(__('filament-onboarding::onboarding.resource.fields.video_threshold'))
                        ->helperText(__('filament-onboarding::onboarding.resource.fields.video_threshold_helper'))
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(100)
                        ->suffix('%')
                        ->default(90)
                        ->visible(fn (Get $get): bool => $get('media_type') === MediaType::Video->value),
                ]),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function tourFields(): array
    {
        $locales = Onboarding::locales();

        return [
            Repeater::make('tour_steps')
                ->hiddenLabel()
                ->addActionLabel(__('filament-onboarding::onboarding.resource.tour.add'))
                ->reorderableWithButtons()
                ->collapsible()
                ->collapsed()
                ->cloneable()
                ->itemLabel(fn (array $state): string => static::tourStopLabel($state))
                ->defaultItems(1)
                ->columnSpanFull()
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('widget')
                            ->label(__('filament-onboarding::onboarding.resource.tour.widget'))
                            ->prefixIcon(Heroicon::OutlinedSquares2x2)
                            ->helperText(__('filament-onboarding::onboarding.resource.tour.widget_helper'))
                            ->options(fn (): array => PanelTargets::widgetOptions($this->panelId()))
                            ->searchable()
                            ->native(false),

                        TextInput::make('selector')
                            ->label(__('filament-onboarding::onboarding.resource.tour.selector'))
                            ->prefixIcon(Heroicon::OutlinedViewfinderCircle)
                            ->helperText(__('filament-onboarding::onboarding.resource.tour.selector_helper'))
                            ->placeholder('[data-onboarding="create-server"]')
                            ->maxLength(255),
                    ]),

                    Grid::make(3)->schema([
                        Select::make('route')
                            ->label(__('filament-onboarding::onboarding.resource.tour.route'))
                            ->prefixIcon(Heroicon::OutlinedWindow)
                            ->helperText(__('filament-onboarding::onboarding.resource.tour.route_helper'))
                            ->options(fn (): array => PanelTargets::pageOptions($this->panelId()))
                            ->searchable()
                            ->native(false)
                            ->columnSpan(2),

                        Select::make('placement')
                            ->label(__('filament-onboarding::onboarding.resource.tour.placement'))
                            ->options([
                                'auto'   => __('filament-onboarding::onboarding.resource.tour.placements.auto'),
                                'top'    => __('filament-onboarding::onboarding.resource.tour.placements.top'),
                                'bottom' => __('filament-onboarding::onboarding.resource.tour.placements.bottom'),
                            ])
                            ->default('auto')
                            ->selectablePlaceholder(false)
                            ->native(false),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('condition')
                            ->label(__('filament-onboarding::onboarding.resource.tour.visibility'))
                            ->prefixIcon(Heroicon::OutlinedFunnel)
                            ->helperText(__('filament-onboarding::onboarding.resource.tour.visibility_helper'))
                            ->options(fn (): array => Onboarding::conditions()->options())
                            ->placeholder(__('filament-onboarding::onboarding.resource.fields.visibility_everyone'))
                            ->searchable()
                            ->native(false),

                        TextInput::make('advance')
                            ->label(__('filament-onboarding::onboarding.resource.tour.advance'))
                            ->prefixIcon(Heroicon::OutlinedForward)
                            ->helperText(__('filament-onboarding::onboarding.resource.tour.advance_helper'))
                            ->placeholder('[wire\\:click="nextStep"]')
                            ->maxLength(255),
                    ]),

                    Toggle::make('optional')
                        ->label(__('filament-onboarding::onboarding.resource.tour.optional'))
                        ->helperText(__('filament-onboarding::onboarding.resource.tour.optional_helper'))
                        ->default(false),

                    Tabs::make('tour_translations')
                        ->contained(false)
                        ->tabs(
                            collect($locales)
                                ->map(fn (string $locale): Tab => Tab::make($locale)
                                    ->label(Str::upper(str_replace('_', '-', $locale)))
                                    ->schema([
                                        TextInput::make("title.{$locale}")
                                            ->label(__('filament-onboarding::onboarding.resource.fields.title'))
                                            ->maxLength(255),

                                        Textarea::make("body.{$locale}")
                                            ->label(__('filament-onboarding::onboarding.resource.tour.body'))
                                            ->rows(2)
                                            ->maxLength(500),
                                    ]))
                                ->all()
                        ),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->label(__('filament-onboarding::onboarding.resource.fields.title'))
                    ->state(fn (OnboardingStep $record): ?string => $record->translate('title'))
                    ->description(fn (OnboardingStep $record): string => $record->key)
                    ->icon(fn (OnboardingStep $record): ?string => $record->icon)
                    ->weight(FontWeight::Medium)
                    ->wrap(),

                TextColumn::make('type')
                    ->label(__('filament-onboarding::onboarding.resource.fields.type'))
                    ->badge(),

                TextColumn::make('completion_mode')
                    ->label(__('filament-onboarding::onboarding.resource.fields.completion_mode'))
                    ->badge()
                    ->color('gray')
                    // What the mode hangs off, so a step's wiring is visible from
                    // the list rather than two clicks away.
                    ->description(fn (OnboardingStep $record): ?string => match (true) {
                        filled($record->condition_key) => $record->condition_key,
                        filled($record->visit_url)     => $record->visit_url,
                        default                        => null,
                    }),

                TextColumn::make('media_type')
                    ->label(__('filament-onboarding::onboarding.resource.fields.media_type'))
                    ->badge()
                    ->color('info')
                    ->state(fn (OnboardingStep $record): ?string => $record->hasMedia()
                        ? $record->media_type->getLabel()
                        : null)
                    ->placeholder('—')
                    ->visibleFrom('lg'),

                TextColumn::make('visibility_condition')
                    ->label(__('filament-onboarding::onboarding.resource.fields.visibility'))
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn (string $state): string => Onboarding::conditions()->options()[$state] ?? $state)
                    ->placeholder(__('filament-onboarding::onboarding.resource.fields.visibility_everyone'))
                    ->visibleFrom('xl'),

                IconColumn::make('is_required')
                    ->label(__('filament-onboarding::onboarding.resource.fields.is_required'))
                    ->boolean()
                    ->alignCenter()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label(__('filament-onboarding::onboarding.resource.fields.is_active'))
                    ->boolean()
                    ->alignCenter(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon(Heroicon::OutlinedPlus)
                    ->modalWidth(Width::FiveExtraLarge),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->icon(Heroicon::OutlinedPencilSquare)
                        ->modalWidth(Width::FiveExtraLarge),
                    DeleteAction::make()
                        ->icon(Heroicon::OutlinedTrash),
                ])->icon(Heroicon::OutlinedEllipsisVertical),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedListBullet)
            ->emptyStateHeading(__('filament-onboarding::onboarding.resource.steps.empty_heading'))
            ->emptyStateDescription(__('filament-onboarding::onboarding.resource.steps.empty_description'));
    }

    /**
     * A tour stop names itself by what it points at, so a collapsed repeater
     * still reads as a list of stops rather than "Item 1, Item 2".
     *
     * @param  array<string, mixed>  $state
     */
    private static function tourStopLabel(array $state): string
    {
        $target = $state['selector'] ?? null;

        if (blank($target) && filled($state['widget'] ?? null)) {
            $target = class_basename((string) $state['widget']);
        }

        $title = collect($state['title'] ?? [])->filter()->first();

        return collect([$title, $target])
            ->filter()
            ->implode(' — ') ?: __('filament-onboarding::onboarding.resource.tour.add');
    }

    /**
     * Whether this step carries media at all — everything in the tab hangs off
     * this answer.
     */
    private static function wantsMedia(Get $get): bool
    {
        return filled($get('media_type')) && $get('media_type') !== MediaType::None->value;
    }

    /**
     * The panel whose pages and widgets this flow's steps can point at. A flow
     * left without a panel belongs to all of them, and so do its options.
     */
    private function panelId(): ?string
    {
        $flow = $this->getOwnerRecord();

        return $flow->panel_id;
    }
}
