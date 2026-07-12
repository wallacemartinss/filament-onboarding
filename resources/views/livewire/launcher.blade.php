@php
    /** @var \Wallacemartinss\FilamentOnboarding\States\FlowState|null $flow */
    $circumference = 2 * M_PI * 25;
    $offset = $flow ? $circumference * (1 - ($flow->percentage() / 100)) : $circumference;
    $remaining = $flow ? $flow->pendingSteps()->count() : 0;
@endphp

<div class="fio">
    @if ($hasTours)
        @include('filament-onboarding::components.tour')
    @endif

    {{-- Always present: a step anywhere in the panel may carry an image or a
         video, and this is what opens it. --}}
    @include('filament-onboarding::components.media')

    @if ($hasLauncher && $flow && ! $flow->isDismissed())
        <div class="fio-launcher fio-launcher--{{ $position }}">
            @if ($isOpen)
                <div class="fio-panel fio-animate-in" role="dialog" aria-label="{{ $flow->title() }}">
                    <div class="fio-panel-header">
                        <div class="fio-panel-title-row">
                            <div>
                                <h2 class="fio-panel-title">{{ $flow->title() }}</h2>

                                @if ($flow->description())
                                    <p class="fio-panel-description">{{ $flow->description() }}</p>
                                @endif
                            </div>

                            <button
                                type="button"
                                class="fio-icon-button"
                                wire:click="toggle"
                                aria-label="{{ __('filament-onboarding::onboarding.checklist.close') }}"
                            >
                                <x-filament-onboarding::icons.x />
                            </button>
                        </div>

                        <div class="fio-progress">
                            <div class="fio-progress-track">
                                <div class="fio-progress-value" style="width: {{ $flow->percentage() }}%"></div>
                            </div>

                            <span class="fio-progress-label">
                                {{ $flow->resolvedCount() }}/{{ $flow->total() }}
                            </span>
                        </div>

                        {{-- More than one journey: the checklist shows one at a time,
                             so the others need a way in. --}}
                        @if ($flows->count() > 1)
                            <div class="fio-tabs">
                                @foreach ($flows as $candidate)
                                    <button
                                        type="button"
                                        wire:key="fio-tab-{{ $candidate->key() }}"
                                        wire:click="selectFlow(@js($candidate->key()))"
                                        @class([
                                            'fio-tab',
                                            'fio-tab--active' => $candidate->key() === $flow->key(),
                                        ])
                                    >
                                        {{ $candidate->title() }}
                                        <span class="fio-tab-count">{{ $candidate->percentage() }}%</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if ($flow->isCompleted())
                        <div class="fio-complete">
                            <div class="fio-complete-icon">
                                <x-filament-onboarding::icons.check />
                            </div>

                            <h3 class="fio-panel-title">
                                {{ __('filament-onboarding::onboarding.checklist.completed_title') }}
                            </h3>

                            <p class="fio-panel-description">
                                {{ __('filament-onboarding::onboarding.checklist.completed_description') }}
                            </p>

                            @if ($flow->isDismissible())
                                <div style="margin-block-start: 1rem;">
                                    <button type="button" class="fio-button fio-button--primary" wire:click="dismissFlow">
                                        {{ __('filament-onboarding::onboarding.checklist.done') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    @else
                        @include('filament-onboarding::components.steps', ['flow' => $flow])

                        @if ($flow->isDismissible())
                            <div class="fio-panel-footer">
                                <span class="fio-footer-note">
                                    {{ __('filament-onboarding::onboarding.checklist.footer_note') }}
                                </span>

                                <button type="button" class="fio-button fio-button--ghost" wire:click="dismissFlow">
                                    {{ __('filament-onboarding::onboarding.checklist.dismiss') }}
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            @endif

            <button
                type="button"
                class="fio-launcher-button"
                wire:click="toggle"
                aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                aria-label="{{ $flow->title() }}"
            >
                <svg class="fio-ring" viewBox="0 0 56 56" aria-hidden="true">
                    <circle class="fio-ring-track" cx="28" cy="28" r="25" />
                    <circle
                        class="fio-ring-value"
                        cx="28"
                        cy="28"
                        r="25"
                        stroke-dasharray="{{ $circumference }}"
                        stroke-dashoffset="{{ $offset }}"
                    />
                </svg>

                @if ($flow->isCompleted())
                    <x-filament-onboarding::icons.check />
                @else
                    <span class="fio-launcher-count">{{ $flow->percentage() }}%</span>

                    @if ($remaining > 0 && ! $isOpen)
                        <span class="fio-launcher-badge">{{ $remaining }}</span>
                    @endif
                @endif
            </button>
        </div>
    @endif
</div>
