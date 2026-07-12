<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Wallacemartinss\FilamentOnboarding\Enums\ModalPosition;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Pages\OnboardingProgress;
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

    protected ?ModalPosition $modalPosition = null;

    protected bool $hasProgressPage = false;

    protected bool $hasProgressPageNavigation = true;

    protected string $progressPageSlug = 'onboarding';

    protected string|Closure|null $progressPageLabel = null;

    protected string|Closure|null $progressPageIcon = null;

    protected string|Closure|null $progressPageGroup = null;

    protected int|Closure|null $progressPageSort = null;

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

        if ($this->hasProgressPage) {
            $panel->pages([
                OnboardingProgress::class,
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
        if ($this->modalPosition !== null) {
            // The panel's default, which a step may still override.
            config(['filament-onboarding.modal.position' => $this->modalPosition->value]);
        }

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
     * Register the page that lays the journey out — what is done, what is next,
     * what is left. Off by default: a product may prefer the floating checklist
     * on its own.
     *
     * @param  bool  $shouldRegisterNavigation  False keeps the page reachable by URL but out of the menu.
     */
    public function progressPage(bool $condition = true, bool $shouldRegisterNavigation = true): static
    {
        $this->hasProgressPage           = $condition;
        $this->hasProgressPageNavigation = $shouldRegisterNavigation;

        return $this;
    }

    /**
     * Where the media modal opens, for every step that does not say otherwise:
     * center, top, bottom, top-left, top-right, bottom-left, bottom-right.
     *
     * Docking it in a corner leaves the page usable behind it, which is what you
     * want when the video is meant to be followed along with.
     */
    public function modalPosition(ModalPosition|string $position): static
    {
        $this->modalPosition = $position instanceof ModalPosition
            ? $position
            : (ModalPosition::tryFrom($position) ?? ModalPosition::Center);

        return $this;
    }

    public function getModalPosition(): ?ModalPosition
    {
        return $this->modalPosition;
    }

    public function progressPageSlug(string $slug): static
    {
        $this->progressPageSlug = $slug;

        return $this;
    }

    /**
     * Navigation of the progress page. Closures are evaluated per request, so a
     * translated label follows the locale of whoever is reading it.
     */
    public function progressPageNavigation(
        string|Closure|null $label = null,
        string|Closure|null $icon = null,
        string|Closure|null $group = null,
        int|Closure|null $sort = null,
    ): static {
        $this->progressPageLabel = $label ?? $this->progressPageLabel;
        $this->progressPageIcon  = $icon ?? $this->progressPageIcon;
        $this->progressPageGroup = $group ?? $this->progressPageGroup;
        $this->progressPageSort  = $sort ?? $this->progressPageSort;

        return $this;
    }

    public function hasProgressPage(): bool
    {
        return $this->hasProgressPage;
    }

    public function hasProgressPageNavigation(): bool
    {
        return $this->hasProgressPageNavigation;
    }

    public function getProgressPageSlug(): string
    {
        return $this->progressPageSlug;
    }

    public function getProgressPageLabel(): ?string
    {
        return $this->evaluate($this->progressPageLabel);
    }

    public function getProgressPageIcon(): ?string
    {
        return $this->evaluate($this->progressPageIcon);
    }

    public function getProgressPageGroup(): ?string
    {
        return $this->evaluate($this->progressPageGroup);
    }

    public function getProgressPageSort(): ?int
    {
        return $this->evaluate($this->progressPageSort);
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
