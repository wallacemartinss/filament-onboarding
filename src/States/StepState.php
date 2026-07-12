<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\States;

use Wallacemartinss\FilamentOnboarding\Enums\{CompletionMode, StepType};
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingStep, OnboardingStepProgress};

/**
 * A step, told from the point of view of the subject looking at it.
 */
class StepState
{
    /**
     * @param  array<string, mixed>  $urlParameters
     */
    public function __construct(
        public readonly OnboardingStep $step,
        public readonly ?OnboardingStepProgress $progress = null,
        public readonly array $urlParameters = [],
    ) {
    }

    public function key(): string
    {
        return $this->step->key;
    }

    public function type(): StepType
    {
        return $this->step->type;
    }

    public function title(): ?string
    {
        return $this->step->translate('title');
    }

    public function description(): ?string
    {
        return $this->step->translate('description');
    }

    public function ctaLabel(): ?string
    {
        return $this->step->translate('cta_label');
    }

    public function icon(): ?string
    {
        return $this->step->icon;
    }

    public function url(): ?string
    {
        return $this->step->resolveUrl($this->urlParameters);
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    public function tour(): array
    {
        return $this->step->resolveTourSteps($this->urlParameters);
    }

    public function hasTour(): bool
    {
        return $this->step->type === StepType::Tour && filled($this->tour());
    }

    public function isCompleted(): bool
    {
        return $this->progress?->completed_at !== null;
    }

    public function isSkipped(): bool
    {
        return !$this->isCompleted() && $this->progress?->skipped_at !== null;
    }

    public function isSeen(): bool
    {
        return $this->progress?->seen_at !== null;
    }

    /**
     * Settled one way or another — done, or deliberately passed over.
     */
    public function isResolved(): bool
    {
        return $this->isCompleted() || $this->isSkipped();
    }

    public function isPending(): bool
    {
        return !$this->isResolved();
    }

    public function isRequired(): bool
    {
        return $this->step->is_required;
    }

    public function canSkip(): bool
    {
        return !$this->step->is_required && $this->isPending();
    }

    /**
     * Whether the subject may tick this step off themselves.
     */
    public function canSelfComplete(): bool
    {
        return $this->step->completion_mode->isSelfServed() && $this->isPending();
    }

    /**
     * Steps waiting on a condition cannot be ticked off — the application says
     * when they are done — so the checklist offers a link instead of a checkbox.
     */
    public function isAwaitingCondition(): bool
    {
        return $this->step->completion_mode === CompletionMode::Condition && $this->isPending();
    }

    public function hasAction(): bool
    {
        return $this->hasTour() || filled($this->url());
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key'         => $this->key(),
            'type'        => $this->type()->value,
            'title'       => $this->title(),
            'description' => $this->description(),
            'icon'        => $this->icon(),
            'url'         => $this->url(),
            'cta_label'   => $this->ctaLabel(),
            'tour'        => $this->tour(),
            'completed'   => $this->isCompleted(),
            'skipped'     => $this->isSkipped(),
            'required'    => $this->isRequired(),
        ];
    }
}
