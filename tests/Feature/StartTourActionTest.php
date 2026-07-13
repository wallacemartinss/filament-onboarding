<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use Wallacemartinss\FilamentOnboarding\Actions\StartTourAction;
use Wallacemartinss\FilamentOnboarding\Enums\{CompletionMode, StepType};
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingFlow, OnboardingStep};
use Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Subject;
use Wallacemartinss\FilamentOnboarding\Tests\TestCase;

/**
 * The header button that starts a tour on the page it explains.
 *
 * Its whole promise is that it asks nothing it does not have to: one tour and it
 * simply starts; several and it asks which; none the subject can take, and the
 * button is not there at all.
 */
class StartTourActionTest extends TestCase
{
    private Subject $subject;

    private OnboardingFlow $flow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = Subject::create(['name' => 'Ada']);

        Onboarding::resolveSubjectUsing(fn (): Subject => $this->subject);

        $this->flow = OnboardingFlow::create([
            'key'       => 'journey',
            'title'     => ['en' => 'Get started'],
            'is_active' => true,
        ]);

        Onboarding::condition('never', fn (): bool => false);
    }

    public function test_one_tour_starts_outright_instead_of_asking_which(): void
    {
        $this->tour('servers-tour');

        $action = StartTourAction::make('servers-tour');

        // A modal here is an empty modal: it asks "which tutorial?" and offers
        // nothing, because there is nothing to choose. Filament opens one for a
        // custom modal heading alone — the schema is never consulted — so the
        // action has to turn the modal off itself.
        $this->assertFalse($action->shouldOpenModal());
        $this->assertTrue($action->isVisible());
    }

    public function test_several_tours_are_offered_as_a_choice(): void
    {
        $this->tour('servers-tour');
        $this->tour('databases-tour');

        $action = StartTourAction::make(['servers-tour', 'databases-tour']);

        $this->assertTrue($action->shouldOpenModal());
        $this->assertTrue($action->isVisible());
    }

    public function test_a_list_whose_tours_are_mostly_missing_still_starts_the_one_that_is_there(): void
    {
        // The list names two, and only one of them exists — an unwritten tour, a
        // typo in a key, a tour whose step was deactivated. The button must not
        // ask which of the one.
        $this->tour('servers-tour');

        $action = StartTourAction::make(['servers-tour', 'never-written-tour']);

        $this->assertFalse($action->shouldOpenModal());
        $this->assertTrue($action->isVisible());
    }

    public function test_a_tour_the_subject_cannot_take_takes_the_button_with_it(): void
    {
        $this->tour('paid-plan-tour', ['visibility_condition' => 'never']);

        $action = StartTourAction::make('paid-plan-tour');

        // A plan that cannot see the journey never sees the invitation either.
        $this->assertFalse($action->isVisible());
    }

    public function test_an_unknown_key_is_not_an_invitation(): void
    {
        $action = StartTourAction::make('nobody-wrote-this');

        $this->assertFalse($action->isVisible());
    }

    public function test_a_step_with_no_tour_is_not_offered_as_one(): void
    {
        OnboardingStep::create([
            'flow_id'         => $this->flow->id,
            'key'             => 'just-a-task',
            'type'            => StepType::Task,
            'title'           => ['en' => 'Just a task'],
            'completion_mode' => CompletionMode::Manual,
        ]);

        $this->assertFalse(StartTourAction::make('just-a-task')->isVisible());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function tour(string $key, array $attributes = []): OnboardingStep
    {
        return OnboardingStep::create([
            'flow_id'         => $this->flow->id,
            'key'             => $key,
            'type'            => StepType::Tour,
            'title'           => ['en' => $key],
            'completion_mode' => CompletionMode::Manual,
            'tour_steps'      => [
                ['selector' => '#somewhere', 'title' => ['en' => 'Here'], 'body' => ['en' => '...']],
            ],
            ...$attributes,
        ]);
    }
}
