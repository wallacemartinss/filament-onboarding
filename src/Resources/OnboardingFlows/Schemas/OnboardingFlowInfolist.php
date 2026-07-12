<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\Schemas;

use Filament\Infolists\Components\{IconEntry, TextEntry};
use Filament\Schemas\Components\{Grid, Section, Tabs};
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Models\OnboardingFlow;

/**
 * The journey at a glance: whether it reaches anybody, what it says, and how it
 * is wired. The steps themselves are the relation manager below.
 */
class OnboardingFlowInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('filament-onboarding::onboarding.resource.sections.overview'))
                    ->icon(Heroicon::OutlinedChartBar)
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('steps_count')
                                ->label(__('filament-onboarding::onboarding.resource.fields.steps'))
                                ->icon(Heroicon::OutlinedListBullet)
                                ->state(fn (OnboardingFlow $record): int => $record->steps()->count())
                                ->size('lg')
                                ->weight('bold')
                                ->helperText(fn (OnboardingFlow $record): ?string => $record->steps()->count() === 0
                                    ? __('filament-onboarding::onboarding.resource.no_steps_warning')
                                    : null),

                            TextEntry::make('panel_id')
                                ->label(__('filament-onboarding::onboarding.resource.fields.panel'))
                                ->badge()
                                ->color('gray')
                                ->placeholder(__('filament-onboarding::onboarding.resource.all_panels')),

                            IconEntry::make('is_active')
                                ->label(__('filament-onboarding::onboarding.resource.fields.is_active'))
                                ->boolean(),

                            IconEntry::make('is_dismissible')
                                ->label(__('filament-onboarding::onboarding.resource.fields.is_dismissible'))
                                ->boolean(),
                        ]),
                    ]),

                Section::make(__('filament-onboarding::onboarding.resource.sections.content'))
                    ->icon(Heroicon::OutlinedLanguage)
                    ->schema([
                        Tabs::make('translations')
                            ->contained(false)
                            ->columnSpanFull()
                            ->tabs(
                                collect(Onboarding::locales())
                                    ->map(fn (string $locale): Tab => Tab::make($locale)
                                        ->label(self::localeLabel($locale))
                                        ->schema([
                                            TextEntry::make("title.{$locale}")
                                                ->label(__('filament-onboarding::onboarding.resource.fields.title'))
                                                ->weight('bold')
                                                ->placeholder('—'),

                                            TextEntry::make("description.{$locale}")
                                                ->label(__('filament-onboarding::onboarding.resource.fields.description'))
                                                ->placeholder('—'),
                                        ]))
                                    ->all()
                            ),
                    ]),

                Section::make(__('filament-onboarding::onboarding.resource.sections.publishing'))
                    ->icon(Heroicon::OutlinedRocketLaunch)
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('key')
                                ->label(__('filament-onboarding::onboarding.resource.fields.key'))
                                ->icon(Heroicon::OutlinedKey)
                                ->fontFamily('mono')
                                ->copyable(),

                            TextEntry::make('visibility_condition')
                                ->label(__('filament-onboarding::onboarding.resource.fields.visibility'))
                                ->badge()
                                ->color('warning')
                                ->formatStateUsing(fn (string $state): string => Onboarding::conditions()->options()[$state] ?? $state)
                                ->placeholder(__('filament-onboarding::onboarding.resource.fields.visibility_everyone')),

                            TextEntry::make('sort_order')
                                ->label(__('filament-onboarding::onboarding.resource.fields.sort_order'))
                                ->icon(Heroicon::OutlinedBars3BottomLeft),

                            TextEntry::make('updated_at')
                                ->label(__('filament-onboarding::onboarding.resource.fields.updated_at'))
                                ->icon(Heroicon::OutlinedClock)
                                ->since(),
                        ]),
                    ]),
            ]);
    }

    protected static function localeLabel(string $locale): string
    {
        return Str::upper(str_replace('_', '-', $locale));
    }
}
