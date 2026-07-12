<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\RelationManagers;

use Filament\Actions\{BulkActionGroup, CreateAction, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Forms\Components\{Repeater, Select, TextInput, Textarea, Toggle, ToggleButtons};
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\{Grid, Section};
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Wallacemartinss\FilamentOnboarding\Enums\{CompletionMode, StepType};
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Models\OnboardingStep;

class StepsRelationManager extends RelationManager
{
    protected static string $relationship = 'steps';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('filament-onboarding::onboarding.resource.steps.title');
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

                        TextInput::make('icon')
                            ->label(__('filament-onboarding::onboarding.resource.fields.icon'))
                            ->placeholder('heroicon-o-server')
                            ->maxLength(255),

                        TextInput::make('cta_url')
                            ->label(__('filament-onboarding::onboarding.resource.fields.cta_url'))
                            ->helperText(__('filament-onboarding::onboarding.resource.fields.cta_url_helper'))
                            ->placeholder('/app/{tenant}/servers/create')
                            ->maxLength(2048),

                        TextInput::make('cta_route')
                            ->label(__('filament-onboarding::onboarding.resource.fields.cta_route'))
                            ->helperText(__('filament-onboarding::onboarding.resource.fields.cta_route_helper'))
                            ->maxLength(255),
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
                                TextInput::make('selector')
                                    ->label(__('filament-onboarding::onboarding.resource.tour.selector'))
                                    ->helperText(__('filament-onboarding::onboarding.resource.tour.selector_helper'))
                                    ->placeholder('[data-onboarding="create-server"]')
                                    ->maxLength(255),

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
