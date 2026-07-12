<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Actions;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Livewire\Component;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\States\StepState;

/**
 * A header button that starts one tour, right where the tour belongs.
 *
 * The launcher starts tours from the checklist; this starts one from the page
 * it explains — "how does this screen work?" answered on the screen itself:
 *
 *     StartTourAction::make('servers-tour'),
 *
 * The button only exists when the tour does: an unknown key, a step without a
 * tour, or one hidden from this subject by a visibility condition all make the
 * action disappear rather than render a button that does nothing. So a plan
 * that cannot see the journey never sees the invitation either.
 */
class StartTourAction
{
    public static function make(string $stepKey): Action
    {
        return Action::make("start-tour-{$stepKey}")
            ->label(__('filament-onboarding::onboarding.tour.start'))
            ->icon(Heroicon::OutlinedSparkles)
            ->color('gray')
            ->visible(fn (): bool => static::step($stepKey)?->hasTour() ?? false)
            ->action(function (Component $livewire) use ($stepKey): void {
                $step = static::step($stepKey);

                if (!$step instanceof StepState || !$step->hasTour()) {
                    return;
                }

                Onboarding::current()?->markSeen($stepKey);

                $livewire->dispatch('onboarding-tour-start', key: $stepKey, steps: $step->tour());
            });
    }

    protected static function step(string $stepKey): ?StepState
    {
        return Onboarding::current()?->stepState(
            $stepKey,
            Filament::getCurrentOrDefaultPanel()?->getId(),
        );
    }
}
