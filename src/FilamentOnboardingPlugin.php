<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Resources\OnboardingFlows\OnboardingFlowResource;

class FilamentOnboardingPlugin implements Plugin
{
    use EvaluatesClosures;

    protected bool|Closure $hasLauncher = true;

    protected bool|Closure $hasTours = true;

    protected bool $managesFlows = false;

    protected string $launcherPosition = 'bottom-right';

    protected ?Closure $subjectResolver = null;

    protected ?Closure $scopeResolver = null;

    protected ?Closure $urlParametersResolver = null;

    /** @var array<string, Closure|class-string> */
    protected array $conditions = [];

    /** @var array<string, string> */
    protected array $conditionLabels = [];

    protected string|Closure|null $navigationGroup = null;

    protected string|Closure|null $navigationIcon = null;

    protected int|Closure|null $navigationSort = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'filament-onboarding';
    }

    public function register(Panel $panel): void
    {
        if ($this->managesFlows) {
            $panel->resources([
                OnboardingFlowResource::class,
            ]);
        }

        // The launcher carries the tour runner, so it is rendered whenever either
        // is on — pages, resources and widgets alike, since it hangs off the body.
        $panel->renderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => $this->renderLauncher(),
        );
    }

    /**
     * The package already resolves the subject, the scope and {tenant} on its
     * own — this only takes over when the panel was given something else.
     */
    public function boot(Panel $panel): void
    {
        if ($this->subjectResolver !== null) {
            Onboarding::resolveSubjectUsing($this->subjectResolver);
        }

        if ($this->scopeResolver !== null) {
            Onboarding::resolveScopeUsing($this->scopeResolver);
        }

        if ($this->urlParametersResolver !== null) {
            Onboarding::resolveUrlParametersUsing($this->urlParametersResolver);
        }

        foreach ($this->conditions as $key => $condition) {
            Onboarding::condition($key, $condition, $this->conditionLabels[$key] ?? null);
        }
    }

    /**
     * Show the floating progress button on every page of the panel.
     */
    public function launcher(bool|Closure $condition = true): static
    {
        $this->hasLauncher = $condition;

        return $this;
    }

    /**
     * Where the floating button sits: bottom-right, bottom-left, top-right or top-left.
     */
    public function launcherPosition(string $position): static
    {
        $this->launcherPosition = $position;

        return $this;
    }

    public function tours(bool|Closure $condition = true): static
    {
        $this->hasTours = $condition;

        return $this;
    }

    /**
     * Register the resource that authors flows and steps in this panel.
     */
    public function manageFlows(bool $condition = true): static
    {
        $this->managesFlows = $condition;

        return $this;
    }

    /**
     * Whose progress this is. Defaults to the authenticated user.
     */
    public function subject(Closure $callback): static
    {
        $this->subjectResolver = $callback;

        return $this;
    }

    /**
     * The context progress is kept per. Defaults to the current tenant, so the
     * same user onboards separately in each tenant they belong to.
     */
    public function scope(?Closure $callback): static
    {
        $this->scopeResolver = $callback ?? fn () => null;

        return $this;
    }

    /**
     * Values for {placeholders} in step URLs.
     */
    public function urlParameters(Closure $callback): static
    {
        $this->urlParametersResolver = $callback;

        return $this;
    }

    /**
     * @param  array<string, Closure|class-string>  $conditions
     * @param  array<string, string>  $labels
     */
    public function conditions(array $conditions, array $labels = []): static
    {
        $this->conditions      = [...$this->conditions, ...$conditions];
        $this->conditionLabels = [...$this->conditionLabels, ...$labels];

        return $this;
    }

    /**
     * Navigation of the flow resource. Closures are evaluated per request, so a
     * translated label follows the locale of whoever is reading it.
     */
    public function navigationGroup(string|Closure|null $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function navigationIcon(string|Closure|null $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function navigationSort(int|Closure|null $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->evaluate($this->navigationGroup)
            ?? config('filament-onboarding.resource.navigation_group');
    }

    public function getNavigationIcon(): ?string
    {
        return $this->evaluate($this->navigationIcon)
            ?? config('filament-onboarding.resource.navigation_icon', 'heroicon-o-map');
    }

    public function getNavigationSort(): ?int
    {
        return $this->evaluate($this->navigationSort)
            ?? config('filament-onboarding.resource.navigation_sort');
    }

    public function hasLauncher(): bool
    {
        return (bool) $this->evaluate($this->hasLauncher);
    }

    public function hasTours(): bool
    {
        return (bool) $this->evaluate($this->hasTours);
    }

    public function getLauncherPosition(): string
    {
        return $this->launcherPosition;
    }

    protected function renderLauncher(): string
    {
        if (!$this->hasLauncher() && !$this->hasTours()) {
            return '';
        }

        if (!Onboarding::resolveSubject() instanceof \Illuminate\Database\Eloquent\Model) {
            return '';
        }

        return Blade::render('@livewire(\'filament-onboarding-launcher\')');
    }
}
