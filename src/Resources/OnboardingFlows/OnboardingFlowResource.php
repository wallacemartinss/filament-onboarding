<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows;

use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Facades\Filament;
use Filament\Forms\Components\{Select, TextInput, Textarea, Toggle};
use Filament\Resources\Resource;
use Filament\Schemas\Components\{Grid, Section};
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Table;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\FilamentOnboardingPlugin;
use Wallacemartinss\FilamentOnboarding\Models\OnboardingFlow;
use Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\Pages\{CreateOnboardingFlow, EditOnboardingFlow, ListOnboardingFlows};
use Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\RelationManagers\StepsRelationManager;
use Wallacemartinss\FilamentOnboarding\Support\IconInput;

class OnboardingFlowResource extends Resource
{
    public static function getModel(): string
    {
        return Onboarding::flowModel();
    }

    public static function getNavigationIcon(): ?string
    {
        return static::plugin()?->getNavigationIcon()
            ?? config('filament-onboarding.resource.navigation_icon', 'heroicon-o-map');
    }

    public static function getNavigationGroup(): ?string
    {
        return static::plugin()?->getNavigationGroup()
            ?? config('filament-onboarding.resource.navigation_group');
    }

    public static function getNavigationSort(): ?int
    {
        return static::plugin()?->getNavigationSort()
            ?? config('filament-onboarding.resource.navigation_sort');
    }

    protected static function plugin(): ?FilamentOnboardingPlugin
    {
        try {
            return FilamentOnboardingPlugin::get();
        } catch (\Throwable) {
            return null;
        }
    }

    public static function getModelLabel(): string
    {
        return __('filament-onboarding::onboarding.resource.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-onboarding::onboarding.resource.plural');
    }

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'onboarding-flows';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('filament-onboarding::onboarding.resource.sections.content'))
                ->schema([
                    Tabs::make('translations')
                        ->tabs(
                            collect(Onboarding::locales())
                                ->map(fn (string $locale): Tab => Tab::make($locale)
                                    ->label(static::localeLabel($locale))
                                    ->schema([
                                        TextInput::make("title.{$locale}")
                                            ->label(__('filament-onboarding::onboarding.resource.fields.title'))
                                            ->required($locale === Onboarding::locales()[0])
                                            ->maxLength(255),

                                        Textarea::make("description.{$locale}")
                                            ->label(__('filament-onboarding::onboarding.resource.fields.description'))
                                            ->rows(2)
                                            ->maxLength(500),
                                    ]))
                                ->all()
                        )
                        ->columnSpanFull(),
                ]),

            Section::make(__('filament-onboarding::onboarding.resource.sections.settings'))
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('key')
                            ->label(__('filament-onboarding::onboarding.resource.fields.key'))
                            ->helperText(__('filament-onboarding::onboarding.resource.fields.key_helper'))
                            ->required()
                            ->alphaDash()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Select::make('panel_id')
                            ->label(__('filament-onboarding::onboarding.resource.fields.panel'))
                            ->helperText(__('filament-onboarding::onboarding.resource.fields.panel_helper'))
                            ->options(static::panelOptions())
                            ->searchable()
                            ->native(false),

                        IconInput::make('icon')
                            ->label(__('filament-onboarding::onboarding.resource.fields.icon')),

                        Select::make('color')
                            ->label(__('filament-onboarding::onboarding.resource.fields.color'))
                            ->options([
                                'primary' => 'primary',
                                'info'    => 'info',
                                'success' => 'success',
                                'warning' => 'warning',
                                'danger'  => 'danger',
                                'gray'    => 'gray',
                            ])
                            ->default('primary')
                            ->native(false),

                        TextInput::make('sort_order')
                            ->label(__('filament-onboarding::onboarding.resource.fields.sort_order'))
                            ->numeric()
                            ->default(0),
                    ]),

                    Grid::make(2)->schema([
                        Toggle::make('is_active')
                            ->label(__('filament-onboarding::onboarding.resource.fields.is_active'))
                            ->default(true),

                        Toggle::make('is_dismissible')
                            ->label(__('filament-onboarding::onboarding.resource.fields.is_dismissible'))
                            ->helperText(__('filament-onboarding::onboarding.resource.fields.is_dismissible_helper'))
                            ->default(true),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->label(__('filament-onboarding::onboarding.resource.fields.title'))
                    ->state(fn (OnboardingFlow $record): ?string => $record->translate('title'))
                    ->description(fn (OnboardingFlow $record): string => $record->key)
                    ->searchable(['key'])
                    ->sortable(),

                TextColumn::make('panel_id')
                    ->label(__('filament-onboarding::onboarding.resource.fields.panel'))
                    ->badge()
                    ->placeholder(__('filament-onboarding::onboarding.resource.all_panels')),

                TextColumn::make('steps_count')
                    ->label(__('filament-onboarding::onboarding.resource.fields.steps'))
                    ->counts('steps')
                    ->badge(),

                IconColumn::make('is_active')
                    ->label(__('filament-onboarding::onboarding.resource.fields.is_active'))
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label(__('filament-onboarding::onboarding.resource.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

    public static function getRelations(): array
    {
        return [
            StepsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListOnboardingFlows::route('/'),
            'create' => CreateOnboardingFlow::route('/create'),
            'edit'   => EditOnboardingFlow::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected static function panelOptions(): array
    {
        return collect(Filament::getPanels())
            ->mapWithKeys(fn ($panel): array => [$panel->getId() => $panel->getId()])
            ->all();
    }

    protected static function localeLabel(string $locale): string
    {
        return \Illuminate\Support\Str::upper(str_replace('_', '-', $locale));
    }
}
