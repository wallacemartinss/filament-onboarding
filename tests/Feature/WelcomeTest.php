<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use Wallacemartinss\FilamentOnboarding\Enums\{CompletionMode, StepType};
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingFlow, OnboardingStep};
use Wallacemartinss\FilamentOnboarding\Tests\Fixtures\{OnboardingEndpoint, Subject};
use Wallacemartinss\FilamentOnboarding\Tests\TestCase;

/**
 * The welcome screen, and the three answers a person can give it.
 *
 * Interrupting somebody who logged in to do something else is only defensible if
 * the interruption carries its own way out. So it offers two: *not now*, which is
 * about this moment, and *not ever*, which is about them — and the second one has
 * to mean it. A checklist that keeps hovering in the corner after being told to
 * go away is not onboarding, it is nagging.
 */
class WelcomeTest extends TestCase
{
    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = Subject::create(['name' => 'Ada']);

        Onboarding::resolveSubjectUsing(fn (): Subject => $this->subject);

        $flow = OnboardingFlow::create([
            'key'       => 'journey',
            'title'     => ['en' => 'Get started'],
            'is_active' => true,
        ]);

        OnboardingStep::create([
            'flow_id'         => $flow->id,
            'key'             => 'first',
            'type'            => StepType::Task,
            'title'           => ['en' => 'First'],
            'completion_mode' => CompletionMode::Manual,
        ]);
    }

    public function test_somebody_arriving_for_the_first_time_is_welcomed(): void
    {
        $this->assertTrue($this->endpoint()->shouldWelcome());
    }

    public function test_welcoming_happens_once_and_not_again(): void
    {
        $endpoint = $this->endpoint();

        $endpoint->startOnboarding();

        $this->assertFalse($this->endpoint()->shouldWelcome());
        $this->assertTrue(Onboarding::for($this->subject)->hasBeenWelcomed());
    }

    public function test_not_now_means_this_session_and_not_this_person(): void
    {
        $this->endpoint()->remindMeLater();

        $this->assertFalse($this->endpoint()->shouldWelcome());

        // Nothing was written down: "later" is an answer about now, and the next
        // time they log in is a new now.
        $this->assertFalse(Onboarding::for($this->subject)->hasBeenWelcomed());
        $this->assertDatabaseMissing('onboarding_preferences', ['welcomed_at' => now()]);

        session()->flush();

        $this->assertTrue($this->endpoint()->shouldWelcome());
    }

    public function test_never_means_never_and_takes_the_floating_button_with_it(): void
    {
        $this->endpoint()->neverShowOnboarding();

        $this->assertTrue($this->endpoint()->isOnboardingHidden());
        $this->assertFalse($this->endpoint()->shouldWelcome());

        // The point of the whole feature: no welcome, and no ring in the corner
        // either. Not even a small reminder of the thing they turned off.
        session()->flush();

        $this->assertTrue($this->endpoint()->isOnboardingHidden());
    }

    public function test_it_can_be_turned_back_on(): void
    {
        $endpoint = $this->endpoint();

        $endpoint->neverShowOnboarding();
        $endpoint->showOnboardingAgain();

        $this->assertFalse($this->endpoint()->isOnboardingHidden());

        // The welcome does not come back with it: they have seen it. What comes
        // back is the checklist, which is what they turned off.
        $this->assertFalse($this->endpoint()->shouldWelcome());
    }

    public function test_nobody_is_welcomed_to_an_empty_panel(): void
    {
        OnboardingFlow::query()->update(['is_active' => false]);

        $this->assertFalse($this->endpoint()->shouldWelcome());
    }

    public function test_the_decision_belongs_to_one_subject_in_one_scope(): void
    {
        $tenant = Subject::create(['name' => 'Acme']);
        $other  = Subject::create(['name' => 'Globex']);

        Onboarding::for($this->subject, $tenant)->hide();

        $this->assertTrue(Onboarding::for($this->subject, $tenant)->isHidden());

        // The same person, in another tenant, has not asked for anything.
        $this->assertFalse(Onboarding::for($this->subject, $other)->isHidden());
    }

    private function endpoint(): OnboardingEndpoint
    {
        return new OnboardingEndpoint();
    }
}
