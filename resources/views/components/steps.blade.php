{{-- The list of steps, shared by the launcher panel and the dashboard widget.
     Both hosts use InteractsWithOnboarding, so the wire:click targets resolve
     against whichever component included this. --}}
@php
    /** @var \Wallacemartinss\FilamentOnboarding\States\FlowState $flow */
    $nextStep = $flow->nextStep();
@endphp

<div class="fio-steps">
    @foreach ($flow->steps as $step)
        <div
            wire:key="fio-step-{{ $step->key() }}"
            @class([
                'fio-step',
                'fio-step--done' => $step->isCompleted(),
                'fio-step--skipped' => $step->isSkipped(),
                'fio-step--next' => $nextStep?->key() === $step->key(),
            ])
        >
            @if ($step->canSelfComplete())
                <button
                    type="button"
                    class="fio-step-marker"
                    wire:click="completeStep(@js($step->key()), @js($flow->key()))"
                    wire:loading.attr="disabled"
                    title="{{ __('filament-onboarding::onboarding.checklist.mark_done') }}"
                >
                    <x-filament-onboarding::icons.check />
                </button>
            @else
                <span class="fio-step-marker" aria-hidden="true">
                    @if ($step->isCompleted())
                        <x-filament-onboarding::icons.check />
                    @elseif ($step->isSkipped())
                        <x-filament-onboarding::icons.minus />
                    @endif
                </span>
            @endif

            <div>
                <div class="fio-step-title">
                    @if ($step->icon())
                        <x-filament::icon
                            :icon="$step->icon()"
                            class="fio-inline-icon fio-inline-icon--sm"
                        />
                    @endif

                    {{ $step->title() }}
                </div>

                @if ($step->description())
                    <p class="fio-step-description">{{ $step->description() }}</p>
                @endif

                {{-- A button, not a clickable image: an <img> with a click handler
                     cannot be reached by keyboard and is announced to a screen
                     reader as decoration. --}}
                @if ($step->hasImage())
                    <button
                        type="button"
                        class="fio-thumb-button fio-stack-tight"
                        wire:click="openMedia(@js($step->key()), @js($flow->key()))"
                        aria-label="{{ __('filament-onboarding::onboarding.page.open_image', ['title' => $step->title()]) }}"
                    >
                        <img src="{{ $step->imageUrl() }}" alt="{{ $step->title() }}" class="fio-thumb" />
                    </button>
                @endif

                {{-- A finished step is not a dead end: the tour can be watched
                     again, the video replayed, the page revisited. People who
                     already have the data still want to see how it works. --}}
                @if ($step->isResolved() && $step->canReplay())
                    <div class="fio-step-actions">
                        @if ($step->hasTour())
                            <button type="button" class="fio-button fio-button--ghost" wire:click="startTour(@js($step->key()), @js($flow->key()))">
                                <x-filament-onboarding::icons.sparkles />
                                {{ __('filament-onboarding::onboarding.page.replay_tour') }}
                            </button>
                        @elseif ($step->hasVideo())
                            <button type="button" class="fio-button fio-button--ghost" wire:click="openMedia(@js($step->key()), @js($flow->key()))">
                                <x-filament-onboarding::icons.play />
                                {{ __('filament-onboarding::onboarding.page.replay_video') }}
                            </button>
                        @elseif ($step->url())
                            <a href="{{ $step->url() }}" class="fio-button fio-button--ghost">
                                {{ __('filament-onboarding::onboarding.page.open_again') }}
                                <x-filament-onboarding::icons.arrow-right />
                            </a>
                        @endif
                    </div>
                @endif

                {{-- canSkip() keeps this block alive for an optional step with
                     no destination at all: skipping is an action too, and the
                     progress page must not be the only place it exists. --}}
                @if ($step->isPending() && ($step->hasAction() || $step->hasVideo() || $step->canSkip()))
                    <div class="fio-step-actions">
                        @if ($step->hasVideo())
                            <button
                                type="button"
                                class="fio-button fio-button--primary"
                                wire:click="openMedia(@js($step->key()), @js($flow->key()))"
                            >
                                <x-filament-onboarding::icons.play />
                                {{ $step->ctaLabel() ?? __('filament-onboarding::onboarding.media.watch') }}
                            </button>
                        @elseif ($step->hasTour())
                            <button
                                type="button"
                                class="fio-button fio-button--primary"
                                wire:click="startTour(@js($step->key()), @js($flow->key()))"
                            >
                                <x-filament-onboarding::icons.sparkles />
                                {{ $step->ctaLabel() ?? __('filament-onboarding::onboarding.checklist.start_tour') }}
                            </button>
                        @elseif ($step->url())
                            <a href="{{ $step->url() }}" class="fio-button fio-button--primary">
                                {{ $step->ctaLabel() ?? __('filament-onboarding::onboarding.checklist.go') }}
                                <x-filament-onboarding::icons.arrow-right />
                            </a>
                        @endif

                        @if ($step->canSkip())
                            <button
                                type="button"
                                class="fio-button fio-button--ghost"
                                wire:click="skipStep(@js($step->key()), @js($flow->key()))"
                            >
                                {{ __('filament-onboarding::onboarding.checklist.skip') }}
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>
