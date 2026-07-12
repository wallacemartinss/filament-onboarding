<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\Pages;

use Filament\Resources\Pages\CreateRecord;
use Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\OnboardingFlowResource;

class CreateOnboardingFlow extends CreateRecord
{
    protected static string $resource = OnboardingFlowResource::class;

    protected function getRedirectUrl(): string
    {
        // Straight to the edit page, where the steps live.
        return static::getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
