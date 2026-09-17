<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Schema;

/**
 * The page a panel registers through ->tenantProfile(), which is the whole
 * point: it is not in ->pages(), so getPages() never lists it.
 */
class EditTeamProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Team profile';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name'),
        ]);
    }
}
