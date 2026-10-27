<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

/**
 * A settings screen, the kind every panel has and no resource covers: a
 * custom page holding more than one form. The second one is named, so its
 * fields render under `connectionForm.` rather than `form.`.
 */
class StoreSettings extends Page
{
    protected static ?string $slug = 'store-settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    /** @var array<string, mixed>|null */
    public ?array $connection = [];

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            TextInput::make('store_name')->label('Store name'),
        ]);
    }

    public function connectionForm(Schema $schema): Schema
    {
        return $schema->statePath('connection')->components([
            TextInput::make('client_id')->label('Client ID'),
        ]);
    }
}
