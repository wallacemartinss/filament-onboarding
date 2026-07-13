<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Enums;

use Filament\Support\Contracts\{HasDescription, HasLabel};

/**
 * The two shapes a condition written in the panel can take.
 *
 * Between them they cover the questions onboarding actually asks — "have they
 * added a client yet?", "is their email verified?" — without a line of code.
 * Anything they cannot express is a condition class, and the package goes
 * looking for those on its own.
 */
enum ConditionType: string implements HasDescription, HasLabel
{
    /**
     * Counts rows of another model that belong to the subject: clients, servers,
     * invoices. "Has at least one" is the common case; the threshold makes
     * "has at least three" possible too.
     */
    case Aggregate = 'aggregate';

    /**
     * Asks about the subject itself — a column on the user: email verified,
     * a plan, a flag.
     */
    case Attribute = 'attribute';

    public function getLabel(): string
    {
        return __("filament-onboarding::onboarding.enums.condition_type.{$this->value}.label");
    }

    public function getDescription(): string
    {
        return __("filament-onboarding::onboarding.enums.condition_type.{$this->value}.description");
    }
}
