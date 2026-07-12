<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\States;

use Illuminate\Support\Collection;
use Wallacemartinss\FilamentOnboarding\Enums\CompletionMode;
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingFlow, OnboardingFlowProgress};

/**
 * A flow, told from the point of view of the subject walking it.
 */
class FlowState
{
    /**
     * @param  Collection<int, StepState>  $steps
     */
    public function __construct(
        public readonly OnboardingFlow $flow,
        public readonly Collection $steps,
        public readonly ?OnboardingFlowProgress $progress = null,
    ) {
    }

    public function key(): string
    {
        return $this->flow->key;
    }

    public function title(): ?string
    {
        return $this->flow->translate('title');
    }

    public function description(): ?string
    {
        return $this->flow->translate('description');
    }

    public function icon(): ?string
    {
        return $this->flow->icon;
    }

    public function color(): string
    {
        return $this->flow->color ?: 'primary';
    }

    public function total(): int
    {
        return $this->steps->count();
    }

    public function completedCount(): int
    {
        return $this->steps->filter(fn (StepState $step): bool => $step->isCompleted())->count();
    }

    /**
     * Steps that no longer ask anything of the subject, skipped ones included —
     * this is what the progress bar reflects.
     */
    public function resolvedCount(): int
    {
        return $this->steps->filter(fn (StepState $step): bool => $step->isResolved())->count();
    }

    public function percentage(): int
    {
        if ($this->total() === 0) {
            return 0;
        }

        return (int) round(($this->resolvedCount() / $this->total()) * 100);
    }

    /**
     * A flow is done once every required step is done; optional steps may be
     * left behind.
     */
    public function isCompleted(): bool
    {
        if ($this->total() === 0) {
            return false;
        }

        return $this->steps
            ->filter(fn (StepState $step): bool => $step->isRequired())
            ->every(fn (StepState $step): bool => $step->isCompleted());
    }

    public function isStarted(): bool
    {
        return $this->resolvedCount() > 0;
    }

    /**
     * Whether any step of this flow answers to the application rather than to the
     * subject — which is what makes "start over" only go so far.
     */
    public function hasConditionSteps(): bool
    {
        return $this->steps->contains(
            fn (StepState $step): bool => $step->step->completion_mode === CompletionMode::Condition
        );
    }

    public function isDismissed(): bool
    {
        return $this->progress?->dismissed_at !== null;
    }

    public function isDismissible(): bool
    {
        return $this->flow->is_dismissible;
    }

    /**
     * @return Collection<int, StepState>
     */
    public function pendingSteps(): Collection
    {
        return $this->steps->filter(fn (StepState $step): bool => $step->isPending())->values();
    }

    public function nextStep(): ?StepState
    {
        return $this->pendingSteps()->first();
    }

    public function step(string $key): ?StepState
    {
        return $this->steps->first(fn (StepState $step): bool => $step->key() === $key);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key'         => $this->key(),
            'title'       => $this->title(),
            'description' => $this->description(),
            'icon'        => $this->icon(),
            'color'       => $this->color(),
            'percentage'  => $this->percentage(),
            'completed'   => $this->isCompleted(),
            'dismissed'   => $this->isDismissed(),
            'steps'       => $this->steps->map(fn (StepState $step): array => $step->toArray())->all(),
        ];
    }
}
