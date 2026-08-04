<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Fixtures;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Authenticatable because Filament's authorization asks Gate::forUser() about
 * whoever is signed in — a subject that cannot sign in cannot be asked.
 */
class Subject extends Model implements AuthenticatableContract
{
    use Authenticatable;
    use HasUuids;

    protected $table = 'subjects';

    protected $guarded = [];
}
