<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Widgets;

use Filament\Widgets\Widget;
use Livewire\Attributes\On;
use Wallacemartinss\FilamentOnboarding\Concerns\InteractsWithOnboarding;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;

/**
 * The checklist as a dashboard card. Disappears once the flow is finished or
 * dismissed, so a seasoned account is not nagged forever.
 */
class OnboardingChecklistWidget extends Widget
{
    use InteractsWithOnboarding;

    protected static bool $isDiscovered = false;

    protected string $view = 'filament-onboarding::widgets.checklist';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -10;

    /**
     * Keep showing the finished flow for a while? Set to true and the widget
     * congratulates instead of vanishing.
     */
    public bool $showWhenCompleted = false;

    public static function canView(): bool
    {
        return Onboarding::current()?->currentFlow(
            \Filament\Facades\Filament::getCurrentOrDefaultPanel()?->getId()
        ) !== null;
    }

    #[On('onboarding-updated')]
    public function refreshOnboarding(): void
    {
        // Re-renders with fresh progress.
    }

    #[On('onboarding-tour-finished')]
    public function onTourFinished(string $key): void
    {
        $this->finishTour($key);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'flow' => $this->flowState(),
        ];
    }
}
