<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Policies;

/**
 * Who may write the questions steps hang off. Worth narrowing sooner than the
 * flows: a condition reads real columns of real models, so the audience that
 * may author one is the audience you would hand a read-only report builder to.
 */
class OnboardingConditionPolicy extends OnboardingPolicy
{
}
