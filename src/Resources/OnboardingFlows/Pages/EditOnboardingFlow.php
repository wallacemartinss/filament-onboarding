<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\OnboardingFlowResource;

class EditOnboardingFlow extends EditRecord
{
    protected static string $resource = OnboardingFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
