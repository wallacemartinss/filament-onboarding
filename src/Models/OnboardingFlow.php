<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Models;

use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\{HasMany, HasManyThrough};
use Wallacemartinss\FilamentOnboarding\Concerns\HasTranslatableColumns;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;

/**
 * @property string $id
 * @property string $key
 * @property string|null $panel_id
 * @property array<string, string> $title
 * @property array<string, string>|null $description
 * @property string|null $icon
 * @property string|null $color
 * @property string|null $visibility_condition
 * @property bool $is_active
 * @property bool $is_dismissible
 * @property int $sort_order
 */
class OnboardingFlow extends Model
{
    use HasTranslatableColumns;
    use HasUuids;

    protected $guarded = [];

    /** @var array<int, string> */
    public array $translatable = ['title', 'description'];

    public function getTable(): string
    {
        return config('filament-onboarding.tables.flows', 'onboarding_flows');
    }

    protected static function booted(): void
    {
        static::saved(fn () => Onboarding::flushCache());
        static::deleted(fn () => Onboarding::flushCache());
    }

    /**
     * @return HasMany<OnboardingStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(Onboarding::stepModel(), 'flow_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<OnboardingFlowProgress, $this>
     */
    public function progress(): HasMany
    {
        return $this->hasMany(Onboarding::flowProgressModel(), 'flow_id');
    }

    /**
     * @return HasManyThrough<OnboardingStepProgress, OnboardingStep, $this>
     */
    public function stepProgress(): HasManyThrough
    {
        return $this->hasManyThrough(Onboarding::stepProgressModel(), Onboarding::stepModel(), 'flow_id', 'step_id');
    }

    /**
     * @param  Builder<$this>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Flows without a panel belong to every panel.
     *
     * @param  Builder<$this>  $query
     */
    public function scopeForPanel(Builder $query, ?string $panelId): void
    {
        $query->where(function (Builder $query) use ($panelId): void {
            $query->whereNull('panel_id');

            if ($panelId !== null) {
                $query->orWhere('panel_id', $panelId);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'title'          => 'array',
            'description'    => 'array',
            'is_active'      => 'boolean',
            'is_dismissible' => 'boolean',
            'sort_order'     => 'integer',
        ];
    }
}
