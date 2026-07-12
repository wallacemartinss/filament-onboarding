<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use Wallacemartinss\FilamentOnboarding\Enums\{CompletionMode, StepType};
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingFlow, OnboardingStep};
use Wallacemartinss\FilamentOnboarding\Support\TranslatableText;
use Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Subject;
use Wallacemartinss\FilamentOnboarding\Tests\TestCase;

class OnboardingTest extends TestCase
{
    private Subject $subject;

    private OnboardingFlow $flow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = Subject::create(['name' => 'Ada']);

        $this->flow = OnboardingFlow::create([
            'key'       => 'journey',
            'title'     => ['en' => 'Get started', 'pt_BR' => 'Comece aqui'],
            'is_active' => true,
        ]);
    }

    public function test_it_completes_a_manual_step(): void
    {
        $this->step('first');
        $this->step('second');

        Onboarding::for($this->subject)->complete('first');

        $flow = Onboarding::for($this->subject)->flow('journey');

        $this->assertSame(50, $flow->percentage());
        $this->assertTrue($flow->step('first')->isCompleted());
        $this->assertSame('second', $flow->nextStep()->key());
    }

    public function test_a_condition_completes_a_step_retroactively(): void
    {
        $this->step('has-server', [
            'completion_mode' => CompletionMode::Condition,
            'condition_key'   => 'has_server',
        ]);

        Onboarding::condition('has_server', fn (): bool => true);

        $flow = Onboarding::for($this->subject)->flow('journey');

        $this->assertTrue($flow->step('has-server')->isCompleted());
        $this->assertTrue($flow->isCompleted());
    }

    public function test_an_unregistered_condition_never_completes_a_step(): void
    {
        $this->step('ghost', [
            'completion_mode' => CompletionMode::Condition,
            'condition_key'   => 'dropped_condition',
        ]);

        $flow = Onboarding::for($this->subject)->flow('journey');

        $this->assertTrue($flow->step('ghost')->isPending());
    }

    public function test_a_visit_completes_the_step_that_names_it(): void
    {
        $this->step('visit', [
            'completion_mode' => CompletionMode::Visit,
            'visit_url'       => '/admin/*/servers/create',
        ]);

        Onboarding::for($this->subject)->handleVisit('/admin/acme/servers/create');

        $this->assertTrue(
            Onboarding::for($this->subject)->flow('journey')->step('visit')->isCompleted()
        );
    }

    public function test_a_required_step_cannot_be_skipped(): void
    {
        $this->step('required', ['is_required' => true]);

        Onboarding::for($this->subject)->skip('required');

        $this->assertTrue(
            Onboarding::for($this->subject)->flow('journey')->step('required')->isPending()
        );
    }

    public function test_content_is_read_in_the_current_locale(): void
    {
        app()->setLocale('pt_BR');
        $this->assertSame('Comece aqui', $this->flow->translate('title'));

        app()->setLocale('en');
        $this->assertSame('Get started', $this->flow->translate('title'));
    }

    public function test_it_falls_back_when_the_locale_has_no_content(): void
    {
        config()->set('app.fallback_locale', 'en');

        $this->assertSame('Hello', TranslatableText::resolve(['en' => 'Hello'], 'de'));
        $this->assertSame('Olá', TranslatableText::resolve(['pt' => 'Olá'], 'pt_BR'));
        $this->assertNull(TranslatableText::resolve([], 'en'));
    }

    public function test_step_urls_take_panel_parameters(): void
    {
        $step = $this->step('create', ['cta_url' => '/app/{tenant}/servers/create']);

        $this->assertSame('/app/acme/servers/create', $step->resolveUrl(['tenant' => 'acme']));
        $this->assertNull($step->resolveUrl([]));
    }

    public function test_a_dismissed_flow_stops_being_current(): void
    {
        $this->step('first');

        $this->assertNotNull(Onboarding::for($this->subject)->currentFlow());

        Onboarding::for($this->subject)->dismiss('journey');

        $this->assertNull(Onboarding::for($this->subject)->currentFlow());
    }

    /**
     * A stop can name the control that carries the application to it — the next
     * button of a wizard — so the field it explains is on screen when it speaks.
     */
    public function test_a_tour_stop_carries_the_control_that_advances_the_application(): void
    {
        $this->step('tour', [
            'type'       => StepType::Tour,
            'tour_steps' => [
                ['selector' => '#on-step-two', 'advance' => '[wire\\:click="nextStep"]', 'title' => ['en' => 'Name'], 'body' => ['en' => '...']],
                ['selector' => '#here-already', 'title' => ['en' => 'Here'], 'body' => ['en' => '...']],
            ],
        ]);

        $tour = Onboarding::for($this->subject)->flow('journey')->step('tour')->tour();

        $this->assertSame('[wire\\:click="nextStep"]', $tour[0]['advance']);
        $this->assertNull($tour[1]['advance']);
    }

    /**
     * The launcher sits in the layout of every page. A condition that throws must
     * not take the product down with it — a renamed relation, a database that
     * blinked, a class deleted while a flow still names it.
     */
    public function test_a_condition_that_throws_does_not_take_the_panel_down(): void
    {
        $this->step('has-server', [
            'completion_mode' => CompletionMode::Condition,
            'condition_key'   => 'explodes',
        ]);

        $this->step('visible-to-nobody', ['visibility_condition' => 'explodes']);

        Onboarding::condition('explodes', function (): bool {
            throw new \RuntimeException('the servers table is gone');
        });

        $flow = Onboarding::for($this->subject)->flow('journey');

        // Answered "no", both times: the step stays pending, the guarded step
        // stays hidden, and the page renders.
        $this->assertFalse($flow->step('has-server')->isCompleted());
        $this->assertNull($flow->step('visible-to-nobody'));
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
