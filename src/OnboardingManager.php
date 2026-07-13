<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding;

use Closure;
use Illuminate\Database\Eloquent\{Collection, Model};
use Illuminate\Support\Facades\Cache;
use Wallacemartinss\FilamentOnboarding\Conditions\ConditionRegistry;
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingFlow, OnboardingFlowProgress, OnboardingPreference, OnboardingStep, OnboardingStepProgress};

class OnboardingManager
{
    protected ?Closure $subjectResolver = null;

    protected ?Closure $scopeResolver = null;

    protected ?Closure $urlParametersResolver = null;

    public function __construct(protected ConditionRegistry $conditions)
    {
    }

    /**
     * Register a named check a step can be completed by.
     *
     * @param  Closure|class-string  $condition
     */
    public function condition(string $key, Closure|string $condition, ?string $label = null): static
    {
        $this->conditions->register($key, $condition, $label);

        return $this;
    }

    public function conditions(): ConditionRegistry
    {
        return $this->conditions;
    }

    /**
     * Onboarding as seen by one subject — the authenticated user, unless told
     * otherwise — within an optional scope, such as the current tenant.
     */
    public function for(?Model $subject = null, ?Model $scope = null): SubjectOnboarding
    {
        $subject ??= $this->resolveSubject();

        if (!$subject instanceof Model) {
            throw new \RuntimeException('Onboarding needs a subject: pass one to Onboarding::for(), or teach it how to find one with Onboarding::resolveSubjectUsing().');
        }

        return new SubjectOnboarding($this, $subject, $scope ?? $this->resolveScope());
    }

    /**
     * Onboarding for whoever is browsing right now, or null when nobody is.
     */
    public function current(): ?SubjectOnboarding
    {
        $subject = $this->resolveSubject();

        if (!$subject instanceof Model) {
            return null;
        }

        return new SubjectOnboarding($this, $subject, $this->resolveScope());
    }

    public function resolveSubjectUsing(?Closure $callback): static
    {
        $this->subjectResolver = $callback;

        return $this;
    }

    public function resolveScopeUsing(?Closure $callback): static
    {
        $this->scopeResolver = $callback;

        return $this;
    }

    /**
     * Parameters filling {placeholders} in step URLs — `{tenant}`, typically.
     */
    public function resolveUrlParametersUsing(?Closure $callback): static
    {
        $this->urlParametersResolver = $callback;

        return $this;
    }

    public function resolveSubject(): ?Model
    {
        $subject = $this->subjectResolver ? ($this->subjectResolver)() : null;

        return $subject instanceof Model ? $subject : null;
    }

    public function resolveScope(): ?Model
    {
        $scope = $this->scopeResolver ? ($this->scopeResolver)() : null;

        return $scope instanceof Model ? $scope : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function urlParameters(): array
    {
        $parameters = $this->urlParametersResolver ? ($this->urlParametersResolver)() : [];

        return is_array($parameters) ? $parameters : [];
    }

    /**
     * Every active flow with its active steps. Definitions change rarely and are
     * read on every panel request, so they are cached until one is written.
     *
     * @return Collection<int, OnboardingFlow>
     */
    public function flows(?string $panelId = null): Collection
    {
        $flows = $this->cachedFlows();

        if ($panelId !== null) {
            $flows = $flows->filter(
                fn (OnboardingFlow $flow): bool => $flow->panel_id === null || $flow->panel_id === $panelId
            );
        }

        return $flows->values();
    }

    public function flow(string $key, ?string $panelId = null): ?OnboardingFlow
    {
        return $this->flows($panelId)->firstWhere('key', $key);
    }

    public function flushCache(): void
    {
        $this->cacheStore()->forget($this->cacheKey());
    }

    /** @return class-string<OnboardingFlow> */
    public function flowModel(): string
    {
        return config('filament-onboarding.models.flow', OnboardingFlow::class);
    }

    /** @return class-string<OnboardingStep> */
    public function stepModel(): string
    {
        return config('filament-onboarding.models.step', OnboardingStep::class);
    }

    /** @return class-string<OnboardingFlowProgress> */
    public function flowProgressModel(): string
    {
        return config('filament-onboarding.models.flow_progress', OnboardingFlowProgress::class);
    }

    /** @return class-string<OnboardingStepProgress> */
    public function stepProgressModel(): string
    {
        return config('filament-onboarding.models.step_progress', OnboardingStepProgress::class);
    }

    /** @return class-string<OnboardingPreference> */
    public function preferenceModel(): string
    {
        return config('filament-onboarding.models.preferences', OnboardingPreference::class);
    }

    /**
     * @return array<int, string>
     */
    public function locales(): array
    {
        $locales = config('filament-onboarding.locales', []);

        return filled($locales) ? array_values((array) $locales) : [app()->getLocale()];
    }

    /**
     * The definitions, from cache when there is one.
     *
     * What goes into the cache is **arrays, never objects**. Laravel refuses to
     * unserialize classes out of the cache unless the application lists them —
     * `cache.serializable_classes` is `false` in a stock Laravel 13, to keep a
     * leaked APP_KEY from turning cached objects into a gadget chain. Handing it
     * a Collection of Eloquent models therefore writes fine and reads back as
     * `__PHP_Incomplete_Class`: the second request of the panel's life, and every
     * one after it, dies on the return type — on every page, since the launcher
     * lives in the layout.
     *
     * So the models are taken apart on the way in and rebuilt on the way out. A
     * cache holding anything else (an object written by an older release, junk
     * from a shared prefix) reads as a miss, is re-queried and overwritten.
     *
     * @return Collection<int, OnboardingFlow>
     */
    protected function cachedFlows(): Collection
    {
        if (!config('filament-onboarding.cache.enabled', true)) {
            return $this->queryFlows();
        }

        $cached = $this->cacheStore()->get($this->cacheKey());

        $flows = is_array($cached) ? $this->hydrateFlows($cached) : null;

        if ($flows instanceof Collection) {
            return $flows;
        }

        $flows = $this->queryFlows();

        $this->cacheStore()->put(
            $this->cacheKey(),
            $this->dehydrateFlows($flows),
            (int) config('filament-onboarding.cache.ttl', 3600),
        );

        return $flows;
    }

    /**
     * Flows and their steps as the database handed them over: plain attributes,
     * no objects anywhere.
     *
     * @param  Collection<int, OnboardingFlow>  $flows
     * @return array<int, array{flow: array<string, mixed>, steps: array<int, array<string, mixed>>}>
     */
    protected function dehydrateFlows(Collection $flows): array
    {
        return $flows
            ->map(fn (OnboardingFlow $flow): array => [
                'flow'  => $flow->getAttributes(),
                'steps' => $flow->steps
                    ->map(fn (OnboardingStep $step): array => $step->getAttributes())
                    ->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * Rebuild what dehydrateFlows() took apart. Anything that is not the shape
     * it wrote is treated as a miss rather than trusted.
     *
     * @param  array<mixed>  $cached
     * @return Collection<int, OnboardingFlow>|null
     */
    protected function hydrateFlows(array $cached): ?Collection
    {
        /** @var class-string<OnboardingFlow> $flowModel */
        $flowModel = $this->flowModel();

        /** @var class-string<OnboardingStep> $stepModel */
        $stepModel = $this->stepModel();

        $flows = [];

        foreach ($cached as $row) {
            if (!is_array($row) || !is_array($row['flow'] ?? null) || !is_array($row['steps'] ?? null)) {
                return null;
            }

            $steps = [];

            foreach ($row['steps'] as $attributes) {
                if (!is_array($attributes)) {
                    return null;
                }

                // newFromBuilder(), not make(): these attributes came out of the
                // database, so they are raw — the casts apply on the way out, and
                // the model knows it already exists.
                $steps[] = (new $stepModel())->newFromBuilder($attributes);
            }

            $flow = (new $flowModel())->newFromBuilder($row['flow']);

            $flow->setRelation('steps', new Collection($steps));

            $flows[] = $flow;
        }

        return new Collection($flows);
    }

    /**
     * @return Collection<int, OnboardingFlow>
     */
    protected function queryFlows(): Collection
    {
        /** @var class-string<OnboardingFlow> $model */
        $model = $this->flowModel();

        return $model::query()
            ->active()
            ->with(['steps' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();
    }

    protected function cacheStore(): \Illuminate\Contracts\Cache\Repository
    {
        return Cache::store(config('filament-onboarding.cache.store'));
    }

    protected function cacheKey(): string
    {
        return config('filament-onboarding.cache.prefix', 'filament-onboarding') . '.flows';
    }
}
