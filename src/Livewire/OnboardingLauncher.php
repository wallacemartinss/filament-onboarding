<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Wallacemartinss\FilamentOnboarding\Concerns\InteractsWithOnboarding;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\FilamentOnboardingPlugin;
use Wallacemartinss\FilamentOnboarding\States\FlowState;

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

    /**
     * "Let's go": the welcome is done with, and they are taken to the journey —
     * the progress page if the panel has one, the checklist otherwise.
     */
    public function beginOnboarding(): void
    {
        $this->startOnboarding();

        if ($this->progressPageUrl() === null) {
            $this->isOpen = true;
        }
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

    /**
     * Switch the panel to another journey.
     */
    public function selectFlow(string $flowKey): void
    {
        $this->flowKey = $flowKey;
    }

    public function render(): View
    {
        $plugin = $this->plugin();

        // Every journey still on the table, not only the one being walked. The
        // checklist shows one at a time, and a finished journey used to sit in
        // front of an unfinished one with no way past it.
        $flows = ($this->onboarding()?->flows($this->onboardingPanelId()) ?? collect())
            ->filter(fn (FlowState $flow): bool => !$flow->isDismissed())
            ->values();

        // Somebody who turned onboarding off gets no button, no ring, and no
        // welcome. Tours and the media modal stay wired: they are opened by hand
        // — from the progress page, from a "view the tutorial" button — and
        // turning the checklist off is not the same as never wanting help again.
        $hidden = $this->isOnboardingHidden();

        return view('filament-onboarding::livewire.launcher', [
            'flow'        => $this->flowState(),
            'flows'       => $flows,
            'hasLauncher' => ($plugin?->hasLauncher() ?? true) && !$hidden,
            'hasTours'    => $plugin?->hasTours() ?? true,
            'position'    => $plugin?->getLauncherPosition() ?? 'bottom-right',
            'welcome'     => ($plugin?->hasWelcome() ?? false) && $this->shouldWelcome(),
            'progressUrl' => $this->progressPageUrl(),
        ]);
    }

    /**
     * Where "let's go" leads: the page that lays the journey out, when the panel
     * has one. Without it, the welcome screen simply opens the checklist.
     */
    protected function progressPageUrl(): ?string
    {
        $plugin = $this->plugin();

        if (!$plugin?->hasProgressPage()) {
            return null;
        }

        try {
            return \Wallacemartinss\FilamentOnboarding\Pages\OnboardingProgress::getUrl();
        } catch (\Throwable) {
            return null;
        }
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
