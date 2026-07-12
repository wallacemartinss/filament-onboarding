<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Enums;

use Filament\Support\Contracts\{HasDescription, HasLabel};

enum CompletionMode: string implements HasDescription, HasLabel
{
    /**
     * The subject ticks the step off by hand.
     */
    case Manual = 'manual';

    /**
     * A named condition registered by the application decides, so steps the
     * subject already fulfilled before the flow existed come back completed.
     */
    case Condition = 'condition';

    /**
     * Reaching a URL completes the step.
     */
    case Visit = 'visit';

    /**
     * Watching the step's video completes it, once enough of it has been seen.
     */
    case Video = 'video';

    /**
     * Only application code completes it, through Onboarding::for($user)->complete('key').
     */
    case Programmatic = 'programmatic';

    public function getLabel(): string
    {
        return __("filament-onboarding::onboarding.enums.completion_mode.{$this->value}.label");
    }

    public function getDescription(): string
    {
        return __("filament-onboarding::onboarding.enums.completion_mode.{$this->value}.description");
    }

    /**
     * Whether the subject may tick the step off in the checklist.
     */
    public function isSelfServed(): bool
    {
        return $this === self::Manual;
    }
}
