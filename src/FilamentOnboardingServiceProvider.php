<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding;

use Filament\Facades\Filament;
use Filament\Support\Assets\Asset;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\{Package, PackageServiceProvider};
use Wallacemartinss\FilamentOnboarding\Assets\{VersionedAlpineComponent, VersionedCss};
use Wallacemartinss\FilamentOnboarding\Commands\ResetOnboardingCommand;
use Wallacemartinss\FilamentOnboarding\Conditions\ConditionRegistry;
use Wallacemartinss\FilamentOnboarding\Livewire\OnboardingLauncher;
use Wallacemartinss\FilamentOnboarding\Widgets\OnboardingChecklistWidget;

class FilamentOnboardingServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-onboarding';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations([
                'create_onboarding_tables',
                'add_media_to_onboarding_steps',
                'add_visibility_to_onboarding',
            ])
            ->hasCommand(ResetOnboardingCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(ConditionRegistry::class);

        $this->app->singleton(
            OnboardingManager::class,
            fn ($app): OnboardingManager => new OnboardingManager($app->make(ConditionRegistry::class)),
        );
    }

    public function packageBooted(): void
    {
        $this->registerDefaultResolvers();
        $this->registerConditionsFromConfig();
        $this->registerPublishableAssets();

        Livewire::component('filament-onboarding-launcher', OnboardingLauncher::class);
        Livewire::component('filament-onboarding-checklist-widget', OnboardingChecklistWidget::class);

        FilamentAsset::register($this->assets(), package: 'wallacemartinss/filament-onboarding');
    }

    /**
     * The tour runner always ships; the stylesheet is replaceable. Point
     * `styles.path` at a file of your own to restyle the checklist and tours
     * without forking the package, or turn styles off entirely and dress the
     * `.fio-*` classes from your panel theme.
     *
     * @return array<int, Asset>
     */
    private function assets(): array
    {
        // Versioned by content: the ?v= changes when the file changes, so an
        // edited stylesheet actually reaches the browser — no release, no bump.
        $assets = [
            VersionedAlpineComponent::make('onboarding-tour', __DIR__ . '/../resources/dist/js/onboarding-tour.js'),
            VersionedAlpineComponent::make('onboarding-media', __DIR__ . '/../resources/dist/js/onboarding-media.js'),
        ];

        if (!config('filament-onboarding.styles.enabled', true)) {
            return $assets;
        }

        $stylesheet = config('filament-onboarding.styles.path')
            ?: __DIR__ . '/../resources/dist/css/onboarding.css';

        $assets[] = VersionedCss::make('filament-onboarding', $stylesheet);

        return $assets;
    }

    /**
     * Publishing the stylesheet hands over the whole thing — copy it, edit it,
     * point `styles.path` at the copy, then re-run `php artisan filament:assets`.
     */
    private function registerPublishableAssets(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../resources/css/onboarding.css' => resource_path('css/vendor/filament-onboarding/onboarding.css'),
        ], 'filament-onboarding-styles');
    }

    /**
     * Sensible defaults for any Filament app: the logged-in user onboards, the
     * current tenant scopes the progress, and {tenant} resolves in step URLs.
     * A plugin that was given its own resolvers overrides these when the panel
     * boots.
     */
    private function registerDefaultResolvers(): void
    {
        $manager = $this->app->make(OnboardingManager::class);

        $manager->resolveSubjectUsing(function (): ?Model {
            try {
                return Filament::auth()->user() ?? auth()->user();
            } catch (\Throwable) {
                return auth()->user();
            }
        });

        $manager->resolveScopeUsing(function (): ?Model {
            try {
                return Filament::getTenant();
            } catch (\Throwable) {
                return null;
            }
        });

        $manager->resolveUrlParametersUsing(function (): array {
            try {
                $tenant = Filament::getTenant();

                if ($tenant === null) {
                    return [];
                }

                $slugAttribute = Filament::getCurrentOrDefaultPanel()?->getTenantSlugAttribute();

                return [
                    'tenant' => $slugAttribute
                        ? $tenant->getAttribute($slugAttribute)
                        : $tenant->getKey(),
                ];
            } catch (\Throwable) {
                return [];
            }
        });
    }

    private function registerConditionsFromConfig(): void
    {
        /** @var array<string, \Closure|class-string> $conditions */
        $conditions = config('filament-onboarding.conditions', []);

        $this->app->make(ConditionRegistry::class)->registerMany($conditions);
    }
}
