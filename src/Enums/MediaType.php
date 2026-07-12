<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Enums;

use Filament\Support\Contracts\{HasIcon, HasLabel};

enum MediaType: string implements HasIcon, HasLabel
{
    case None = 'none';

    case Image = 'image';

    case Video = 'video';

    public function getLabel(): string
    {
        return __("filament-onboarding::onboarding.enums.media_type.{$this->value}");
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::None  => 'heroicon-o-minus',
            self::Image => 'heroicon-o-photo',
            self::Video => 'heroicon-o-play-circle',
        };
    }
}
