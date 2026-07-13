<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Wallacemartinss\FilamentOnboarding\Concerns\InteractsWithOnboarding;
use Wallacemartinss\FilamentOnboarding\Enums\CompletionMode;
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\FilamentOnboardingPlugin;
use Wallacemartinss\FilamentOnboarding\States\{FlowState, StepState};

/**
 * The journey, laid out: what the subject has done, what is next, what is left.
 *
 * Registered only when a panel asks for it — FilamentOnboardingPlugin::make()
 * ->progressPage() — since some products would rather keep onboarding to the
 * floating checklist alone.
 */
class OnboardingProgress extends Page
{
    use InteractsWithOnboarding;

    protected static bool $isDiscovered = false;

    protected string $view = 'filament-onboarding::pages.progress';

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return static::plugin()?->getProgressPageSlug() ?? 'onboarding';
    }

    public static function getNavigationLabel(): string
    {
        return static::plugin()?->getProgressPageLabel()
            ?? __('filament-onboarding::onboarding.page.title');
    }

    public function getTitle(): string
    {
        return static::getNavigationLabel();
    }

    public function getSubheading(): ?string
    {
        return __('filament-onboarding::onboarding.page.subheading');
    }

    public static function getNavigationIcon(): ?string
    {
        return static::plugin()?->getProgressPageIcon() ?? 'heroicon-o-map';
    }

    public static function getNavigationGroup(): ?string
    {
        return static::plugin()?->getProgressPageGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return static::plugin()?->getProgressPageSort();
    }

    /**
     * Hidden from the menu once there is nothing left to onboard — a finished
     * journey should not keep a permanent seat in the navigation.
     */
    public static function shouldRegisterNavigation(): bool
    {
        if (!(static::plugin()?->hasProgressPageNavigation() ?? true)) {
            return false;
        }

        return Onboarding::current()?->flows(
            \Filament\Facades\Filament::getCurrentOrDefaultPanel()?->getId()
        )->isNotEmpty() ?? false;
    }

    /**
     * Starting over throws work away, so it asks first — through Filament's own
     * confirmation modal, not a browser dialog.
     *
     * A button, and not a link. These two sat at the foot of a journey as bare
     * underlined words, which is how a control that *does* something ends up
     * reading like a footnote: nothing about them said they could be pressed, and
     * so nobody pressed them. Filament dresses a secondary action as a grey
     * button — the same one its own "Cancel" wears — and that is what these are.
     */
    public function restartFlowAction(): Action
    {
        return Action::make('restartFlow')
            ->label(__('filament-onboarding::onboarding.page.restart'))
            ->icon(Heroicon::ArrowPath)
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading(__('filament-onboarding::onboarding.page.restart'))
            ->modalDescription(__('filament-onboarding::onboarding.page.restart_confirm'))
            ->modalSubmitActionLabel(__('filament-onboarding::onboarding.page.restart'))
            ->action(function (array $arguments): void {
                $flowKey = $arguments['flow'] ?? null;

                $this->restartFlow($flowKey);

                // A journey made of conditions comes back completed the moment it
                // is asked again — the backup destination still exists. Without
                // saying so, the button looks broken: you click, and nothing
                // appears to happen.
                $reinstated = $this->resolveFlowState($flowKey)
                    ?->steps
                    ->filter(fn (StepState $step): bool => $step->isCompleted() && $step->step->completion_mode === CompletionMode::Condition)
                    ->count() ?? 0;

                Notification::make()
                    ->title(__('filament-onboarding::onboarding.page.restarted'))
                    ->body($reinstated > 0
                        ? __('filament-onboarding::onboarding.page.restarted_reinstated', ['count' => $reinstated])
                        : null)
                    ->success()
                    ->send();
            });
    }

    public function dismissFlowAction(): Action
    {
        return Action::make('dismissFlow')
            ->label(__('filament-onboarding::onboarding.checklist.dismiss'))
            ->icon(Heroicon::EyeSlash)
            ->color('gray')
            ->action(function (array $arguments): void {
                $this->dismissFlow($arguments['flow'] ?? null);
            });
    }

    public function restoreFlowAction(): Action
    {
        return Action::make('restoreFlow')
            ->label(__('filament-onboarding::onboarding.page.restore'))
            ->icon(Heroicon::Eye)
            ->color('gray')
            ->action(function (array $arguments): void {
                $this->restoreFlow($arguments['flow'] ?? null);
            });
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
     * Every journey of this panel — including the ones the subject has hidden or
     * already finished, which is the point of a page you can come back to.
     *
     * @return Collection<int, FlowState>
     */
    public function flows(): Collection
    {
        return Onboarding::current()?->flows($this->onboardingPanelId()) ?? collect();
    }

    protected static function plugin(): ?FilamentOnboardingPlugin
    {
        try {
            return FilamentOnboardingPlugin::get();
        } catch (\Throwable) {
            return null;
        }
    }
}
