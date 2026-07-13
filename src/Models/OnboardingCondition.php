<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Models;

use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Wallacemartinss\FilamentOnboarding\Concerns\HasTranslatableColumns;
use Wallacemartinss\FilamentOnboarding\Enums\ConditionType;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;

/**
 * A condition written in the panel rather than in code.
 *
 * @property string $id
 * @property string $key
 * @property array<string, string> $label
 * @property array<string, string>|null $description
 * @property ConditionType $type
 * @property class-string<Model>|null $model
 * @property string|null $subject_column
 * @property string|null $scope_column
 * @property array<int, array<string, mixed>>|null $filters
 * @property int $minimum
 * @property bool $is_active
 */
class OnboardingCondition extends Model
{
    use HasTranslatableColumns;
    use HasUuids;

    protected $guarded = [];

    /** @var array<int, string> */
    public array $translatable = ['label', 'description'];

    public function getTable(): string
    {
        return config('filament-onboarding.tables.conditions', 'onboarding_conditions');
    }

    protected static function booted(): void
    {
        static::saved(fn () => Onboarding::flushCache());
        static::deleted(fn () => Onboarding::flushCache());
    }

    /**
     * @param  Builder<$this>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type'        => ConditionType::class,
            'label'       => 'array',
            'description' => 'array',
            'filters'     => 'array',
            'minimum'     => 'integer',
            'is_active'   => 'boolean',
        ];
    }
}
