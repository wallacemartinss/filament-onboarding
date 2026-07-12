<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Contracts;

use Illuminate\Database\Eloquent\Model;

interface OnboardingCondition
{
    /**
     * Whether the subject has already done what the step asks for.
     *
     * @param  Model  $subject  Usually the authenticated user.
     * @param  Model|null  $scope  The context the progress belongs to, such as a tenant.
     */
    public function isCompleted(Model $subject, ?Model $scope = null): bool;
}
