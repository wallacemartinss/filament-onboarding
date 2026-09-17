<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Note;

/**
 * A resource whose form is shaped like the ones worth touring.
 *
 * Two things about it are deliberate. It asks who is rendering it, the way a
 * plugin field or a label that leans on the record does — and `getLivewire()`
 * is typed, so a form asked to build itself against nothing gets a TypeError
 * rather than a null. And it nests a field under a state path, the way any
 * `Group`, `Section` or wizard step with a state path of its own does, so the
 * name a stop stores and the path the markup wears are not the same string.
 */
class NoteResource extends Resource
{
    protected static ?string $model = Note::class;

    public static function form(Schema $schema): Schema
    {
        $renderedBy = class_basename($schema->getLivewire());

        return $schema->components([
            TextInput::make('title')
                ->label("Title on {$renderedBy}"),

            Group::make()
                ->statePath('details')
                ->schema([
                    TextInput::make('subtitle'),
                ]),
        ]);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'create' => CreateNote::route('/create'),
        ];
    }
}
