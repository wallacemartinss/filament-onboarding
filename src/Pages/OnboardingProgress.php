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
     */
    public function restartFlowAction(): Action
    {
        return Action::make('restartFlow')
            ->label(__('filament-onboarding::onboarding.page.restart'))
            ->icon(Heroicon::ArrowPath)
            ->color('gray')
            ->link()
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
            ->color('gray')
            ->link()
            ->action(function (array $arguments): void {
                $this->dismissFlow($arguments['flow'] ?? null);
            });
    }

    public function restoreFlowAction(): Action
    {
        return Action::make('restoreFlow')
            ->label(__('filament-onboarding::onboarding.page.restore'))
            ->color('gray')
            ->link()
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
