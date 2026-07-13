<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding;

use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Support\Collection;
use Wallacemartinss\FilamentOnboarding\Enums\CompletionMode;
use Wallacemartinss\FilamentOnboarding\Events\{FlowCompleted, StepCompleted};
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingFlow, OnboardingFlowProgress, OnboardingPreference, OnboardingStep, OnboardingStepProgress};
use Wallacemartinss\FilamentOnboarding\States\{FlowState, StepState};

/**
 * Onboarding as one subject sees it: the flows, their steps, and this subject's
 * progress through them.
 */
class SubjectOnboarding
{
    /** @var Collection<string, OnboardingFlowProgress>|null */
    protected ?Collection $flowProgress = null;

    /** @var Collection<string, OnboardingStepProgress>|null */
    protected ?Collection $stepProgress = null;

    /** @var array<string, bool> */
    protected array $syncedPanels = [];

    protected ?OnboardingPreference $preference = null;

    public function __construct(
        protected OnboardingManager $manager,
        protected Model $subject,
        protected ?Model $scope = null,
    ) {
    }

    public function subject(): Model
    {
        return $this->subject;
    }

    public function scope(): ?Model
    {
        return $this->scope;
    }

    /**
     * The journeys this subject can actually walk.
     *
     * A flow whose every step was hidden by a visibility condition is left out
     * too: it would show up as a card at 0% that can never be finished, which is
     * worse than not being there at all.
     *
     * @return Collection<int, FlowState>
     */
    public function flows(?string $panelId = null): Collection
    {
        $flows = $this->manager->flows($panelId)
            ->filter(fn (OnboardingFlow $flow): bool => $this->isVisible($flow->visibility_condition));

        $this->syncConditions($flows, $panelId);

        return $flows
            ->map(fn (OnboardingFlow $flow): FlowState => $this->state($flow))
            ->filter(fn (FlowState $state): bool => $state->total() > 0)
            ->values();
    }

    /**
     * Whether something guarded by a visibility condition is for this subject.
     *
     * No condition means "for everybody". A condition that is not registered
     * hides what it guards: if the application cannot answer "is Docker included
     * in this plan?", teaching Docker is the wrong default.
     */
    public function isVisible(?string $conditionKey): bool
    {
        if (blank($conditionKey)) {
            return true;
        }

        if (!$this->manager->conditions()->has($conditionKey)) {
            return false;
        }

        return $this->manager->conditions()->passes($conditionKey, $this->subject, $this->scope);
    }

    public function flow(string $key, ?string $panelId = null): ?FlowState
    {
        return $this->flows($panelId)->firstWhere(fn (FlowState $state): bool => $state->flow->key === $key);
    }

    /**
     * A step by key, looked for across every journey of the panel — a caller
     * holding a key rarely knows (or cares) which flow it lives in.
     */
    public function stepState(string $stepKey, ?string $panelId = null): ?StepState
    {
        foreach ($this->flows($panelId) as $flow) {
            $step = $flow->step($stepKey);

            if ($step instanceof StepState) {
                return $step;
            }
        }

        return null;
    }

    /**
     * The flow to put in front of the subject: the first one still unfinished
     * and not dismissed, falling back to the first that is merely unfinished.
     */
    public function currentFlow(?string $panelId = null): ?FlowState
    {
        $flows = $this->flows($panelId);

        // "Unfinished" means anything still pending — optional steps included.
        // A journey whose required work is done but whose tour has not been
        // taken is still the one to put in front of the subject.
        return $flows->first(fn (FlowState $state): bool => !$state->isDismissed() && !$state->isFinished())
            ?? $flows->first(fn (FlowState $state): bool => !$state->isDismissed());
    }

    public function complete(OnboardingStep|string $step, array $meta = []): ?OnboardingStepProgress
    {
        $step = $this->resolveStep($step);

        if (!$step instanceof OnboardingStep) {
            return null;
        }

        $progress = $this->progressFor($step);

        if ($progress->completed_at !== null) {
            return $progress;
        }

        $progress->forceFill([
            'completed_at' => now(),
            'skipped_at'   => null,
            'meta'         => filled($meta) ? [...($progress->meta ?? []), ...$meta] : $progress->meta,
        ])->save();

        $this->stepProgress?->put($step->getKey(), $progress);

        StepCompleted::dispatch($step, $progress, $this->subject, $this->scope);

        $this->refreshFlowCompletion($step->flow_id);

        return $progress;
    }

    public function skip(OnboardingStep|string $step): ?OnboardingStepProgress
    {
        $step = $this->resolveStep($step);

        if (!$step instanceof OnboardingStep || $step->is_required) {
            return null;
        }

        $progress = $this->progressFor($step);

        $progress->forceFill(['skipped_at' => now()])->save();

        $this->stepProgress?->put($step->getKey(), $progress);

        $this->refreshFlowCompletion($step->flow_id);

        return $progress;
    }

    /**
     * Record that the subject watched the tour attached to a step.
     */
    public function markSeen(OnboardingStep|string $step): ?OnboardingStepProgress
    {
        $step = $this->resolveStep($step);

        if (!$step instanceof OnboardingStep) {
            return null;
        }

        $progress = $this->progressFor($step);

        $progress->forceFill(['seen_at' => now()])->save();

        $this->stepProgress?->put($step->getKey(), $progress);

        return $progress;
    }

    /**
     * How far into a tour the subject got. Stored on the step's progress, so a
     * tour abandoned at stop 2 of 5 reads as 40% instead of as untouched.
     */
    public function recordTourProgress(OnboardingStep|string $step, int $index, int $total): ?OnboardingStepProgress
    {
        $step = $this->resolveStep($step);

        if (!$step instanceof OnboardingStep || $total < 1) {
            return null;
        }

        $progress = $this->progressFor($step);

        $reached = max((int) ($progress->meta['tour_index'] ?? 0), $index);

        $progress->forceFill([
            'seen_at' => $progress->seen_at ?? now(),
            'meta'    => [
                ...($progress->meta ?? []),
                'tour_index' => min($reached, $total - 1),
                'tour_total' => $total,
            ],
        ])->save();

        $this->stepProgress?->put($step->getKey(), $progress);

        return $progress;
    }

    /**
     * How much of a step's video the subject has watched.
     *
     * The furthest point reached is what is kept — skipping back to rewatch a bit
     * does not undo the ground already covered — and a video watched past its
     * threshold completes the step when that is how the step is meant to finish.
     */
    public function recordVideoProgress(
        OnboardingStep|string $step,
        float $seconds,
        float $duration,
    ): ?OnboardingStepProgress {
        $step = $this->resolveStep($step);

        if (!$step instanceof OnboardingStep || $duration <= 0) {
            return null;
        }

        $progress = $this->progressFor($step);

        $watched = max((float) ($progress->meta['video_seconds'] ?? 0), min($seconds, $duration));
        $percent = (int) round(($watched / $duration) * 100);

        $progress->forceFill([
            'seen_at' => $progress->seen_at ?? now(),
            'meta'    => [
                ...($progress->meta ?? []),
                'video_seconds'  => round($watched, 1),
                'video_duration' => round($duration, 1),
                'video_percent'  => min($percent, 100),
            ],
        ])->save();

        $this->stepProgress?->put($step->getKey(), $progress);

        if (
            $step->completion_mode === CompletionMode::Video
            && $percent >= (int) $step->video_completion_threshold
            && $progress->completed_at === null
        ) {
            return $this->complete($step, ['completed_by' => 'video']);
        }

        return $progress;
    }

    public function uncomplete(OnboardingStep|string $step): void
    {
        $step = $this->resolveStep($step);

        if (!$step instanceof OnboardingStep) {
            return;
        }

        $progress = $this->progressFor($step);

        $progress->forceFill(['completed_at' => null, 'skipped_at' => null])->save();

        $this->stepProgress?->put($step->getKey(), $progress);

        $this->flowProgressFor($step->flow_id)->forceFill(['completed_at' => null])->save();
    }

    public function dismiss(OnboardingFlow|string $flow): ?OnboardingFlowProgress
    {
        $flow = $this->resolveFlow($flow);

        if (!$flow instanceof OnboardingFlow || !$flow->is_dismissible) {
            return null;
        }

        $progress = $this->flowProgressFor($flow->getKey());

        $progress->forceFill(['dismissed_at' => now()])->save();

        $this->flowProgress?->put($flow->getKey(), $progress);

        return $progress;
    }

    public function restore(OnboardingFlow|string $flow): ?OnboardingFlowProgress
    {
        $flow = $this->resolveFlow($flow);

        if (!$flow instanceof OnboardingFlow) {
            return null;
        }

        $progress = $this->flowProgressFor($flow->getKey());

        $progress->forceFill(['dismissed_at' => null])->save();

        $this->flowProgress?->put($flow->getKey(), $progress);

        return $progress;
    }

    /**
     * Wipe this subject's progress through a flow, so it starts over.
     */
    public function reset(OnboardingFlow|string $flow): void
    {
        $flow = $this->resolveFlow($flow);

        if (!$flow instanceof OnboardingFlow) {
            return;
        }

        $this->stepProgressQuery()->where('flow_id', $flow->getKey())->delete();
        $this->flowProgressQuery()->where('flow_id', $flow->getKey())->delete();

        $this->flowProgress = null;
        $this->stepProgress = null;
        $this->syncedPanels = [];
    }

    /**
     * How many steps of a flow this subject has any progress on — what a reset
     * would actually wipe.
     */
    public function progressCount(OnboardingFlow|string $flow): int
    {
        $flow = $this->resolveFlow($flow);

        if (!$flow instanceof OnboardingFlow) {
            return 0;
        }

        return $this->stepProgressQuery()->where('flow_id', $flow->getKey())->count();
    }

    /**
     * Whether this subject wants to see onboarding at all.
     *
     * This is not the same as having finished it, and not the same as dismissing
     * a journey. It is the person saying "stop showing me this" — and when they
     * say it, everything goes: the welcome screen, the floating button, the ring
     * with the percentage. A checklist that keeps hovering over somebody who
     * asked it to leave is not onboarding, it is nagging.
     *
     * It is theirs to take back (`show()`), which is why the progress page stays
     * reachable from the menu.
     */
    public function isHidden(): bool
    {
        return $this->preference()->hidden_at !== null;
    }

    public function hide(): void
    {
        $this->preference()->forceFill([
            'hidden_at'   => now(),
            'welcomed_at' => now(),
        ])->save();
    }

    public function show(): void
    {
        $this->preference()->forceFill(['hidden_at' => null])->save();
    }

    /**
     * Whether the subject has already been through the welcome screen.
     *
     * Said once, and never again — "later" is a different answer, and it does not
     * live here (it lives in the session, because it means "not now" rather than
     * "not ever").
     */
    public function hasBeenWelcomed(): bool
    {
        return $this->preference()->welcomed_at !== null;
    }

    public function markWelcomed(): void
    {
        if ($this->hasBeenWelcomed()) {
            return;
        }

        $this->preference()->forceFill(['welcomed_at' => now()])->save();
    }

    public function preference(): OnboardingPreference
    {
        if ($this->preference instanceof OnboardingPreference) {
            return $this->preference;
        }

        /** @var class-string<OnboardingPreference> $model */
        $model = $this->manager->preferenceModel();

        return $this->preference = $model::query()->firstOrCreate($this->subjectKeys());
    }

    /**
     * Complete every step waiting on the subject reaching this URL.
     */
    public function handleVisit(string $path, ?string $panelId = null): void
    {
        foreach ($this->manager->flows($panelId) as $flow) {
            // A flow the subject cannot see is not a flow they can make
            // progress in — not even by standing on the right page.
            if (!$this->isVisible($flow->visibility_condition)) {
                continue;
            }

            foreach ($flow->steps as $step) {
                if ($step->completion_mode !== CompletionMode::Visit) {
                    continue;
                }

                // A step the subject cannot see is not a step they can finish,
                // even by walking onto the page it names.
                if (!$this->isVisible($step->visibility_condition)) {
                    continue;
                }

                if ($this->isCompleted($step)) {
                    continue;
                }

                if ($step->matchesVisit($path)) {
                    $this->complete($step);
                }
            }
        }
    }

    public function isCompleted(OnboardingStep $step): bool
    {
        return $this->stepProgress()->get($step->getKey())?->completed_at !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function urlParameters(): array
    {
        return $this->manager->urlParameters();
    }

    /**
     * Steps completed by a condition catch up on their own, which is what lets a
     * flow published today show an account created last year as already ahead.
     *
     * @param  Collection<int, OnboardingFlow>  $flows
     */
    protected function syncConditions(Collection $flows, ?string $panelId): void
    {
        $cacheKey = $panelId ?? '*';

        if ($this->syncedPanels[$cacheKey] ?? false) {
            return;
        }

        $this->syncedPanels[$cacheKey] = true;

        foreach ($flows as $flow) {
            foreach ($flow->steps as $step) {
                if ($step->completion_mode !== CompletionMode::Condition || blank($step->condition_key)) {
                    continue;
                }

                if (!$this->isVisible($step->visibility_condition)) {
                    continue;
                }

                if ($this->isCompleted($step)) {
                    continue;
                }

                if ($this->manager->conditions()->passes($step->condition_key, $this->subject, $this->scope)) {
                    $this->complete($step, ['completed_by' => 'condition']);
                }
            }
        }
    }

    protected function state(OnboardingFlow $flow): FlowState
    {
        $parameters = $this->urlParameters();

        $steps = $flow->steps
            ->filter(fn (OnboardingStep $step): bool => $this->isVisible($step->visibility_condition))
            ->map(fn (OnboardingStep $step): StepState => new StepState(
                step: $step,
                progress: $this->stepProgress()->get($step->getKey()),
                urlParameters: $parameters,
                // A tour stop can be guarded too: a stop pointing at a Docker card
                // must not exist for a plan without Docker.
                visibilityResolver: fn (?string $key): bool => $this->isVisible($key),
            ))
            ->values();

        return new FlowState(
            flow: $flow,
            steps: $steps,
            progress: $this->flowProgress()->get($flow->getKey()),
        );
    }

    protected function refreshFlowCompletion(string $flowId): void
    {
        $flow = $this->manager->flows()->firstWhere('id', $flowId);

        if (!$flow instanceof OnboardingFlow) {
            return;
        }

        $state = $this->state($flow);

        $progress = $this->flowProgressFor($flowId);

        if ($state->isCompleted() && $progress->completed_at === null) {
            $progress->forceFill(['completed_at' => now()])->save();

            $this->flowProgress?->put($flowId, $progress);

            FlowCompleted::dispatch($flow, $progress, $this->subject, $this->scope);

            return;
        }

        if (!$state->isCompleted() && $progress->completed_at !== null) {
            $progress->forceFill(['completed_at' => null])->save();

            $this->flowProgress?->put($flowId, $progress);
        }
    }

    protected function progressFor(OnboardingStep $step): OnboardingStepProgress
    {
        $existing = $this->stepProgress()->get($step->getKey());

        if ($existing instanceof OnboardingStepProgress) {
            return $existing;
        }

        /** @var class-string<OnboardingStepProgress> $model */
        $model = $this->manager->stepProgressModel();

        $progress = $model::query()->firstOrCreate([
            ...$this->subjectKeys(),
            'step_id' => $step->getKey(),
        ], [
            'flow_id' => $step->flow_id,
        ]);

        $this->stepProgress()->put($step->getKey(), $progress);

        return $progress;
    }

    protected function flowProgressFor(string $flowId): OnboardingFlowProgress
    {
        $existing = $this->flowProgress()->get($flowId);

        if ($existing instanceof OnboardingFlowProgress) {
            return $existing;
        }

        /** @var class-string<OnboardingFlowProgress> $model */
        $model = $this->manager->flowProgressModel();

        $progress = $model::query()->firstOrCreate([
            ...$this->subjectKeys(),
            'flow_id' => $flowId,
        ], [
            'started_at' => now(),
        ]);

        $this->flowProgress()->put($flowId, $progress);

        return $progress;
    }

    /**
     * @return Collection<string, OnboardingFlowProgress>
     */
    protected function flowProgress(): Collection
    {
        return $this->flowProgress ??= $this->flowProgressQuery()->get()->keyBy('flow_id');
    }

    /**
     * @return Collection<string, OnboardingStepProgress>
     */
    protected function stepProgress(): Collection
    {
        return $this->stepProgress ??= $this->stepProgressQuery()->get()->keyBy('step_id');
    }

    protected function flowProgressQuery(): Builder
    {
        /** @var class-string<OnboardingFlowProgress> $model */
        $model = $this->manager->flowProgressModel();

        return $model::query()->where($this->subjectKeys());
    }

    protected function stepProgressQuery(): Builder
    {
        /** @var class-string<OnboardingStepProgress> $model */
        $model = $this->manager->stepProgressModel();

        return $model::query()->where($this->subjectKeys());
    }

    /**
     * The row this subject's progress lives in.
     *
     * "No scope" is an empty string, not a null. The unique index on progress
     * contains these columns, and a NULL is not equal to another NULL — an index
     * holding one enforces nothing, which left `firstOrCreate` free to write the
     * same row twice under any application that does not use tenants.
     *
     * @return array<string, string>
     */
    protected function subjectKeys(): array
    {
        return [
            'subject_type' => $this->subject->getMorphClass(),
            'subject_id'   => (string) $this->subject->getKey(),
            'scope_type'   => $this->scope?->getMorphClass() ?? '',
            'scope_id'     => $this->scope !== null ? (string) $this->scope->getKey() : '',
        ];
    }

    protected function resolveStep(OnboardingStep|string $step): ?OnboardingStep
    {
        if ($step instanceof OnboardingStep) {
            return $step;
        }

        foreach ($this->manager->flows() as $flow) {
            $match = $flow->steps->firstWhere('key', $step);

            if ($match instanceof OnboardingStep) {
                return $match;
            }
        }

        return null;
    }

    protected function resolveFlow(OnboardingFlow|string $flow): ?OnboardingFlow
    {
        return $flow instanceof OnboardingFlow
            ? $flow
            : $this->manager->flow($flow);
    }
}
