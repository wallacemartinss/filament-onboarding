<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Conditions;

use Illuminate\Database\Eloquent\Model;
use Wallacemartinss\FilamentOnboarding\Contracts\OnboardingCondition;

/**
 * A class that would rather not be named after itself — because the key is what
 * steps in the database point at, and a class can be renamed long after they do.
 */
class NamesItselfCondition implements OnboardingCondition
{
    public static function key(): string
    {
        return 'the_chosen_key';
    }

    public function isCompleted(Model $subject, ?Model $scope = null): bool
    {
        return true;
    }
}
