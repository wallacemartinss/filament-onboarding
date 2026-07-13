<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingConditions\Pages;

use Filament\Resources\Pages\CreateRecord;
use Wallacemartinss\FilamentOnboarding\Resources\OnboardingConditions\OnboardingConditionResource;

class CreateOnboardingCondition extends CreateRecord
{
    protected static string $resource = OnboardingConditionResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
