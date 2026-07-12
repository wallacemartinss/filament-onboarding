<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Wallacemartinss\FilamentOnboarding\Concerns\InteractsWithOnboarding;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\FilamentOnboardingPlugin;

/**
 * Hangs off the body of every panel page: a floating progress button that opens
 * the checklist, plus the runner that drives guided tours.
 */
class OnboardingLauncher extends Component
{
    use InteractsWithOnboarding;

    public bool $isOpen = false;

    public function mount(): void
    {
        // Steps completed by reaching a URL settle here, on the page they name.
        Onboarding::current()?->handleVisit(request()->path(), $this->onboardingPanelId());
    }

    #[On('onboarding-updated')]
    public function refreshOnboarding(): void
    {
        // Re-renders with fresh progress.
    }

    #[On('onboarding-tour-finished')]
    public function onTourFinished(string $key): void
    {
        $this->finishTour($key);
    }

    /**
     * The launcher carries the runner, so it is the one that hears the stops —
     * the widget and the progress page pick the change up from
     * `onboarding-updated`.
     */
    #[On('onboarding-tour-progress')]
    public function onTourProgress(string $key, int $index, int $total): void
    {
        $this->tourProgress($key, $index, $total);
    }

    /**
     * The player lives here too, so this is where watch time lands — whichever
     * surface the subject opened the video from.
     */
    #[On('onboarding-video-progress')]
    public function onVideoProgress(string $key, float $seconds, float $duration): void
    {
        $this->videoProgress($key, $seconds, $duration);
    }

    #[On('onboarding-open')]
    public function open(): void
    {
        $this->isOpen = true;
    }

    public function toggle(): void
    {
        $this->isOpen = !$this->isOpen;
    }

    public function render(): View
    {
        $plugin = $this->plugin();

        return view('filament-onboarding::livewire.launcher', [
            'flow'        => $this->flowState(),
            'hasLauncher' => $plugin?->hasLauncher() ?? true,
            'hasTours'    => $plugin?->hasTours() ?? true,
            'position'    => $plugin?->getLauncherPosition() ?? 'bottom-right',
        ]);
    }

    protected function plugin(): ?FilamentOnboardingPlugin
    {
        try {
            return FilamentOnboardingPlugin::get();
        } catch (\Throwable) {
            return null;
        }
    }
}
