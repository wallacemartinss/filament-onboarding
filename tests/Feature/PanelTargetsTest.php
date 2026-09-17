<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use Wallacemartinss\FilamentOnboarding\Support\PanelTargets;
use Wallacemartinss\FilamentOnboarding\Tests\TestCase;

/**
 * Where a step can send somebody is picked from the panel's own dropdowns, so
 * that a destination can only ever be one that exists — and so that it stays
 * right when a slug is renamed, because what is stored is the route name.
 *
 * Which means anything the panel can reach and this cannot see is, from the
 * author's side, simply not there.
 */
class PanelTargetsTest extends TestCase
{
    public function test_a_multi_tenant_panel_offers_its_tenant_profile(): void
    {
        $options = PanelTargets::pageOptions('tenancy');

        $pages = $options['Tenancy · ' . __('filament-onboarding::onboarding.resource.targets.pages')] ?? [];

        // Registered through ->tenantProfile() and never through ->pages(), so
        // getPages() has never listed it and the picker never offered it —
        // while "finish setting up your team" is the first thing a multi-tenant
        // onboarding wants to say. (#21)
        $this->assertArrayHasKey('filament.tenancy.tenant.profile', $pages);

        $this->assertSame(
            __('filament-onboarding::onboarding.resource.targets.tenant_profile'),
            $pages['filament.tenancy.tenant.profile'],
        );
    }

    public function test_the_tenant_route_placeholder_is_not_what_keeps_a_page_out(): void
    {
        // The profile lives behind {tenant}, like everything else on that panel.
        // A page is left out for wanting a *record* — there is no record to
        // onboard somebody towards — and {tenant} is not that.
        $route = app('router')->getRoutes()->getByName('filament.tenancy.tenant.profile');

        $this->assertNotNull($route);
        $this->assertSame(['tenant'], $route->parameterNames());
    }

    public function test_a_panel_without_tenants_is_not_offered_a_tenant_profile(): void
    {
        $options = PanelTargets::pageOptions('test');

        $offered = array_keys(array_merge(...array_values($options)));

        foreach ($offered as $routeName) {
            $this->assertStringNotContainsString('tenant.profile', $routeName);
        }
    }

    public function test_every_panel_is_read_when_none_is_named(): void
    {
        $groups = array_keys(PanelTargets::pageOptions());

        $this->assertNotEmpty(array_filter($groups, fn (string $g): bool => str_starts_with($g, 'Test · ')));
        $this->assertNotEmpty(array_filter($groups, fn (string $g): bool => str_starts_with($g, 'Tenancy · ')));
    }
}
