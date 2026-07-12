<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Fixtures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasUuids;

    protected $table = 'subjects';

    protected $guarded = [];
}
