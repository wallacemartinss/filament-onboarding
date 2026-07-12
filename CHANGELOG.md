# Changelog

All notable changes to `filament-onboarding` are documented here.

Versions follow Filament: **2.x targets Filament v5**, and 1.x is reserved for a Filament v4
backport. That is why the first release is 2.0.0 — there is no 1.0.0 to upgrade from.

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
> every page of the panel. Use 2.1.0.

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
