# Screenshots

Every image the main README uses. Two folders, because the package has two audiences: the
people being onboarded, and the people writing the onboarding.

Shot on a Filament v5 panel in light mode, at 1920 px, on an account that already has clients
and products — which is why the journey opens part-done rather than at 0%. That is not a
detail of the demo: it is the feature.

## `01-user/` — what the user sees

| File | What it shows |
|------|---------------|
| `01_get_started_welcome.png` | The welcome screen over the first page after login. The ring behind it reads **38%** — the account has never seen the journey, and three of its steps were already true. |
| `02_get_started_progress.png` | **The hero.** The dashboard: the checklist as a widget, the floating checklist open beside it, tabs for two journeys. |
| `02_get_started_panel.png` | The progress page: the ring, the counters, the next step, a card per step, and *Start over* / *Hide*. |
| `03_get_started_video.png` | A video docked bottom-right, playing, the watched percentage under it, the page still usable behind. |
| `04_get_started_tour_01.png` · `_02.png` | A tour of a list page: the table, then the button that leads off it. |
| `05_get_started_create_01.png` · `_02.png` · `_03.png` | A tour of a create form — a text field, a select in the next section, the save button. **No CSS was written for any of it.** |
| `06_get_started_tutorial_00.png` | The *View the tutorial* header action, on the page it is about. |
| `06_get_started_tutorial_01.png` … `_05.png` | Five stops across the three steps of a wizard. The tour presses the wizard's own *Next* to reach the fields that are not on screen yet. |

## `02-admin/` — what the author sees

| File | What it shows |
|------|---------------|
| `01_menu.png` | What `->manageFlows()` adds: Onboarding, and Conditions. |
| `02_onbording.png` | The journeys of a panel. |
| `02_onbording_create.png` | Writing a flow: content per locale, key, panel, visibility, order, dismissible. |
| `02_onbording_step.png` | The steps, and the **Completed by** column — manual, condition, video. |
| `02_onbording_tour_options.png` | The step editor on its Tour tab: the stops, and *What to spotlight* read out of the panel itself. |
| `03_onbording_conditions.png` | The questions a step can hang off, written in the panel. |
| `04_onbording_conditions_form.png` | Building one: count a model, scope it to the subject, add rules. No SQL, no class. |

## Replacing them

Keep the names — the README points at them. Same panel, same theme, same width, and an account
with a history: a journey half-done tells the story, a journey at 0% tells nothing.
