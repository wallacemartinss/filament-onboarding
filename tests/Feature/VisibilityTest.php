<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use Wallacemartinss\FilamentOnboarding\Enums\{CompletionMode, StepType};
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingFlow, OnboardingStep};
use Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Subject;
use Wallacemartinss\FilamentOnboarding\Tests\TestCase;

/**
 * Not every journey is for everybody.
 *
 * A `visibility_condition` decides whether a flow, a step or a single tour stop
 * exists at all for a given subject — typically a feature the plan does not
 * include: teaching it would spotlight a control that is not on the screen.
 *
 * Hidden is not the same as pending: a hidden step is left out of the count, so
 * a subject can finish a journey without ever seeing the parts that were never
 * theirs to walk.
 */
class VisibilityTest extends TestCase
{
    private Subject $subject;

    private OnboardingFlow $flow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = Subject::create(['name' => 'Ada']);

        $this->flow = OnboardingFlow::create([
            'key'       => 'journey',
            'title'     => ['en' => 'Get started'],
            'is_active' => true,
        ]);

        Onboarding::condition('on_the_plan', fn (): bool => true);
        Onboarding::condition('off_the_plan', fn (): bool => false);
    }

    public function test_a_flow_guarded_by_a_failing_condition_is_not_handed_over(): void
    {
        $this->step('anything');

        $this->flow->update(['visibility_condition' => 'off_the_plan']);

        $this->assertCount(0, Onboarding::for($this->subject)->flows());
        $this->assertNull(Onboarding::for($this->subject)->flow('journey'));
    }

    public function test_a_flow_guarded_by_a_passing_condition_is_handed_over(): void
    {
        $this->step('anything');

        $this->flow->update(['visibility_condition' => 'on_the_plan']);

        $this->assertCount(1, Onboarding::for($this->subject)->flows());
    }

    public function test_a_hidden_step_does_not_count_towards_the_percentage(): void
    {
        $this->step('visible');
        $this->step('for-another-plan', ['visibility_condition' => 'off_the_plan']);

        $flow = Onboarding::for($this->subject)->flow('journey');

        $this->assertSame(1, $flow->total());
        $this->assertNull($flow->step('for-another-plan'));

        Onboarding::for($this->subject)->complete('visible');

        $flow = Onboarding::for($this->subject)->flow('journey');

        $this->assertSame(100, $flow->percentage());
        $this->assertTrue($flow->isFinished());
    }

    public function test_a_step_guarded_by_an_unregistered_condition_stays_hidden(): void
    {
        $this->step('ghost', ['visibility_condition' => 'nobody_registered_this']);

        // Nothing left to show, so the flow itself is not handed over: a card at
        // 0% that can never be finished is worse than no card.
        $this->assertCount(0, Onboarding::for($this->subject)->flows());
        $this->assertNull(Onboarding::for($this->subject)->flow('journey'));
    }

    public function test_a_visit_makes_no_progress_in_a_flow_the_subject_cannot_see(): void
    {
        $this->step('see-the-page', [
            'completion_mode' => CompletionMode::Visit,
            'visit_url'       => '/servers/create',
        ]);

        $this->flow->update(['visibility_condition' => 'off_the_plan']);

        Onboarding::for($this->subject)->handleVisit('/servers/create');

        // The flow does not exist for this subject, so standing on the right
        // page proves nothing — no tick, and no progress row invented for a
        // journey they were never offered.
        $this->assertDatabaseCount('onboarding_step_progress', 0);
    }

    public function test_a_tour_leaves_out_the_stops_the_subject_is_not_entitled_to(): void
    {
        $this->step('tour', [
            'type'       => StepType::Tour,
            'tour_steps' => [
                ['selector' => '#everyone', 'title' => ['en' => 'For everyone'], 'body' => ['en' => '...']],
                ['selector' => '#paid', 'title' => ['en' => 'Paid only'], 'body' => ['en' => '...'], 'condition' => 'off_the_plan'],
                ['selector' => '#also-everyone', 'title' => ['en' => 'Also for everyone'], 'body' => ['en' => '...'], 'condition' => 'on_the_plan'],
            ],
        ]);

        $tour = Onboarding::for($this->subject)->flow('journey')->step('tour')->tour();

        $this->assertCount(2, $tour);
        $this->assertSame(['#everyone', '#also-everyone'], array_column($tour, 'selector'));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function step(string $key, array $attributes = []): OnboardingStep
    {
        return OnboardingStep::create([
            'flow_id'         => $this->flow->id,
            'key'             => $key,
            'type'            => StepType::Task,
            'title'           => ['en' => $key],
            'completion_mode' => CompletionMode::Manual,
            'is_required'     => false,
            ...$attributes,
        ]);
    }
}
