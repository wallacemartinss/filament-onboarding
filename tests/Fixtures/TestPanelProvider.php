<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Fixtures;

use Filament\{Panel, PanelProvider};

/**
 * A panel for the surfaces to live on.
 *
 * The launcher is a Livewire component rendered into a panel, and half of what
 * the package promises — scoping a step to a panel, hiding one from a subject —
 * is only true if it is true *on a panel*. Testing those against no panel at all
 * would be testing the wrong thing.
 */
class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('test')
            ->path('test');
    }
}
