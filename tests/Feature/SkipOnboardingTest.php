<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Wallacemartinss\FilamentOnboarding\Enums\CompletionMode;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\{FilamentOnboardingPlugin, SubjectOnboarding};
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingFlow, OnboardingStep};
use Wallacemartinss\FilamentOnboarding\Tests\Fixtures\{OnboardingEndpoint, Subject};
use Wallacemartinss\FilamentOnboarding\Tests\TestCase;
use Wallacemartinss\FilamentOnboarding\Widgets\OnboardingChecklistWidget;

/**
 * The per-user switch. Once the application declares somebody done with
 * onboarding — an onboarded_at column, typically — the whole machine steps
 * aside: no surface mounts, no progress row is read, and the Livewire endpoints
 * a browser could still call write nothing.
 */
class SkipOnboardingTest extends TestCase
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
            'key'             => 'first-thing',
            'title'           => ['en' => 'Do the first thing'],
            'completion_mode' => CompletionMode::Manual,
            'is_active'       => true,
            'sort_order'      => 1,
        ]);
    }

    public function test_a_skipped_subject_has_no_current_onboarding(): void
    {
        Onboarding::skipWhen(fn (): bool => true);

        $this->assertNull(Onboarding::current());
    }

    public function test_an_unskipped_subject_keeps_it(): void
    {
        Onboarding::skipWhen(fn (): bool => false);

        $this->assertInstanceOf(SubjectOnboarding::class, Onboarding::current());
    }

    public function test_the_switch_reads_the_subject(): void
    {
        // The verified_at column stands in for the onboarded_at the README
        // suggests: a timestamp on the subject, flipped by the application.
        Onboarding::skipWhen(fn (Subject $subject): bool => $subject->verified_at !== null);

        $this->assertInstanceOf(SubjectOnboarding::class, Onboarding::current());

        $this->subject->update(['verified_at' => now()]);

        $this->assertNull(Onboarding::current());
    }

    public function test_the_endpoints_write_nothing_for_a_skipped_subject(): void
    {
        Onboarding::skipWhen(fn (): bool => true);

        // The launcher never renders for a skipped subject, but its methods are
        // still network endpoints a browser can call by hand.
        $endpoint = new OnboardingEndpoint();
        $endpoint->completeStep('first-thing');
        $endpoint->skipStep('first-thing');

        $this->assertDatabaseCount('onboarding_step_progress', 0);
        $this->assertDatabaseCount('onboarding_flow_progress', 0);
    }

    public function test_the_widget_hides_for_a_skipped_subject(): void
    {
        $this->assertTrue(OnboardingChecklistWidget::canView());

        Onboarding::skipWhen(fn (): bool => true);

        $this->assertFalse(OnboardingChecklistWidget::canView());
    }

    public function test_the_plugin_forwards_the_switch(): void
    {
        $plugin = FilamentOnboardingPlugin::make()
            ->skipWhen(fn (Subject $subject): bool => $subject->name === 'Ada');

        $plugin->boot(Filament::getPanel('test'));

        $this->assertNull(Onboarding::current());
    }

    public function test_for_still_reaches_a_skipped_subject(): void
    {
        Onboarding::skipWhen(fn (): bool => true);

        // The explicit API ignores the switch on purpose: resetting or
        // inspecting a done subject's onboarding must remain possible.
        $onboarding = Onboarding::for($this->subject);

        $this->assertInstanceOf(SubjectOnboarding::class, $onboarding);

        $onboarding->complete('first-thing');

        $this->assertTrue($onboarding->flow('journey')->isCompleted());
    }

    public function test_the_switch_receives_the_scope_too(): void
    {
        $seen = null;

        Onboarding::resolveScopeUsing(fn (): Subject => $this->subject);
        Onboarding::skipWhen(function (Model $subject, ?Model $scope) use (&$seen): bool {
            $seen = $scope;

            return false;
        });

        Onboarding::current();

        $this->assertTrue($this->subject->is($seen));
    }
}
