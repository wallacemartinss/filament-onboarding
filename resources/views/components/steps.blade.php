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
                    wire:click="completeStep(@js($step->key()))"
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
                            style="display: inline-block; width: 0.9375rem; height: 0.9375rem; vertical-align: -2px; margin-inline-end: 0.25rem;"
                        />
                    @endif

                    {{ $step->title() }}
                </div>

                @if ($step->description())
                    <p class="fio-step-description">{{ $step->description() }}</p>
                @endif

                @if ($step->hasImage())
                    <img
                        src="{{ $step->imageUrl() }}"
                        alt="{{ $step->title() }}"
                        class="fio-thumb"
                        style="margin-block-start: 0.5rem;"
                        wire:click="openMedia(@js($step->key()))"
                    />
                @endif

                @if ($step->isPending() && ($step->hasAction() || $step->hasVideo()))
                    <div class="fio-step-actions">
                        @if ($step->hasVideo())
                            <button
                                type="button"
                                class="fio-button fio-button--primary"
                                wire:click="openMedia(@js($step->key()))"
                            >
                                <x-filament-onboarding::icons.play />
                                {{ $step->ctaLabel() ?? __('filament-onboarding::onboarding.media.watch') }}
                            </button>
                        @elseif ($step->hasTour())
                            <button
                                type="button"
                                class="fio-button fio-button--primary"
                                wire:click="startTour(@js($step->key()))"
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
                                wire:click="skipStep(@js($step->key()))"
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
