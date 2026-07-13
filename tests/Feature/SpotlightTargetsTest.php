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

    public function test_an_unknown_page_still_offers_the_escape_hatch(): void
    {
        $options = SpotlightTargets::options(null, 'test');

        $this->assertArrayHasKey(
            __('filament-onboarding::onboarding.resource.tour.targets.advanced'),
            $options,
        );
    }
}
