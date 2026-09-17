<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding;

use Filament\Facades\Filament;
use Filament\Support\Assets\Asset;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{Event, Gate};
use Livewire\Livewire;
use Spatie\LaravelPackageTools\{Package, PackageServiceProvider};
use Wallacemartinss\FilamentOnboarding\Assets\{VersionedAlpineComponent, VersionedCss};
use Wallacemartinss\FilamentOnboarding\Commands\{MakeConditionCommand, ResetOnboardingCommand};
use Wallacemartinss\FilamentOnboarding\Conditions\{ConditionDiscovery, ConditionRegistry};
use Wallacemartinss\FilamentOnboarding\Livewire\OnboardingLauncher;
use Wallacemartinss\FilamentOnboarding\Policies\{OnboardingConditionPolicy, OnboardingFlowPolicy, OnboardingStepPolicy};
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
        $this->registerPolicies();
        $this->registerPublishableAssets();
        $this->forgetMemosAtEveryBoundary();

        Livewire::component('filament-onboarding-launcher', OnboardingLauncher::class);
        Livewire::component('filament-onboarding-checklist-widget', OnboardingChecklistWidget::class);

        FilamentAsset::register($this->assets(), package: 'wallacemartinss/filament-onboarding');
    }

    /**
     * Tie the in-memory copy of the definitions to the request, on the workers
     * where the request is not the end of the process.
     *
     * The manager and the condition registry are singletons, and both of them
     * memoise what they read so that one panel request does not ask the cache
     * store the same question a dozen times. Under PHP-FPM that memo cannot
     * outlive the request that made it. Under Octane it is the same object on
     * every request the worker serves, and under `queue:work` it is the same
     * object on every job — so an author who switches a flow off in the panel
     * watches a handful of workers go on serving it.
     *
     * The write itself is not the problem: it flushes the shared cache, and it
     * does so from whichever process handled it. The problem is every *other*
     * process, which never heard. So the memo is dropped at the boundaries a
     * long-lived worker does have.
     *
     * This costs one cache read per request, not one query: what is dropped is
     * the copy in this process, never what the processes share.
     */
    private function forgetMemosAtEveryBoundary(): void
    {
        $forget = function (): void {
            $this->app->make(OnboardingManager::class)->forgetMemoized();
        };

        $boundaries = [
            // Octane, when it is installed. Referenced by name so that it is
            // not a dependency: a panel on FPM never loads these.
            'Laravel\\Octane\\Events\\RequestReceived',
            'Laravel\\Octane\\Events\\TaskReceived',
            'Laravel\\Octane\\Events\\TickReceived',

            // A queue worker is long-lived too, and a job that completes a step
            // for somebody is exactly the kind of thing that runs in one.
            \Illuminate\Queue\Events\JobProcessing::class,
        ];

        foreach ($boundaries as $event) {
            if (class_exists($event)) {
                Event::listen($event, $forget);
            }
        }
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
     * A policy for each model the panel exposes, so `->strictAuthorization()`
     * has something to find. The defaults answer yes to everything — the access
     * the resources had before policies existed.
     *
     * For the stock models nothing needs registering at all: the policies sit
     * in the package's Policies namespace, which is exactly where Laravel's
     * guesser looks. What this method wires is the two other arrangements:
     *
     *   - a class named in `filament-onboarding.policies` is registered
     *     explicitly, which beats the guessed default;
     *   - a swapped model (`filament-onboarding.models`) is outside the
     *     package namespace, so nothing is guessable for it — the default is
     *     registered for it, unless the application's own policy already
     *     resolves, which is left exactly where it is.
     *
     * A `Gate::policy()` in the application's provider boots later either way,
     * so it simply overwrites whatever was decided here.
     */
    private function registerPolicies(): void
    {
        $manager = $this->app->make(OnboardingManager::class);

        $policies = [
            'flow'      => [$manager->flowModel(), OnboardingFlowPolicy::class],
            'step'      => [$manager->stepModel(), OnboardingStepPolicy::class],
            'condition' => [$manager->conditionModel(), OnboardingConditionPolicy::class],
        ];

        foreach ($policies as $key => [$model, $default]) {
            $configured = config("filament-onboarding.policies.{$key}");

            if (is_string($configured) && $configured !== '') {
                Gate::policy($model, $configured);

                continue;
            }

            if (Gate::getPolicyFor($model) === null) {
                Gate::policy($model, $default);
            }
        }
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
