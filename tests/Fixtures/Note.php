<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Fixtures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Something a subject can have some of — a stand-in for the clients, invoices and
 * servers a real application counts when it asks "have they done the thing yet?".
 */
class Note extends Model
{
    use HasUuids;

    protected $table = 'notes';

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }
}
