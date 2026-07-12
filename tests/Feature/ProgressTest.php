<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Wallacemartinss\FilamentOnboarding\Contracts\{HasConditionLabel, OnboardingCondition};
use Wallacemartinss\FilamentOnboarding\Enums\CompletionMode;
use Wallacemartinss\FilamentOnboarding\Events\{FlowCompleted, StepCompleted};
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingFlow, OnboardingStep};
use Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Subject;
use Wallacemartinss\FilamentOnboarding\Tests\TestCase;

class AlwaysTrueCondition implements HasConditionLabel, OnboardingCondition
{
    public static function label(): string
    {
        return 'Already done';
    }

    public function isCompleted(Model $subject, ?Model $scope = null): bool
    {
        return true;
    }
}

class ProgressTest extends TestCase
{
    private Subject $subject;

    private OnboardingFlow $flow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = Subject::create(['name' => 'Ada']);

        $this->flow = OnboardingFlow::create([
            'key'       => 'journey',
            'title'     => ['en' => 'Journey'],
            'is_active' => true,
        ]);
    }

    public function test_it_dispatches_events_as_progress_is_made(): void
    {
        Event::fake([StepCompleted::class, FlowCompleted::class]);

        $this->step('only', ['is_required' => true]);

        Onboarding::for($this->subject)->complete('only');

        Event::assertDispatched(StepCompleted::class);
        Event::assertDispatched(FlowCompleted::class);
    }

    public function test_restarting_clears_what_the_subject_did(): void
    {
        $this->step('first');
        $this->step('second');

        Onboarding::for($this->subject)->complete('first');
        $this->assertSame(50, Onboarding::for($this->subject)->flow('journey')->percentage());

        Onboarding::for($this->subject)->reset('journey');

        $this->assertSame(0, Onboarding::for($this->subject)->flow('journey')->percentage());
    }

    public function test_restarting_cannot_undo_what_is_still_true(): void
    {
        $this->step('has-thing', [
            'completion_mode' => CompletionMode::Condition,
            'condition_key'   => 'always_true',
        ]);

        Onboarding::condition('always_true', AlwaysTrueCondition::class);

        $this->assertTrue(Onboarding::for($this->subject)->flow('journey')->step('has-thing')->isCompleted());

        Onboarding::for($this->subject)->reset('journey');

        // The condition answers to the application, not to the reset.
        $this->assertTrue(Onboarding::for($this->subject)->flow('journey')->step('has-thing')->isCompleted());
        $this->assertFalse(Onboarding::for($this->subject)->flow('journey')->step('has-thing')->canUndo());
    }

    public function test_a_condition_can_name_itself_for_the_panel(): void
    {
        Onboarding::condition('always_true', AlwaysTrueCondition::class);

        $this->assertSame('Already done', Onboarding::conditions()->options()['always_true']);
    }

    public function test_a_finished_step_can_be_gone_through_again(): void
    {
        $this->step('first', ['cta_url' => '/somewhere']);

        Onboarding::for($this->subject)->complete('first');

        $step = Onboarding::for($this->subject)->flow('journey')->step('first');

        $this->assertTrue($step->isCompleted());
        $this->assertTrue($step->canReplay());
        $this->assertTrue($step->canUndo());
    }

    public function test_the_reset_command_wipes_a_subject_progress(): void
    {
        $this->step('first');

        Onboarding::for($this->subject)->complete('first');

        $this->artisan('onboarding:reset', [
            'flow'            => 'journey',
            '--subject'       => $this->subject->getKey(),
            '--subject-model' => Subject::class,
        ])->assertSuccessful();

        $this->assertSame(0, Onboarding::for($this->subject)->flow('journey')->percentage());
    }

    public function test_the_reset_command_refuses_an_unknown_flow(): void
    {
        $this->artisan('onboarding:reset', ['flow' => 'nope'])->assertFailed();
    }

    /**
     * Having the data is not the same as having seen the tutorial.
     *
     * A journey whose only required step is a condition the account already meets
     * used to announce itself as finished and bury its own video and tour — the
     * two things a new user actually came for.
     */
    public function test_a_met_condition_does_not_bury_the_tutorial(): void
    {
        // Required: the thing the account already did.
        $this->step('has-thing', [
            'completion_mode' => CompletionMode::Condition,
            'condition_key'   => 'always_true',
            'is_required'     => true,
        ]);

        // Optional: the tutorial.
        $this->step('watch-the-tour', [
            'completion_mode' => CompletionMode::Programmatic,
            'is_required'     => false,
        ]);

        Onboarding::condition('always_true', AlwaysTrueCondition::class);

        $flow = Onboarding::for($this->subject)->flow('journey');

        // The required work is done...
        $this->assertTrue($flow->isCompleted());

        // ...but there is still something to do, so the UI must not say "all set",
        // and this is the journey to keep in front of the subject.
        $this->assertFalse($flow->isFinished());
        $this->assertSame(50, $flow->percentage());
        $this->assertSame('watch-the-tour', $flow->nextStep()->key());
        $this->assertSame('journey', Onboarding::for($this->subject)->currentFlow()->key());
    }

    public function test_a_journey_is_finished_only_when_nothing_is_left(): void
    {
        $this->step('required-one', ['is_required' => true]);
        $this->step('optional-one', ['is_required' => false]);

        Onboarding::for($this->subject)->complete('required-one');
        $this->assertFalse(Onboarding::for($this->subject)->flow('journey')->isFinished());

        // Skipping settles it too — the subject decided.
        Onboarding::for($this->subject)->skip('optional-one');
        $this->assertTrue(Onboarding::for($this->subject)->flow('journey')->isFinished());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function step(string $key, array $attributes = []): OnboardingStep
    {
        return OnboardingStep::create([
            'flow_id'         => $this->flow->id,
            'key'             => $key,
            'title'           => ['en' => $key],
            'completion_mode' => CompletionMode::Manual,
            'is_required'     => $attributes['is_required'] ?? false,
            ...$attributes,
        ]);
    }
}
