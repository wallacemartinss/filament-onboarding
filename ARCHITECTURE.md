# Architecture & maintenance guide

Everything a human or an AI needs to maintain this package without reverse-engineering it.

Read the [README](README.md) first for *what* the package does. This document is about *how* it does it, *why* it was built that way, and the traps that cost us real debugging time. Where a decision looks odd, there is a paragraph explaining what broke before it was made.

---

## 1. Mental model

Three things, kept strictly apart:

| | Lives in | Written by | Cached |
|---|---|---|---|
| **Definitions** — journeys and their steps | `onboarding_flows`, `onboarding_steps` | the admin, through the Filament resource | **yes** (flushed on write) |
| **Progress** — what one subject has done | `onboarding_flow_progress`, `onboarding_step_progress` | the package, as the subject acts | **never** |
| **Conditions** — questions about the subject | the host application (PHP) | the developer | n/a |

A **subject** is who onboards (a `User`). A **scope** is the context progress belongs to (a `Tenant`). Both are morphs, so neither is hard-coded. Progress is `(subject, scope, step)`: the same user onboards separately in each tenant, and that is by design.

The package never asks "is this user new?". It asks "what has this subject done?", and a **condition** can answer for work done long before the journey existed. That single idea is why an existing account opens the checklist already half-way through instead of being told to do things it did years ago.

---

## 2. Map of the code

```
src/
├── FilamentOnboardingServiceProvider.php   Registers everything: config, views, translations,
│                                           migrations, command, assets, Livewire components,
│                                           default resolvers, conditions from config.
├── FilamentOnboardingPlugin.php            Per-panel configuration. Registers the resource,
│                                           the progress page, and the BODY_END render hook.
│
├── OnboardingManager.php                   Singleton. Owns definitions (+cache), the condition
│                                           registry, and the subject/scope/url resolvers.
├── SubjectOnboarding.php                   The engine. One subject's view of everything:
│                                           reads progress, evaluates conditions, writes progress.
├── States/FlowState.php                    A journey, as this subject sees it.
├── States/StepState.php                    A step, as this subject sees it. What the views ask.
│
├── Conditions/ConditionRegistry.php        Named checks. Closures or classes.
├── Contracts/OnboardingCondition.php       The check itself.
├── Contracts/HasConditionLabel.php         So the panel shows a name, not a raw key.
│
├── Support/PanelTargets.php                Panel discovery: pages, routes, widgets. §6
├── Support/TranslatableText.php            Locale resolution chain. §10
├── Support/MediaUrl.php                    Disk → URL, signing private disks. §8
├── Support/VideoEmbed.php                  Any YouTube/Vimeo link → provider + id. §8
├── Support/IconInput.php                   Optional icon-picker integration. §11
│
├── Assets/HasContentVersion.php            Cache-busting by file hash. §9 — READ THIS
├── Livewire/OnboardingLauncher.php         Hosts the checklist, the tour runner, the media modal.
├── Widgets/OnboardingChecklistWidget.php   The checklist as a dashboard card.
├── Pages/OnboardingProgress.php            The progress page.
├── Concerns/InteractsWithOnboarding.php    Behaviour shared by all three surfaces.
│
├── Commands/ResetOnboardingCommand.php     onboarding:reset
├── Events/{StepCompleted,FlowCompleted}.php
├── Enums/                                  StepType, CompletionMode, MediaType, MediaSource,
│                                           ModalPosition
└── Resources/OnboardingFlows/              The admin resource (4-tab step form). §11

resources/
├── js/onboarding-tour.js                   The tour runner. §7
├── js/onboarding-media.js                  The player. §8 — READ THE COMMENTS
├── css/onboarding.css                      Self-contained. `.fio-*`, themable via --fio-theme-*
├── views/                                  Blade for every surface
└── lang/{en,pt_BR,es}/onboarding.php
```

`resources/dist/` is **build output** (esbuild). Never edit it; run `npm run build`.

---

## 3. Data model

Four tables. Names configurable (`config('filament-onboarding.tables')`), models swappable (`config('...models')`).

### `onboarding_flows`
`key` (unique), `panel_id` (nullable = every panel), `title`/`description` (JSON, one entry per locale), `icon`, `color`, `is_active`, `is_dismissible`, `sort_order`, `visibility_condition`.

### `onboarding_steps`
`flow_id`, `key` (unique within flow), `type` (task|tour), `title`/`description`/`cta_label`/`media_caption` (JSON per locale), `icon`, `completion_mode`, `condition_key`, `visibility_condition`, `visit_url`, `cta_route`, `cta_url`, `tour_steps` (JSON), media columns (`media_type`, `media_source`, `media_disk`, `media_path`, `media_url`, `modal_position`, `video_completion_threshold`), `is_required`, `is_active`, `sort_order`.

Note the two condition columns, which are asked different questions of the same registry: `condition_key` answers *is this step done*, `visibility_condition` answers *is this step for you at all* (section 5b). A single tour stop inside `tour_steps` carries its own optional `condition`, evaluated the same way.

### `onboarding_flow_progress` / `onboarding_step_progress`
`subject_type`+`subject_id`, `scope_type`+`scope_id` (nullable), timestamps (`completed_at`, `skipped_at`, `seen_at`, `dismissed_at`, `started_at`), and `meta` (JSON) which carries **tour and video progress**:

```json
{ "tour_index": 3, "tour_total": 6,
  "video_seconds": 30.5, "video_duration": 634.6, "video_percent": 5,
  "completed_by": "condition|tour|video" }
```

### Why the morph columns have explicit lengths

```php
$table->string('subject_type', 191);
$table->string('subject_id', 64);
```

The progress tables are unique on `(step, subject, scope)`. With MySQL's default `utf8mb4`, a five-column index over 255-char strings is **4224 bytes** — past InnoDB's 3072-byte limit, and `CREATE TABLE` fails with `ERROR 1071`. At 191/64 the key is ~2.2 KB and still holds any class name and any UUID or integer id. PostgreSQL does not care either way, which is exactly why this would have shipped broken had it only ever been run on Postgres. **Keep the lengths.**

Ids are stored as strings so the package works whether the host uses UUIDs or auto-increments.

---

## 4. How a page render works

```
View asks for flows
   └─ Onboarding::current()                      OnboardingManager
        ├─ resolveSubject()  → auth user         (resolvers set in the ServiceProvider,
        └─ resolveScope()    → Filament tenant    overridable per panel by the plugin)
   └─ SubjectOnboarding::flows($panelId)
        ├─ manager->flows()          definitions, from cache (1 query on miss, with steps)
        ├─ syncConditions()          ← the retroactive magic, see below
        ├─ flowProgress()            1 query, memoised per instance
        ├─ stepProgress()            1 query, memoised per instance
        └─ map → FlowState[ StepState[] ]
```

Two progress queries per request, regardless of how many journeys or steps. Definitions are cached.

**`syncConditions()`** runs once per panel per `SubjectOnboarding` instance. For every *pending* step whose mode is `Condition`, it calls the registered check; if it passes, it **persists** the completion (`meta.completed_by = 'condition'`). Consequences worth knowing:

- The first render for a given subject may write rows. That is intended.
- Passing conditions are never re-evaluated (the step is completed, so it is skipped next time).
- A condition that is **not registered** never completes anything. It goes quiet rather than throwing on every request — a journey authored against a condition the app later dropped must not take the panel down.

---

## 5. Completion, and the one distinction that matters

`CompletionMode`: `Manual`, `Condition`, `Visit`, `Video`, `Programmatic`.

`Visit` is settled in `OnboardingLauncher::mount()` → `handleVisit(request()->path())`. `mount()` only runs on a real page request, not on Livewire updates, which is why the check is there and not in `render()`.

### `isCompleted()` vs `isFinished()` — do not conflate these

```php
$flow->isCompleted();  // every REQUIRED step is done
$flow->isFinished();   // NOTHING is pending — optional steps included
```

- `isCompleted()` decides `completed_at` on the flow progress row and the `FlowCompleted` event.
- **`isFinished()` is what the UI must ask** before it says "you are all set", and what `currentFlow()` uses to decide which journey to show.

This exists because of a real bug. The "Connect your cloud provider" journey had one required step (a condition: *has a provider*) and two optional ones (a video and a tour). A tenant that already had providers saw the journey announce itself as **finished** — the launcher replaced the step list with "You are all set" and **buried its own video and tour**, which is precisely what a user opens onboarding for. Having the data is not the same as having seen the tutorial. If you touch the UI's notion of "done", ask `isFinished()`.

---

## 5b. Visibility — who a step is *for*

`SubjectOnboarding::isVisible(?string $conditionKey)` is the single gate, and it is applied in three places:

| Guarded thing | Column | Filtered in |
|---|---|---|
| Flow | `onboarding_flows.visibility_condition` | `SubjectOnboarding::flows()` |
| Step | `onboarding_steps.visibility_condition` | `SubjectOnboarding::state()` |
| Tour stop | `tour_steps[].condition` | `StepState::tour()`, via the `visibilityResolver` closure the engine hands the state |

The motivating case: a panel renders the Docker and Kubernetes mode cards only for tenants whose plan carries the feature. A tour stop pointing at a card that is not on the screen would spotlight nothing, and would advertise a feature the account cannot use. Guarding the stop with `has_docker_feature` removes it before the tour ever reaches the browser — the runner is never told it existed, so numbering, "3 / 12" and the Next button all stay coherent. (Which is also why the stop titles must not be hand-numbered: on a free account the tour would count 8, 12, 13.)

Three rules, each of which a change here tends to break:

1. **Hidden is not pending.** A hidden step is dropped from `state()` before the steps are counted, so it does not hold the percentage below 100 and does not keep a journey unfinished. `syncConditions()` skips it too — an invisible step is never completed behind the subject's back.
2. **An unregistered condition hides.** Same posture as completion (a missing condition never completes), inverted to the safe side: if the application cannot answer the question, do not teach the feature.
3. **A flow with no visible step is not shown at all** (`flows()` drops `total() === 0`), rather than appearing as a card at 0% that can never be finished.

---

## 6. Panel discovery (`Support/PanelTargets.php`)

Authoring a step must never mean typing `/app/{tenant}/servers/create` from memory. Everything is discovered from the panel.

### `pageOptions(?string $panelId): array`

Grouped `[group => [routeName => label]]`, built from `$panel->getResources()` and `$panel->getPages()`.

- **Route names are stored, not URLs.** Renaming a resource slug then does not break a journey, and `{tenant}` is filled at render time.
- Pages that need a **record** (`edit`, `view`) are filtered out by `isReachable()`, which reads the actual route:

  ```php
  $unknown = array_diff($route->parameterNames(), ['tenant']);
  return blank($unknown);
  ```

  Onboarding has no record to hand them; offering those routes would guarantee a broken link.
- Labels come from `$resource::getPluralModelLabel()` and `$page::getNavigationLabel()`, both wrapped in `safely()` — a third-party resource that throws while labelling itself must not take the whole dropdown down.

### `widgetOptions(?string $panelId): array`

Two sources, and the second is not optional:

1. `$panel->getWidgets()` — widgets registered on the panel.
2. **A scan of `$panel->getWidgetDirectories()` / `getWidgetNamespaces()`.**

Why the scan: Filament widgets attached to a *page* rather than to the panel declare `protected static bool $isDiscovered = false`, and therefore **never appear in `$panel->getWidgets()`**. In the app this package was built for, `getWidgets()` returned an **empty array** — every widget was page-attached. Relying on the getter alone yields an empty dropdown in exactly the projects that have the most widgets. The scan mirrors what Filament's own `discoverComponents()` does: walk the directory, build the FQCN, keep non-abstract subclasses of `Widget`.

### `widgetSelector()` and `livewireName()`

```php
PanelTargets::widgetSelector(SomeWidget::class);   // "@widget:<livewire component name>"
```

The DOM carries the component's **name**, and a name is *not* always the class:

- A Filament widget is registered by class → the name **is** the FQCN.
- A component registered under an alias (`edit_password_form`, `multi_factor_authentication`) → the name is the **alias**.
- A component in `app/Livewire` → the name is kebab-case (`delete-account-section`).

So the selector always goes through `app('livewire.factory')->resolveComponentName($class)`. Hard-coding the class would work for widgets and silently fail for everything else.

---

## 7. The tour runner (`resources/js/onboarding-tour.js`)

An Alpine component mounted by the launcher, therefore present on **every** page of the panel.

### Lifecycle

```
Livewire: startTour(stepKey)
   └─ dispatch('onboarding-tour-start', {key, steps})
        └─ runner.start()
             ├─ navigateIfNeeded(0)   stop lives elsewhere? → persist + window.location.assign
             └─ render()              find element → scroll → spotlight → position popover
                  └─ report()          Livewire.dispatch('onboarding-tour-progress', {key, index, total})

on next page load: init() → resume()   ← reads sessionStorage, continues at the stored index
last stop → finish() → Livewire.dispatch('onboarding-tour-finished', {key}) → step completed
skip()   → close()  → step left pending, progress kept (the stop reached is remembered)
```

Cross-page state lives in `sessionStorage['filament-onboarding.tour']` as `{key, steps, index, path}`. It is cleared on finish/skip.

**`path` is not decoration.** It is the page the tour is parked on. `resume()` compares it with `window.location.pathname` and *ends the tour* when they differ. Without it, a tour survives the browser's back button and the sidebar: the runner comes up on a page where the stop's element will never exist, the popover centres itself, and the subject gets the profile tour floating over the server list. Every write to storage sets `path` — `persist()` with the current page, `navigateIfNeeded()` with the page it is sending the subject to.

### The load-bearing question: is the element *on screen*?

Everything the runner does hangs off `find()`. And the naive answer — `document.querySelector` — is **wrong against Filament itself**:

- A **wizard** renders *every* step and hides the inactive ones with `visibility: hidden; height: 0` (see `.fi-sc-wizard-step:not(.fi-active)` in Filament's theme). The fields of step 3 are in the DOM while the subject is on step 1.
- A **collapsed section** is `display: none`. Same story.

A selector matches both. Trust it and the whole design inverts: the spotlight is drawn around an invisible field (a 16px box in the corner of the screen, because the rect is degenerate), `waiting` never engages, and `advance` never fires — it only clicks when the element is *missing*, and the runner believed it was there.

So `find()` filters every match through `isOnScreen()`: no boxes (`getClientRects().length === 0`, which is what `display: none` gives you), `visibility: hidden` (inherited, which is what catches the wizard pane), or `display: none` means **not found**. And `observeFor()` watches `attributes` as well as `childList`, because a wizard step is revealed by flipping a *class* and a section by flipping a *style* — neither inserts a node, and an observer watching only `childList` waits forever for something that already happened.

### The three things a naive runner gets wrong

**1. Measuring before the scroll lands.** `element.scrollIntoView({behavior: 'smooth'})` is *asynchronous*. Reading `getBoundingClientRect()` on the next line gives the position the element had **before** the page moved, so the spotlight is drawn around whatever used to be there — the field above, typically. `scrollIntoView()` here returns a promise that resolves through `whenStill()`: the rectangle is polled per frame until it repeats twice, or `SCROLL_TIMEOUT` runs out. Never measure without awaiting it.

**2. Positioning a box whose size you guessed.** The popover's height depends on the copy and its width on the viewport. `placePopover()` awaits `$nextTick()`, reads `offsetWidth`/`offsetHeight` off the real node, and clamps inside the viewport — below, above, or *beside* the element, in that order. The old code used a 320px constant and last render's height, which is how the "next" button ended up off the right edge of the screen. (The CSS caps the box at `calc(100vh - 2rem)` and scrolls it, so long copy on a short screen cannot push the buttons out either.)

**3. Pointing at an input nobody can see.** A styled toggle keeps its real `<input>` `sr-only` behind the control the subject looks at — spotlighting it draws a dot next to the thing it means. `resolveTarget()` walks from the matched element to its `<label>` (the whole row, for a toggle), and failing that up the tree until an ancestor has real size. **The guard on that walk matters as much as the walk**: a *visible* input must be returned as-is, because climbing from it finds its caption label — the sliver of text above the field — and for one release the spotlight sat on the word "Name" instead of the name field. Substantial element first, climbing only for the hidden ones.

**3b. Rendering twice at once.** The runner's own smooth scroll fires scroll events, the scroll listener fires renders, and two async renders interleave — the *older* one finishes last and writes a stale rectangle. Every render takes a `renderToken` and checks it after each await; only the newest may commit. The scroll listener is also rAF-debounced.

### What the scroll listener may do (and what it may not)

Scroll and resize call `measure()` — re-measure the spotlight where the element is now, and nothing else. They must **never** call `render()`:

- `render()` scrolls the element into view, and "in view" means the *whole* element. Nudging the page one pixel snapped the subject back to centre: they were jailed to the spotlight, unable to read around it.
- `render()` calls `report()`, which is a Livewire round-trip, a database write, and a re-render of every surface showing onboarding. Driven by scroll, that is ~60 of those *per second*. `report()` is now also gated on the stop actually having changed.

`close()` bumps `renderToken` and clears the pending `waitForElement` polls, so a render already in flight cannot scroll the page under a subject who just dismissed the tour.

### Tour and application, walking together (`advance` + `firstStopOnScreenAhead()`)

A wizard has its own cursor, and the tour has another. Left alone they drift: the tour explains "the server name" while the subject is still on the provider step, pointing at a field that is not in the DOM.

Both directions are handled, and neither takes control away from the subject:

- **Application → tour.** When the current stop's element is missing, the `MutationObserver` also looks *ahead*: if a later stop's element is on screen, the subject has moved past this one, and the tour jumps to them. Advancing the wizard advances the tour.
- **Tour → application.** A stop may carry `advance`, a selector for the control that gets the app there (`[wire\:click="nextStep"]`). It is clicked in `next()` — on the subject's intent, never on autopilot, and never when the element is already on screen.

While waiting, `waiting` is true: the popover says so, and **the next button is disabled** (the guard in `next()` also covers the arrow key). A waiting tour cannot be paged through — pressing next five times on a form that has not moved would walk the popover through five stops of nothing. The way forward is the form; the observer brings the tour along. Escape hatches stay live: back, and skip.

### Selector resolution (`find()`)

Three forms, in this order:

1. `@widget:<name>` / `@livewire:<name>` → `[wire\:name="<name>"]`, falling back to parsing `wire:snapshot` (`memo.name`) for older Livewire. **Livewire v4 puts the name straight on the element**; the snapshot-only version of this function silently found nothing, which presents as "the tour explains but never highlights".
2. Any CSS selector, via `document.querySelector` inside a `try` — a selector the browser cannot parse must not throw mid-tour.
3. Nothing found → `waitForElement()` polls for 3 s (the element may be a Livewire render away), then gives up gracefully: the page dims, the popover is centred, the copy still reads. **A tour never breaks; at worst it stops pointing.**

### Spotlight

A single fixed div with `box-shadow: 0 0 0 9999px rgba(0,0,0,.6)` — the "hole" is the element's rect plus 8 px. Cheap, and it animates. The popover is placed below, else above, else centred, always clamped to the viewport. Repositioned on `scroll` and `resize`.

---

## 8. Media & the player (`resources/js/onboarding-media.js`)

Handles images and four video providers: an uploaded/direct file (`<video>`), YouTube (IFrame API), Vimeo (SDK), and any other iframe (**no tracking — and nothing is invented about it**).

Watch time is reported every 5 s and once more on pause/end/close. `SubjectOnboarding::recordVideoProgress()` keeps the **furthest point reached** (`max()`), so rewinding to rewatch does not undo the ground covered, and the video resumes where it stopped. If the step's mode is `Video` and the percentage crosses `video_completion_threshold` (default 90 — nobody watches the credits), the step completes itself.

### Three bugs that will come back if you "clean up" this file

**1. `wire:ignore` on the modal root is load-bearing.**
The modal lives inside a Livewire component. Every Livewire round trip morphs the DOM — including the `<iframe>` the video API just created. The player is torn out mid-playback and the API says so: *"The YouTube player is not attached to the DOM."* `wire:ignore` keeps Livewire out of that subtree. The modal is pure Alpine and needs nothing from Livewire's re-render.

**2. The iframe `src` must not be reactive.**
An earlier version built the URL from `this.seconds` (`&start=${seconds}`). Alpine rewrote the `src` on every tick of the watch timer, which reloaded the iframe, which detached the player, which restarted the video — forever, reporting nothing. The URL is computed **once** in `show()` (`buildSrc()`), into a non-reactive `src` property.

**3. The API must build its own iframe.**
`new YT.Player(existingIframe)` does not complete the handshake here: the object exists, `playVideo` never appears, `onReady` never fires. The player is given a `<div>` mount (`x-ref="youtube"`) and replaces it with its own iframe. That only survives because of (1).

Also: autoplay is blocked without a user gesture (`getPlayerState() === 5`, "cued"), so a test that expects playback must click the player.

### `MediaUrl` and private disks

An upload on a **private** disk is signed with `temporaryUrl()` (TTL from config). Local disks cannot sign, so it falls back to `url()` rather than showing a broken image. A bucket kept closed stays closed.

### `VideoEmbed`

Takes whatever was pasted — watch, share, shorts, embed, live, or a bare id — and returns provider + id. Add a provider here and in `MediaSource`, then teach `onboarding-media.js` to mount it and (if possible) report time; if it cannot report, `tracksWatchTime()` must return `false`.

---

## 9. Assets & cache-busting — read before touching CSS/JS

Build: `npm run build` (esbuild) → `resources/dist/{js,css}`. Publish: `php artisan filament:assets` → `public/{js,css}/wallacemartinss/filament-onboarding/`.

Filament stamps asset URLs with `?v={composer version of the package}`. That is fine for a tagged release and **useless everywhere else**: during development the version does not move, and a package installed from a **path repository** reports something like `dev-main` forever. The consequence is nasty and quiet: `filament:assets` publishes the new CSS, the browser keeps the old file, and the page renders half-styled. We lost an afternoon to a progress ring the size of the viewport because of exactly this.

Fix (`Assets/HasContentVersion.php`): `VersionedCss` and `VersionedAlpineComponent` override `getVersion()` to return `substr(md5_file($path), 0, 12)`. **The URL now changes exactly when the file changes** — no version bumps, no hard reloads.

If you register a new asset, use the `Versioned*` classes.

---

## 10. Locales

Translatable columns are JSON maps: `{"pt_BR": "…", "en": "…"}` — the same shape spatie/laravel-translatable uses, but without the dependency.

`TranslatableText::resolve()` tries, in order: the exact locale, its variations (`pt_BR` → `pt-BR` → `pt`), the configured fallback (and *its* variations), then the first non-empty value. A user in a locale nobody wrote for still sees a usable checklist.

A **plain string** (not an array) is passed through `__()`, so a journey can point at the host's language files instead of storing copy in the database.

### The trap: Filament section ids are translated

Filament builds a `Section`'s DOM id from its **heading**. The same section is:

```
form.update-password::data::section        (en)
form.atualizar-senha::data::section        (pt_BR)
form.actualizar-contrasena::data::section  (es)
```

A tour anchored to that id **works in one language and silently spotlights nothing in the others** — in a package whose whole promise is "any locale". Field ids (`form.email`, `form.locale`) come from the state path and are stable; sections must be addressed by their **Livewire component** (`@livewire:edit_password_form`). The guard for this is a test that renders the page in every locale (§12).

---

## 11. Surfaces, and how they talk to each other

Three surfaces share `Concerns/InteractsWithOnboarding`: the **launcher** (Livewire, injected into `PanelsRenderHook::BODY_END` by the plugin, so it is on every page), the **widget**, and the **progress page**.

The launcher is special: it **hosts the tour runner and the media modal**. Any surface can ask to open a video (`openMedia`) or start a tour (`startTour`); the launcher is what actually renders them, which is why they work from a dashboard widget as well as from the page.

Events on the wire:

| Event | From → To | Why |
|---|---|---|
| `onboarding-updated` | any surface → all | Tick a step in the launcher, the widget behind it refreshes. |
| `onboarding-tour-start` | Livewire → runner | Hands the tour over. |
| `onboarding-tour-progress` | runner → launcher | The stop reached. |
| `onboarding-tour-finished` | runner → launcher | Completes the step. |
| `onboarding-media-open` | Livewire → modal | Hands the media over. |
| `onboarding-video-progress` | player → launcher | Watch time. |

The **admin resource** (`Resources/OnboardingFlows/`) is registered only on panels that call `->manageFlows()`. The step form is four tabs (content / behaviour / media / tour) because it used to be four stacked sections, most of them empty for any given step. `IconInput` returns the icon-picker field when `wallacemartinss/filament-icon-picker` is installed, and a plain text input otherwise — the dependency is `suggest`, never `require`, so the package does not force it on anyone.

---

## 12. Testing

Two suites, and they are not interchangeable:

- **`packages/filament-onboarding/tests`** (Orchestra Testbench) — **this is the only one that ships**. Anyone installing from Packagist gets these and nothing else. If you add a feature to the package, the test belongs here.
- The host app's suite (in this repo, `tests/Feature/Onboarding`) covers the integration: real panels, real resources, real routes.

Three guards are worth keeping and copying into any host app:

1. **Every step's route still exists** (`Route::has()`), otherwise a renamed slug silently breaks a journey.
2. **Every tour stop still finds its element — in every locale.** Renders the real page and asserts the selector matches. This is what catches the translated-section-id trap and a renamed Livewire component.
3. **The README's promises hold** (`PackageTest`): the five publish tags, the command, the assets, the Livewire components and every documented config key exist.

- **`tests/js`** (vitest + jsdom) — the runner. Its core question ("is this element on screen?") is answered against stubbed boxes, because jsdom does no layout, and that is exactly the decision that was wrong for a release: a wizard field the subject cannot see was treated as found. `npm test`, and CI runs it.

What tests still cannot catch: anything that only exists in a real browser. The player bugs of §8, the Livewire morph, autoplay policy — all were found by driving Chrome. When touching the runner or the player, **open a browser too**.

`resources/dist` is committed, and CI fails if it is stale — Filament serves the built files and versions them by content hash, so forgetting `npm run build` ships old behaviour behind a perfectly convincing cache-busting URL.

---

## 13. Recipes

**Add a completion mode.** `Enums/CompletionMode.php` → handle it wherever progress is written (`SubjectOnboarding`) → expose its field in `StepsRelationManager::behaviourFields()` (visible only for that mode) → translations in three files → a test.

**Add a video provider.** `Enums/MediaSource.php` (and `tracksWatchTime()`) → `Support/VideoEmbed.php` (parse the link) → `onboarding-media.js` (`mount<Provider>()`) → the `<template>` in `views/components/media.blade.php` → `npm run build`.

**Add a surface.** Use `InteractsWithOnboarding`, listen to `onboarding-updated`, render `views/components/steps.blade.php`. Do **not** re-host the tour runner or the media modal — the launcher already does, once.

**Add a config key.** `config/filament-onboarding.php` → document it in the README table → add it to `PackageTest::test_the_config_has_every_key_the_readme_documents`.

**Rename a table or swap a model.** `config('filament-onboarding.tables'/'models')`. Models read their table from config; never hard-code a table name.

---

## 14. Debugging playbook

| Symptom | First thing to check |
|---|---|
| Styles half-applied, giant ring, unstyled tabs | Is the browser holding an old CSS? `?v=` must be a 12-char hash (§9). Then `npm run build && php artisan filament:assets`. |
| Tour explains but never highlights | The selector found nothing. In the console: `document.querySelector('[wire\\:name="x"]')`. Remember §10 (translated section ids). |
| Tour vanishes after navigating | `sessionStorage['filament-onboarding.tour']` — is it there? Is `resume()` running (`Alpine.$data(el).active`)? |
| Video plays but nothing is recorded | Is the provider trackable? Did `wire:ignore` survive an edit? Is the `src` reactive again (§8)? |
| Video restarts forever | The `src` is reactive. §8, bug 2. |
| A journey says "all set" with steps left | Something is asking `isCompleted()` where it should ask `isFinished()`. §5. |
| A step nobody can complete | Its condition is not registered → it never passes. Check `config('filament-onboarding.conditions')`. |
| Widget dropdown empty when authoring a tour | `$isDiscovered = false` widgets. The directory scan in `PanelTargets` should cover it — check the panel's widget directories. |
| Migration fails on MySQL (`ERROR 1071`) | Someone widened the morph columns. §3. |
| Journey does not show up at all | `is_active`? `panel_id` matching the panel? Any steps at all? (A journey with zero steps is invisible — the resource flags it in red.) |

Useful one-liners in the browser console:

```js
Alpine.$data(document.querySelector('[x-data="onboardingTour"]'))    // runner state
Alpine.$data(document.querySelector('[x-data="onboardingMedia"]'))   // player state
sessionStorage.getItem('filament-onboarding.tour')                   // tour in flight
```

And in tinker:

```php
Onboarding::for($user, $tenant)->flow('journey')->percentage();
Onboarding::conditions()->options();          // what the panel offers
Onboarding::flushCache();                     // after editing definitions by hand
```

---

## 15. Invariants — break these and something quietly stops working

1. Progress is **never** cached. Definitions **are**, and every write to a flow or a step flushes them (`booted()` in the models).
2. The UI asks `isFinished()`, not `isCompleted()`.
3. Route names are stored, never URLs.
4. Section ids are translated; component names are not.
5. `wire:ignore` stays on the media modal.
6. The iframe `src` is computed once and is not reactive.
7. Morph columns keep their lengths (191/64).
8. Assets are versioned by content hash.
9. A hidden step is **not pending** — it is out of the count, not waiting in it. And an unregistered visibility condition **hides**, where an unregistered completion condition never completes: both fail towards not teaching a feature the account may not have.
10. Tour stops are **not hand-numbered** in their titles. A guarded stop is removed for accounts that cannot see it, and the numbers would skip.
11. The tour's rectangle is read **after** the scroll settles, and the popover is placed from **measured** size. Both are async; both were bugs.
12. The parked tour remembers its page. Resume anywhere else **ends** the tour.
13. **Every browser-reachable method re-asks what the interface asked.** The engine is the trusted API and questions nothing; the surfaces question everything (`Concerns/InteractsWithOnboarding`). A public method on a Livewire component is a network endpoint.
14. **A condition that throws answers "no".** The launcher is in the layout of every page; an exception here is a 500 on the whole product.
15. **The scope is captured at mount, `#[Locked]`, never re-resolved on an update.** A lost tenant does not throw — it writes to the wrong row, silently.
16. **The absence of a scope is an empty string, not a NULL.** A NULL in a unique index enforces nothing.
17. **A hidden element is not a found element.** Filament keeps hidden wizard steps in the DOM.
18. A missing condition, a missing element, a missing route: **degrade, never throw**. Onboarding is not the product; it must never take the panel down with it.
