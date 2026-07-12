{{-- The media modal: an image to look at, or a video to watch. It hangs off the
     body with the tour runner, so it opens over any page of the panel — and it
     sits wherever the step (or the panel) says it should. --}}
<div
    class="fio"
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

                    <template x-if="media?.type === 'video' && media?.provider === 'youtube'">
                        <div class="fio-modal-frame"><div x-ref="youtube"></div></div>
                    </template>

                    <template x-if="media?.type === 'video' && media?.provider === 'vimeo'">
                        <div class="fio-modal-frame"><div x-ref="vimeo"></div></div>
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
