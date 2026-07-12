<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows;

use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Facades\Filament;
use Filament\Forms\Components\{Select, TextInput, Textarea, Toggle};
use Filament\Resources\Resource;
use Filament\Schemas\Components\{Grid, Group, Section};
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Filters\TernaryFilter;
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
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        // What the subject reads, in every locale — the bulk of the
                        // work, so it takes the bulk of the screen.
                        Section::make(__('filament-onboarding::onboarding.resource.sections.content'))
                            ->description(__('filament-onboarding::onboarding.resource.sections.content_description'))
                            ->icon('heroicon-o-language')
                            ->columnSpan(2)
                            ->schema([
                                Tabs::make('translations')
                                    ->contained(false)
                                    ->tabs(
                                        collect(Onboarding::locales())
                                            ->map(fn (string $locale): Tab => Tab::make($locale)
                                                ->label(static::localeLabel($locale))
                                                ->schema([
                                                    TextInput::make("title.{$locale}")
                                                        ->label(__('filament-onboarding::onboarding.resource.fields.title'))
                                                        ->placeholder(__('filament-onboarding::onboarding.resource.placeholders.flow_title'))
                                                        ->required($locale === Onboarding::locales()[0])
                                                        ->maxLength(255),

                                                    Textarea::make("description.{$locale}")
                                                        ->label(__('filament-onboarding::onboarding.resource.fields.description'))
                                                        ->placeholder(__('filament-onboarding::onboarding.resource.placeholders.flow_description'))
                                                        ->rows(2)
                                                        ->maxLength(500),
                                                ]))
                                            ->all()
                                    ),
                            ]),

                        Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make(__('filament-onboarding::onboarding.resource.sections.publishing'))
                                    ->icon('heroicon-o-rocket-launch')
                                    ->schema([
                                        TextInput::make('key')
                                            ->label(__('filament-onboarding::onboarding.resource.fields.key'))
                                            ->helperText(__('filament-onboarding::onboarding.resource.fields.key_helper'))
                                            ->placeholder('getting-started')
                                            ->required()
                                            ->alphaDash()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),

                                        Select::make('panel_id')
                                            ->label(__('filament-onboarding::onboarding.resource.fields.panel'))
                                            ->helperText(__('filament-onboarding::onboarding.resource.fields.panel_helper'))
                                            ->options(static::panelOptions())
                                            ->placeholder(__('filament-onboarding::onboarding.resource.all_panels'))
                                            ->searchable()
                                            ->native(false),

                                        TextInput::make('sort_order')
                                            ->label(__('filament-onboarding::onboarding.resource.fields.sort_order'))
                                            ->helperText(__('filament-onboarding::onboarding.resource.fields.sort_order_helper'))
                                            ->numeric()
                                            ->default(0),

                                        Toggle::make('is_active')
                                            ->label(__('filament-onboarding::onboarding.resource.fields.is_active'))
                                            ->helperText(__('filament-onboarding::onboarding.resource.fields.is_active_helper'))
                                            ->default(true),

                                        Toggle::make('is_dismissible')
                                            ->label(__('filament-onboarding::onboarding.resource.fields.is_dismissible'))
                                            ->helperText(__('filament-onboarding::onboarding.resource.fields.is_dismissible_helper'))
                                            ->default(true),
                                    ]),

                                Section::make(__('filament-onboarding::onboarding.resource.sections.appearance'))
                                    ->icon('heroicon-o-paint-brush')
                                    ->schema([
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
                                            ->selectablePlaceholder(false)
                                            ->native(false),
                                    ]),
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
                    ->icon(fn (OnboardingFlow $record): ?string => $record->icon)
                    ->iconColor(fn (OnboardingFlow $record): string => $record->color ?: 'primary')
                    ->weight(FontWeight::Medium)
                    ->searchable(['key'])
                    ->sortable(),

                TextColumn::make('steps_count')
                    ->label(__('filament-onboarding::onboarding.resource.fields.steps'))
                    ->counts('steps')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'primary' : 'danger')
                    ->tooltip(fn (int $state): ?string => $state === 0
                        ? __('filament-onboarding::onboarding.resource.no_steps_warning')
                        : null)
                    ->alignCenter(),

                TextColumn::make('panel_id')
                    ->label(__('filament-onboarding::onboarding.resource.fields.panel'))
                    ->badge()
                    ->color('gray')
                    ->placeholder(__('filament-onboarding::onboarding.resource.all_panels')),

                IconColumn::make('is_active')
                    ->label(__('filament-onboarding::onboarding.resource.fields.is_active'))
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('updated_at')
                    ->label(__('filament-onboarding::onboarding.resource.fields.updated_at'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('filament-onboarding::onboarding.resource.fields.is_active')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('filament-onboarding::onboarding.resource.empty.heading'))
            ->emptyStateDescription(__('filament-onboarding::onboarding.resource.empty.description'))
            ->emptyStateIcon('heroicon-o-map');
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
