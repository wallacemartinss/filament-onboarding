<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use Wallacemartinss\FilamentOnboarding\Enums\{CompletionMode, ConditionOperator, ConditionType, StepType};
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingCondition, OnboardingFlow, OnboardingStep};
use Wallacemartinss\FilamentOnboarding\Tests\Fixtures\{Note, Subject};
use Wallacemartinss\FilamentOnboarding\Tests\TestCase;

/**
 * Conditions written in the panel.
 *
 * The package's whole argument is that a journey is a thing you write, not a
 * thing you deploy — and it held right up to the most valuable kind of step,
 * the one that completes itself, which needed a closure in a service provider
 * and a commit to go with it. Imagine a product person having to open a pull
 * request to ask "have they added a client yet?".
 *
 * Now that question is a row. These tests are about it being a *safe* row: the
 * model comes from an allowlist, the column from that table's real columns, the
 * operator from an enum, and the one thing an author freely types — the value —
 * is bound rather than interpolated.
 */
class ConditionBuilderTest extends TestCase
{
    private Subject $subject;

    private OnboardingFlow $flow;

    protected function setUp(): void
    {
        parent::setUp();

        // The application says what may be counted. Left empty this discovers
        // app/Models, which does not exist in the package's own test kernel.
        config()->set('filament-onboarding.conditions_builder.models', [Note::class]);
        config()->set('filament-onboarding.conditions_builder.subject_model', Subject::class);

        $this->subject = Subject::create(['name' => 'Ada']);

        Onboarding::resolveSubjectUsing(fn (): Subject => $this->subject);

        $this->flow = OnboardingFlow::create([
            'key'       => 'journey',
            'title'     => ['en' => 'Get started'],
            'is_active' => true,
        ]);
    }

    public function test_a_condition_written_in_the_panel_completes_a_step(): void
    {
        $this->condition('has_note', [
            'type'           => ConditionType::Aggregate,
            'model'          => Note::class,
            'subject_column' => 'subject_id',
            'minimum'        => 1,
        ]);

        $this->step('add-a-note', 'has_note');

        // Nothing yet — and nothing was registered in code, either. The question
        // is a row, and it answers no.
        $this->assertFalse($this->state('add-a-note')->isCompleted());

        Note::create(['subject_id' => $this->subject->id, 'title' => 'First']);

        // And it answers yes the moment it is true — retroactively, which is the
        // whole point: nobody is asked to confirm work they already did.
        $this->assertTrue($this->state('add-a-note')->isCompleted());
    }

    public function test_it_counts_only_what_belongs_to_the_subject(): void
    {
        $this->condition('has_note', [
            'type'           => ConditionType::Aggregate,
            'model'          => Note::class,
            'subject_column' => 'subject_id',
        ]);

        $this->step('add-a-note', 'has_note');

        $somebodyElse = Subject::create(['name' => 'Grace']);

        Note::create(['subject_id' => $somebodyElse->id, 'title' => 'Not hers']);

        $this->assertFalse($this->state('add-a-note')->isCompleted());
    }

    public function test_a_threshold_asks_for_more_than_one(): void
    {
        $this->condition('is_established', [
            'type'           => ConditionType::Aggregate,
            'model'          => Note::class,
            'subject_column' => 'subject_id',
            'minimum'        => 3,
        ]);

        $this->step('be-established', 'is_established');

        Note::create(['subject_id' => $this->subject->id]);
        Note::create(['subject_id' => $this->subject->id]);

        $this->assertFalse($this->state('be-established')->isCompleted());

        Note::create(['subject_id' => $this->subject->id]);

        $this->assertTrue($this->state('be-established')->isCompleted());
    }

    public function test_filters_narrow_what_counts(): void
    {
        $this->condition('has_published_note', [
            'type'           => ConditionType::Aggregate,
            'model'          => Note::class,
            'subject_column' => 'subject_id',
            'filters'        => [
                ['column' => 'is_published', 'operator' => ConditionOperator::Equals->value, 'value' => 'true'],
            ],
        ]);

        $this->step('publish-a-note', 'has_published_note');

        Note::create(['subject_id' => $this->subject->id, 'is_published' => false]);

        $this->assertFalse($this->state('publish-a-note')->isCompleted());

        Note::create(['subject_id' => $this->subject->id, 'is_published' => true]);

        $this->assertTrue($this->state('publish-a-note')->isCompleted());
    }

    public function test_an_attribute_condition_asks_about_the_subject_itself(): void
    {
        $this->condition('is_verified', [
            'type'    => ConditionType::Attribute,
            'filters' => [
                ['column' => 'verified_at', 'operator' => ConditionOperator::IsSet->value],
            ],
        ]);

        $this->step('verify-yourself', 'is_verified');

        $this->assertFalse($this->state('verify-yourself')->isCompleted());

        $this->subject->forceFill(['verified_at' => now()])->save();

        $this->assertTrue($this->state('verify-yourself')->isCompleted());
    }

    public function test_a_condition_counts_within_the_tenant_it_is_asked_in(): void
    {
        $this->condition('has_note', [
            'type'           => ConditionType::Aggregate,
            'model'          => Note::class,
            'subject_column' => 'subject_id',
            'scope_column'   => 'tenant_id',
        ]);

        $this->step('add-a-note', 'has_note');

        $acme   = Subject::create(['name' => 'Acme']);
        $globex = Subject::create(['name' => 'Globex']);

        Note::create(['subject_id' => $this->subject->id, 'tenant_id' => $acme->id]);

        // The same person, the same question, two tenants: they have done it in
        // one of them, and onboarding in the other has not moved.
        $this->assertTrue(
            Onboarding::for($this->subject, $acme)->flow('journey')->step('add-a-note')->isCompleted()
        );

        $this->assertFalse(
            Onboarding::for($this->subject, $globex)->flow('journey')->step('add-a-note')->isCompleted()
        );
    }

    public function test_a_model_the_application_does_not_allow_is_never_queried(): void
    {
        $this->condition('has_note', [
            'type'           => ConditionType::Aggregate,
            'model'          => Note::class,
            'subject_column' => 'subject_id',
        ]);

        $this->step('add-a-note', 'has_note');

        Note::create(['subject_id' => $this->subject->id]);

        $this->assertTrue($this->state('add-a-note')->isCompleted());

        // The allowlist is asked again at evaluation, not only when the record was
        // written: a model taken off it stops being queried, without anybody
        // having to remember the conditions that named it.
        config()->set('filament-onboarding.conditions_builder.models', []);
        config()->set('filament-onboarding.conditions_builder.path', __DIR__ . '/does-not-exist');

        Onboarding::for($this->subject)->uncomplete('add-a-note');

        $this->assertFalse($this->state('add-a-note')->isCompleted());
    }

    public function test_what_an_author_types_is_a_value_and_never_sql(): void
    {
        $this->condition('injected', [
            'type'           => ConditionType::Aggregate,
            'model'          => Note::class,
            'subject_column' => 'subject_id',
            'filters'        => [
                ['column' => 'title', 'operator' => ConditionOperator::Equals->value, 'value' => "x'); drop table notes; --"],
            ],
        ]);

        $this->step('nope', 'injected');

        Note::create(['subject_id' => $this->subject->id, 'title' => 'First']);

        // It asks whether the title equals that string. It does not, so: no. And
        // the notes table is still there to be asked again.
        $this->assertFalse($this->state('nope')->isCompleted());
        $this->assertSame(1, Note::query()->count());
    }

    public function test_a_broken_condition_answers_no_rather_than_throwing(): void
    {
        // A column that does not exist — a migration rolled back, a rename.
        $this->condition('broken', [
            'type'           => ConditionType::Aggregate,
            'model'          => Note::class,
            'subject_column' => 'subject_id',
            'filters'        => [
                ['column' => 'no_such_column', 'operator' => ConditionOperator::Equals->value, 'value' => '1'],
            ],
        ]);

        $this->step('broken-step', 'broken');

        Note::create(['subject_id' => $this->subject->id]);

        // Onboarding is not the product, and must never be why it is down.
        $this->assertFalse($this->state('broken-step')->isCompleted());
    }

    public function test_a_condition_written_in_the_panel_can_hide_a_step_too(): void
    {
        $this->condition('is_established', [
            'type'           => ConditionType::Aggregate,
            'model'          => Note::class,
            'subject_column' => 'subject_id',
            'minimum'        => 3,
        ]);

        OnboardingStep::create([
            'flow_id'              => $this->flow->id,
            'key'                  => 'advanced',
            'type'                 => StepType::Task,
            'title'                => ['en' => 'Advanced'],
            'completion_mode'      => CompletionMode::Manual,
            'visibility_condition' => 'is_established',
        ]);

        $this->step('basic', null);

        // Same registry, different question: this one decides whether the step
        // exists for them at all.
        $this->assertNull(Onboarding::for($this->subject)->flow('journey')->step('advanced'));

        Note::create(['subject_id' => $this->subject->id]);
        Note::create(['subject_id' => $this->subject->id]);
        Note::create(['subject_id' => $this->subject->id]);

        $this->assertNotNull(Onboarding::for($this->subject)->flow('journey')->step('advanced'));
    }

    public function test_an_inactive_condition_never_passes(): void
    {
        $condition = $this->condition('has_note', [
            'type'           => ConditionType::Aggregate,
            'model'          => Note::class,
            'subject_column' => 'subject_id',
        ]);

        $this->step('add-a-note', 'has_note');

        Note::create(['subject_id' => $this->subject->id]);

        $this->assertTrue($this->state('add-a-note')->isCompleted());

        Onboarding::for($this->subject)->uncomplete('add-a-note');

        $condition->forceFill(['is_active' => false])->save();

        // Switched off, it is not a question anybody is asking any more — and an
        // unasked question is not one to answer yes to.
        $this->assertFalse($this->state('add-a-note')->isCompleted());
    }

    public function test_code_wins_a_name_clash(): void
    {
        Onboarding::condition('has_note', fn (): bool => false, label: 'The code one');

        $this->condition('has_note', [
            'type'           => ConditionType::Aggregate,
            'model'          => Note::class,
            'subject_column' => 'subject_id',
            'label'          => ['en' => 'The panel one'],
        ]);

        $this->step('add-a-note', 'has_note');

        Note::create(['subject_id' => $this->subject->id]);

        // The row says yes; the class says no. The class wins — a row must not be
        // able to quietly redefine what a step already means.
        $this->assertFalse($this->state('add-a-note')->isCompleted());
        $this->assertSame('The code one', Onboarding::conditions()->options()['has_note']);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function condition(string $key, array $attributes): OnboardingCondition
    {
        return OnboardingCondition::create([
            'key'       => $key,
            'label'     => ['en' => $key],
            'is_active' => true,
            ...$attributes,
        ]);
    }

    private function step(string $key, ?string $conditionKey): OnboardingStep
    {
        return OnboardingStep::create([
            'flow_id'         => $this->flow->id,
            'key'             => $key,
            'type'            => StepType::Task,
            'title'           => ['en' => $key],
            'completion_mode' => $conditionKey === null ? CompletionMode::Manual : CompletionMode::Condition,
            'condition_key'   => $conditionKey,
            'is_required'     => false,
        ]);
    }

    private function state(string $key): \Wallacemartinss\FilamentOnboarding\States\StepState
    {
        return Onboarding::for($this->subject)->flow('journey')->step($key);
    }
}
