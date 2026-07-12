@php
    /** @var \Wallacemartinss\FilamentOnboarding\States\FlowState|null $flow */
@endphp

<div class="fio">
    @if ($flow && ! $flow->isDismissed() && (! $flow->isCompleted() || $showWhenCompleted))
        <div class="fio-card">
            <div class="fio-panel-header">
                <div class="fio-panel-title-row">
                    <div>
                        <h2 class="fio-panel-title">
                            @if ($flow->icon())
                                <x-filament::icon
                                    :icon="$flow->icon()"
                                    style="display: inline-block; width: 1.125rem; height: 1.125rem; vertical-align: -3px; margin-inline-end: 0.375rem;"
                                />
                            @endif

                            {{ $flow->title() }}
                        </h2>

                        @if ($flow->description())
                            <p class="fio-panel-description">{{ $flow->description() }}</p>
                        @endif
                    </div>

                    @if ($flow->isDismissible())
                        <button
                            type="button"
                            class="fio-icon-button"
                            wire:click="dismissFlow"
                            aria-label="{{ __('filament-onboarding::onboarding.checklist.dismiss') }}"
                            title="{{ __('filament-onboarding::onboarding.checklist.dismiss') }}"
                        >
                            <x-filament-onboarding::icons.x />
                        </button>
                    @endif
                </div>

                <div class="fio-progress">
                    <div class="fio-progress-track">
                        <div class="fio-progress-value" style="width: {{ $flow->percentage() }}%"></div>
                    </div>

                    <span class="fio-progress-label">
                        {{ $flow->resolvedCount() }}/{{ $flow->total() }}
                    </span>
                </div>
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
                </div>
            @else
                @include('filament-onboarding::components.steps', ['flow' => $flow])
            @endif
        </div>
    @endif
</div>
