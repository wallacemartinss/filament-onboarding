# Filament Onboarding

Database-driven onboarding for Filament v5: a progress checklist that follows the user across every page of the panel, and guided spotlight tours that can walk them through it.

Flows are authored in the panel, not in code, so product people can rewrite the journey without a deploy. Steps that are completed by a condition catch up on their own, which means **users who signed up long before the flow existed enter it already half-done** instead of being told to do things they did years ago.

- **Checklist** — a floating progress button on every page (pages, resources, widgets), plus an optional dashboard widget.
- **Tours** — spotlight an element, explain it, move on. Tours can cross pages: the runner navigates and picks up where it left off.
- **Any locale** — content is stored per locale and read back in whichever locale the user picked. Nothing is hard-coded to a language.
- **Multi-tenant** — progress is scoped, so the same user onboards separately in each tenant.

## Installation

```bash
composer require wallacemartinss/filament-onboarding

php artisan vendor:publish --tag=filament-onboarding-migrations
php artisan vendor:publish --tag=filament-onboarding-config
php artisan migrate
php artisan filament:assets
```

The migrations run on **PostgreSQL and MySQL/MariaDB** alike — the morph columns
carry explicit lengths so the composite unique keys stay inside InnoDB's
3072-byte limit.

### Icons

The icon field of a flow or a step takes any Blade icon name — `heroicon-o-server`,
`phosphor-rocket`, whatever your app has installed. Add
[`wallacemartinss/filament-icon-picker`](https://github.com/wallacemartinss/filament-icon-picker)
and the field becomes a visual picker on its own; the package detects it and needs
no configuration:

```bash
composer require wallacemartinss/filament-icon-picker
```

Without it, the field stays a plain text input and everything else works the same.

Everything is publishable:

| Tag | What it gives you |
|-----|-------------------|
| `filament-onboarding-config` | `config/filament-onboarding.php` |
| `filament-onboarding-migrations` | the four tables |
| `filament-onboarding-views` | every Blade view — the checklist, the launcher, the tour popover |
| `filament-onboarding-translations` | the UI strings, per locale |
| `filament-onboarding-styles` | the stylesheet, to restyle without forking |

Register the plugin on the panel the user is onboarded in:

```php
use Wallacemartinss\FilamentOnboarding\FilamentOnboardingPlugin;

$panel->plugins([
    FilamentOnboardingPlugin::make()
        ->launcher()                        // floating checklist, every page
        ->tours()                           // guided tours
        ->launcherPosition('bottom-right'), // or bottom-left, top-right, top-left
]);
```

And on the panel where flows are authored — usually an admin panel:

```php
FilamentOnboardingPlugin::make()
    ->manageFlows()
    ->launcher(false)
    ->tours(false)
    ->navigationGroup(fn (): string => __('navigation.system')),
```

## Locales

List the locales you write content in:

```php
// config/filament-onboarding.php
'locales' => ['pt_BR', 'en', 'es'],
```

The panel then shows one tab per locale for every piece of text. At render time the package reads `app()->getLocale()` and falls back — exact locale, then the base language (`pt_BR` → `pt`), then the app fallback, then whatever is filled in. A user reading in Spanish sees Spanish; a user in a locale nobody wrote for still sees a usable checklist.

A plain string is treated as a translation key, so a flow can point at your own language files instead:

```php
'title' => 'onboarding.journey.title',   // resolved through __()
```

## Conditions

A step can be ticked off by hand, by reaching a URL, by your code — or by a **condition**: a named check the application registers and the panel picks from a dropdown.

```php
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;

Onboarding::condition('has_server', fn (User $user, ?Tenant $tenant): bool =>
    $tenant->servers()->exists()
);
```

Or as a class:

```php
// config/filament-onboarding.php
'conditions' => [
    'has_server' => \App\Onboarding\Conditions\HasServerCondition::class,
],
```

```php
class HasServerCondition implements OnboardingCondition
{
    public function isCompleted(Model $subject, ?Model $scope = null): bool
    {
        return $scope->servers()->exists();
    }
}
```

Conditions are evaluated for pending steps only, and the result is persisted the first time it passes — so an established account opens the checklist and finds its history already reflected there.

A condition that is no longer registered never completes a step; it goes quiet rather than throwing on every request.

## Tours

Give the element you want to spotlight a stable hook:

```php
Action::make('create')
    ->extraAttributes(['data-onboarding' => 'create-server']),
```

Then author a tour step in the panel with the selector `[data-onboarding="create-server"]`. Each stop takes a selector, a placement, a title and a body — and optionally a page, in which case the tour navigates there and resumes.

Selectors that no longer match do not break the tour: the page dims and the popover is centred, so the copy still reads.

## Making it look like your product

Three levels, cheapest first.

**1. Retheme with variables.** The stylesheet reads an override for every value it uses, so declaring these anywhere in your own CSS wins regardless of load order:

```css
:root {
    --fio-theme-accent: var(--color-kronn-600);
    --fio-theme-accent-dark: var(--color-kronn-400);
    --fio-theme-radius: 1rem;
    --fio-theme-panel-width: 24rem;
    --fio-theme-surface-dark: var(--color-gray-900);
}
```

Available: `--fio-theme-{accent,accent-soft,success,surface,surface-muted,border,text,text-muted,shadow,shadow-lg,radius,radius-sm,panel-width}`, each with a `-dark` counterpart for the dark variants.

**2. Replace the stylesheet.** Publish it, edit freely, then point the config at your copy:

```bash
php artisan vendor:publish --tag=filament-onboarding-styles
```

```php
// config/filament-onboarding.php
'styles' => [
    'enabled' => true,
    'path'    => resource_path('css/vendor/filament-onboarding/onboarding.css'),
],
```

```bash
php artisan filament:assets   # re-publishes the CSS Filament serves
```

Set `enabled => false` instead and no stylesheet ships at all — the markup keeps its `.fio-*` classes for you to dress from your own panel theme.

**3. Replace the markup.** Publish the views and rewrite them; the Livewire components keep working, since they only call `completeStep`, `skipStep`, `startTour` and `dismissFlow`:

```bash
php artisan vendor:publish --tag=filament-onboarding-views
```

## The dashboard widget

```php
use Wallacemartinss\FilamentOnboarding\Widgets\OnboardingChecklistWidget;

// In a Dashboard page or the panel's widgets()
OnboardingChecklistWidget::class,
```

It hides itself once the flow is finished or dismissed.

## Programmatic API

```php
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;

$onboarding = Onboarding::for($user, $tenant);   // or Onboarding::current()

$onboarding->complete('first-deploy');
$onboarding->skip('invite-team');
$onboarding->dismiss('kronn-journey');
$onboarding->reset('kronn-journey');

$flow = $onboarding->flow('kronn-journey');

$flow->percentage();     // 43
$flow->isCompleted();    // false
$flow->nextStep()->title();
```

Events are dispatched as progress is made — `StepCompleted` and `FlowCompleted`, both carrying the subject and the scope.

## Who onboards, and in what context

By default the subject is the authenticated user and the scope is the current Filament tenant. Override either:

```php
FilamentOnboardingPlugin::make()
    ->subject(fn () => auth()->user())
    ->scope(fn () => Filament::getTenant())
    ->urlParameters(fn (): array => ['tenant' => Filament::getTenant()?->slug]),
```

`urlParameters` fills `{placeholders}` in step URLs, so a step authored as `/app/{tenant}/servers/create` lands on the right tenant.

## Development

```bash
npm install && npm run build   # builds the Alpine tour + CSS into resources/dist
vendor/bin/pint
vendor/bin/phpunit
```

## License

MIT.
