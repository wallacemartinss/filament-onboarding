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
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Wallacemartinss\FilamentOnboarding\FilamentOnboardingServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runPackageMigrations();
        $this->createSubjectsTable();
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
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('filament-onboarding.cache.enabled', false);
        config()->set('filament-onboarding.locales', ['en', 'pt_BR', 'es']);
    }

    private function runPackageMigrations(): void
    {
        foreach (['create_onboarding_tables', 'add_media_to_onboarding_steps'] as $name) {
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
