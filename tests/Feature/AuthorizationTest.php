<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use Wallacemartinss\FilamentOnboarding\Enums\{CompletionMode, MediaSource, MediaType, StepType};
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingFlow, OnboardingStep};
use Wallacemartinss\FilamentOnboarding\Tests\Fixtures\{OnboardingEndpoint, Subject};
use Wallacemartinss\FilamentOnboarding\Tests\TestCase;

/**
 * The checklist is a Livewire component, so every one of its methods is a network
 * endpoint: the browser can call completeStep() with any key it likes, whatever
 * the interface happens to be showing.
 *
 * The engine (SubjectOnboarding) is the trusted API — the application drives it
 * and it asks no questions. These tests are about the layer above it, where the
 * questions get asked. They bypass the interface entirely and call the endpoints
 * the way an attacker would, because that is the only way to prove the guard is
 * on the server and not merely in the Blade.
 */
class AuthorizationTest extends TestCase
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

    public function test_a_condition_step_cannot_be_ticked_off_by_hand(): void
    {
        $this->step('has-server', [
            'completion_mode' => CompletionMode::Condition,
            'condition_key'   => 'never',
        ]);

        $this->endpoint()->completeStep('has-server');

        // The condition is false, so the step is not done — saying so does not
        // make it so. Otherwise a user marks "two-factor enabled" forever, and
        // nothing ever un-marks it.
        $this->assertFalse($this->state('has-server')->isCompleted());
    }

    public function test_a_programmatic_step_cannot_be_completed_from_the_browser(): void
    {
        $this->step('billing-verified', ['completion_mode' => CompletionMode::Programmatic]);

        $this->endpoint()->completeStep('billing-verified');

        $this->assertFalse($this->state('billing-verified')->isCompleted());

        // The application, on the other hand, is exactly who may say so.
        Onboarding::for($this->subject)->complete('billing-verified');

        $this->assertTrue($this->state('billing-verified')->isCompleted());
    }

    public function test_a_visit_step_cannot_be_completed_without_the_visit(): void
    {
        $this->step('see-the-page', [
            'completion_mode' => CompletionMode::Visit,
            'visit_url'       => '/somewhere',
        ]);

        $this->endpoint()->completeStep('see-the-page');

        $this->assertFalse($this->state('see-the-page')->isCompleted());
    }

    public function test_a_step_hidden_from_the_subject_cannot_be_touched(): void
    {
        $this->step('for-another-plan', [
            'completion_mode'      => CompletionMode::Manual,
            'visibility_condition' => 'never',
        ]);

        $endpoint = $this->endpoint();

        $endpoint->completeStep('for-another-plan');
        $endpoint->skipStep('for-another-plan');
        $endpoint->tourProgress('for-another-plan', 1, 3);

        // Not visible, not touchable: no progress row is invented for a step this
        // subject was never offered.
        $this->assertDatabaseCount('onboarding_step_progress', 0);
    }

    public function test_finishing_a_tour_only_completes_a_step_that_has_one(): void
    {
        $this->step('no-tour-here', ['completion_mode' => CompletionMode::Manual]);

        $this->endpoint()->finishTour('no-tour-here');

        // finishTour() used to be a second front door to completing anything at
        // all — no tour required.
        $this->assertFalse($this->state('no-tour-here')->isCompleted());
    }

    public function test_a_tour_is_not_a_second_door_around_the_completion_mode(): void
    {
        $this->step('secure-the-account', [
            'type'            => StepType::Tour,
            'tour_steps'      => [['selector' => '#two-factor', 'title' => ['en' => 'Turn it on']]],
            'completion_mode' => CompletionMode::Condition,
            'condition_key'   => 'never',
        ]);

        $this->endpoint()->finishTour('secure-the-account');

        // The tour was watched, and that much is remembered. But the step
        // answers to the condition, and the condition still says no — clicking
        // "next" to the end must not mark two-factor as enabled.
        $this->assertFalse($this->state('secure-the-account')->isCompleted());
        $this->assertTrue($this->state('secure-the-account')->isSeen());
    }

    public function test_a_manual_tour_step_still_completes_by_finishing_its_tour(): void
    {
        $this->step('meet-the-dashboard', [
            'type'       => StepType::Tour,
            'tour_steps' => [['selector' => '#sidebar', 'title' => ['en' => 'The sidebar']]],
        ]);

        $this->endpoint()->finishTour('meet-the-dashboard');

        // For a step that finishes by hand, the tour is the task.
        $this->assertTrue($this->state('meet-the-dashboard')->isCompleted());
    }

    public function test_watch_time_is_only_recorded_for_a_step_that_carries_a_video(): void
    {
        $this->step('read-the-docs', [
            'completion_mode' => CompletionMode::Manual,
            'media_type'      => MediaType::None,
        ]);

        $this->endpoint()->videoProgress('read-the-docs', 99999.0, 99999.0);

        $this->assertFalse($this->state('read-the-docs')->isCompleted());
        $this->assertDatabaseCount('onboarding_step_progress', 0);
    }

    public function test_a_required_step_cannot_be_skipped_from_the_browser(): void
    {
        $this->step('the-important-one', [
            'completion_mode' => CompletionMode::Manual,
            'is_required'     => true,
        ]);

        $this->endpoint()->skipStep('the-important-one');

        $this->assertFalse($this->state('the-important-one')->isSkipped());
    }

    public function test_a_condition_step_cannot_be_undone_from_the_browser(): void
    {
        Onboarding::condition('always', fn (): bool => true);

        $this->step('has-backup', [
            'completion_mode' => CompletionMode::Condition,
            'condition_key'   => 'always',
        ]);

        // The condition passes, so the engine completes it on its own.
        $this->assertTrue($this->state('has-backup')->isCompleted());

        $this->endpoint()->undoStep('has-backup');

        $this->assertTrue($this->state('has-backup')->isCompleted());
    }

    public function test_what_the_subject_may_do_still_works(): void
    {
        $this->step('tick-me', ['completion_mode' => CompletionMode::Manual]);
        $this->step('skip-me', ['completion_mode' => CompletionMode::Manual, 'is_required' => false]);

        $endpoint = $this->endpoint();

        $endpoint->completeStep('tick-me');
        $endpoint->skipStep('skip-me');

        $this->assertTrue($this->state('tick-me')->isCompleted());
        $this->assertTrue($this->state('skip-me')->isSkipped());

        // And having ticked it, they may take it back.
        $endpoint->undoStep('tick-me');

        $this->assertFalse($this->state('tick-me')->isCompleted());
    }

    public function test_a_shared_key_lands_on_the_flow_the_surface_is_showing(): void
    {
        // Step keys are only unique within a flow. An earlier journey wearing
        // the same key used to catch the tick meant for a later one.
        $decoy = OnboardingFlow::create([
            'key'        => 'earlier-journey',
            'title'      => ['en' => 'Earlier'],
            'is_active'  => true,
            'sort_order' => 0,
        ]);

        OnboardingStep::create([
            'flow_id'         => $decoy->id,
            'key'             => 'invite-team',
            'type'            => StepType::Task,
            'title'           => ['en' => 'Invite (earlier)'],
            'completion_mode' => CompletionMode::Manual,
            'is_required'     => false,
        ]);

        $this->flow->update(['sort_order' => 5]);

        $this->step('invite-team', ['completion_mode' => CompletionMode::Manual]);

        // The surface is pinned to the later journey — the launcher showing a
        // tab, the widget given a flowKey. Its tick belongs to that journey.
        $endpoint          = $this->endpoint();
        $endpoint->flowKey = 'journey';

        $endpoint->completeStep('invite-team');

        $this->assertTrue($this->state('invite-team')->isCompleted());
        $this->assertFalse(
            Onboarding::for($this->subject)->flow('earlier-journey')->step('invite-team')->isCompleted()
        );

        // And a caller that names the flow outright is answered from it alone.
        $this->endpoint()->undoStep('invite-team', 'journey');

        $this->assertFalse($this->state('invite-team')->isCompleted());
    }

    public function test_a_video_step_still_tracks_the_watching_it_is_for(): void
    {
        $this->step('watch-me', [
            'completion_mode'            => CompletionMode::Video,
            'media_type'                 => MediaType::Video,
            'media_source'               => MediaSource::YouTube,
            'media_url'                  => 'https://youtu.be/aqz-KE-bpKQ',
            'video_completion_threshold' => 90,
        ]);

        $this->endpoint()->videoProgress('watch-me', 95.0, 100.0);

        $this->assertTrue($this->state('watch-me')->isCompleted());
    }

    private function endpoint(): OnboardingEndpoint
    {
        return new OnboardingEndpoint();
    }

    private function state(string $key): \Wallacemartinss\FilamentOnboarding\States\StepState
    {
        return Onboarding::for($this->subject)->flow('journey')->step($key);
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
