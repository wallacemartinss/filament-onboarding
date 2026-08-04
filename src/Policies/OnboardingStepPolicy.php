<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Policies;

/**
 * Who may write the steps of a journey. The steps live inside the flow resource
 * as a relation manager, and a relation manager authorizes against the related
 * model — so strict mode asks about steps in their own right.
 */
class OnboardingStepPolicy extends OnboardingPolicy
{
}
