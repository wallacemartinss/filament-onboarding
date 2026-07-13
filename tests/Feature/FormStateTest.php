<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use Wallacemartinss\FilamentOnboarding\Enums\{CompletionMode, MediaType, StepType};
use Wallacemartinss\FilamentOnboarding\Support\FormState;
use Wallacemartinss\FilamentOnboarding\Tests\TestCase;

/**
 * The form state of a field backed by an enum comes in two shapes, and the whole
 * step editor hung off getting that wrong.
 *
 * Creating a step, the state is what the browser posted: the string 'tour'.
 * Editing one, the form was filled from the record, and the record's attribute is
 * cast — so the state is StepType::Tour, the enum itself.
 *
 * `$get('type') === StepType::Tour->value` is therefore true exactly half the
 * time. The other half it answers no, and a field guarded by it does not merely
 * misbehave — it is *not there*. The Tour tab vanished the moment somebody opened
 * a tour to edit it, taking every stop of that tour with it; the condition
 * dropdown went the same way, and so did the media fields. Nothing threw. The
 * form rendered less of itself, on the one path nobody tests: the second time you
 * open something.
 */
class FormStateTest extends TestCase
{
    public function test_it_recognises_the_state_a_form_being_filled_in_holds(): void
    {
        // Creating: the browser posted a value.
        $this->assertTrue(FormState::is('tour', StepType::Tour));
        $this->assertFalse(FormState::is('task', StepType::Tour));
    }

    public function test_it_recognises_the_state_a_loaded_record_holds(): void
    {
        // Editing: the record's cast handed the form an enum. This is the half
        // that was answering "no" to a question whose answer was plainly yes.
        $this->assertTrue(FormState::is(StepType::Tour, StepType::Tour));
        $this->assertFalse(FormState::is(StepType::Task, StepType::Tour));
    }

    public function test_the_two_shapes_answer_the_same(): void
    {
        foreach ([StepType::Task, StepType::Tour] as $case) {
            $this->assertSame(
                FormState::is($case, StepType::Tour),
                FormState::is($case->value, StepType::Tour),
                "The enum and its value disagree about {$case->value}, which is the whole bug.",
            );
        }

        foreach (CompletionMode::cases() as $case) {
            $this->assertSame(
                FormState::is($case, CompletionMode::Condition),
                FormState::is($case->value, CompletionMode::Condition),
            );
        }

        foreach (MediaType::cases() as $case) {
            $this->assertSame(
                FormState::is($case, MediaType::Video),
                FormState::is($case->value, MediaType::Video),
            );
        }
    }

    public function test_nothing_is_not_a_match(): void
    {
        $this->assertFalse(FormState::is(null, StepType::Tour));
        $this->assertFalse(FormState::is('', StepType::Tour));
    }

    public function test_it_hands_back_a_value_whichever_shape_it_was_given(): void
    {
        // A `(string)` cast would have done for one half and killed the request on
        // the other: an enum is not a string, and PHP says so by dying.
        $this->assertSame('tour', FormState::value(StepType::Tour));
        $this->assertSame('tour', FormState::value('tour'));
        $this->assertNull(FormState::value(null));
        $this->assertNull(FormState::value(''));
    }
}
