<x-filament-panels::page>
    <div class="fio fio-dash">
        @forelse ($this->flows() as $flow)
            @php
                $circumference = 2 * M_PI * 40;
                $offset = $circumference * (1 - ($flow->percentage() / 100));
                $next = $flow->nextStep();
                $skipped = $flow->steps->filter(fn ($step) => $step->isSkipped())->count();
            @endphp

            <section class="fio-journey" wire:key="fio-journey-{{ $flow->key() }}">
                <div class="fio-hero">
                    <div class="fio-hero-ring">
                        <svg viewBox="0 0 88 88" aria-hidden="true">
                            <circle class="fio-ring-track" cx="44" cy="44" r="40" />
                            <circle
                                class="fio-ring-value"
                                cx="44"
                                cy="44"
                                r="40"
                                stroke-dasharray="{{ $circumference }}"
                                stroke-dashoffset="{{ $offset }}"
                            />
                        </svg>

                        <span class="fio-hero-percentage">{{ $flow->percentage() }}%</span>
                    </div>

                    <div class="fio-hero-body">
                        <div class="fio-hero-heading">
                            <h2 class="fio-hero-title">
                                @if ($flow->icon())
                                    <x-filament::icon
                                        :icon="$flow->icon()"
                                        style="display: inline-block; width: 1.25rem; height: 1.25rem; vertical-align: -3px; margin-inline-end: 0.375rem;"
                                    />
                                @endif

                                {{ $flow->title() }}
                            </h2>

                            @if ($flow->isDismissed())
                                <span class="fio-chip">
                                    {{ __('filament-onboarding::onboarding.page.hidden') }}
                                </span>
                            @endif
                        </div>

                        @if ($flow->description())
                            <p class="fio-hero-description">{{ $flow->description() }}</p>
                        @endif

                        <div class="fio-stats">
                            <div class="fio-stat">
                                <span class="fio-stat-value">{{ $flow->completedCount() }}</span>
                                <span class="fio-stat-label">{{ __('filament-onboarding::onboarding.page.stats.completed') }}</span>
                            </div>

                            <div class="fio-stat">
                                <span class="fio-stat-value">{{ $flow->pendingSteps()->count() }}</span>
                                <span class="fio-stat-label">{{ __('filament-onboarding::onboarding.page.stats.remaining') }}</span>
                            </div>

                            <div class="fio-stat">
                                <span class="fio-stat-value">{{ $skipped }}</span>
                                <span class="fio-stat-label">{{ __('filament-onboarding::onboarding.page.stats.skipped') }}</span>
                            </div>
                        </div>

                        @if ($flow->isCompleted())
                            <p class="fio-hero-note">
                                {{ __('filament-onboarding::onboarding.checklist.completed_description') }}
                            </p>
                        @elseif ($next)
                            <div class="fio-next">
                                <div>
                                    <span class="fio-next-label">{{ __('filament-onboarding::onboarding.page.next') }}</span>
                                    <p class="fio-next-title">{{ $next->title() }}</p>
                                </div>

                                @if ($next->hasTour())
                                    <button type="button" class="fio-button fio-button--primary" wire:click="startTour(@js($next->key()))">
                                        <x-filament-onboarding::icons.sparkles />
                                        {{ $next->ctaLabel() ?? __('filament-onboarding::onboarding.checklist.start_tour') }}
                                    </button>
                                @elseif ($next->url())
                                    <a href="{{ $next->url() }}" class="fio-button fio-button--primary">
                                        {{ $next->ctaLabel() ?? __('filament-onboarding::onboarding.checklist.go') }}
                                        <x-filament-onboarding::icons.arrow-right />
                                    </a>
                                @elseif ($next->canSelfComplete())
                                    <button type="button" class="fio-button fio-button--primary" wire:click="completeStep(@js($next->key()))">
                                        <x-filament-onboarding::icons.check />
                                        {{ __('filament-onboarding::onboarding.checklist.mark_done') }}
                                    </button>
                                @endif
                            </div>
                        @endif

                        {{-- Filament's own actions: its confirmation modal, its button
                             styling, its keyboard handling — no browser dialogs. --}}
                        <div class="fio-hero-actions">
                            @if ($flow->isStarted())
                                {{ ($this->restartFlowAction)(['flow' => $flow->key()]) }}
                            @endif

                            @if ($flow->isDismissible())
                                @if ($flow->isDismissed())
                                    {{ ($this->restoreFlowAction)(['flow' => $flow->key()]) }}
                                @else
                                    {{ ($this->dismissFlowAction)(['flow' => $flow->key()]) }}
                                @endif
                            @endif
                        </div>

                        @if ($flow->isCompleted() && $flow->hasConditionSteps())
                            {{-- Restarting cannot undo what is simply true: a step that
                                 asks the application comes straight back completed. --}}
                            <p class="fio-footer-note" style="margin-block-start: 0.5rem;">
                                {{ __('filament-onboarding::onboarding.page.restart_note') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="fio-tiles">
                    @foreach ($flow->steps as $index => $step)
                        @php $tour = $step->tourProgress(); @endphp

                        <article
                            wire:key="fio-tile-{{ $flow->key() }}-{{ $step->key() }}"
                            @class([
                                'fio-tile',
                                'fio-tile--done' => $step->isCompleted(),
                                'fio-tile--skipped' => $step->isSkipped(),
                                'fio-tile--next' => $next?->key() === $step->key(),
                            ])
                        >
                            <header class="fio-tile-header">
                                <span class="fio-tile-marker">
                                    @if ($step->isCompleted())
                                        <x-filament-onboarding::icons.check />
                                    @elseif ($step->isSkipped())
                                        <x-filament-onboarding::icons.minus />
                                    @elseif ($step->icon())
                                        <x-filament::icon :icon="$step->icon()" style="width: 1rem; height: 1rem;" />
                                    @else
                                        <span class="fio-tile-index">{{ $index + 1 }}</span>
                                    @endif
                                </span>

                                <span class="fio-tile-status">
                                    @if ($step->isCompleted())
                                        {{ __('filament-onboarding::onboarding.page.status.completed') }}
                                    @elseif ($step->isSkipped())
                                        {{ __('filament-onboarding::onboarding.page.status.skipped') }}
                                    @elseif ($next?->key() === $step->key())
                                        {{ __('filament-onboarding::onboarding.page.status.next') }}
                                    @else
                                        {{ __('filament-onboarding::onboarding.page.status.pending') }}
                                    @endif
                                </span>
                            </header>

                            @if ($step->hasImage())
                                <img
                                    src="{{ $step->imageUrl() }}"
                                    alt="{{ $step->title() }}"
                                    class="fio-thumb"
                                    wire:click="openMedia(@js($step->key()))"
                                />
                            @endif

                            <h3 class="fio-tile-title">{{ $step->title() }}</h3>

                            @if ($step->description())
                                <p class="fio-tile-description">{{ $step->description() }}</p>
                            @endif

                            @php $video = $step->videoProgress(); @endphp

                            @if ($video && ! $step->isCompleted())
                                {{-- Watch time is real: the player reported it. --}}
                                <div class="fio-tile-progress">
                                    <div class="fio-progress-track">
                                        <div class="fio-progress-value" style="width: {{ $video['percent'] }}%"></div>
                                    </div>

                                    <span class="fio-progress-label">
                                        {{ $video['percent'] }}% {{ __('filament-onboarding::onboarding.media.watched') }}
                                    </span>
                                </div>
                            @endif

                            @if ($tour)
                                {{-- A tour walked half-way says so, instead of reading as untouched. --}}
                                <div class="fio-tile-progress">
                                    <div class="fio-progress-track">
                                        <div class="fio-progress-value" style="width: {{ $step->percentage() }}%"></div>
                                    </div>

                                    <span class="fio-progress-label">
                                        {{ __('filament-onboarding::onboarding.page.tour_progress', [
                                            'reached' => $tour['reached'] + 1,
                                            'total' => $tour['total'],
                                        ]) }}
                                    </span>
                                </div>
                            @endif

                            <footer class="fio-tile-footer">
                                @if ($step->isResolved())
                                    <span class="fio-tile-meta">
                                        @if ($step->completedAt())
                                            {{ __('filament-onboarding::onboarding.page.completed_at', [
                                                'time' => $step->completedAt()->diffForHumans(),
                                            ]) }}
                                        @endif

                                        {{-- Says why this one keeps coming back done. --}}
                                        @if ($step->step->completion_mode === \Wallacemartinss\FilamentOnboarding\Enums\CompletionMode::Condition)
                                            · {{ __('filament-onboarding::onboarding.page.awaiting_condition') }}
                                        @endif
                                    </span>

                                    {{-- Finishing a step is not the same as being done with
                                         it: the tour can be watched again, the video replayed,
                                         the page revisited — none of which undoes anything. --}}
                                    <div class="fio-tile-actions">
                                        @if ($step->canReplay())
                                            @if ($step->hasTour())
                                                <button type="button" class="fio-button fio-button--ghost" wire:click="startTour(@js($step->key()))">
                                                    <x-filament-onboarding::icons.sparkles />
                                                    {{ __('filament-onboarding::onboarding.page.replay_tour') }}
                                                </button>
                                            @elseif ($step->hasVideo())
                                                <button type="button" class="fio-button fio-button--ghost" wire:click="openMedia(@js($step->key()))">
                                                    <x-filament-onboarding::icons.play />
                                                    {{ __('filament-onboarding::onboarding.page.replay_video') }}
                                                </button>
                                            @elseif ($step->url())
                                                <a href="{{ $step->url() }}" class="fio-button fio-button--ghost">
                                                    {{ __('filament-onboarding::onboarding.page.open_again') }}
                                                    <x-filament-onboarding::icons.arrow-right />
                                                </a>
                                            @endif
                                        @endif

                                        @if ($step->canUndo())
                                            <button type="button" class="fio-button fio-button--ghost" wire:click="undoStep(@js($step->key()))">
                                                {{ __('filament-onboarding::onboarding.page.undo') }}
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    @if ($step->hasVideo())
                                        <button type="button" class="fio-button fio-button--primary" wire:click="openMedia(@js($step->key()))">
                                            <x-filament-onboarding::icons.play />
                                            {{ $step->ctaLabel() ?? ($video
                                                ? __('filament-onboarding::onboarding.media.resume')
                                                : __('filament-onboarding::onboarding.media.watch')) }}
                                        </button>
                                    @elseif ($step->hasTour())
                                        <button type="button" class="fio-button fio-button--primary" wire:click="startTour(@js($step->key()))">
                                            <x-filament-onboarding::icons.sparkles />
                                            {{ $step->ctaLabel() ?? __('filament-onboarding::onboarding.checklist.start_tour') }}
                                        </button>
                                    @elseif ($step->url())
                                        <a href="{{ $step->url() }}" class="fio-button fio-button--primary">
                                            {{ $step->ctaLabel() ?? __('filament-onboarding::onboarding.checklist.go') }}
                                            <x-filament-onboarding::icons.arrow-right />
                                        </a>
                                    @elseif ($step->canSelfComplete())
                                        <button type="button" class="fio-button fio-button--primary" wire:click="completeStep(@js($step->key()))">
                                            <x-filament-onboarding::icons.check />
                                            {{ __('filament-onboarding::onboarding.checklist.mark_done') }}
                                        </button>
                                    @endif

                                    @if ($step->canSkip())
                                        <button type="button" class="fio-button fio-button--ghost" wire:click="skipStep(@js($step->key()))">
                                            {{ __('filament-onboarding::onboarding.checklist.skip') }}
                                        </button>
                                    @endif

                                    @if ($step->isAwaitingCondition())
                                        <span class="fio-tile-meta">
                                            {{ __('filament-onboarding::onboarding.page.awaiting_condition') }}
                                        </span>
                                    @endif
                                @endif
                            </footer>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="fio-empty">
                <div class="fio-complete-icon">
                    <x-filament-onboarding::icons.sparkles />
                </div>

                <h2 class="fio-panel-title">{{ __('filament-onboarding::onboarding.page.empty_title') }}</h2>
                <p class="fio-panel-description">{{ __('filament-onboarding::onboarding.page.empty_description') }}</p>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
