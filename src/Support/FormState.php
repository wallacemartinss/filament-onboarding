<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Support;

use BackedEnum;

/**
 * Reading the state of a form field that is backed by an enum.
 *
 * A Select or a ToggleButtons whose options come from an enum does not hold one
 * kind of value — it holds two, and which one depends on how the form was filled:
 *
 *   creating — somebody picked an option, so the state is the option's **value**,
 *              a string, because that is what a browser posts.
 *
 *   editing  — the form was filled from a record, and the record's attribute is
 *              cast to the **enum**. The enum is what the state holds.
 *
 * So `$get('type') === StepType::Tour->value` is true exactly half the time. The
 * other half it silently answers no — and a field guarded by it is not merely
 * wrong, it is *absent*: the Tour tab disappeared the moment somebody opened a
 * tour to edit it, taking every stop of that tour with it, and the condition
 * dropdown went the same way. Nothing threw. The form simply rendered less of
 * itself, and it did so only on the path nobody tests, which is the second time
 * you open something.
 *
 * Ask through here instead, and both halves answer the same.
 */
final class FormState
{
    public static function is(mixed $state, BackedEnum $case): bool
    {
        if ($state instanceof BackedEnum) {
            return $state === $case;
        }

        return $state === $case->value;
    }

    /**
     * The state as the enum's value — the shape everything downstream (a config
     * lookup, a comparison, a key) expects. A `(string)` cast would do for one
     * half of the story and fatal on the other: an enum is not a string, and PHP
     * says so by dying.
     */
    public static function value(mixed $state): ?string
    {
        if ($state instanceof BackedEnum) {
            return (string) $state->value;
        }

        return blank($state) ? null : (string) $state;
    }
}
