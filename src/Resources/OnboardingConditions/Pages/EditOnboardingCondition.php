<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Resources\OnboardingConditions\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Wallacemartinss\FilamentOnboarding\Resources\OnboardingConditions\OnboardingConditionResource;

class EditOnboardingCondition extends EditRecord
{
    protected static string $resource = OnboardingConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->icon(Heroicon::OutlinedTrash),
        ];
    }
}
