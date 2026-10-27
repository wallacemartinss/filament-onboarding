<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Note;

/**
 * A resource that creates in a modal (ManageRecords): no create page, and a
 * "New" button that is an action rather than a link.
 */
class TagResource extends Resource
{
    protected static ?string $model = Note::class;

    protected static ?string $slug = 'tags';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('label')->label('Label'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ManageTags::route('/'),
        ];
    }
}
