<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Wallacemartinss\FilamentOnboarding\Enums\CompletionMode;
use Wallacemartinss\FilamentOnboarding\Events\{FlowCompleted, StepCompleted};
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingFlow, OnboardingFlowProgress, OnboardingStep, OnboardingStepProgress};
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
     * @return Collection<int, FlowState>
     */
    public function flows(?string $panelId = null): Collection
    {
        $flows = $this->manager->flows($panelId);

        $this->syncConditions($flows, $panelId);

        return $flows
            ->map(fn (OnboardingFlow $flow): FlowState => $this->state($flow))
            ->values();
    }

    public function flow(string $key, ?string $panelId = null): ?FlowState
    {
        return $this->flows($panelId)->firstWhere(fn (FlowState $state): bool => $state->flow->key === $key);
    }

    /**
     * The flow to put in front of the subject: the first one still unfinished
     * and not dismissed, falling back to the first that is merely unfinished.
     */
    public function currentFlow(?string $panelId = null): ?FlowState
    {
        $flows = $this->flows($panelId);

        return $flows->first(fn (FlowState $state): bool => !$state->isDismissed() && !$state->isCompleted())
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
     * Complete every step waiting on the subject reaching this URL.
     */
    public function handleVisit(string $path, ?string $panelId = null): void
    {
        foreach ($this->manager->flows($panelId) as $flow) {
            foreach ($flow->steps as $step) {
                if ($step->completion_mode !== CompletionMode::Visit) {
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
            ->map(fn (OnboardingStep $step): StepState => new StepState(
                step: $step,
                progress: $this->stepProgress()->get($step->getKey()),
                urlParameters: $parameters,
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

    protected function flowProgressQuery(): \Illuminate\Database\Eloquent\Builder
    {
        /** @var class-string<OnboardingFlowProgress> $model */
        $model = $this->manager->flowProgressModel();

        return $model::query()->where($this->subjectKeys());
    }

    protected function stepProgressQuery(): \Illuminate\Database\Eloquent\Builder
    {
        /** @var class-string<OnboardingStepProgress> $model */
        $model = $this->manager->stepProgressModel();

        return $model::query()->where($this->subjectKeys());
    }

    /**
     * @return array<string, string|null>
     */
    protected function subjectKeys(): array
    {
        return [
            'subject_type' => $this->subject->getMorphClass(),
            'subject_id'   => (string) $this->subject->getKey(),
            'scope_type'   => $this->scope?->getMorphClass(),
            'scope_id'     => $this->scope !== null ? (string) $this->scope->getKey() : null,
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
