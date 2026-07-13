# Changelog

All notable changes to `filament-onboarding` are documented here.

Versions follow Filament: **2.x targets Filament v5**, and 1.x is reserved for a Filament v4
backport. That is why the first release is 2.0.0 — there is no 1.0.0 to upgrade from.

## 2.2.2

### Fixed

- **`StartTourAction` with one tour asked which of the one.** It always set a modal heading,
  and Filament opens a modal for that alone — `shouldOpenModal()` asks
  `hasCustomModalHeading()` long before it looks at whether there is a schema to show. So the
  common case, `StartTourAction::make('servers-tour')`, opened an empty modal asking "which
  tutorial do you want?" and offering no answer. The modal is now turned off unless there is
  more than one tour to choose between — including when the list names several and only one
  of them exists.

---

## 2.2.1

**Required on Laravel 13 — and on any Laravel 12.63+ whose cache config is current.**

### Fixed

- **The definitions cache took the whole panel down on the second request.** Laravel will not
  unserialize classes out of the cache unless the application lists them: a stock Laravel 13
  ships `cache.serializable_classes => false`, so that a leaked `APP_KEY` cannot be turned
  into a gadget chain through the cache. The manager cached a `Collection` of Eloquent
  models — which writes perfectly well, and reads back as `__PHP_Incomplete_Class`. The first
  request of the panel's life populated the cache and worked; every request after it died on
  the return type of `cachedFlows()`, which is a **500 on every page**, since the launcher
  renders in the layout. Definitions are now cached as plain attributes and rebuilt on the
  way out — casts, relations and translatable columns intact — so nothing in that cache is an
  object. A cache holding the old shape reads as a miss and is overwritten, so upgrading
  needs no `cache:clear`.

### Changed

- **The progress page folds.** It used to open every step of every journey at once: a wall of
  cards, most of them about work already done, with the one thing the person came for buried
  among them. Each journey is now a collapsible section whose header carries the ring, the
  count and the next thing to do — which, for a folded journey, is usually all anyone wants.
  A journey with something pending opens; a finished one starts folded; and the choice is
  remembered per journey, so a completed one that gets opened stays open.

---

## 2.2.0

**The release to upgrade to, from anywhere.** The 2.0.0 → 2.1.0 migration now survives the
duplicate rows 2.0.0 left behind, so nothing stands between 2.0.0 and here.

### Added

- **A welcome screen** (`->welcome()`) — shown once, on the first page after logging in, with
  "get started", "not now" (this session) and "do not show this again" (which takes the
  floating button and its ring with it). Reversible from the progress page.
- **`StartTourAction` accepts several tours** — hand it a list and it asks which one, naming
  each by its own title; a screen can be walked more than one way. With a single tour there
  is nothing to ask, and it starts outright.
- A tour stop can be marked **optional**: about something an account may not have yet (a
  tag, an empty chart), the tour steps aside instead of waiting for it.

### Security

- **Finishing a tour no longer completes a step whose mode says otherwise.** Any step
  carrying a tour — including one bound to a *condition* ("has two-factor") or marked
  *programmatic* ("only my code completes this") — could be completed from the browser by
  clicking "next" to the end: a second front door around the very checks `completeStep()`
  refuses. A tour now completes only a **Manual** step, where the tour is the task; for
  every other mode it records that the tour was watched and leaves completion to the mode.

### Fixed

- **The scope-hardening migration survives the data it was written for.** An installation
  that ran 2.0.0 can hold duplicate progress rows — the NULL scopes the old unique index
  could not compare. Converting those NULLs to `''` made the duplicates collide with the
  index mid-`UPDATE`, and `php artisan migrate` died on precisely the installations that
  needed it. Duplicates are now merged first: the oldest row stays and inherits the
  furthest the others got — the latest of each timestamp, the union of the metas.
- **"Get started" on the welcome screen no longer races itself.** It was a link that also
  fired a Livewire call: the browser navigated while the request recording the answer was
  still in the air, and when the write lost, the welcome greeted the subject again — on
  the very page it had just sent them to. One button, one request: the answer is recorded,
  *then* the server redirects.
- **Opening a video while another is up no longer crosses the wires.** A docked modal
  leaves the page usable, so a second video can be opened over a first — whose player was
  never torn down: its poll kept reporting the *old* video's seconds under the *new*
  step's key, and the new player often failed to build at all (the provider's iframe had
  replaced the mount the template still pointed at). The leaving player now settles its
  account — reports under its own key, tears down — and the markup is re-stamped fresh.
- **A step key is only accepted once per flow.** The database always said so; the form now
  says it as a validation message under the field instead of a `QueryException` over the
  whole modal.
- **A step key shared by two journeys lands on the journey the surface is showing.** Keys
  are unique per flow, and the panel-wide search took the first flow wearing the key —
  ticking "invite-team" on one journey completed it on another. Surfaces now name the flow
  they are showing (the step methods take an optional `$flowKey`, so published views keep
  working).
- **Visiting a URL makes no progress in a flow the subject cannot see.** `handleVisit()`
  checked the step's visibility condition but not the flow's, so standing on the right
  page completed steps of a journey that did not exist for that account.
- **A journey of only optional steps completes when everything is settled** — not at the
  first touch. "Every required step is done" is vacuously true of a journey with no
  required steps, and used to stamp `completed_at` and fire `FlowCompleted` after the
  first tick, with the journey barely begun.
- **An optional step with nowhere to send you can still be skipped from the launcher.**
  The skip button only rendered next to a destination, so a step with no URL, tour or
  video could be skipped from the progress page alone.
- **"Advance with" presses the control the way a mouse does** — mousedown, mouseup, click.
  Plenty of things do not listen for a bare click: a Filament dropdown (the filter panel of
  a table, for one) opens on mousedown, so a tour pointing into it waited forever for a
  panel that was never going to open.
- **Switching tabs mid-tour no longer reads as a stuck form.** Pressing a control is a
  round trip and a re-render; calling it "blocked" after 1.5 s put a warning on screen for
  a page that was merely thinking. The window is 3 s, and the message fits both cases.
- **A provider script that never arrives costs the tracking, not the video.** When an ad
  blocker (or a walled network) keeps the YouTube/Vimeo API out, the modal now falls back
  to the provider's plain embed instead of opening empty. A plain iframe cannot be asked
  about watch time, so none is invented — a video-completion step completes some other way.
- The welcome dialog traps focus and answers Escape with "not now" — `aria-modal` was
  promising both without delivering either. The duplicated `class` attribute on the
  progress page's restart note now applies its spacing.

### Changed

- One `SubjectOnboarding` per component request instead of one per question: a launcher
  render asked "current flow?", "hidden?", "welcome?" and each fresh engine re-read all of
  the subject's progress. The engine keeps its own maps current as it writes, so nothing
  stale survives the memoisation.

## 2.1.0

**Upgrade from 2.0.0. Do not run 2.0.0.**

### Security

- **A user could complete any step, by any name.** The checklist is a Livewire component,
  so its methods are network endpoints: `completeStep('has-two-factor')` reached the server
  whatever the screen was showing, and nothing on the server asked whether that step was
  the caller's to touch or whether it finishes that way. Steps bound to a **condition**
  ("has a server", "enabled two-factor") could be marked done — permanently, since a
  condition step is never un-completed — as could `Programmatic` steps, `Visit` steps
  without the visit, videos without the watching, steps hidden from the account by a
  visibility condition, and steps belonging to another panel. Any application logic hanging
  off `StepCompleted` / `FlowCompleted` fired for work nobody did.
  Every browser-reachable method now re-asks what the interface asked, within the panel and
  the visibility the subject actually has.
- **A condition that threw took the whole panel down.** The checklist renders in the layout
  of every page, so an exception raised inside an application-registered condition — a
  renamed relation, a database that blinked, a class deleted while a flow still named it —
  was a 500 on every screen. A broken question now answers "no" and is reported.
- **Progress could land in the wrong tenant's row, or in nobody's.** The scope was
  re-resolved on every request and answered `null` silently when the panel was not there to
  ask. It is captured at mount and locked.
- Uploaded SVGs are no longer accepted by default (an SVG is a document, and it can carry
  script), and an "embed" URL must be an address — `javascript:` and `data:text/html` are
  refused.

### Fixed

- **Tours work inside a wizard.** Filament keeps the inactive steps of a wizard in the DOM
  and hides them with CSS, so the runner believed a field was on screen while the subject
  was two steps away from it: the spotlight was drawn around nothing, the waiting state
  never engaged, and "advance with" never fired. A hidden element is no longer a found one.
- Tours and wizards now walk together: moving the form moves the tour, and a stop can carry
  the control that brings the application to it (**"Advance with"**).
- Scrolling no longer drags the subject back to the spotlight, nor floods the server (a
  two-second scroll was ~120 Livewire round-trips, each with a database write).
- Arrow keys and Escape no longer hijack a subject typing in a field.
- The spotlight glides instead of jumping (the transitions were declared on properties the
  runner never sets), and it lands on the whole row of a toggle rather than on the hidden
  checkbox behind it.
- Progress rows are genuinely unique per subject and scope, including when there is no
  scope — a NULL in a unique index enforces nothing, and concurrent requests could write a
  step twice.
- A disk that cannot build a URL costs the image, not the page; a private disk that cannot
  sign one hands back nothing rather than a public URL.
- `onboarding:reset` says when it wiped nothing (resetting without `--scope` in a tenanted
  application resets nobody).

### Added

- **`StartTourAction`** — a header action for any page or resource that starts one tour in
  place ("How does it work?"), visible only when the subject can actually take it.
- A **JavaScript test suite** (vitest), and CI that runs it and refuses a stale `dist`.

---

## 2.0.0 — first release

> **Withdrawn.** This release lets any authenticated user complete any onboarding step by
> calling the component's methods directly, and a condition that throws returns a 500 on
> every page of the panel. Upgrade to 2.2.0 — its migration is the one that survives the
> duplicate progress rows this release could write.

Database-driven onboarding for Filament v5.

### Journeys

- Flows and steps authored in the panel, not in code (`->manageFlows()`).
- Five ways a step can finish: by hand, by a **condition** the application registers, by
  **reaching a URL**, by **watching a video**, or from your own code.
- Conditions complete **retroactively** — an account that already did the thing opens the
  checklist with the step already ticked.
- **Visibility conditions** on a flow, a step or a single tour stop: gate onboarding on a
  plan, so an account is never taught a feature it does not have. A hidden step is left
  out of the count, not left pending.
- Progress is scoped: the same user onboards separately in each tenant.

### Surfaces

- **Launcher** — a floating progress button on every page of the panel, with tabs when
  there is more than one journey.
- **Dashboard widget** — the checklist as a card.
- **Progress page** — the journey laid out in cards, registered per panel with
  `->progressPage()`. Includes "start over".
- **Guided tours** — spotlight an element, explain it, move on; tours cross pages and
  resume where they left off, and report the stop they reached.
- **`StartTourAction`** — a header action for any page or resource that starts one tour in
  place ("How does it work?"), visible only when the subject can actually take that tour.
- Tours **walk through a wizard**: they follow the user when the form moves on, and an
  "Advance with" selector lets a stop bring the application to it. A tour parked on a page
  ends when the user goes somewhere else, instead of floating over the wrong screen.

### Safety

- Every browser-reachable method re-checks what the interface checked: a step is only
  ticked off by hand if it *can* be, only finished by a tour if it *has* one, and only
  ever within the panel and the visibility the subject actually has.
- A condition that throws answers "no" and is reported, rather than 500-ing every page of
  the panel (the checklist lives in the layout).
- The tenant a surface was rendered for is captured at mount and locked, so progress
  cannot land in another tenant's row — or in no tenant's row — on a later request.
- Progress rows are genuinely unique per subject and scope, including when there is no
  scope at all.

### Media

- Images and videos on a step: upload (S3, R2, local), direct URL, YouTube, Vimeo, or any
  iframe.
- A private disk is **signed at render time** rather than exposed.
- **Watch time is measured**, not guessed — through the `<video>` element, the YouTube
  IFrame API and the Vimeo SDK — and a video resumes where it stopped.
- Modal position configurable per panel and per step.

### Authoring

- Destinations, pages and widgets are **discovered from the panel**: no URLs typed by hand,
  and route names are stored, so a renamed slug does not break a journey.
- Any Livewire component can be spotlighted (`@livewire:edit_password_form`), which covers
  form sections that carry no CSS hook of their own.
- Translatable content per locale, with a fallback chain (`pt_BR` → `pt` → app fallback).
- Optional integration with `wallacemartinss/filament-icon-picker`, detected at runtime.

### Elsewhere

- `onboarding:reset` command.
- `StepCompleted` and `FlowCompleted` events.
- Self-contained stylesheet, restyleable through `--fio-theme-*` variables, a published
  stylesheet, or published views.
- Assets are versioned by content hash, so an edited stylesheet actually reaches the browser.
- Migrations run on PostgreSQL and MySQL/MariaDB alike.
