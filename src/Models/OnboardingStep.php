<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Models;

use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Support\Str;
use Wallacemartinss\FilamentOnboarding\Concerns\HasTranslatableColumns;
use Wallacemartinss\FilamentOnboarding\Enums\{CompletionMode, StepType};
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Support\{PanelTargets, TranslatableText};

/**
 * @property string $id
 * @property string $flow_id
 * @property string $key
 * @property StepType $type
 * @property array<string, string> $title
 * @property array<string, string>|null $description
 * @property string|null $icon
 * @property array<string, string>|null $cta_label
 * @property string|null $cta_url
 * @property string|null $cta_route
 * @property CompletionMode $completion_mode
 * @property string|null $condition_key
 * @property string|null $visit_url
 * @property array<int, array<string, string|null>>|null $tour_steps
 * @property bool $is_required
 * @property bool $is_active
 * @property int $sort_order
 */
class OnboardingStep extends Model
{
    use HasTranslatableColumns;
    use HasUuids;

    protected $guarded = [];

    /** @var array<int, string> */
    public array $translatable = ['title', 'description', 'cta_label'];

    public function getTable(): string
    {
        return config('filament-onboarding.tables.steps', 'onboarding_steps');
    }

    protected static function booted(): void
    {
        static::saved(fn () => Onboarding::flushCache());
        static::deleted(fn () => Onboarding::flushCache());
    }

    /**
     * @return BelongsTo<OnboardingFlow, $this>
     */
    public function flow(): BelongsTo
    {
        return $this->belongsTo(Onboarding::flowModel(), 'flow_id');
    }

    /**
     * @return HasMany<OnboardingStepProgress, $this>
     */
    public function progress(): HasMany
    {
        return $this->hasMany(Onboarding::stepProgressModel(), 'step_id');
    }

    /**
     * @param  Builder<$this>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * The link the call to action points at, with {placeholders} filled from the
     * panel's URL parameters (a tenant, most often).
     *
     * @param  array<string, mixed>  $parameters
     */
    public function resolveUrl(array $parameters = []): ?string
    {
        if (filled($this->cta_route)) {
            try {
                return route($this->cta_route, $parameters);
            } catch (\Throwable) {
                return null;
            }
        }

        if (blank($this->cta_url)) {
            return null;
        }

        $url = $this->cta_url;

        foreach ($parameters as $key => $value) {
            $url = str_replace('{' . $key . '}', (string) $value, $url);
        }

        // An unresolved placeholder would send the subject to a broken page.
        if (Str::contains($url, ['{', '}'])) {
            return null;
        }

        return Str::startsWith($url, ['http://', 'https://', '/'])
            ? $url
            : url($url);
    }

    /**
     * Whether a URL the subject just reached satisfies this step. Supports the
     * `*` wildcard, so a pattern like "/app/[*]/servers/create" — with a literal
     * asterisk in place of the brackets — matches the page under any tenant.
     */
    public function matchesVisit(string $path): bool
    {
        if ($this->completion_mode !== CompletionMode::Visit || blank($this->visit_url)) {
            return false;
        }

        $path    = '/' . ltrim(parse_url($path, PHP_URL_PATH) ?: $path, '/');
        $pattern = '/' . ltrim($this->visit_url, '/');

        return Str::is($pattern, $path) || Str::is($pattern, rtrim($path, '/'));
    }

    /**
     * The tour handed to the browser, already in the reader's locale.
     *
     * @param  array<string, mixed>  $parameters
     * @return array<int, array{selector: string|null, title: string|null, body: string|null, placement: string, url: string|null}>
     */
    public function resolveTourSteps(array $parameters = []): array
    {
        if ($this->type !== StepType::Tour || blank($this->tour_steps)) {
            return [];
        }

        return collect($this->tour_steps)
            ->map(fn (array $tourStep): array => [
                'selector'  => $this->resolveTourSelector($tourStep),
                'title'     => TranslatableText::resolve($tourStep['title'] ?? null),
                'body'      => TranslatableText::resolve($tourStep['body'] ?? null),
                'placement' => $tourStep['placement'] ?? 'auto',
                'url'       => $this->resolveTourUrl($tourStep, $parameters),
            ])
            ->all();
    }

    /**
     * A tour stop points at a CSS selector, or at a widget picked from the panel
     * — widgets have no selector of their own, so they are addressed by the
     * Livewire component they are and found in the DOM by the tour runner.
     *
     * @param  array<string, mixed>  $tourStep
     */
    private function resolveTourSelector(array $tourStep): ?string
    {
        $selector = $tourStep['selector'] ?? null;

        if (filled($selector)) {
            return (string) $selector;
        }

        $widget = $tourStep['widget'] ?? null;

        return filled($widget)
            ? PanelTargets::widgetSelector((string) $widget)
            : null;
    }

    /**
     * The page a stop lives on: a route picked from the panel, or a URL typed by
     * hand. The route wins, and it survives a renamed slug.
     *
     * @param  array<string, mixed>  $tourStep
     * @param  array<string, mixed>  $parameters
     */
    private function resolveTourUrl(array $tourStep, array $parameters): ?string
    {
        $route = $tourStep['route'] ?? null;

        if (filled($route)) {
            try {
                return route((string) $route, $parameters);
            } catch (\Throwable) {
                return null;
            }
        }

        $url = $tourStep['url'] ?? null;

        if (blank($url)) {
            return null;
        }

        foreach ($parameters as $key => $value) {
            $url = str_replace('{' . $key . '}', (string) $value, (string) $url);
        }

        return Str::contains($url, ['{', '}']) ? null : (string) $url;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type'            => StepType::class,
            'completion_mode' => CompletionMode::class,
            'title'           => 'array',
            'description'     => 'array',
            'cta_label'       => 'array',
            'tour_steps'      => 'array',
            'is_required'     => 'boolean',
            'is_active'       => 'boolean',
            'sort_order'      => 'integer',
        ];
    }
}
