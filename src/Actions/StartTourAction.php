<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Actions;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Radio;
use Filament\Support\Icons\Heroicon;
use Livewire\Component;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\States\StepState;

/**
 * A header button that offers the tours of the page it sits on.
 *
 * The launcher starts tours from the checklist; this starts one from the screen
 * it explains — "show me how this works", answered where the question is asked:
 *
 *     StartTourAction::make('servers-tour'),
 *     StartTourAction::make(['servers-tour', 'create-server-cloud-tour', 'create-server-byos-tour']),
 *
 * With one tour it simply starts it. With several — a screen can be walked more
 * than one way; a server is created *with* a provider or by bringing your own —
 * it asks which, naming each by its own title, and starts the one chosen. Nobody
 * has to guess what is behind a button.
 *
 * Only tours the subject can actually take are offered: an unknown key, a step
 * with no tour, or one hidden by a visibility condition is left out. When none
 * survive, the button is not there at all — so a plan that cannot see the journey
 * never sees the invitation either.
 */
class StartTourAction
{
    /**
     * @param  string|array<int, string>  $stepKeys
     */
    public static function make(string|array $stepKeys): Action
    {
        $keys = array_values((array) $stepKeys);

        return Action::make('start-tour-' . ($keys[0] ?? 'onboarding'))
            ->label(__('filament-onboarding::onboarding.tour.start'))
            ->icon(Heroicon::OutlinedSparkles)
            ->color('gray')
            ->visible(fn (): bool => filled(static::available($keys)))
            ->modalHeading(__('filament-onboarding::onboarding.tour.choose'))
            ->modalSubmitActionLabel(__('filament-onboarding::onboarding.tour.begin'))
            ->modalWidth('lg')
            // One tour needs no question, and Filament shows no modal for an empty
            // schema — so the button starts it outright.
            ->schema(fn (): array => count(static::available($keys)) > 1
                ? [static::chooser($keys)]
                : [])
            ->action(function (array $data, Component $livewire) use ($keys): void {
                $available = static::available($keys);

                $chosen = $data['tour'] ?? array_key_first($available);

                $step = $available[$chosen] ?? null;

                if (!$step instanceof StepState) {
                    return;
                }

                Onboarding::current()?->markSeen($step->step);

                $livewire->dispatch('onboarding-tour-start', key: $step->key(), steps: $step->tour());
            });
    }

    /**
     * @param  array<int, string>  $keys
     */
    protected static function chooser(array $keys): Radio
    {
        $available = static::available($keys);

        return Radio::make('tour')
            ->hiddenLabel()
            ->required()
            ->options(array_map(
                fn (StepState $step): string => $step->title() ?? $step->key(),
                $available,
            ))
            ->descriptions(array_filter(array_map(
                fn (StepState $step): ?string => $step->description(),
                $available,
            )))
            ->default(array_key_first($available));
    }

    /**
     * The tours behind these keys that this subject can actually take, in the
     * order they were asked for.
     *
     * @param  array<int, string>  $keys
     * @return array<string, StepState>
     */
    protected static function available(array $keys): array
    {
        $onboarding = Onboarding::current();

        if ($onboarding === null) {
            return [];
        }

        $panelId = Filament::getCurrentOrDefaultPanel()?->getId();

        $tours = [];

        foreach ($keys as $key) {
            $step = $onboarding->stepState($key, $panelId);

            if ($step instanceof StepState && $step->hasTour()) {
                $tours[$key] = $step;
            }
        }

        return $tours;
    }
}
