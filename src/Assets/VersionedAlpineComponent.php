<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Assets;

use Filament\Support\Assets\AlpineComponent;

class VersionedAlpineComponent extends AlpineComponent
{
    use HasContentVersion;
}
