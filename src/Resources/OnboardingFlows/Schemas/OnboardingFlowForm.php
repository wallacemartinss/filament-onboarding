<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\{Select, TextInput, Textarea, Toggle};
use Filament\Schemas\Components\{Section, Tabs};
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Support\IconInput;

/**
 * The journey itself: what it says, where it shows up, and how it looks.
 *
 * Sections are stacked full width rather than squeezed into a sidebar — the
 * copy is the work here, and it needs the room to be read while it is written.
 */
class OnboardingFlowForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                self::contentSection(),
                self::publishingSection(),
                self::appearanceSection(),
            ]);
    }

    protected static function contentSection(): Section
    {
        return Section::make(__('filament-onboarding::onboarding.resource.sections.content'))
            ->icon(Heroicon::OutlinedLanguage)
            ->description(__('filament-onboarding::onboarding.resource.sections.content_description'))
            ->schema([
                Tabs::make('translations')
                    ->contained(false)
                    ->columnSpanFull()
                    ->tabs(
                        collect(Onboarding::locales())
                            ->map(fn (string $locale): Tab => Tab::make($locale)
                                ->label(self::localeLabel($locale))
                                ->schema([
                                    TextInput::make("title.{$locale}")
                                        ->label(__('filament-onboarding::onboarding.resource.fields.title'))
                                        ->prefixIcon(Heroicon::OutlinedFlag)
                                        ->required($locale === Onboarding::locales()[0])
                                        ->maxLength(255)
                                        ->placeholder(__('filament-onboarding::onboarding.resource.placeholders.flow_title')),

                                    Textarea::make("description.{$locale}")
                                        ->label(__('filament-onboarding::onboarding.resource.fields.description'))
                                        ->rows(3)
                                        ->maxLength(500)
                                        ->placeholder(__('filament-onboarding::onboarding.resource.placeholders.flow_description')),
                                ]))
                            ->all()
                    ),
            ]);
    }

    protected static function publishingSection(): Section
    {
        return Section::make(__('filament-onboarding::onboarding.resource.sections.publishing'))
            ->icon(Heroicon::OutlinedRocketLaunch)
            ->description(__('filament-onboarding::onboarding.resource.sections.publishing_description'))
            ->columns(2)
            ->schema([
                TextInput::make('key')
                    ->label(__('filament-onboarding::onboarding.resource.fields.key'))
                    ->prefixIcon(Heroicon::OutlinedKey)
                    ->required()
                    ->alphaDash()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('getting-started')
                    ->helperText(__('filament-onboarding::onboarding.resource.fields.key_helper')),

                Select::make('panel_id')
                    ->label(__('filament-onboarding::onboarding.resource.fields.panel'))
                    ->prefixIcon(Heroicon::OutlinedWindow)
                    ->options(self::panelOptions())
                    ->searchable()
                    ->native(false)
                    ->placeholder(__('filament-onboarding::onboarding.resource.all_panels'))
                    ->helperText(__('filament-onboarding::onboarding.resource.fields.panel_helper')),

                Select::make('visibility_condition')
                    ->label(__('filament-onboarding::onboarding.resource.fields.visibility'))
                    ->prefixIcon(Heroicon::OutlinedFunnel)
                    ->options(fn (): array => Onboarding::conditions()->options())
                    ->searchable()
                    ->native(false)
                    ->placeholder(__('filament-onboarding::onboarding.resource.fields.visibility_everyone'))
                    ->helperText(__('filament-onboarding::onboarding.resource.fields.visibility_helper')),

                TextInput::make('sort_order')
                    ->label(__('filament-onboarding::onboarding.resource.fields.sort_order'))
                    ->prefixIcon(Heroicon::OutlinedBars3BottomLeft)
                    ->numeric()
                    ->default(0)
                    ->helperText(__('filament-onboarding::onboarding.resource.fields.sort_order_helper')),

                Toggle::make('is_active')
                    ->label(__('filament-onboarding::onboarding.resource.fields.is_active'))
                    ->default(true)
                    ->helperText(__('filament-onboarding::onboarding.resource.fields.is_active_helper')),

                Toggle::make('is_dismissible')
                    ->label(__('filament-onboarding::onboarding.resource.fields.is_dismissible'))
                    ->default(true)
                    ->helperText(__('filament-onboarding::onboarding.resource.fields.is_dismissible_helper')),
            ]);
    }

    protected static function appearanceSection(): Section
    {
        return Section::make(__('filament-onboarding::onboarding.resource.sections.appearance'))
            ->icon(Heroicon::OutlinedPaintBrush)
            ->description(__('filament-onboarding::onboarding.resource.sections.appearance_description'))
            ->columns(2)
            ->schema([
                IconInput::make('icon')
                    ->label(__('filament-onboarding::onboarding.resource.fields.icon')),

                Select::make('color')
                    ->label(__('filament-onboarding::onboarding.resource.fields.color'))
                    ->prefixIcon(Heroicon::OutlinedSwatch)
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
            ]);
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
        return Str::upper(str_replace('_', '-', $locale));
    }
}
