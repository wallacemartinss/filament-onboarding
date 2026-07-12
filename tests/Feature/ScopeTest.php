<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use Wallacemartinss\FilamentOnboarding\Enums\{CompletionMode, StepType};
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingFlow, OnboardingStep};
use Wallacemartinss\FilamentOnboarding\Tests\Fixtures\{OnboardingEndpoint, Subject};
use Wallacemartinss\FilamentOnboarding\Tests\TestCase;

/**
 * Progress belongs to a subject *within a scope*: the same user onboards
 * separately in each tenant.
 *
 * The scope comes from the panel, and the surfaces are plain Livewire components
 * hanging off the layout — so on an update request the panel may not be there to
 * ask, and a lost tenant does not throw: the resolver quietly answers null. The
 * tick would then land in a row nobody reads, and every tenant that user belongs
 * to would share one bucket.
 *
 * Which is why the scope is captured when the page is rendered and carried on the
 * component from then on. The test simulates the failure directly: the tenant is
 * there at mount and gone at click.
 */
class ScopeTest extends TestCase
{
    private Subject $subject;

    private Subject $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = Subject::create(['name' => 'Ada']);
        $this->tenant  = Subject::create(['name' => 'Acme']);

        Onboarding::resolveSubjectUsing(fn (): Subject => $this->subject);
        Onboarding::resolveScopeUsing(fn (): Subject => $this->tenant);

        $flow = OnboardingFlow::create([
            'key'       => 'journey',
            'title'     => ['en' => 'Get started'],
            'is_active' => true,
        ]);

        OnboardingStep::create([
            'flow_id'         => $flow->id,
            'key'             => 'tick-me',
            'type'            => StepType::Task,
            'title'           => ['en' => 'Tick me'],
            'completion_mode' => CompletionMode::Manual,
            'is_required'     => false,
        ]);
    }

    public function test_the_tick_lands_in_the_tenant_the_page_was_rendered_for(): void
    {
        $endpoint = new OnboardingEndpoint();

        // The page renders: the panel knows the tenant.
        $endpoint->mountInteractsWithOnboarding();

        // The click comes back on a request where it does not.
        Onboarding::resolveScopeUsing(fn (): mixed => null);

        $endpoint->completeStep('tick-me');

        $this->assertDatabaseHas('onboarding_step_progress', [
            'subject_id' => $this->subject->getKey(),
            'scope_id'   => $this->tenant->getKey(),
        ]);

        $this->assertDatabaseMissing('onboarding_step_progress', [
            'scope_id' => null,
        ]);
    }

    public function test_the_step_reads_back_as_done_under_that_tenant(): void
    {
        $endpoint = new OnboardingEndpoint();

        $endpoint->mountInteractsWithOnboarding();

        Onboarding::resolveScopeUsing(fn (): mixed => null);

        $endpoint->completeStep('tick-me');

        // Which is the whole point: a tick that goes to the wrong row is a tick
        // that disappears the moment the page is loaded again.
        $state = Onboarding::for($this->subject, $this->tenant)->flow('journey')->step('tick-me');

        $this->assertTrue($state->isCompleted());
    }

    public function test_the_scope_is_not_something_the_browser_may_choose(): void
    {
        // Livewire refuses to hydrate a #[Locked] property from the payload, so a
        // crafted request cannot point the write at another tenant. This asserts
        // the attribute is there, because losing it would be silent.
        $property = new \ReflectionProperty(OnboardingEndpoint::class, 'onboardingScopeId');

        $this->assertNotEmpty($property->getAttributes(\Livewire\Attributes\Locked::class));
    }

    public function test_a_subject_without_a_tenant_cannot_end_up_with_two_progress_rows(): void
    {
        Onboarding::resolveScopeUsing(fn (): mixed => null);

        Onboarding::for($this->subject)->complete('tick-me');

        $row = \DB::table('onboarding_step_progress')->first();

        // The database is now willing to compare the absence of a scope, which is
        // what makes the unique index mean something: writing the row again is a
        // constraint violation, not a second row. Without it, two requests that
        // race — and the launcher renders on every page, in every tab — both look,
        // both miss, and both insert.
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        \DB::table('onboarding_step_progress')->insert([
            'id'           => (string) \Illuminate\Support\Str::uuid(),
            'flow_id'      => $row->flow_id,
            'step_id'      => $row->step_id,
            'subject_type' => $row->subject_type,
            'subject_id'   => $row->subject_id,
            'scope_type'   => $row->scope_type,
            'scope_id'     => $row->scope_id,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }
}
