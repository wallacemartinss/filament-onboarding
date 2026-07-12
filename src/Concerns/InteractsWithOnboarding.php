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

    /**
     * A surface showing one journey — the launcher, the widget — dismisses that
     * one. A surface showing several — the progress page — says which.
     */
    public function dismissFlow(?string $flowKey = null): void
    {
        $flow = $this->resolveFlowState($flowKey);

        if ($flow === null) {
            return;
        }

        $this->onboarding()?->dismiss($flow->flow);

        $this->afterOnboardingChanged();
    }

    public function restoreFlow(?string $flowKey = null): void
    {
        $flow = $this->resolveFlowState($flowKey);

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
        $step = $this->findStepState($stepKey);

        if (!$step instanceof StepState || !$step->hasTour()) {
            return;
        }

        $this->onboarding()?->markSeen($stepKey);

        $this->dispatch('onboarding-tour-start', key: $stepKey, steps: $step->tour());
    }

    /**
     * The runner reports each stop as the subject reaches it, so a tour left
     * half-way shows as half-way instead of as untouched.
     */
    public function tourProgress(string $key, int $index, int $total): void
    {
        $this->onboarding()?->recordTourProgress($key, $index, $total);

        $this->afterOnboardingChanged();
    }

    /**
     * Open the image or the video a step carries. The modal lives with the
     * runner, hanging off the body, so it opens over any page of the panel.
     */
    public function openMedia(string $stepKey): void
    {
        $step = $this->findStepState($stepKey);

        $media = $step?->media();

        if ($media === null) {
            return;
        }

        $this->dispatch('onboarding-media-open', key: $stepKey, media: [
            ...$media,
            'title'    => $step->title(),
            'watched'  => $step->videoProgress()['seconds'] ?? 0,
            'complete' => $step->isCompleted(),
        ]);
    }

    /**
     * The player reports what has been watched, which is what lets a step be
     * completed by watching — and a half-watched video read as half-watched.
     */
    public function videoProgress(string $key, float $seconds, float $duration): void
    {
        $this->onboarding()?->recordVideoProgress($key, $seconds, $duration);

        $this->afterOnboardingChanged();
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

    protected function resolveFlowState(?string $flowKey): ?FlowState
    {
        if ($flowKey === null) {
            return $this->flowState();
        }

        return $this->onboarding()?->flow($flowKey, $this->onboardingPanelId());
    }

    /**
     * A step by key, looked for across every journey of the panel — the progress
     * page shows more than one at a time.
     */
    protected function findStepState(string $stepKey): ?StepState
    {
        $onboarding = $this->onboarding();

        if (!$onboarding instanceof SubjectOnboarding) {
            return null;
        }

        foreach ($onboarding->flows($this->onboardingPanelId()) as $flow) {
            $step = $flow->step($stepKey);

            if ($step instanceof StepState) {
                return $step;
            }
        }

        return null;
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
