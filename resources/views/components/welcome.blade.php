{{-- The welcome screen: the one moment onboarding gets to introduce itself.
     It shows up once, over the first page after logging in, and it always offers
     the way out — "later", or "never" — because that is the price of interrupting
     somebody who came here to do something else. --}}
<div class="fio-welcome-layer" role="dialog" aria-modal="true" aria-labelledby="fio-welcome-title">
    <div class="fio-welcome-backdrop" wire:click="remindMeLater" aria-hidden="true"></div>

    <div class="fio-welcome fio-animate-in">
        <div class="fio-welcome-body">
            <div class="fio-welcome-badge" @style(["color: var(--{$flow->color()}-500)"])>
                @if ($flow->icon())
                    <x-filament::icon :icon="$flow->icon()" class="fio-welcome-icon" />
                @else
                    <x-filament-onboarding::icons.sparkles />
                @endif
            </div>

            <h2 class="fio-welcome-title" id="fio-welcome-title">{{ $flow->title() }}</h2>

            @if ($flow->description())
                <p class="fio-welcome-description">{{ $flow->description() }}</p>
            @endif

            {{-- What they are being invited to, in numbers: a journey with a
                 visible end is one people finish. --}}
            <p class="fio-welcome-meta">
                {{ trans_choice('filament-onboarding::onboarding.welcome.steps', $flow->total(), ['count' => $flow->total()]) }}
            </p>
        </div>

        <div class="fio-welcome-actions">
            @if ($progressUrl)
                <a href="{{ $progressUrl }}" class="fio-button fio-button--primary fio-button--lg" wire:click="startOnboarding">
                    {{ __('filament-onboarding::onboarding.welcome.begin') }}
                    <x-filament-onboarding::icons.arrow-right />
                </a>
            @else
                <button type="button" class="fio-button fio-button--primary fio-button--lg" wire:click="beginOnboarding">
                    {{ __('filament-onboarding::onboarding.welcome.begin') }}
                    <x-filament-onboarding::icons.arrow-right />
                </button>
            @endif

            <button type="button" class="fio-button fio-button--ghost" wire:click="remindMeLater">
                {{ __('filament-onboarding::onboarding.welcome.later') }}
            </button>
        </div>

        {{-- Deliberately quiet, and deliberately there. Hiding the way out is how
             a helpful thing becomes an annoying one. --}}
        <button type="button" class="fio-welcome-never" wire:click="neverShowOnboarding">
            {{ __('filament-onboarding::onboarding.welcome.never') }}
        </button>
    </div>
</div>
