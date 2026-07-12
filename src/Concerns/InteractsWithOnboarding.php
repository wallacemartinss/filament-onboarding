<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Concerns;

use Filament\Facades\Filament;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\States\{FlowState, StepState};
use Wallacemartinss\FilamentOnboarding\SubjectOnboarding;

/**
 * The behaviour shared by everything that renders a checklist: the launcher, the
 * dashboard widget, and any component the application writes itself.
 */
trait InteractsWithOnboarding
{
    /**
     * Pin the component to one flow. Left null, it follows whichever flow the
     * subject is currently walking.
     */
    public ?string $flowKey = null;

    public function completeStep(string $stepKey): void
    {
        $this->onboarding()?->complete($stepKey);

        $this->afterOnboardingChanged();
    }

    public function skipStep(string $stepKey): void
    {
        $this->onboarding()?->skip($stepKey);

        $this->afterOnboardingChanged();
    }

    public function undoStep(string $stepKey): void
    {
        $this->onboarding()?->uncomplete($stepKey);

        $this->afterOnboardingChanged();
    }

    public function dismissFlow(): void
    {
        $flow = $this->flowState();

        if ($flow === null) {
            return;
        }

        $this->onboarding()?->dismiss($flow->flow);

        $this->afterOnboardingChanged();
    }

    public function restoreFlow(): void
    {
        $flow = $this->flowState();

        if ($flow === null) {
            return;
        }

        $this->onboarding()?->restore($flow->flow);

        $this->afterOnboardingChanged();
    }

    /**
     * Hand a tour to the browser. The runner takes it from here — navigating
     * first if the tour starts on another page.
     */
    public function startTour(string $stepKey): void
    {
        $step = $this->flowState()?->step($stepKey);

        if (!$step instanceof StepState || !$step->hasTour()) {
            return;
        }

        $this->onboarding()?->markSeen($stepKey);

        $this->dispatch('onboarding-tour-start', key: $stepKey, steps: $step->tour());
    }

    /**
     * The browser reached the end of a tour, so the step it belongs to is done.
     */
    public function finishTour(string $stepKey): void
    {
        $this->onboarding()?->complete($stepKey, ['completed_by' => 'tour']);

        $this->afterOnboardingChanged();
    }

    public function flowState(): ?FlowState
    {
        $onboarding = $this->onboarding();

        if (!$onboarding instanceof SubjectOnboarding) {
            return null;
        }

        $panelId = $this->onboardingPanelId();

        return $this->flowKey !== null
            ? $onboarding->flow($this->flowKey, $panelId)
            : $onboarding->currentFlow($panelId);
    }

    protected function onboarding(): ?SubjectOnboarding
    {
        return Onboarding::current();
    }

    protected function onboardingPanelId(): ?string
    {
        return Filament::getCurrentOrDefaultPanel()?->getId();
    }

    /**
     * Every surface showing onboarding refreshes together, so ticking a step off
     * in the launcher updates the dashboard widget behind it.
     */
    protected function afterOnboardingChanged(): void
    {
        $this->dispatch('onboarding-updated');
    }
}
