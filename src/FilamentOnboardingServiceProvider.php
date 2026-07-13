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
use Wallacemartinss\FilamentOnboarding\Commands\{MakeConditionCommand, ResetOnboardingCommand};
use Wallacemartinss\FilamentOnboarding\Conditions\{ConditionDiscovery, ConditionRegistry};
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
                'harden_onboarding_progress_scope',
                'create_onboarding_preferences',
                'create_onboarding_conditions',
            ])
            ->hasCommands([
                ResetOnboardingCommand::class,
                MakeConditionCommand::class,
            ]);
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
        $this->registerConditions();
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

    /**
     * Where the questions come from — all three sources, wired at boot.
     *
     * The order matters, and it is the order of how deliberate each one is: what
     * the application registered by hand, then what it merely wrote (and the
     * package found), then what somebody built in the panel. Code wins a name
     * clash, so a row can never quietly redefine what `has_server` means.
     *
     * The panel's conditions are *deferred*: the registry is built while the
     * application boots, and the database is not something to reach for there.
     * They are read on the first question anybody asks, and cached.
     */
    private function registerConditions(): void
    {
        $registry = $this->app->make(ConditionRegistry::class);

        /** @var array<string, \Closure|class-string> $configured */
        $configured = config('filament-onboarding.conditions', []);

        $registry->registerMany($configured);

        // Written in app/Onboarding/Conditions, and registered nowhere: writing
        // the class and then naming it in a config file is saying the same thing
        // twice, and the second half is what gets forgotten in a deploy.
        $registry->registerMany(ConditionDiscovery::discover());

        $registry->loadRecordsUsing(
            fn (): array => $this->app->make(OnboardingManager::class)->recordedConditions(),
        );
    }
}
