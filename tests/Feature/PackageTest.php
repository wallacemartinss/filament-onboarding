<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Wallacemartinss\FilamentOnboarding\Livewire\OnboardingLauncher;
use Wallacemartinss\FilamentOnboarding\Tests\TestCase;
use Wallacemartinss\FilamentOnboarding\Widgets\OnboardingChecklistWidget;

/**
 * What the package promises to the outside world: the publish tags, the command,
 * the assets, the config. The README documents these — this is what keeps the
 * README honest.
 */
class PackageTest extends TestCase
{
    public function test_it_publishes_everything_the_readme_says_it_does(): void
    {
        $groups = ServiceProvider::publishableGroups();

        foreach ([
            'filament-onboarding-config',
            'filament-onboarding-migrations',
            'filament-onboarding-views',
            'filament-onboarding-translations',
            'filament-onboarding-styles',
        ] as $tag) {
            $this->assertContains($tag, $groups, "The publish tag [{$tag}] is documented but not registered.");
        }
    }

    public function test_it_registers_its_command(): void
    {
        $this->assertArrayHasKey('onboarding:reset', Artisan::all());
    }

    public function test_it_registers_its_assets(): void
    {
        $ids = collect(FilamentAsset::getAlpineComponents(['wallacemartinss/filament-onboarding']))
            ->map(fn ($asset): string => $asset->getId())
            ->all();

        $this->assertContains('onboarding-tour', $ids);
        $this->assertContains('onboarding-media', $ids);

        $css = collect(FilamentAsset::getStyles(['wallacemartinss/filament-onboarding']))
            ->map(fn ($asset): string => $asset->getId())
            ->all();

        $this->assertContains('filament-onboarding', $css);
    }

    /**
     * The ?v= has to change when the file changes. Filament stamps the Composer
     * version, which does not move during development or from a path repository
     * — so the package hashes the file instead.
     */
    public function test_assets_are_versioned_by_content(): void
    {
        $asset = collect(FilamentAsset::getAlpineComponents(['wallacemartinss/filament-onboarding']))
            ->firstWhere(fn ($asset): bool => $asset->getId() === 'onboarding-tour');

        $version = $asset->getVersion();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{12}$/', $version);
        $this->assertSame(md5_file($asset->getPath()), $version . substr(md5_file($asset->getPath()), 12));
    }

    public function test_it_registers_its_livewire_components(): void
    {
        $this->assertTrue(Livewire::isDiscoverable('filament-onboarding-launcher'));
        $this->assertSame(
            OnboardingLauncher::class,
            app('livewire.factory')->resolveComponentClass('filament-onboarding-launcher'),
        );

        $this->assertSame(
            OnboardingChecklistWidget::class,
            app('livewire.factory')->resolveComponentClass('filament-onboarding-checklist-widget'),
        );
    }

    public function test_the_config_has_every_key_the_readme_documents(): void
    {
        foreach ([
            'locales',
            'fallback_locale',
            'conditions',
            'cache.enabled',
            'media.disk',
            'media.visibility',
            'media.url_ttl',
            'modal.position',
            'styles.enabled',
            'styles.path',
            'tables.flows',
            'models.flow',
            'resource.navigation_icon',
        ] as $key) {
            $this->assertTrue(
                config()->has("filament-onboarding.{$key}"),
                "The config key [{$key}] is documented but missing.",
            );
        }
    }
}
