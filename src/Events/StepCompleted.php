<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingStep, OnboardingStepProgress};

class StepCompleted
{
    use Dispatchable;

    public function __construct(
        public readonly OnboardingStep $step,
        public readonly OnboardingStepProgress $progress,
        public readonly Model $subject,
        public readonly ?Model $scope = null,
    ) {
    }
}
