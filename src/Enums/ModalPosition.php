<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Where the media modal sits on screen. Centred is the obvious default, but a
 * player parked in a corner lets the subject follow along on the real page
 * instead of watching over a blocked one.
 */
enum ModalPosition: string implements HasLabel
{
    case Center = 'center';

    case Top = 'top';

    case Bottom = 'bottom';

    case TopLeft = 'top-left';

    case TopRight = 'top-right';

    case BottomLeft = 'bottom-left';

    case BottomRight = 'bottom-right';

    public function getLabel(): string
    {
        return __("filament-onboarding::onboarding.enums.modal_position.{$this->value}");
    }

    /**
     * A modal in a corner leaves the page usable behind it, so it does not dim.
     */
    public function isDocked(): bool
    {
        return $this !== self::Center;
    }
}
