<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Wallacemartinss\FilamentOnboarding\Enums\{CompletionMode, StepType};
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingFlow, OnboardingStep};
use Wallacemartinss\FilamentOnboarding\Support\SpotlightTargets;
use Wallacemartinss\FilamentOnboarding\Tests\TestCase;

/**
 * What a tour stop points at, picked from a list rather than typed as CSS.
 *
 * Writing a stop used to mean typing `[data-onboarding="client-submit"]`, which
 * assumes two things about whoever is writing it: that they know CSS, and that
 * they know a developer went and put that hook in the code. Journeys are supposed
 * to be product's to write, and product knows neither.
 *
 * So the choice is what gets stored — `field:status` — and the CSS is worked out
 * at render. Same reason a route name is stored and not a URL: what the markup
 * looks like is Filament's business, and a journey should survive Filament
 * changing its mind about it.
 */
class SpotlightTargetsTest extends TestCase
{
    public function test_a_field_is_spotlit_whole_rather_than_by_its_input(): void
    {
        $selector = SpotlightTargets::selector('field:status');

        // The wrapper, so the label comes with the box you type in — a spotlight
        // around an input alone explains half of what it points at.
        $this->assertStringContainsString('.fi-fo-field:has(label[for="form.status"])', $selector);

        // And the input as a fallback, for a field whose label is hidden. A Select
        // has no id at all, which is why the label is what both hang off.
        $this->assertStringContainsString('[id="form.status"]', $selector);
    }

    public function test_a_field_nested_under_a_state_path_is_still_found_by_its_name(): void
    {
        $selector = SpotlightTargets::selector('field:title');

        // What a stop stores is the field's name; what the markup wears is its
        // state path. A form that nests its fields renders something longer —
        // any Group, Section, Tab or wizard step with a state path of its own
        // prefixes what is under it, and a field still called `title` comes out
        // as `form.details.title`. The stop should not have to know that, so
        // the name is matched at the end of the path as well as as the whole
        // of it.
        $this->assertStringContainsString('label[for^="form."][for$=".title"]', $selector);
        $this->assertStringContainsString('[id^="form."][id$=".title"]', $selector);

        // And the plain path still answers, for the forms that do not nest.
        $this->assertStringContainsString('.fi-fo-field:has(label[for="form.title"])', $selector);
    }

    public function test_a_longer_field_name_is_not_caught_by_a_shorter_one(): void
    {
        // The dot is part of what is matched, which is what keeps `subtitle`
        // out of `title`: `form.details.subtitle` does not end in `.title`.
        $this->assertStringNotContainsString('[for$="title"]', SpotlightTargets::selector('field:title'));
        $this->assertStringContainsString('[for$=".title"]', SpotlightTargets::selector('field:title'));
    }

    public function test_the_save_button_answers_on_a_create_page_and_on_an_edit_one(): void
    {
        $selector = SpotlightTargets::selector('action:submit');

        // Create pages say `create`; edit pages say `save`. One stop rides both,
        // and only ever one of them is on the page.
        $this->assertStringContainsString('[wire\:target="create"]', $selector);
        $this->assertStringContainsString('[wire\:target="save"]', $selector);
    }

    public function test_the_parts_of_a_list_page_are_named(): void
    {
        $this->assertSame('.fi-ta', SpotlightTargets::selector('part:table'));
        $this->assertSame('.fi-ta input[type="search"]', SpotlightTargets::selector('part:search'));
    }

    public function test_a_button_that_goes_somewhere_is_a_link_to_a_route(): void
    {
        Route::get('/clients/create', fn (): string => '')->name('clients.create');

        // The "New client" button is not the page's own — it is a link to the
        // create page, and a route is something this package keeps hold of. So
        // the button is found by where it goes.
        $this->assertSame(
            'a[href$="/clients/create"]',
            SpotlightTargets::selector('link:clients.create'),
        );
    }

    public function test_a_column_is_found_by_the_name_filament_gives_its_class(): void
    {
        // Filament names the header cell after the column, through
        // str($name)->camel()->kebab() — so `is_active` becomes `is-active`.
        $this->assertSame(
            '[class~="fi-ta-header-cell-is-active"]',
            SpotlightTargets::selector('column:is_active'),
        );

        $this->assertSame(
            '[class~="fi-ta-header-cell-status"]',
            SpotlightTargets::selector('column:status'),
        );
    }

    public function test_a_target_that_makes_no_sense_points_at_nothing(): void
    {
        $this->assertNull(SpotlightTargets::selector('nonsense:whatever'));
        $this->assertNull(SpotlightTargets::selector('field:'));
        $this->assertNull(SpotlightTargets::selector('link:no.such.route'));
    }

    public function test_the_stop_stores_the_choice_and_the_browser_gets_the_css(): void
    {
        $flow = OnboardingFlow::create([
            'key'       => 'journey',
            'title'     => ['en' => 'Get started'],
            'is_active' => true,
        ]);

        $step = OnboardingStep::create([
            'flow_id'         => $flow->id,
            'key'             => 'tour',
            'type'            => StepType::Tour,
            'title'           => ['en' => 'A tour'],
            'completion_mode' => CompletionMode::Manual,
            'tour_steps'      => [
                ['target' => 'field:status', 'title' => ['en' => 'The status']],
                ['target' => 'part:table', 'title' => ['en' => 'The table']],
            ],
        ]);

        $tour = $step->resolveTourSteps();

        $this->assertStringContainsString('form.status', $tour[0]['selector']);
        $this->assertSame('.fi-ta', $tour[1]['selector']);
    }

    public function test_a_stop_written_before_the_picker_existed_still_works(): void
    {
        $flow = OnboardingFlow::create([
            'key'       => 'journey',
            'title'     => ['en' => 'Get started'],
            'is_active' => true,
        ]);

        $step = OnboardingStep::create([
            'flow_id'         => $flow->id,
            'key'             => 'tour',
            'type'            => StepType::Tour,
            'title'           => ['en' => 'A tour'],
            'completion_mode' => CompletionMode::Manual,
            'tour_steps'      => [
                // The old words: raw CSS, and a widget by class.
                ['selector' => '[data-onboarding="create-server"]', 'title' => ['en' => 'One']],
                ['target' => 'custom', 'selector' => '#somewhere', 'title' => ['en' => 'Two']],
            ],
        ]);

        $tour = $step->resolveTourSteps();

        $this->assertSame('[data-onboarding="create-server"]', $tour[0]['selector']);

        // "custom" is the picker saying "the author wrote their own" — the selector
        // is still where it always was.
        $this->assertSame('#somewhere', $tour[1]['selector']);
    }

    public function test_a_form_that_cannot_be_read_costs_its_fields_and_nothing_else(): void
    {
        // This is the disclaimer, as a test. A form is built to be *rendered*, and
        // some will not survive being asked what is in them outside of a request:
        // one that leans on the record being edited, or on who is logged in. That
        // is not a reason to take the panel down — the fields are simply not
        // offered, and the CSS box is right there.
        $options = SpotlightTargets::options('filament.test.resources.nothing.create', 'test');

        $this->assertIsArray($options);

        // Whatever else it could not work out, the way through is always offered.
        $advanced = $options[__('filament-onboarding::onboarding.resource.tour.targets.advanced')] ?? [];

        $this->assertArrayHasKey('custom', $advanced);
    }

    public function test_a_form_that_asks_who_renders_it_still_offers_its_fields(): void
    {
        $options = SpotlightTargets::options('filament.test.resources.notes.create', 'test');

        $onThisPage = $options[__('filament-onboarding::onboarding.resource.tour.targets.on_this_page')] ?? [];

        // The fixture's form reads `getLivewire()`, the way a plugin field or a
        // label that leans on the record does. Built against nothing that is a
        // TypeError, and the whole form loses its fields silently — on exactly
        // the resources worth touring, the ones assembled from enough parts
        // that one of them asks. Built against the resource's own page, it
        // reads.
        $this->assertArrayHasKey('field:title', $onThisPage);

        // Including the one nested under a state path, offered by its name.
        $this->assertArrayHasKey('field:subtitle', $onThisPage);

        // And a form that was read has a button worth pointing at.
        $this->assertArrayHasKey('action:submit', $onThisPage);
    }

    public function test_an_unknown_page_still_offers_the_escape_hatch(): void
    {
        $options = SpotlightTargets::options(null, 'test');

        $this->assertArrayHasKey(
            __('filament-onboarding::onboarding.resource.tour.targets.advanced'),
            $options,
        );
    }

    public function test_a_custom_page_offers_the_fields_of_each_of_its_forms(): void
    {
        $options    = SpotlightTargets::options('filament.test.pages.store-settings', 'test');
        $onThisPage = $options[__('filament-onboarding::onboarding.resource.tour.targets.on_this_page')] ?? [];

        // A settings screen is a page, not a resource, and it is where most of a
        // product's first steps happen. Its main form is the `form` schema, as on
        // any resource page; a second form says which one it is.
        $this->assertSame('Store name', $onThisPage['field:store_name'] ?? null);
        $this->assertSame('Client ID', $onThisPage['field:client_id@connectionForm'] ?? null);
        $this->assertArrayHasKey('action:submit', $onThisPage);
    }

    public function test_a_field_of_another_schema_is_found_under_that_schema(): void
    {
        $selector = SpotlightTargets::selector('field:client_id@connectionForm');

        // The schema's name is the first part of every id under it.
        $this->assertStringContainsString('.fi-fo-field:has(label[for="connectionForm.client_id"])', $selector);
        $this->assertStringNotContainsString('form.client_id"', $selector);

        // And a stop stored before schemas were named is still the main form.
        $this->assertStringContainsString('label[for="form.status"]', SpotlightTargets::selector('field:status'));
    }

    public function test_a_list_page_that_creates_in_a_modal_offers_the_button_and_its_fields(): void
    {
        $options    = SpotlightTargets::options('filament.test.resources.tags.index', 'test');
        $onThisPage = $options[__('filament-onboarding::onboarding.resource.tour.targets.on_this_page')] ?? [];

        // No create page to link to: the "New" button is an action, and the form
        // it opens is rendered in that action's modal.
        $this->assertArrayHasKey('action:create', $onThisPage);
        $this->assertSame('Label', $onThisPage['field:label@mountedActionSchema0'] ?? null);

        $this->assertSame('[wire\\:click="mountAction(\'create\')"]', SpotlightTargets::selector('action:create'));
        $this->assertStringContainsString('label[for="mountedActionSchema0.label"]', SpotlightTargets::selector('field:label@mountedActionSchema0'));
    }

    public function test_a_tenant_panel_is_read_as_the_tenant_it_names(): void
    {
        // Journeys are written in another panel, with no tenant. A form that
        // reads the tenant while it is built would lose its fields there; the
        // panel names a tenant to be read as instead.
        $options    = SpotlightTargets::options('filament.tenancy.pages.team-settings', 'tenancy');
        $onThisPage = $options[__('filament-onboarding::onboarding.resource.tour.targets.on_this_page')] ?? [];

        $this->assertSame('Invoice prefix for Acme', $onThisPage['field:invoice_prefix'] ?? null);

        // And the request is left as it was found.
        $this->assertNull(\Filament\Facades\Filament::getTenant());
    }

    public function test_a_field_inside_a_repeater_is_not_mistaken_for_the_repeater(): void
    {
        $selector = SpotlightTargets::selector('field:instructions@mountedActionSchema0');

        // A repeater is a field holding fields, and `:has()` answers for every
        // ancestor: the repeater around a row's field would match first and the
        // whole table be spotlit. Only the field whose label is its own answers.
        $this->assertStringContainsString(
            '.fi-fo-field:has(label[for^="mountedActionSchema0."][for$=".instructions"]):not(:has(.fi-fo-field label[for^="mountedActionSchema0."][for$=".instructions"]))',
            $selector,
        );
    }

    public function test_a_field_made_of_several_inputs_is_found_by_its_caption(): void
    {
        $selector = SpotlightTargets::selector('field:pricing_mode');

        // Toggle buttons, a radio, a slider: no single input for a `<label for>`
        // to name. Their caption carries the field's id plus `-label`.
        $this->assertStringContainsString('.fi-fo-field:has([id="form.pricing_mode-label"])', $selector);
        $this->assertStringContainsString('[id^="form."][id$=".pricing_mode-label"]', $selector);
    }
}
