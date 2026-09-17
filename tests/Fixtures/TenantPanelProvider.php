<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Fixtures;

use Filament\{Panel, PanelProvider};
use Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Pages\EditTeamProfile;

/**
 * A second panel, and the only one here with tenants.
 *
 * It is separate on purpose: giving the default panel a tenant would put
 * {tenant} in front of every route the rest of the suite asserts on, and the
 * question this one answers — what a multi-tenant panel offers the target
 * picker — does not need it to.
 */
class TenantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('tenancy')
            ->path('tenancy')
            ->tenant(Team::class)
            ->tenantProfile(EditTeamProfile::class);
    }
}
