# Changelog

All notable changes to `filament-onboarding` are documented here.

## 1.0.0 — first release

Database-driven onboarding for Filament v5.

### Journeys

- Flows and steps authored in the panel, not in code (`->manageFlows()`).
- Five ways a step can finish: by hand, by a **condition** the application registers, by
  **reaching a URL**, by **watching a video**, or from your own code.
- Conditions complete **retroactively** — an account that already did the thing opens the
  checklist with the step already ticked.
- Progress is scoped: the same user onboards separately in each tenant.

### Surfaces

- **Launcher** — a floating progress button on every page of the panel, with tabs when
  there is more than one journey.
- **Dashboard widget** — the checklist as a card.
- **Progress page** — the journey laid out in cards, registered per panel with
  `->progressPage()`. Includes "start over".
- **Guided tours** — spotlight an element, explain it, move on; tours cross pages and
  resume where they left off, and report the stop they reached.

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
