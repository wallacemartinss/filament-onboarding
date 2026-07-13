<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingConditions;

use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\FilamentOnboardingPlugin;
use Wallacemartinss\FilamentOnboarding\Resources\OnboardingConditions\Pages\{CreateOnboardingCondition, EditOnboardingCondition, ListOnboardingConditions};
use Wallacemartinss\FilamentOnboarding\Resources\OnboardingConditions\Schemas\OnboardingConditionForm;
use Wallacemartinss\FilamentOnboarding\Resources\OnboardingConditions\Tables\OnboardingConditionsTable;

/**
 * Conditions, written where the journeys are written.
 *
 * A step that completes itself is the most valuable kind there is — and it used
 * to be the one kind that could not be authored: somebody had to open an editor,
 * write a closure, and ship a deploy. For a journey that is supposed to change
 * whenever product changes its mind, that is a strange place to need a commit.
 */
class OnboardingConditionResource extends Resource
{
    public static function getModel(): string
    {
        return Onboarding::conditionModel();
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
        $sort = static::plugin()?->getNavigationSort()
            ?? config('filament-onboarding.resource.navigation_sort');

        // Sits just under the journeys, since it is what they are made of.
        return $sort === null ? null : $sort + 1;
    }

    public static function getModelLabel(): string
    {
        return __('filament-onboarding::onboarding.conditions.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-onboarding::onboarding.conditions.plural');
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'onboarding-conditions';
    }

    public static function getRecordTitleAttribute(): ?string
    {
        return 'key';
    }

    public static function form(Schema $schema): Schema
    {
        return OnboardingConditionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OnboardingConditionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListOnboardingConditions::route('/'),
            'create' => CreateOnboardingCondition::route('/create'),
            'edit'   => EditOnboardingCondition::route('/{record}/edit'),
        ];
    }

    protected static function plugin(): ?FilamentOnboardingPlugin
    {
        try {
            return FilamentOnboardingPlugin::get();
        } catch (\Throwable) {
            return null;
        }
    }
}
