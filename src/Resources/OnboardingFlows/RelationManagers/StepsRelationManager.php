<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\RelationManagers;

use Filament\Actions\{BulkActionGroup, CreateAction, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Forms\Components\{FileUpload, Hidden, Repeater, Select, TextInput, Textarea, Toggle, ToggleButtons};
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\{Grid, Section};
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\{Get, Set};
use Filament\Schemas\Schema;
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Wallacemartinss\FilamentOnboarding\Enums\{CompletionMode, MediaSource, MediaType, ModalPosition, StepType};
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Models\OnboardingStep;
use Wallacemartinss\FilamentOnboarding\Support\{IconInput, PanelTargets};

class StepsRelationManager extends RelationManager
{
    protected static string $relationship = 'steps';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('filament-onboarding::onboarding.resource.steps.title');
    }

    /**
     * Whether this step carries media at all — everything in the section hangs
     * off this answer.
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

    public function form(Schema $schema): Schema
    {
        $locales     = Onboarding::locales();
        $firstLocale = $locales[0];

        return $schema->components([
            Section::make(__('filament-onboarding::onboarding.resource.sections.content'))
                ->schema([
                    Tabs::make('translations')
                        ->tabs(
                            collect($locales)
                                ->map(fn (string $locale): Tab => Tab::make($locale)
                                    ->label(Str::upper(str_replace('_', '-', $locale)))
                                    ->schema([
                                        TextInput::make("title.{$locale}")
                                            ->label(__('filament-onboarding::onboarding.resource.fields.title'))
                                            ->required($locale === $firstLocale)
                                            ->maxLength(255),

                                        Textarea::make("description.{$locale}")
                                            ->label(__('filament-onboarding::onboarding.resource.fields.description'))
                                            ->rows(2)
                                            ->maxLength(500),

                                        TextInput::make("cta_label.{$locale}")
                                            ->label(__('filament-onboarding::onboarding.resource.fields.cta_label'))
                                            ->placeholder(__('filament-onboarding::onboarding.checklist.go'))
                                            ->maxLength(255),
                                    ]))
                                ->all()
                        )
                        ->columnSpanFull(),
                ]),

            Section::make(__('filament-onboarding::onboarding.resource.sections.behaviour'))
                ->schema([
                    ToggleButtons::make('type')
                        ->label(__('filament-onboarding::onboarding.resource.fields.type'))
                        ->options(StepType::class)
                        ->default(StepType::Task)
                        ->inline()
                        ->live()
                        ->required(),

                    Select::make('completion_mode')
                        ->label(__('filament-onboarding::onboarding.resource.fields.completion_mode'))
                        ->options(CompletionMode::class)
                        ->default(CompletionMode::Manual)
                        ->native(false)
                        ->live()
                        ->required(),

                    Select::make('condition_key')
                        ->label(__('filament-onboarding::onboarding.resource.fields.condition'))
                        ->helperText(__('filament-onboarding::onboarding.resource.fields.condition_helper'))
                        ->options(fn (): array => Onboarding::conditions()->options())
                        ->searchable()
                        ->native(false)
                        ->required(fn (Get $get): bool => $get('completion_mode') === CompletionMode::Condition->value)
                        ->visible(fn (Get $get): bool => $get('completion_mode') === CompletionMode::Condition->value),

                    TextInput::make('visit_url')
                        ->label(__('filament-onboarding::onboarding.resource.fields.visit_url'))
                        ->helperText(__('filament-onboarding::onboarding.resource.fields.visit_url_helper'))
                        ->placeholder('/app/*/servers/create')
                        ->required(fn (Get $get): bool => $get('completion_mode') === CompletionMode::Visit->value)
                        ->visible(fn (Get $get): bool => $get('completion_mode') === CompletionMode::Visit->value),

                    Grid::make(2)->schema([
                        TextInput::make('key')
                            ->label(__('filament-onboarding::onboarding.resource.fields.key'))
                            ->helperText(__('filament-onboarding::onboarding.resource.fields.step_key_helper'))
                            ->required()
                            ->alphaDash()
                            ->maxLength(255),

                        IconInput::make('icon')
                            ->label(__('filament-onboarding::onboarding.resource.fields.icon')),

                        // Discovered from the panel: the destination can only be
                        // a page that exists, and it survives a renamed slug.
                        Select::make('cta_route')
                            ->label(__('filament-onboarding::onboarding.resource.fields.cta_route'))
                            ->helperText(__('filament-onboarding::onboarding.resource.fields.cta_route_helper'))
                            ->options(fn (): array => PanelTargets::pageOptions($this->panelId()))
                            ->searchable()
                            ->native(false),

                        TextInput::make('cta_url')
                            ->label(__('filament-onboarding::onboarding.resource.fields.cta_url'))
                            ->helperText(__('filament-onboarding::onboarding.resource.fields.cta_url_helper'))
                            ->placeholder('/app/{tenant}/servers/create')
                            ->maxLength(2048),
                    ]),

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
                            ->numeric()
                            ->default(0),
                    ]),
                ]),

            Section::make(__('filament-onboarding::onboarding.resource.sections.media'))
                ->description(__('filament-onboarding::onboarding.resource.sections.media_description'))
                ->collapsed(fn (?OnboardingStep $record): bool => !($record?->hasMedia() ?? false))
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('media_type')
                            ->label(__('filament-onboarding::onboarding.resource.fields.media_type'))
                            ->options(MediaType::class)
                            ->default(MediaType::None)
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Set $set): mixed => $set('media_source', null)),

                        Select::make('media_source')
                            ->label(__('filament-onboarding::onboarding.resource.fields.media_source'))
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

                    // Uploads land on the configured disk — S3, R2, local — and a
                    // private disk is signed at render time rather than opened up.
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
                        ->required(fn (Get $get): bool => $get('media_source') === MediaSource::Upload->value)
                        ->visible(fn (Get $get): bool => static::wantsMedia($get) && $get('media_source') === MediaSource::Upload->value)
                        ->afterStateUpdated(fn (Set $set): mixed => $set('media_disk', config('filament-onboarding.media.disk', 'public'))),

                    Hidden::make('media_disk')
                        ->default(fn (): string => config('filament-onboarding.media.disk', 'public')),

                    TextInput::make('media_url')
                        ->label(__('filament-onboarding::onboarding.resource.fields.media_url'))
                        ->helperText(__('filament-onboarding::onboarding.resource.fields.media_url_helper'))
                        ->placeholder('https://youtu.be/dQw4w9WgXcQ')
                        ->url(fn (Get $get): bool => $get('media_source') === MediaSource::Url->value)
                        ->required(fn (Get $get): bool => static::wantsMedia($get) && $get('media_source') !== MediaSource::Upload->value && filled($get('media_source')))
                        ->visible(fn (Get $get): bool => static::wantsMedia($get) && $get('media_source') !== MediaSource::Upload->value && filled($get('media_source')))
                        ->maxLength(2048),

                    Tabs::make('media_translations')
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

                    Grid::make(2)->schema([
                        Select::make('modal_position')
                            ->label(__('filament-onboarding::onboarding.resource.fields.modal_position'))
                            ->helperText(__('filament-onboarding::onboarding.resource.fields.modal_position_helper'))
                            ->options(ModalPosition::class)
                            ->placeholder(__('filament-onboarding::onboarding.resource.fields.modal_position_default'))
                            ->native(false)
                            ->visible(fn (Get $get): bool => static::wantsMedia($get)),

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
                ]),

            Section::make(__('filament-onboarding::onboarding.resource.sections.tour'))
                ->description(__('filament-onboarding::onboarding.resource.sections.tour_description'))
                ->visible(fn (Get $get): bool => $get('type') === StepType::Tour->value)
                ->schema([
                    Repeater::make('tour_steps')
                        ->hiddenLabel()
                        ->addActionLabel(__('filament-onboarding::onboarding.resource.tour.add'))
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['selector'] ?? null)
                        ->defaultItems(1)
                        ->schema([
                            Grid::make(2)->schema([
                                // Widgets share one wrapper class, so there is no
                                // selector to type: pick the widget and the runner
                                // finds it by the Livewire component it is.
                                Select::make('widget')
                                    ->label(__('filament-onboarding::onboarding.resource.tour.widget'))
                                    ->helperText(__('filament-onboarding::onboarding.resource.tour.widget_helper'))
                                    ->options(fn (): array => PanelTargets::widgetOptions($this->panelId()))
                                    ->searchable()
                                    ->native(false),

                                Select::make('placement')
                                    ->label(__('filament-onboarding::onboarding.resource.tour.placement'))
                                    ->options([
                                        'auto'   => __('filament-onboarding::onboarding.resource.tour.placements.auto'),
                                        'top'    => __('filament-onboarding::onboarding.resource.tour.placements.top'),
                                        'bottom' => __('filament-onboarding::onboarding.resource.tour.placements.bottom'),
                                    ])
                                    ->default('auto')
                                    ->native(false),
                            ]),

                            TextInput::make('selector')
                                ->label(__('filament-onboarding::onboarding.resource.tour.selector'))
                                ->helperText(__('filament-onboarding::onboarding.resource.tour.selector_helper'))
                                ->placeholder('[data-onboarding="create-server"]')
                                ->maxLength(255),

                            Select::make('route')
                                ->label(__('filament-onboarding::onboarding.resource.tour.route'))
                                ->helperText(__('filament-onboarding::onboarding.resource.tour.route_helper'))
                                ->options(fn (): array => PanelTargets::pageOptions($this->panelId()))
                                ->searchable()
                                ->native(false),

                            TextInput::make('url')
                                ->label(__('filament-onboarding::onboarding.resource.tour.url'))
                                ->helperText(__('filament-onboarding::onboarding.resource.tour.url_helper'))
                                ->placeholder('/app/{tenant}/servers')
                                ->maxLength(2048),

                            Tabs::make('tour_translations')
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
                ]),
        ]);
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
                    ->description(fn (OnboardingStep $record): string => $record->key),

                TextColumn::make('type')
                    ->label(__('filament-onboarding::onboarding.resource.fields.type'))
                    ->badge(),

                TextColumn::make('completion_mode')
                    ->label(__('filament-onboarding::onboarding.resource.fields.completion_mode'))
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_required')
                    ->label(__('filament-onboarding::onboarding.resource.fields.is_required'))
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label(__('filament-onboarding::onboarding.resource.fields.is_active'))
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
