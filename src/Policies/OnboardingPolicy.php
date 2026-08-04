<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * The answer the panel models gave before there was a policy: yes.
 *
 * These policies exist so that a panel running `->strictAuthorization()` has
 * something to find — without one, Filament throws the moment the resource is
 * looked at, and rightly so: strict mode's whole promise is that no model is
 * reachable on a shrug.
 *
 * Saying yes to everything is deliberately not an opinion. Who may write
 * journeys is the application's call, and the package cannot guess it — so the
 * default preserves exactly the access the resources had before policies
 * existed, and yields the moment the application speaks up: register your own
 * with `Gate::policy()` (it wins, being registered later), name it in
 * `filament-onboarding.policies`, or extend one of these and override only the
 * abilities you mean to narrow.
 *
 * Every ability Filament probes is answered, because in strict mode a policy
 * that exists but lacks the method throws just the same.
 */
abstract class OnboardingPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return true;
    }

    public function view(Authenticatable $user, Model $record): bool
    {
        return true;
    }

    public function create(Authenticatable $user): bool
    {
        return true;
    }

    public function update(Authenticatable $user, Model $record): bool
    {
        return true;
    }

    public function delete(Authenticatable $user, Model $record): bool
    {
        return true;
    }

    public function deleteAny(Authenticatable $user): bool
    {
        return true;
    }

    public function forceDelete(Authenticatable $user, Model $record): bool
    {
        return true;
    }

    public function forceDeleteAny(Authenticatable $user): bool
    {
        return true;
    }

    public function restore(Authenticatable $user, Model $record): bool
    {
        return true;
    }

    public function restoreAny(Authenticatable $user): bool
    {
        return true;
    }

    public function reorder(Authenticatable $user): bool
    {
        return true;
    }

    public function replicate(Authenticatable $user, Model $record): bool
    {
        return true;
    }
}
