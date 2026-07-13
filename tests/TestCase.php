<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests;

use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, View};
use Illuminate\Support\ViewErrorBag;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Wallacemartinss\FilamentOnboarding\FilamentOnboardingServiceProvider;
use Wallacemartinss\FilamentOnboarding\Tests\Fixtures\TestPanelProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runPackageMigrations();
        $this->createSubjectsTable();

        // Livewire reads the shared error bag while rendering. In an application
        // the session middleware shares it; in a bare test kernel nobody does,
        // and the first component render dies inside the Blade compiler.
        View::share('errors', new ViewErrorBag());

    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            BladeIconsServiceProvider::class,
            SupportServiceProvider::class,
            ActionsServiceProvider::class,
            SchemasServiceProvider::class,
            FormsServiceProvider::class,
            TablesServiceProvider::class,
            InfolistsServiceProvider::class,
            WidgetsServiceProvider::class,
            FilamentServiceProvider::class,
            FilamentOnboardingServiceProvider::class,
            TestPanelProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        // Livewire hydrates an error bag off the session; without a driver the
        // first component render dies inside the Blade compiler.
        config()->set('session.driver', 'array');
        config()->set('filament-onboarding.cache.enabled', false);
        config()->set('filament-onboarding.locales', ['en', 'pt_BR', 'es']);
    }

    /**
     * The migrations, in the order they must run.
     *
     * Alphabetical order would run the `add_*` migrations before the tables they
     * alter exist, so the order is declared rather than discovered — and guarded
     * below, because a migration that nobody added here would leave the whole
     * suite testing a schema the host application does not have.
     */
    private const MIGRATIONS = [
        'create_onboarding_tables',
        'add_media_to_onboarding_steps',
        'add_visibility_to_onboarding',
        'harden_onboarding_progress_scope',
        'create_onboarding_preferences',
    ];

    private function runPackageMigrations(): void
    {
        $onDisk = collect(glob(__DIR__ . '/../database/migrations/*.php.stub'))
            ->map(fn (string $path): string => basename($path, '.php.stub'))
            ->sort()
            ->values()
            ->all();

        $declared = collect(self::MIGRATIONS)->sort()->values()->all();

        $this->assertSame(
            $onDisk,
            $declared,
            'A migration on disk is not declared in TestCase::MIGRATIONS — add it, in the order it must run.'
        );

        foreach (self::MIGRATIONS as $name) {
            $migration = include __DIR__ . "/../database/migrations/{$name}.php.stub";

            $migration->up();
        }
    }

    /**
     * A stand-in for the host application's users table, so progress has
     * something to belong to.
     */
    private function createSubjectsTable(): void
    {
        Schema::create('subjects', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }
}
