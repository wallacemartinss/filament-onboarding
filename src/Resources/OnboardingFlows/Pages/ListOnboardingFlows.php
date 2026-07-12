<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\OnboardingFlowResource;

class ListOnboardingFlows extends ListRecords
{
    protected static string $resource = OnboardingFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
