<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\Pages;

use Filament\Actions\{DeleteAction, EditAction};
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\OnboardingFlowResource;

class ViewOnboardingFlow extends ViewRecord
{
    protected static string $resource = OnboardingFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon(Heroicon::OutlinedPencilSquare),

            DeleteAction::make()
                ->icon(Heroicon::OutlinedTrash),
        ];
    }
}
