<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphTo};
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;

/**
 * @property string $id
 * @property string $flow_id
 * @property string $step_id
 * @property string $subject_type
 * @property string $subject_id
 * @property string|null $scope_type
 * @property string|null $scope_id
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $skipped_at
 * @property \Illuminate\Support\Carbon|null $seen_at
 * @property array<string, mixed>|null $meta
 */
class OnboardingStepProgress extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function getTable(): string
    {
        return config('filament-onboarding.tables.step_progress', 'onboarding_step_progress');
    }

    /**
     * @return BelongsTo<OnboardingStep, $this>
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(Onboarding::stepModel(), 'step_id');
    }

    /**
     * @return BelongsTo<OnboardingFlow, $this>
     */
    public function flow(): BelongsTo
    {
        return $this->belongsTo(Onboarding::flowModel(), 'flow_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function scope(): MorphTo
    {
        return $this->morphTo();
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isSkipped(): bool
    {
        return $this->skipped_at !== null;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'skipped_at'   => 'datetime',
            'seen_at'      => 'datetime',
            'meta'         => 'array',
        ];
    }
}
