<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Contracts;

/**
 * A condition that can name itself.
 *
 * Conditions registered from config have no label to go with them, and whoever
 * is authoring a step should not have to pick "has_backup_integration" out of a
 * dropdown. Implement this and the label — translated, since it is resolved per
 * request — is what they see instead.
 */
interface HasConditionLabel
{
    public static function label(): string;
}
