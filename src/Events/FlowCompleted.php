<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingFlow, OnboardingFlowProgress};

class FlowCompleted
{
    use Dispatchable;

    public function __construct(
        public readonly OnboardingFlow $flow,
        public readonly OnboardingFlowProgress $progress,
        public readonly Model $subject,
        public readonly ?Model $scope = null,
    ) {
    }
}
