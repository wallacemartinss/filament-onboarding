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
 * @property string $subject_type
 * @property string $subject_id
 * @property string|null $scope_type
 * @property string|null $scope_id
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $dismissed_at
 * @property array<string, mixed>|null $meta
 */
class OnboardingFlowProgress extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function getTable(): string
    {
        return config('filament-onboarding.tables.flow_progress', 'onboarding_flow_progress');
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

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at'   => 'datetime',
            'completed_at' => 'datetime',
            'dismissed_at' => 'datetime',
            'meta'         => 'array',
        ];
    }
}
