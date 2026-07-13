<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Conditions;

use Illuminate\Database\Eloquent\Model;
use Wallacemartinss\FilamentOnboarding\Contracts\{HasConditionLabel, OnboardingCondition};

/**
 * The shape `make:onboarding-condition` generates: a class, nowhere registered,
 * that the package finds on its own.
 */
class HasSomethingCondition implements HasConditionLabel, OnboardingCondition
{
    public static function label(): string
    {
        return 'Has something';
    }

    public function isCompleted(Model $subject, ?Model $scope = null): bool
    {
        return $subject->getAttribute('verified_at') !== null;
    }
}
