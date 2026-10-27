<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Pages;

use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

/**
 * A tenant's settings page whose form reads the tenant while it is built, as
 * a page that fills its options from the current team does. Without a tenant,
 * it cannot be built at all.
 */
class TeamSettings extends Page
{
    protected static ?string $slug = 'team-settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function form(Schema $schema): Schema
    {
        $team = Filament::getTenant() ?? throw new \RuntimeException('No tenant.');

        return $schema->statePath('data')->components([
            TextInput::make('invoice_prefix')->label("Invoice prefix for {$team->getAttribute('name')}"),
        ]);
    }
}
