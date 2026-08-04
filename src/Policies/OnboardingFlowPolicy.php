<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Policies;

/**
 * Who may write journeys. Everyone who can reach the panel, until the
 * application says otherwise — see OnboardingPolicy for how to say otherwise.
 */
class OnboardingFlowPolicy extends OnboardingPolicy
{
}
