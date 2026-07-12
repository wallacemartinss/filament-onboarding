<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Assets;

use Filament\Support\Assets\Css;

class VersionedCss extends Css
{
    use HasContentVersion;
}
