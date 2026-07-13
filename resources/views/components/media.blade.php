{{-- The media modal: an image to look at, or a video to watch. It hangs off the
     body with the tour runner, so it opens over any page of the panel — and it
     sits wherever the step (or the panel) says it should. --}}
{{-- wire:ignore is load-bearing. This modal lives inside a Livewire component,
     and every Livewire round trip morphs the DOM — including the <iframe> the
     video API just attached itself to. The player would be torn out from under
     it mid-playback (the API says as much: "player is not attached to the DOM").
     The modal is pure Alpine and needs nothing from Livewire's re-render, so it
     is left alone. --}}
<div
    class="fio"
    wire:ignore
    x-data="onboardingMedia"
    x-load
    x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('onboarding-media', 'wallacemartinss/filament-onboarding') }}"
    x-on:keydown.escape.window="open && close()"
>
    <template x-if="open">
        <div class="fio-modal-layer" :class="`fio-modal-layer--${position}`">
            {{-- A centred modal takes the page; a docked one leaves it usable. --}}
            <template x-if="position === 'center'">
                <div class="fio-modal-backdrop" x-on:click="close()"></div>
            </template>

            <div class="fio-modal fio-animate-in" :class="`fio-modal--${position}`" role="dialog" aria-modal="true">
                <header class="fio-modal-header">
                    <h2 class="fio-modal-title" x-text="media?.title"></h2>

                    <button type="button" class="fio-icon-button" x-on:click="close()" aria-label="{{ __('filament-onboarding::onboarding.checklist.close') }}">
                        <x-filament-onboarding::icons.x />
                    </button>
                </header>

                <div class="fio-modal-body">
                    <template x-if="media?.type === 'image'">
                        <img class="fio-modal-image" :src="media.url" :alt="media?.title" />
                    </template>

                    <template x-if="media?.type === 'video' && media?.provider === 'file'">
                        <video class="fio-modal-video" x-ref="video" :src="media.url" controls playsinline></video>
                    </template>

                    {{-- The provider's API builds its own iframe over these mounts.
                         That only survives because of the wire:ignore above: a
                         Livewire morph would otherwise replace the element the API
                         is holding on to. --}}
                    <template x-if="media?.type === 'video' && media?.provider === 'youtube' && ! degraded">
                        <div class="fio-modal-frame"><div x-ref="youtube"></div></div>
                    </template>

                    <template x-if="media?.type === 'video' && media?.provider === 'vimeo' && ! degraded">
                        <div class="fio-modal-frame"><div x-ref="vimeo"></div></div>
                    </template>

                    {{-- The provider's script never arrived — an ad blocker, most
                         often — so there is no API to build a player with. The
                         plain embed needs nothing from us: the video still plays,
                         and watch time is simply not measured. --}}
                    <template x-if="degradedSrc">
                        <div class="fio-modal-frame">
                            <iframe
                                :src="degradedSrc"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                        </div>
                    </template>

                    <template x-if="media?.type === 'video' && media?.provider === 'embed'">
                        <div class="fio-modal-frame">
                            <iframe
                                :src="media.url"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                        </div>
                    </template>

                    <template x-if="media?.caption">
                        <p class="fio-modal-caption" x-text="media.caption"></p>
                    </template>
                </div>

                <template x-if="isTrackable">
                    <footer class="fio-modal-footer">
                        <div class="fio-progress-track">
                            <div class="fio-progress-value" :style="`width: ${watchedPercentage}%`"></div>
                        </div>

                        <span class="fio-progress-label">
                            <span x-text="watchedPercentage"></span>% {{ __('filament-onboarding::onboarding.media.watched') }}
                        </span>
                    </footer>
                </template>
            </div>
        </div>
    </template>
</div>
