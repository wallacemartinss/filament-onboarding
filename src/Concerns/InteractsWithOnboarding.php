<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Concerns;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Attributes\Locked;
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
     * "Show me later" lives in the session: it is an answer about *now*, and the
     * next time they come back is a new now.
     */
    protected const LATER_KEY = 'filament-onboarding.welcome.later';

    /**
     * Pin the component to one flow. Left null, it follows whichever flow the
     * subject is currently walking.
     */
    public ?string $flowKey = null;

    /**
     * The tenant and the panel this surface was rendered for.
     *
     * Progress belongs to a subject *within a scope*: the same user onboards
     * separately in each tenant. The scope is resolved from the panel — and on a
     * Livewire update, the panel is not necessarily there to ask. This surface is
     * a plain Livewire component (it hangs off the layout of every page, not off
     * a Filament page), so a lost tenant does not throw: the resolver quietly
     * answers null, and the write lands in a *different row* than the one the
     * page read. The tick vanishes on the next page load, and every tenant that
     * user belongs to shares one bucket of progress.
     *
     * So the scope is captured once, when the page is rendered and the panel is
     * unambiguous, and carried on the component from then on. Locked, because a
     * value that decides which tenant's row is written must not be something the
     * browser can send.
     */
    #[Locked]
    public ?string $onboardingScopeType = null;

    #[Locked]
    public ?string $onboardingScopeId = null;

    #[Locked]
    public ?string $onboardingPanel = null;

    /**
     * Livewire calls this on mount for every component using the trait.
     */
    public function mountInteractsWithOnboarding(): void
    {
        $this->onboardingPanel = Filament::getCurrentOrDefaultPanel()?->getId();

        $scope = Onboarding::resolveScope();

        if ($scope instanceof Model) {
            $this->onboardingScopeType = $scope->getMorphClass();
            $this->onboardingScopeId   = (string) $scope->getKey();
        }
    }

    /**
     * Everything below this line is reachable from the browser.
     *
     * These are public methods on a Livewire component, which means they are
     * network endpoints: any authenticated user can call any of them with any
     * key they like. The engine underneath (SubjectOnboarding) is the trusted
     * API the application drives, and it deliberately asks no questions — so
     * asking them is this layer's job.
     *
     * Two questions, on every write:
     *
     *   1. Is this step even the subject's to touch? Resolved through
     *      findStepState(), which sees only the current panel and only what the
     *      subject's visibility conditions allow.
     *   2. Does this step finish this way? A step that answers to a condition
     *      cannot be ticked off by hand, and a video is not watched by saying so.
     *
     * Without these, a crafted `completeStep('has-two-factor')` marks two-factor
     * as done forever — the checklist lies, and any application logic hanging off
     * StepCompleted fires for work nobody did.
     */
    public function completeStep(string $stepKey, ?string $flowKey = null): void
    {
        $step = $this->findStepState($stepKey, $flowKey);

        // Only a step the subject may tick off themselves. Condition, Visit,
        // Video and Programmatic steps are settled by the thing they name.
        if (!$step instanceof StepState || !$step->canSelfComplete()) {
            return;
        }

        $this->onboarding()?->complete($step->step);

        $this->afterOnboardingChanged();
    }

    public function skipStep(string $stepKey, ?string $flowKey = null): void
    {
        $step = $this->findStepState($stepKey, $flowKey);

        if (!$step instanceof StepState || !$step->canSkip()) {
            return;
        }

        $this->onboarding()?->skip($step->step);

        $this->afterOnboardingChanged();
    }

    public function undoStep(string $stepKey, ?string $flowKey = null): void
    {
        $step = $this->findStepState($stepKey, $flowKey);

        // A condition step cannot be taken back: the next render would put it
        // straight back, because the thing it asks about is still true.
        if (!$step instanceof StepState || !$step->canUndo()) {
            return;
        }

        $this->onboarding()?->uncomplete($step->step);

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
     * Walk the journey again from the start.
     *
     * Everything the subject did by hand — ticks, skips, tours watched, videos
     * watched — is cleared. Steps that hang off a condition are a different
     * matter: they answer to the application, not to this button, so a step that
     * is true again the moment it is asked (a backup destination that still
     * exists) comes straight back completed. That is the honest behaviour, and
     * the UI says as much.
     */
    public function restartFlow(?string $flowKey = null): void
    {
        $flow = $this->resolveFlowState($flowKey);

        if ($flow === null) {
            return;
        }

        $this->onboarding()?->reset($flow->flow);

        $this->afterOnboardingChanged();
    }

    /**
     * Hand a tour to the browser. The runner takes it from here — navigating
     * first if the tour starts on another page.
     */
    public function startTour(string $stepKey, ?string $flowKey = null): void
    {
        $step = $this->findStepState($stepKey, $flowKey);

        if (!$step instanceof StepState || !$step->hasTour()) {
            return;
        }

        $this->onboarding()?->markSeen($step->step);

        $this->dispatch('onboarding-tour-start', key: $stepKey, steps: $step->tour());
    }

    /**
     * The runner reports each stop as the subject reaches it, so a tour left
     * half-way shows as half-way instead of as untouched.
     */
    public function tourProgress(string $key, int $index, int $total): void
    {
        $step = $this->findStepState($key);

        if (!$step instanceof StepState || !$step->hasTour()) {
            return;
        }

        $this->onboarding()?->recordTourProgress($step->step, $index, $total);

        $this->afterOnboardingChanged();
    }

    /**
     * Open the image or the video a step carries. The modal lives with the
     * runner, hanging off the body, so it opens over any page of the panel.
     */
    public function openMedia(string $stepKey, ?string $flowKey = null): void
    {
        $step = $this->findStepState($stepKey, $flowKey);

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
        $step = $this->findStepState($key);

        // Watch time is only meaningful for a step that carries a video. What it
        // cannot do is prove the video was watched — the browser is the one
        // counting, and the browser belongs to the subject. A step whose
        // completion actually matters should hang off a condition, not a video.
        if (!$step instanceof StepState || !$step->hasVideo()) {
            return;
        }

        $this->onboarding()?->recordVideoProgress($step->step, $seconds, $duration);

        $this->afterOnboardingChanged();
    }

    /**
     * The browser reached the end of a tour.
     *
     * That finishes the step only when the step finishes by hand: for a Manual
     * step, the tour *is* the task. A step that answers to a condition, a
     * visit, a video or the application does not — its mode names the thing
     * that completes it, and clicking "next" until the end is not that thing.
     * Otherwise a tour glued to "has two-factor" is a second front door around
     * the very check completeStep() refuses.
     */
    public function finishTour(string $stepKey, ?string $flowKey = null): void
    {
        $step = $this->findStepState($stepKey, $flowKey);

        // Only a step that actually has a tour finishes by finishing one.
        if (!$step instanceof StepState || !$step->hasTour()) {
            return;
        }

        if (!$step->step->completion_mode->isSelfServed()) {
            $this->onboarding()?->markSeen($step->step);

            $this->afterOnboardingChanged();

            return;
        }

        $this->onboarding()?->complete($step->step, ['completed_by' => 'tour']);

        $this->afterOnboardingChanged();
    }

    /**
     * The welcome screen: the three answers a person can give it.
     *
     * "Later" is a session flag, not a row: it means "not now", and the next time
     * they log in is a new now. "Never" is a row, because a promise not to show
     * something again cannot expire when the cookie does — and it takes the
     * floating button with it, ring and all.
     */
    public function startOnboarding(): void
    {
        $this->onboarding()?->markWelcomed();

        $this->afterOnboardingChanged();
    }

    public function remindMeLater(): void
    {
        session()->put(static::LATER_KEY, true);

        $this->afterOnboardingChanged();
    }

    public function neverShowOnboarding(): void
    {
        $this->onboarding()?->hide();

        $this->afterOnboardingChanged();
    }

    /**
     * Give it back to somebody who turned it off — from the progress page, which
     * stays in the menu precisely so there is a way back.
     */
    public function showOnboardingAgain(): void
    {
        $this->onboarding()?->show();

        session()->forget(static::LATER_KEY);

        $this->afterOnboardingChanged();
    }

    public function isOnboardingHidden(): bool
    {
        return $this->onboarding()?->isHidden() ?? false;
    }

    /**
     * Whether the welcome screen is due: there is something to welcome them to,
     * they have not been welcomed before, they have not asked to be left alone —
     * and they have not said "later" in this session.
     */
    public function shouldWelcome(): bool
    {
        $onboarding = $this->onboarding();

        if ($onboarding === null || $onboarding->isHidden() || $onboarding->hasBeenWelcomed()) {
            return false;
        }

        if (session()->get(static::LATER_KEY, false)) {
            return false;
        }

        return $this->flowState() instanceof FlowState;
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
     * The step a caller means, not merely the first one wearing the key.
     *
     * Step keys are only unique *within* a flow — the database says so — and a
     * panel may hold two journeys that both have an "invite-team". A caller
     * that names the flow is answered from that flow alone. One that does not
     * is assumed to mean the flow this surface is showing, and only a step
     * that is nowhere on it is looked for across the panel — which is how the
     * events the tour runner sends (key only) still land.
     */
    protected function findStepState(string $stepKey, ?string $flowKey = null): ?StepState
    {
        if ($flowKey !== null) {
            return $this->onboarding()?->flow($flowKey, $this->onboardingPanelId())?->step($stepKey);
        }

        $shown = $this->flowState()?->step($stepKey);

        if ($shown instanceof StepState) {
            return $shown;
        }

        return $this->onboarding()?->stepState($stepKey, $this->onboardingPanelId());
    }

    protected function onboarding(): ?SubjectOnboarding
    {
        $subject = Onboarding::resolveSubject();

        if (!$subject instanceof Model) {
            return null;
        }

        return Onboarding::for($subject, $this->onboardingScope());
    }

    /**
     * The tenant this surface belongs to: the one captured at mount, if there was
     * one, and only otherwise whatever the panel says now.
     */
    protected function onboardingScope(): ?Model
    {
        if (blank($this->onboardingScopeType) || blank($this->onboardingScopeId)) {
            return Onboarding::resolveScope();
        }

        /** @var class-string<Model>|null $model */
        $model = Relation::getMorphedModel($this->onboardingScopeType) ?? $this->onboardingScopeType;

        if (!is_string($model) || !class_exists($model)) {
            return Onboarding::resolveScope();
        }

        return $model::query()->find($this->onboardingScopeId);
    }

    protected function onboardingPanelId(): ?string
    {
        return $this->onboardingPanel ?? Filament::getCurrentOrDefaultPanel()?->getId();
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
