<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Fixtures;

use Wallacemartinss\FilamentOnboarding\Concerns\InteractsWithOnboarding;

/**
 * The surface, stripped to what matters: the methods the browser can call.
 *
 * The launcher, the dashboard widget and the progress page are Livewire
 * components, and they get every one of these methods from the same trait — so
 * a public method here is a network endpoint there, callable with any key the
 * caller likes, whatever the interface happens to be showing.
 *
 * Rendering a Livewire component inside the package's bare test kernel is a
 * fight with the harness (Livewire hydrates an error bag the kernel never
 * shares), and it would prove nothing extra: the guards being tested live in the
 * trait, above the engine and below the view. The wire itself is exercised
 * end-to-end in a real application.
 */
class OnboardingEndpoint
{
    use InteractsWithOnboarding;

    /**
     * The surfaces tell each other to refresh through Livewire. Nothing to
     * refresh here.
     */
    protected function afterOnboardingChanged(): void
    {
    }
}
