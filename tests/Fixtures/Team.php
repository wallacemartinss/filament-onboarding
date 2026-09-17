<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Fixtures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Something for a panel to be multi-tenant about.
 */
class Team extends Model
{
    use HasUuids;

    protected $guarded = [];
}
