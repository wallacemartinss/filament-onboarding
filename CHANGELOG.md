# Changelog

All notable changes to `filament-onboarding` are documented here.

Versions follow Filament: **2.x targets Filament v5**, and 1.x is reserved for a Filament v4
backport. That is why the first release is 2.0.0 — there is no 1.0.0 to upgrade from.

## 2.4.1

**Two controls at the foot of a journey that nobody was pressing.** "Start over" and "Hide"
sat under the last step as bare underlined words — which is how a control that *does*
something ends up reading like a footnote. And the text above them was touching the cards
below it: a description with no margin under it does not introduce the thing it describes, it
falls into it.

### Fixed

- **"Start over" and "Hide" are buttons.** Grey ones, with icons and a rule above them — the
  same secondary button Filament dresses its own "Cancel" in. A link is for going somewhere;
  these two change something, and they should look like it.
- **The progress page got its air back.** The journey description had **no bottom margin at
  all** — zero pixels between it and the first card — and the step cards carried 18 px of
  horizontal padding where Filament gives a card 24 px (`.fi-section-header`). Both now match
  what the panel around them does.

### Changed

- **The Composer archive no longer carries the whole repository.** There was no
  `.gitattributes`, so `tests/`, `.github/` and the docs were being unpacked into the `vendor/`
  directory of everyone who installs this package. This release adds 2.7 MB of screenshots to
  that repository, so the file had to exist before the screenshots did — and with it the
  install actually **shrinks, from 1.0 MB to 0.8 MB**, rather than growing to about 3.5 MB.
  Nothing needed at runtime was excluded: `src`, `config`, `database`, `resources/` and
  `stubs` all still ship.

### Documentation

- **The README shows the package instead of describing it.** Twenty-two screenshots of it
  running in a real panel, each placed next to the claim it proves — the wizard sequence under
  the sentence that says the tour presses the wizard's own *Next*, the welcome screen under the
  paragraph about accounts that start part-done, because the ring behind that modal reads 38%
  on a user who has never seen the journey.
- **A "Writing a journey" section.** The README had always claimed that flows, steps and the
  conditions they hang off are written in the panel, and had never once shown the panel.

## 2.4.0

**A tour stop is picked, not typed.** Writing one meant typing
`[data-onboarding="client-submit"]`, which assumed two things about whoever was expected to
do it: that they know CSS, and that they know a developer went and put that hook in the code.
Journeys are supposed to be product's to write. Product knows neither of those.

### Added

- **"What to spotlight" is a dropdown, read out of your own panel.** Pick the page a stop
  lives on, and the package offers what is on it:
  - **the fields of that page's form, by the labels they wear** — including a `Select` and a
    `Toggle`, which have no id of their own; the spotlight lands on the whole field, label
    and all, rather than on the box you type in
  - **the save button**, which answers on a create page and an edit one alike
  - **the table**, **the search box**, and **each column by its heading**
  - **the "New client" button** — which is not the page's own, it is a link to the create
    page, and the package has the route
  - **any widget of the panel**
  - and **a CSS selector of your own**, for what the panel cannot name

  What is stored is the *choice* (`field:status`), not the selector — the same reason a route
  name is stored and not a URL: the markup is Filament's to change, and a journey should
  survive it changing.

  A form that cannot be read from the outside — one that leans on the record being edited, or
  on who is looking — gives up its fields and nothing else: they are not listed, the panel
  does not fall over, and the CSS box is right there.

### Fixed

- **A tour and the welcome screen no longer argue over the same screen.** Both are modal, and
  the state is reachable: a tour parks itself in `sessionStorage` to cross a page, while "not
  now" lives in the server's session. Let the session turn over in between — it expires, it
  rotates on a login — and the page comes back with the tour resuming *and* the welcome sure
  it was never answered. Somebody walking a tour has already started; there is nothing left
  to invite them to, so the welcome stands down while one is running.

---

## 2.3.1

### Fixed

- **The step editor was write-once.** Every field that depends on what kind of step it is —
  the whole **Tour** tab and all of its stops, the **condition** dropdown, the **visit URL**,
  every **media** field — was there when a step was created and *gone* when one was opened to
  be edited. So a tour could be written and then never touched again: to change a stop you
  had to delete the step and start over.

  The state of a field backed by an enum comes in two shapes, and the editor only knew one.
  Creating, the state is what the browser posted — the string `'tour'`. Editing, the form was
  filled from the record, whose attribute is *cast*, so the state is `StepType::Tour`, the
  enum itself. `$get('type') === StepType::Tour->value` is therefore true exactly half the
  time, and the half it answers no is the half where the field simply is not rendered. Nothing
  threw. The form quietly showed less of itself, on the one path nobody tests: the second time
  you open something.

  Both shapes now answer the same. (Two `(string)` casts over the same state went with it —
  an enum is not a string, and PHP says so by dying.)

---

## 2.3.0

**Conditions no longer need a deploy.** The package's argument has always been that a
journey is a thing you *write*, not a thing you ship — and it held right up to the most
valuable kind of step, the one that completes itself, which needed a closure in a service
provider and a commit to go with it. A product person could rewrite the copy of a journey
without asking anybody, and then had to open a pull request to ask "have they added a client
yet?".

### Added

- **Conditions are written in the panel.** Onboarding → Conditions → New, and the two shapes
  cover what onboarding actually asks:
  - **Counts something they have** — `Client · at least 1 · only where status is active`.
    Optionally scoped to the tenant, so a client added in one does not tick the step in another.
  - **Asks about them** — `email_verified_at is filled in`.

  No code, no deploy. And no SQL either: the model comes from an allowlist
  (`conditions_builder.models`, which offers `app/Models` when left empty), the column from
  that table's real columns, the operator from an enum — and the one thing an author types,
  the value, is bound rather than interpolated.
- **`php artisan make:onboarding-condition HasActivePlan`** — for the questions a form cannot
  ask (a subscription, an API call, a score). It writes the class, tells you the key it
  answers to, and there is **nothing to register**.
- **Condition classes are discovered.** Anything in `app/Onboarding/Conditions` is found and
  registered on its own. Writing the class *and* naming it in a config file was saying the
  same thing twice, and the second half is the half that gets forgotten in the deploy where
  it matters. (`discovery.enabled => false` to opt out.)
- Code always wins a name clash: a condition written in the panel cannot take a key a class
  already answers to, so a row can never quietly redefine what a step means.

### Fixed

- **The step editor stops showing an empty dropdown with no explanation.** Picking the
  "condition" completion mode with no conditions registered offered nothing and said nothing —
  the single most reportable thing in the package. It now says where to write one.
- **The journey header is a card again.** Folding the steps away in 2.2.1 took the surface
  with them: `.fio-hero` was what carried the radial gradient, the border and the shadow, and
  the new header left each journey as a bare row on the page background. The header carries
  them now — same gradient, same border, same shadow, holding a summary rather than
  everything.

### Upgrading

New tables ship with new releases, and this one adds `onboarding_conditions`:

```bash
composer update wallacemartinss/filament-onboarding
php artisan vendor:publish --tag=filament-onboarding-migrations   # existing files are left alone
php artisan migrate
php artisan filament:assets
```

Conditions registered in code keep working exactly as they did — in the config, at runtime,
or (now) simply by existing in `app/Onboarding/Conditions`. Nothing needs to be rewritten.

---

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
