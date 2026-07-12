<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows;

use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\FilamentOnboardingPlugin;
use Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\Pages\{CreateOnboardingFlow, EditOnboardingFlow, ListOnboardingFlows, ViewOnboardingFlow};
use Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\RelationManagers\StepsRelationManager;
use Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\Schemas\{OnboardingFlowForm, OnboardingFlowInfolist};
use Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\Tables\OnboardingFlowsTable;

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

    public static function getSlug(?Panel $panel = null): string
    {
        return 'onboarding-flows';
    }

    public static function getRecordTitleAttribute(): ?string
    {
        return 'key';
    }

    public static function form(Schema $schema): Schema
    {
        return OnboardingFlowForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OnboardingFlowInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OnboardingFlowsTable::configure($table);
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
            'view'   => ViewOnboardingFlow::route('/{record}'),
            'edit'   => EditOnboardingFlow::route('/{record}/edit'),
        ];
    }
}
