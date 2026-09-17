<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Resources;

use Filament\Resources\Pages\CreateRecord;

class CreateNote extends CreateRecord
{
    protected static string $resource = NoteResource::class;
}
