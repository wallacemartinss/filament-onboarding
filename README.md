# Filament Onboarding

Database-driven onboarding for Filament v5: a progress checklist that follows the user across every page of the panel, guided spotlight tours, and steps that can be completed by watching a video.

Journeys are authored in the panel, not in code, so product people can rewrite them without a deploy. And steps bound to a **condition** catch up on their own, which means **users who signed up long before the journey existed enter it already half-done** instead of being told to do things they did years ago.

- **Checklist** — a floating progress button on every page (pages, resources, widgets), plus an optional dashboard widget.
- **Tours** — spotlight an element, explain it, move on. Tours cross pages: the runner navigates and picks up where it left off.
- **Progress page** — an optional page laying the journey out: what is done, what is next, what is left.
- **Images and videos** — S3, R2, local, YouTube, Vimeo. **Watch time is measured**, not guessed, and can complete the step.
- **Panel discovery** — destinations, pages and widgets come from dropdowns built out of your own panel. Nobody types a URL.
- **Any locale** — content is stored per locale and read back in whichever locale the user picked.
- **Multi-tenant** — progress is scoped, so the same user onboards separately in each tenant.

Requires PHP 8.2+, Laravel 12 and Filament v5. Runs on PostgreSQL and MySQL/MariaDB.

| Package | Filament |
|---|---|
| `^2.0` | v5 |
| `^1.0` | v4 (planned) |

> Maintaining or extending the package? Read **[ARCHITECTURE.md](ARCHITECTURE.md)** — the data model, panel discovery, the tour runner, the player, asset versioning, and the traps that will bite you if you "clean up" the wrong line.

---

## Installation

```bash
composer require wallacemartinss/filament-onboarding:^2.0

php artisan vendor:publish --tag=filament-onboarding-migrations
php artisan vendor:publish --tag=filament-onboarding-config
php artisan migrate
php artisan filament:assets
```

Register the plugin on the panel your users are onboarded in:

```php
use Wallacemartinss\FilamentOnboarding\FilamentOnboardingPlugin;

$panel->plugins([
    FilamentOnboardingPlugin::make()
        ->launcher()                        // floating checklist, on every page
        ->tours()                           // guided tours
        ->progressPage()                    // optional page laying the journey out
        ->launcherPosition('bottom-right')  // bottom-left, top-right, top-left
        ->modalPosition('center'),          // where images and videos open
]);
```

And on the panel where journeys are written — usually an admin panel:

```php
FilamentOnboardingPlugin::make()
    ->manageFlows()
    ->launcher(false)
    ->tours(false)
    ->navigationGroup(fn (): string => __('navigation.system'))
    ->navigationIcon('heroicon-o-map')
    ->navigationSort(70),
```

That is the whole install. Create a journey in the panel, add its steps, and it shows up.

### Publishing

| Tag | What it gives you |
|-----|-------------------|
| `filament-onboarding-config` | `config/filament-onboarding.php` |
| `filament-onboarding-migrations` | the four tables and the media columns |
| `filament-onboarding-views` | every Blade view — checklist, launcher, tour popover, media modal, progress page |
| `filament-onboarding-translations` | the UI strings, per locale |
| `filament-onboarding-styles` | the stylesheet, to restyle without forking |

> Publishing **views** or **translations** takes them over: from then on your copies win, and updates to the package no longer reach those files. Publish them when you mean to own them.

Assets (CSS and the Alpine components) are served by Filament from `public/`, so run `php artisan filament:assets` after installing and after upgrading. They are versioned by **content hash**, so a changed file always reaches the browser — no cache-busting on your side.

---

## How a step is completed

Every step declares how it finishes, and the checklist behaves accordingly — a step the user cannot tick off themselves is offered as a link, not as a checkbox.

| Mode | Finishes when |
|------|---------------|
| **Manual** | The user ticks it off. |
| **Condition** | A named check the application registered passes — *including retroactively*. |
| **Page visit** | The user reaches a URL (`*` wildcards allowed). |
| **Watching the video** | Enough of the step's video has been watched (90% by default). |
| **Programmatic** | Your code calls `Onboarding::for($user)->complete('key')`. |

### Conditions

A condition is a named question about the subject, registered by the application and picked from a dropdown when the step is written.

```php
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;

Onboarding::condition('has_server', fn (User $user, ?Tenant $tenant): bool =>
    $tenant->servers()->exists()
);
```

Or as a class — which is what you want when both panels need it (the admin panel to offer it in the dropdown, the app panel to evaluate it):

```php
// config/filament-onboarding.php
'conditions' => [
    'has_server' => \App\Onboarding\Conditions\HasServerCondition::class,
],
```

```php
use Wallacemartinss\FilamentOnboarding\Contracts\{HasConditionLabel, OnboardingCondition};

class HasServerCondition implements OnboardingCondition, HasConditionLabel
{
    // Without this, the panel shows the raw key.
    public static function label(): string
    {
        return __('onboarding.conditions.has_server');
    }

    public function isCompleted(Model $subject, ?Model $scope = null): bool
    {
        return $scope?->servers()->exists() ?? false;
    }
}
```

Conditions are evaluated only for pending steps, and the result is persisted the first time it passes — so an established account opens the checklist and finds its history already reflected there. A condition that is no longer registered never completes a step: it goes quiet instead of throwing on every request.

### Visibility: not every journey is for everybody

The same conditions decide who a flow, a step or a single tour stop is *for*. Pick one under **Visibility** and what it guards only exists for subjects the condition passes for.

This is how you gate onboarding on a plan. If Docker is not part of the free plan, the panel does not render the Docker card — and a tour stop pointing at it would spotlight empty space and advertise something the account cannot buy into. Guard the stop with `has_docker_feature` and the subject is walked from the traditional mode straight to the next stop, as if the Docker stop had never been written.

```php
Onboarding::condition('has_docker_feature', fn (User $user, ?Tenant $tenant): bool =>
    $tenant?->hasFeature('docker_services') ?? false
);
```

Three things follow from it, and they are deliberate:

- A hidden step is **not pending** — it is left out of the count, so the percentage is of the journey the subject can actually walk, and they can reach 100% without it.
- A condition that is **not registered hides** what it guards. If the application cannot answer "is Docker on this plan?", teaching Docker is the wrong default.
- A flow whose every step is hidden **is not shown at all**, rather than as a card at 0% that can never be finished.

Visibility and completion are different questions asked of the same registry: `has_docker_feature` decides *whether you see the step*, `has_server` decides *whether the step is done*. A step can use both.

---

## Panel discovery

Nobody types a URL. When a step or a tour stop needs a destination, the dropdown is built from the panel itself:

- **Resources** — every list and create page, labelled the way the panel labels them. Pages that need a record (edit, view) are left out, since onboarding has no record to hand them.
- **Pages** — every custom page, by its navigation label.
- **Widgets** — every widget of the panel, including the ones attached to a page rather than registered on it.

What gets stored is the **route name**, not the URL, so renaming a resource slug does not break a journey, and `{tenant}` is filled in at render time.

---

## Tours

A tour spotlights one element at a time, explains it, and moves on. It can cross pages: give a stop a page and the runner navigates there and carries on.

Three ways to point at something:

```
[data-onboarding="create-server"]        a CSS selector
@widget:App\Filament\...\StatsWidget     a widget, picked from the panel
@livewire:edit_password_form             any Livewire component, by name
```

The last one matters more than it looks. A form section rendered by a third-party plugin has no hook of its own, and **Filament builds a section's id from its heading — which is translated**. Anchoring to `#form.update-password::section` gives you a tour that works in English and silently spotlights nothing in Portuguese. Addressing the component sidesteps that entirely.

For anything you own, a stable hook is cheapest:

```php
Action::make('create')->extraAttributes(['data-onboarding' => 'create-server']),
```

A selector that no longer matches does not break the tour: the page dims and the popover is centred, so the copy still reads.

Tours report the stop the user reached, so a tour abandoned half-way shows as half-way — and can be resumed.

### A "how does it work?" button on the page itself

The launcher starts tours from the checklist. For the pages that deserve their own invitation, drop a header action on them:

```php
use Wallacemartinss\FilamentOnboarding\Actions\StartTourAction;

protected function getHeaderActions(): array
{
    return [
        StartTourAction::make('servers-tour'),
        Actions\CreateAction::make(),
    ];
}
```

It renders a "How does it work?" button (relabel it like any Filament action) that starts that one tour in place. The button only exists when the tour does: an unknown key, a step without a tour, or a journey hidden from this subject by a visibility condition all make it disappear — a plan that cannot see the journey never sees the invitation.

### Tours that walk through a wizard

The field a stop points at often does not exist yet: it lives on the next step of a wizard, or inside a section that is still closed. The tour handles both directions, without ever taking the mouse away from the user.

**The tour follows the user.** Advance the wizard and the tour advances with you — it watches the page, and when the element of a later stop shows up, that is where it goes.

**The tour brings the app along.** Give a stop an **"Advance with"** selector — the control that gets the application there — and pressing *Next* on the tour presses it:

```
[wire\:click="nextStep"]      the wizard's own next button
```

It is clicked only when the user moves on and the element is not on screen. If the application refuses (a required field is empty), the tour waits and says so, rather than pointing at nothing.

---

## Images and videos

A step can carry an image to show or a video to watch. Both open in a modal over the panel, from the checklist, the widget or the progress page.

**Images** are uploaded to the configured disk or addressed by URL, and show as a thumbnail on the step.

**Videos** come from an upload, a direct `.mp4`, YouTube or Vimeo — paste the link however it came, the id is dug out of watch, share and shorts URLs alike. Any other provider can be embedded in an iframe.

```php
// config/filament-onboarding.php
'media' => [
    'disk'       => env('FILESYSTEM_DISK', 's3'),   // S3, R2, local — any Laravel disk
    'directory'  => 'onboarding',
    'visibility' => 'private',                      // signs a temporary URL instead of exposing the file
    'url_ttl'    => 30,
],
```

A **private disk is signed at render time**, so a bucket kept closed stays closed.

### Watch time is real

The player reports where the subject actually is — the `<video>` element for a file, the IFrame API for YouTube, the SDK for Vimeo — every few seconds and once more on the way out. What is kept is the **furthest point reached**, so rewinding to rewatch does not undo the ground covered, and the video resumes where it stopped.

That makes the **Watching the video** completion mode possible: set the threshold (90% by default, since nobody sits through the credits) and the step completes itself when the subject gets there. Until then the card shows the partial percentage.

An iframe embed from an unknown provider cannot be measured, so nothing is invented about it: the video plays, and the step completes some other way.

### Where the modal opens

Centred by default. Docked in a corner, it leaves the page usable behind it — the point when the video is something to follow along with.

```php
FilamentOnboardingPlugin::make()->modalPosition('bottom-right'),
```

`center`, `top`, `bottom`, `top-left`, `top-right`, `bottom-left`, `bottom-right`. Any step may override the panel's choice.

---

## Where onboarding shows up

Three surfaces, all optional, all reading the same progress — tick a step off in one and the others update behind it.

### The floating checklist

`->launcher()` puts a progress button on every page of the panel — pages, resources, widgets alike, since it hangs off the body. With more than one journey, the panel shows tabs, so a finished journey never sits in front of an unfinished one.

### The dashboard widget

```php
use Wallacemartinss\FilamentOnboarding\Widgets\OnboardingChecklistWidget;

// In a Dashboard page, or the panel's widgets()
OnboardingChecklistWidget::class,
```

It hides itself once the journey is finished or dismissed.

### The progress page

The journey laid out in cards: a ring with the percentage, counters for done / left / skipped, the next step highlighted with its call to action, and a card per step showing its state and what to do about it.

```php
FilamentOnboardingPlugin::make()
    ->progressPage()
    ->progressPageSlug('getting-started')        // default: onboarding
    ->progressPageNavigation(
        label: fn (): string => __('Getting started'),
        icon: 'heroicon-o-map',
        group: fn (): string => __('Settings'),
        sort: 90,
    ),
```

`->progressPage(shouldRegisterNavigation: false)` keeps the page reachable by URL but out of the menu. The page also removes itself from the navigation once a user has no journey left.

**Finished is not the same as done with it.** A completed step still offers "watch again", "replay the video", "open again" — none of which undoes anything. Undo is offered separately, and never on a step bound to a condition: unticking it would be undone by the next render, because what it asks about is still true.

**Start over** clears what the user did — ticks, skips, tours and videos watched. It cannot clear what is simply true, so steps bound to conditions come straight back, and the page says so rather than looking broken.

---

## Programmatic API

```php
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;

$onboarding = Onboarding::for($user, $tenant);   // or Onboarding::current()

$onboarding->complete('first-deploy');
$onboarding->skip('invite-team');
$onboarding->uncomplete('first-deploy');
$onboarding->dismiss('getting-started');
$onboarding->restore('getting-started');
$onboarding->reset('getting-started');
$onboarding->handleVisit('/app/acme/servers/create');

$flow = $onboarding->flow('getting-started');      // or ->currentFlow()

$flow->percentage();            // 43
$flow->isCompleted();
$flow->nextStep()?->title();
$flow->steps;                   // StepState[]
```

`StepState` answers what the UI needs: `isCompleted()`, `isSkipped()`, `isPending()`, `percentage()`, `canReplay()`, `canUndo()`, `url()`, `tour()`, `media()`, `videoProgress()`, `tourProgress()`.

### Events

```php
use Wallacemartinss\FilamentOnboarding\Events\{StepCompleted, FlowCompleted};
```

Both carry the step/flow, the progress row, the subject and the scope — enough to award a credit, send an email, or ping a channel when somebody finishes onboarding.

### Command

```bash
php artisan onboarding:reset getting-started --subject=1 --scope=<tenant-id> --scope-model="App\Models\Tenant"
```

Wipes a subject's progress through a journey. Handy while writing one. Steps bound to a condition come straight back — the command says so.

---

## Who onboards, and in what context

By default the subject is the authenticated user and the scope is the current Filament tenant. Override either:

```php
FilamentOnboardingPlugin::make()
    ->subject(fn () => auth()->user())
    ->scope(fn () => Filament::getTenant())
    ->urlParameters(fn (): array => ['tenant' => Filament::getTenant()?->slug]),
```

`urlParameters` fills `{placeholders}` in step URLs, so a step written as `/app/{tenant}/servers/create` lands on the right tenant.

---

## Locales

List the locales you write content in:

```php
'locales' => ['pt_BR', 'en', 'es'],
```

The panel then shows one tab per locale for every piece of text. At render time the package reads `app()->getLocale()` and falls back — exact locale, then the base language (`pt_BR` → `pt`), then the app fallback, then whatever is filled in. A user reading in Spanish sees Spanish; a user in a locale nobody wrote for still sees a usable checklist.

A plain string is treated as a translation key, so a journey can point at your own language files instead:

```php
'title' => 'onboarding.journey.title',   // resolved through __()
```

---

## Making it look like your product

Three levels, cheapest first.

**1. Retheme with variables.** The stylesheet reads an override for every value it uses, so declaring these anywhere in your own CSS wins regardless of load order:

```css
:root {
    --fio-theme-accent: var(--color-brand-600);
    --fio-theme-accent-dark: var(--color-brand-400);
    --fio-theme-radius: 1rem;
    --fio-theme-panel-width: 24rem;
}
```

Available: `--fio-theme-{accent,accent-soft,success,surface,surface-muted,border,text,text-muted,shadow,shadow-lg,radius,radius-sm,panel-width}`, each with a `-dark` counterpart.

**2. Replace the stylesheet.**

```bash
php artisan vendor:publish --tag=filament-onboarding-styles
```

```php
'styles' => [
    'enabled' => true,
    'path'    => resource_path('css/vendor/filament-onboarding/onboarding.css'),
],
```

Then `php artisan filament:assets`. Set `enabled => false` and no stylesheet ships at all — the markup keeps its `.fio-*` classes for you to dress from your own panel theme.

**3. Replace the markup.**

```bash
php artisan vendor:publish --tag=filament-onboarding-views
```

The Livewire components keep working: they only call `completeStep`, `skipStep`, `startTour`, `openMedia`, `dismissFlow`, `restartFlow`.

### Icons

The icon field takes any Blade icon name — `heroicon-o-server`, `phosphor-rocket`. Add [`wallacemartinss/filament-icon-picker`](https://github.com/wallacemartinss/filament-icon-picker) and the field becomes a visual picker on its own; the package detects it and needs no configuration. Without it, the field stays a text input and everything else works the same.

---

## Configuration

`config/filament-onboarding.php`, in full:

| Key | What it does |
|-----|--------------|
| `locales` | Locales offered when writing content. |
| `fallback_locale` | Used when the reader's locale has no content. Defaults to the app fallback. |
| `conditions` | Named checks, as classes. |
| `cache` | Journey **definitions** are cached and flushed on write. Progress is never cached. |
| `media` | Disk, directory, visibility, signed-URL TTL, accepted types and size limits. |
| `modal` | Default position of the media modal. |
| `styles` | Ship the stylesheet, replace it, or turn it off. |
| `tables` / `models` | Rename the tables, or swap the models for your own. |
| `resource` | Navigation of the flow resource, when the plugin does not set it. |

---

## Upgrading

New versions can ship new migrations and new assets:

```bash
composer update wallacemartinss/filament-onboarding
php artisan vendor:publish --tag=filament-onboarding-migrations   # existing files are left alone
php artisan migrate
php artisan filament:assets
```

---

## Development

```bash
composer install
npm install && npm run build   # builds the Alpine components and copies the CSS into resources/dist
vendor/bin/pint
vendor/bin/phpunit
```

## License

MIT. See [LICENSE.md](LICENSE.md).
