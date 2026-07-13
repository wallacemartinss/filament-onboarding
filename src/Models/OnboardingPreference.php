<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * What one subject decided about onboarding: whether to be shown it at all, and
 * whether they have already been welcomed.
 *
 * Not cached, for the same reason progress is not: it is per subject, it changes
 * when they click, and a stale answer here means showing somebody a welcome
 * screen they told you to stop showing.
 */
class OnboardingPreference extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function getTable(): string
    {
        return config('filament-onboarding.tables.preferences', 'onboarding_preferences');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hidden_at'   => 'datetime',
            'welcomed_at' => 'datetime',
        ];
    }
}
