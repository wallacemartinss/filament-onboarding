<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Enums;

use Filament\Support\Contracts\{HasColor, HasIcon, HasLabel};

enum StepType: string implements HasColor, HasIcon, HasLabel
{
    case Task = 'task';

    case Tour = 'tour';

    public function getLabel(): string
    {
        return __("filament-onboarding::onboarding.enums.step_type.{$this->value}");
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Task => 'primary',
            self::Tour => 'info',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Task => 'heroicon-o-check-circle',
            self::Tour => 'heroicon-o-sparkles',
        };
    }
}
