{{-- The tour runner: dims the page, spotlights one element at a time, and walks
     the subject through the steps — across pages when a step lives elsewhere. --}}
<div
    class="fio"
    {{-- The runner lives inside the launcher, and every Livewire round-trip morphs
         its subtree: without this, the spotlight and the popover are torn out and
         rebuilt underneath a tour that is running. --}}
    wire:ignore
    x-data="onboardingTour"
    x-load
    x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('onboarding-tour', 'wallacemartinss/filament-onboarding') }}"
    x-on:keydown.window="onKeydown($event)"
>
    <template x-if="active">
        <div>
            <div
                class="fio-tour-spotlight"
                :class="{ 'fio-tour-spotlight--empty': ! spotlight.visible }"
                :style="spotlight.visible
                    ? `top: ${spotlight.top}px; left: ${spotlight.left}px; width: ${spotlight.width}px; height: ${spotlight.height}px;`
                    : 'top: 50%; left: 50%; width: 0; height: 0;'"
            ></div>

            <div
                class="fio-tour-popover fio-animate-in"
                x-ref="popover"
                :style="`top: ${popover.top}px; left: ${popover.left}px;`"
                role="dialog"
                aria-modal="true"
            >
                <p class="fio-tour-counter">
                    <span x-text="index + 1"></span>/<span x-text="steps.length"></span>
                </p>

                <template x-if="step?.title">
                    <h3 class="fio-tour-title" x-text="step.title"></h3>
                </template>

                <template x-if="step?.body">
                    <p class="fio-tour-body" x-text="step.body"></p>
                </template>

                {{-- The element is not on the page yet: say so, rather than leave
                     a spotlight pointing at nothing. --}}
                <template x-if="waiting">
                    <p class="fio-tour-waiting">
                        <span class="fio-tour-waiting-pulse" aria-hidden="true"></span>
                        {{ __('filament-onboarding::onboarding.tour.waiting') }}
                    </p>
                </template>

                <div class="fio-tour-actions">
                    {{-- With many stops the dots outgrow the popover; the
                         counter above already says where the subject is. --}}
                    <div class="fio-tour-dots" aria-hidden="true" x-show="steps.length <= 8">
                        <template x-for="(tourStep, dot) in steps" :key="dot">
                            <span class="fio-tour-dot" :class="{ 'fio-tour-dot--active': dot === index }"></span>
                        </template>
                    </div>

                    <div class="fio-inline-actions">
                        <button type="button" class="fio-button fio-button--ghost" x-on:click="skip()">
                            {{ __('filament-onboarding::onboarding.tour.skip') }}
                        </button>

                        <template x-if="! isFirst">
                            <button type="button" class="fio-button fio-button--ghost" x-on:click="previous()">
                                {{ __('filament-onboarding::onboarding.tour.previous') }}
                            </button>
                        </template>

                        <button
                            type="button"
                            class="fio-button fio-button--primary"
                            :disabled="waiting"
                            x-on:click="next()"
                        >
                            <span x-text="isLast
                                ? @js(__('filament-onboarding::onboarding.tour.finish'))
                                : @js(__('filament-onboarding::onboarding.tour.next'))"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
