<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The comparisons a condition written in the panel may make.
 *
 * A closed list, on purpose. What an author picks here reaches the database, and
 * the only safe way to let somebody build a query from a form is to let them
 * choose from operators that were written by hand — never to pass their text
 * through as SQL.
 */
enum ConditionOperator: string implements HasLabel
{
    case Equals = 'equals';

    case NotEquals = 'not_equals';

    case GreaterThan = 'greater_than';

    case GreaterThanOrEqual = 'greater_than_or_equal';

    case LessThan = 'less_than';

    case LessThanOrEqual = 'less_than_or_equal';

    case Contains = 'contains';

    case IsSet = 'is_set';

    case IsEmpty = 'is_empty';

    public function getLabel(): string
    {
        return __("filament-onboarding::onboarding.enums.condition_operator.{$this->value}");
    }

    /**
     * Whether the author still has to say *what* to compare against. "Is set"
     * does not: the question is the whole of it.
     */
    public function needsValue(): bool
    {
        return !in_array($this, [self::IsSet, self::IsEmpty], true);
    }

    /**
     * The SQL operator, for the ones that have one.
     */
    public function sql(): ?string
    {
        return match ($this) {
            self::Equals               => '=',
            self::NotEquals            => '!=',
            self::GreaterThan          => '>',
            self::GreaterThanOrEqual   => '>=',
            self::LessThan             => '<',
            self::LessThanOrEqual      => '<=',
            self::Contains             => 'like',
            self::IsSet, self::IsEmpty => null,
        };
    }

    /**
     * Whether a value held in PHP satisfies this comparison. Used for attribute
     * conditions, which ask about the subject already in memory rather than
     * going back to the database for it.
     */
    public function matches(mixed $actual, mixed $expected): bool
    {
        // A boolean written in a form arrives as a string; a column cast to bool
        // arrives as a bool. Compare them as what they are, not as what they were
        // typed as.
        if (is_bool($actual) && !is_bool($expected)) {
            $expected = filter_var($expected, FILTER_VALIDATE_BOOLEAN);
        }

        return match ($this) {
            self::IsSet   => filled($actual),
            self::IsEmpty => blank($actual),
            self::Equals  => $this->isNumeric($actual, $expected)
                ? (float) $actual === (float) $expected
                : $actual == $expected,
            self::NotEquals          => !$this->matchesAs(self::Equals, $actual, $expected),
            self::GreaterThan        => $this->compare($actual, $expected) > 0,
            self::GreaterThanOrEqual => $this->compare($actual, $expected) >= 0,
            self::LessThan           => $this->compare($actual, $expected) < 0,
            self::LessThanOrEqual    => $this->compare($actual, $expected) <= 0,
            self::Contains           => filled($actual)
                && str_contains(mb_strtolower((string) $actual), mb_strtolower((string) $expected)),
        };
    }

    private function matchesAs(self $operator, mixed $actual, mixed $expected): bool
    {
        return $operator->matches($actual, $expected);
    }

    private function isNumeric(mixed $actual, mixed $expected): bool
    {
        return is_numeric($actual) && is_numeric($expected);
    }

    private function compare(mixed $actual, mixed $expected): int
    {
        if ($this->isNumeric($actual, $expected)) {
            return (float) $actual <=> (float) $expected;
        }

        return (string) $actual <=> (string) $expected;
    }
}
