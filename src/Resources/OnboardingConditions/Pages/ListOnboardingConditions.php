<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingConditions\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Wallacemartinss\FilamentOnboarding\Resources\OnboardingConditions\OnboardingConditionResource;

class ListOnboardingConditions extends ListRecords
{
    protected static string $resource = OnboardingConditionResource::class;

    public function getSubheading(): ?string
    {
        return __('filament-onboarding::onboarding.conditions.subheading');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
