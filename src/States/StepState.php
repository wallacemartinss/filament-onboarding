<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\States;

use Illuminate\Support\Carbon;
use Wallacemartinss\FilamentOnboarding\Enums\{CompletionMode, MediaType, StepType};
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingStep, OnboardingStepProgress};

/**
 * A step, told from the point of view of the subject looking at it.
 */
class StepState
{
    /**
     * @param  array<string, mixed>  $urlParameters
     * @param  (\Closure(?string): bool)|null  $visibilityResolver  Answers whether a guarded tour stop is for this subject.
     */
    public function __construct(
        public readonly OnboardingStep $step,
        public readonly ?OnboardingStepProgress $progress = null,
        public readonly array $urlParameters = [],
        public readonly ?\Closure $visibilityResolver = null,
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
     * The tour, with any stop the subject is not entitled to left out.
     *
     * A stop that points at a feature the plan does not include would spotlight
     * a control that is not on the screen — so it is not handed to the browser
     * at all, and the tour reads as if it never existed.
     *
     * @return array<int, array<string, string|null>>
     */
    public function tour(): array
    {
        $stops = $this->step->resolveTourSteps($this->urlParameters);

        if ($this->visibilityResolver === null) {
            return $stops;
        }

        return array_values(array_filter(
            $stops,
            fn (array $stop): bool => ($this->visibilityResolver)($stop['condition'] ?? null),
        ));
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

    public function completedAt(): ?Carbon
    {
        return $this->progress?->completed_at;
    }

    /**
     * Whether the subject can go through this step again without undoing it —
     * watch the tour once more, replay the video, open the page.
     *
     * Finishing a step is not the same as being done with it: people come back to
     * the two-factor explanation months later.
     */
    public function canReplay(): bool
    {
        return $this->isResolved() && ($this->hasTour() || $this->hasVideo() || $this->hasImage() || filled($this->url()));
    }

    /**
     * Whether the subject can take a completed step back.
     *
     * A step that answers to a condition cannot: unticking it would be undone by
     * the next render, because the thing it asks about is still true.
     */
    public function canUndo(): bool
    {
        return $this->isResolved()
            && $this->step->completion_mode !== CompletionMode::Condition;
    }

    /**
     * @return array{type: string, source: string, url: string|null, provider: string|null, video_id: string|null, caption: string|null, position: string, threshold: int, trackable: bool}|null
     */
    public function media(): ?array
    {
        return $this->step->resolveMedia();
    }

    public function hasImage(): bool
    {
        return $this->step->media_type === MediaType::Image && $this->media() !== null;
    }

    public function hasVideo(): bool
    {
        return $this->step->media_type === MediaType::Video && $this->media() !== null;
    }

    /**
     * The image itself, for a step that carries one — shown inline on the card
     * and opened in the modal when clicked.
     */
    public function imageUrl(): ?string
    {
        return $this->hasImage() ? $this->media()['url'] : null;
    }

    /**
     * How much of the video the subject has watched.
     *
     * @return array{seconds: float, duration: float, percent: int}|null
     */
    public function videoProgress(): ?array
    {
        $duration = (float) ($this->progress?->meta['video_duration'] ?? 0);

        if (!$this->hasVideo() || $duration <= 0) {
            return null;
        }

        return [
            'seconds'  => (float) ($this->progress?->meta['video_seconds'] ?? 0),
            'duration' => $duration,
            'percent'  => (int) ($this->progress?->meta['video_percent'] ?? 0),
        ];
    }

    /**
     * How far the subject got into this step, as a percentage.
     *
     * A task is all or nothing. A tour and a video are not: a tour has stops and
     * a video has minutes, both are reported as they happen, and a step left
     * half-way says so.
     */
    public function percentage(): int
    {
        if ($this->isCompleted()) {
            return 100;
        }

        $video = $this->videoProgress();

        if ($video !== null) {
            return $video['percent'];
        }

        $total = (int) ($this->progress?->meta['tour_total'] ?? 0);

        if (!$this->hasTour() || $total < 1) {
            return 0;
        }

        $reached = (int) ($this->progress?->meta['tour_index'] ?? 0);

        return (int) round(($reached / $total) * 100);
    }

    /**
     * "2 / 5" — the stop the subject reached, for a tour they have started.
     *
     * @return array{reached: int, total: int}|null
     */
    public function tourProgress(): ?array
    {
        $total = (int) ($this->progress?->meta['tour_total'] ?? 0);

        if (!$this->hasTour() || $total < 1 || $this->isCompleted()) {
            return null;
        }

        return [
            'reached' => (int) ($this->progress?->meta['tour_index'] ?? 0),
            'total'   => $total,
        ];
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
