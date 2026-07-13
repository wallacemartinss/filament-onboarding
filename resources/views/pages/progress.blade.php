<x-filament-panels::page>
    <div class="fio fio-dash">
        {{-- Somebody who turned onboarding off still has this page in the menu.
             This is the way back — without it, "do not show this again" would be
             a door that locks behind you. --}}
        @if ($this->isOnboardingHidden())
            <div class="fio-callout" role="status">
                <p class="fio-callout-text">{{ __('filament-onboarding::onboarding.welcome.off') }}</p>

                <button type="button" class="fio-button fio-button--primary" wire:click="showOnboardingAgain">
                    <x-filament-onboarding::icons.arrow-path />
                    {{ __('filament-onboarding::onboarding.welcome.back') }}
                </button>
            </div>
        @endif

        @forelse ($this->flows() as $flow)
            @php
                $circumference = 2 * M_PI * 40;
                $offset = $circumference * (1 - ($flow->percentage() / 100));
                $next = $flow->nextStep();
                $skipped = $flow->steps->filter(fn ($step) => $step->isSkipped())->count();
            @endphp

            {{-- A journey is a section you can fold away.
                 The page used to lay every step of every journey out at once: a
                 wall of cards, most of them about work already done, and the one
                 thing somebody came here for buried in the middle of it. Now the
                 header carries the answer — how far, what is next — and the steps
                 are one click behind it. A journey with nothing pending starts
                 folded; the choice is remembered, per journey. --}}
            <section
                class="fio-journey"
                wire:key="fio-journey-{{ $flow->key() }}"
                x-data="{ open: $persist(@js(! $flow->isFinished() && ! $flow->isDismissed())).as('fio-journey-{{ $flow->key() }}') }"
            >
                <header class="fio-journey-header">
                    <div class="fio-hero-ring fio-hero-ring--sm">
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

                    <div class="fio-journey-heading">
                        <h2 class="fio-hero-title">
                            @if ($flow->icon())
                                <x-filament::icon
                                    :icon="$flow->icon()"
                                    class="fio-inline-icon fio-inline-icon--lg"
                                />
                            @endif

                            {{ $flow->title() }}

                            @if ($flow->isDismissed())
                                <span class="fio-chip">
                                    {{ __('filament-onboarding::onboarding.page.hidden') }}
                                </span>
                            @endif
                        </h2>

                        {{-- The numbers on one line: what the wall of cards was
                             being read for anyway. --}}
                        <p class="fio-journey-meta">
                            <span class="fio-journey-meta-done">{{ __('filament-onboarding::onboarding.page.meta.completed', ['count' => $flow->completedCount(), 'total' => $flow->total()]) }}</span>

                            @if ($flow->pendingSteps()->count() > 0)
                                <span>·</span>
                                <span>{{ trans_choice('filament-onboarding::onboarding.page.meta.remaining', $flow->pendingSteps()->count(), ['count' => $flow->pendingSteps()->count()]) }}</span>
                            @endif

                            @if ($skipped > 0)
                                <span>·</span>
                                <span>{{ trans_choice('filament-onboarding::onboarding.page.meta.skipped', $skipped, ['count' => $skipped]) }}</span>
                            @endif
                        </p>
                    </div>

                    {{-- The next thing to do, in the header: on a folded journey it
                         is the only thing most people want from this page. --}}
                    @if (! $flow->isFinished() && $next)
                        <div class="fio-journey-next">
                            @if ($next->hasTour())
                                <button type="button" class="fio-button fio-button--primary" wire:click="startTour(@js($next->key()), @js($flow->key()))">
                                    <x-filament-onboarding::icons.sparkles />
                                    {{ $next->ctaLabel() ?? __('filament-onboarding::onboarding.checklist.start_tour') }}
                                </button>
                            @elseif ($next->url())
                                <a href="{{ $next->url() }}" class="fio-button fio-button--primary">
                                    {{ $next->ctaLabel() ?? __('filament-onboarding::onboarding.checklist.go') }}
                                    <x-filament-onboarding::icons.arrow-right />
                                </a>
                            @elseif ($next->canSelfComplete())
                                <button type="button" class="fio-button fio-button--primary" wire:click="completeStep(@js($next->key()), @js($flow->key()))">
                                    <x-filament-onboarding::icons.check />
                                    {{ __('filament-onboarding::onboarding.checklist.mark_done') }}
                                </button>
                            @endif
                        </div>
                    @endif

                    <button
                        type="button"
                        class="fio-journey-toggle"
                        x-on:click="open = ! open"
                        :aria-expanded="open ? 'true' : 'false'"
                        aria-controls="fio-journey-body-{{ $flow->key() }}"
                        :aria-label="open
                            ? @js(__('filament-onboarding::onboarding.page.collapse'))
                            : @js(__('filament-onboarding::onboarding.page.expand'))"
                    >
                        <span class="fio-journey-chevron" :class="{ 'fio-journey-chevron--open': open }">
                            <x-filament-onboarding::icons.chevron-down />
                        </span>
                    </button>
                </header>

                <div id="fio-journey-body-{{ $flow->key() }}" x-show="open" x-collapse>
                    @if ($flow->description())
                        <p class="fio-hero-description">{{ $flow->description() }}</p>
                    @endif

                    @if ($flow->isFinished())
                        <p class="fio-hero-note">
                            {{ __('filament-onboarding::onboarding.checklist.completed_description') }}
                        </p>
                    @endif

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
                                        <x-filament::icon :icon="$step->icon()" class="fio-icon-sm" />
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
                                <button
                                    type="button"
                                    class="fio-thumb-button"
                                    wire:click="openMedia(@js($step->key()), @js($flow->key()))"
                                    aria-label="{{ __('filament-onboarding::onboarding.page.open_image', ['title' => $step->title()]) }}"
                                >
                                    <img src="{{ $step->imageUrl() }}" alt="{{ $step->title() }}" class="fio-thumb" />
                                </button>
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
                                        @endif

                                        @if ($step->canUndo())
                                            <button type="button" class="fio-button fio-button--ghost" wire:click="undoStep(@js($step->key()), @js($flow->key()))">
                                                {{ __('filament-onboarding::onboarding.page.undo') }}
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    @if ($step->hasVideo())
                                        <button type="button" class="fio-button fio-button--primary" wire:click="openMedia(@js($step->key()), @js($flow->key()))">
                                            <x-filament-onboarding::icons.play />
                                            {{ $step->ctaLabel() ?? ($video
                                                ? __('filament-onboarding::onboarding.media.resume')
                                                : __('filament-onboarding::onboarding.media.watch')) }}
                                        </button>
                                    @elseif ($step->hasTour())
                                        <button type="button" class="fio-button fio-button--primary" wire:click="startTour(@js($step->key()), @js($flow->key()))">
                                            <x-filament-onboarding::icons.sparkles />
                                            {{ $step->ctaLabel() ?? __('filament-onboarding::onboarding.checklist.start_tour') }}
                                        </button>
                                    @elseif ($step->url())
                                        <a href="{{ $step->url() }}" class="fio-button fio-button--primary">
                                            {{ $step->ctaLabel() ?? __('filament-onboarding::onboarding.checklist.go') }}
                                            <x-filament-onboarding::icons.arrow-right />
                                        </a>
                                    @elseif ($step->canSelfComplete())
                                        <button type="button" class="fio-button fio-button--primary" wire:click="completeStep(@js($step->key()), @js($flow->key()))">
                                            <x-filament-onboarding::icons.check />
                                            {{ __('filament-onboarding::onboarding.checklist.mark_done') }}
                                        </button>
                                    @endif

                                    @if ($step->canSkip())
                                        <button type="button" class="fio-button fio-button--ghost" wire:click="skipStep(@js($step->key()), @js($flow->key()))">
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

                    @if ($flow->isFinished() && $flow->hasConditionSteps())
                        {{-- Restarting cannot undo what is simply true: a step that
                             asks the application comes straight back completed. --}}
                        <p class="fio-footer-note fio-stack-tight">
                            {{ __('filament-onboarding::onboarding.page.restart_note') }}
                        </p>
                    @endif
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
