/**
 * Guided tour runner.
 *
 * Lives on every panel page (the launcher hangs it off the body), listens for a
 * tour handed over by Livewire, and walks the subject through it — spotlighting
 * one element at a time, following the tour across pages when a step lives
 * somewhere else.
 */

const STORAGE_KEY = 'filament-onboarding.tour';
const PREFIXES = ['@widget:', '@livewire:'];
const SPOTLIGHT_PADDING = 8;
const POPOVER_GAP = 14;
const POPOVER_WIDTH = 320;
const ELEMENT_TIMEOUT = 3000;

export default function onboardingTour() {
    return {
        active: false,
        stepKey: null,
        steps: [],
        index: 0,
        spotlight: { top: 0, left: 0, width: 0, height: 0, visible: false },
        popover: { top: 0, left: 0 },
        reposition: null,

        init() {
            this.resume();

            window.addEventListener('onboarding-tour-start', (event) => {
                const detail = event.detail ?? {};

                this.start(detail.key, detail.steps ?? []);
            });

            this.reposition = () => {
                if (this.active) {
                    this.render();
                }
            };

            window.addEventListener('resize', this.reposition, { passive: true });
            window.addEventListener('scroll', this.reposition, { passive: true, capture: true });
        },

        destroy() {
            window.removeEventListener('resize', this.reposition);
            window.removeEventListener('scroll', this.reposition, { capture: true });
        },

        /**
         * A tour that crossed a page boundary picks up where it left off.
         */
        resume() {
            const stored = sessionStorage.getItem(STORAGE_KEY);

            if (!stored) {
                return;
            }

            try {
                const { key, steps, index } = JSON.parse(stored);

                if (!key || !Array.isArray(steps) || steps.length === 0) {
                    return this.clearStorage();
                }

                this.stepKey = key;
                this.steps = steps;
                this.index = index ?? 0;
                this.active = true;

                this.$nextTick(() => this.render());
            } catch {
                this.clearStorage();
            }
        },

        start(key, steps) {
            if (!key || !Array.isArray(steps) || steps.length === 0) {
                return;
            }

            this.stepKey = key;
            this.steps = steps;
            this.index = 0;

            if (this.navigateIfNeeded(0)) {
                return;
            }

            this.active = true;
            this.$nextTick(() => this.render());
        },

        get step() {
            return this.steps[this.index] ?? null;
        },

        get isLast() {
            return this.index >= this.steps.length - 1;
        },

        get isFirst() {
            return this.index === 0;
        },

        next() {
            if (this.isLast) {
                return this.finish();
            }

            const target = this.index + 1;

            if (this.navigateIfNeeded(target)) {
                return;
            }

            this.index = target;
            this.render();
        },

        previous() {
            if (this.isFirst) {
                return;
            }

            const target = this.index - 1;

            if (this.navigateIfNeeded(target)) {
                return;
            }

            this.index = target;
            this.render();
        },

        finish() {
            const key = this.stepKey;

            this.close();

            if (key && window.Livewire) {
                window.Livewire.dispatch('onboarding-tour-finished', { key });
            }
        },

        /**
         * Left early: the step stays open, so the tour can be taken again.
         */
        skip() {
            this.close();
        },

        close() {
            this.active = false;
            this.steps = [];
            this.index = 0;
            this.stepKey = null;
            this.spotlight.visible = false;
            this.clearStorage();
        },

        /**
         * When the step points at another page, park the tour and go there —
         * resume() picks it back up once the page has loaded.
         */
        navigateIfNeeded(index) {
            const step = this.steps[index];
            const url = step?.url;

            if (!url) {
                return false;
            }

            const target = new URL(url, window.location.origin);

            if (target.pathname === window.location.pathname) {
                return false;
            }

            sessionStorage.setItem(
                STORAGE_KEY,
                JSON.stringify({ key: this.stepKey, steps: this.steps, index }),
            );

            window.location.assign(target.toString());

            return true;
        },

        clearStorage() {
            sessionStorage.removeItem(STORAGE_KEY);
        },

        async render() {
            const step = this.step;

            if (!step) {
                return this.close();
            }

            this.persist();
            this.report();

            const element = step.selector ? await this.waitForElement(step.selector) : null;

            if (!element) {
                // Nothing to point at — dim the page and centre the popover, so a
                // tour still reads rather than breaking on a renamed selector.
                this.spotlight.visible = false;
                this.popover = {
                    top: window.innerHeight / 2 - 100,
                    left: window.innerWidth / 2 - POPOVER_WIDTH / 2,
                };

                return;
            }

            this.scrollIntoView(element);

            const rect = element.getBoundingClientRect();

            this.spotlight = {
                top: rect.top - SPOTLIGHT_PADDING,
                left: rect.left - SPOTLIGHT_PADDING,
                width: rect.width + SPOTLIGHT_PADDING * 2,
                height: rect.height + SPOTLIGHT_PADDING * 2,
                visible: true,
            };

            this.popover = this.positionPopover(rect, step.placement ?? 'auto');
        },

        persist() {
            if (!this.active) {
                return;
            }

            sessionStorage.setItem(
                STORAGE_KEY,
                JSON.stringify({ key: this.stepKey, steps: this.steps, index: this.index }),
            );
        },

        /**
         * Tell the server which stop we are on, so a tour the subject walked away
         * from still shows the ground they covered.
         */
        report() {
            if (!this.stepKey || !window.Livewire) {
                return;
            }

            window.Livewire.dispatch('onboarding-tour-progress', {
                key: this.stepKey,
                index: this.index,
                total: this.steps.length,
            });
        },

        /**
         * The element may still be on its way in — a Livewire render, a lazy
         * widget — so give it a moment before giving up on it.
         */
        waitForElement(selector) {
            return new Promise((resolve) => {
                const existing = this.find(selector);

                if (existing) {
                    return resolve(existing);
                }

                const startedAt = Date.now();

                const poll = setInterval(() => {
                    const element = this.find(selector);

                    if (element) {
                        clearInterval(poll);

                        return resolve(element);
                    }

                    if (Date.now() - startedAt > ELEMENT_TIMEOUT) {
                        clearInterval(poll);

                        resolve(null);
                    }
                }, 100);
            });
        },

        /**
         * A CSS selector, or a Livewire component — "@widget:some-widget",
         * "@livewire:edit_password_form". Widgets all share one wrapper class,
         * and a form section rendered by a plugin has no hook of its own, so
         * both are found by the component they are rather than by CSS.
         */
        find(selector) {
            if (!PREFIXES.some((prefix) => selector.startsWith(prefix))) {
                try {
                    return document.querySelector(selector);
                } catch {
                    // A selector the panel can no longer parse: the tour carries
                    // on without a spotlight rather than throwing.
                    return null;
                }
            }

            const prefix = PREFIXES.find((candidate) => selector.startsWith(candidate));
            const component = selector.slice(prefix.length);

            for (const element of document.querySelectorAll('[wire\\:snapshot]')) {
                try {
                    const snapshot = JSON.parse(element.getAttribute('wire:snapshot'));

                    if (snapshot?.memo?.name === component) {
                        return element;
                    }
                } catch {
                    continue;
                }
            }

            return null;
        },

        scrollIntoView(element) {
            const rect = element.getBoundingClientRect();
            const isVisible = rect.top >= 0 && rect.bottom <= window.innerHeight;

            if (isVisible) {
                return;
            }

            element.scrollIntoView({
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                block: 'center',
                inline: 'nearest',
            });
        },

        positionPopover(rect, placement) {
            const height = this.$refs.popover?.offsetHeight ?? 180;
            const margin = 12;

            const below = rect.bottom + POPOVER_GAP;
            const above = rect.top - height - POPOVER_GAP;

            const fitsBelow = below + height <= window.innerHeight - margin;
            const fitsAbove = above >= margin;

            let top;

            if (placement === 'top' && fitsAbove) {
                top = above;
            } else if (placement === 'bottom' && fitsBelow) {
                top = below;
            } else if (fitsBelow) {
                top = below;
            } else if (fitsAbove) {
                top = above;
            } else {
                top = Math.max(margin, window.innerHeight / 2 - height / 2);
            }

            const centred = rect.left + rect.width / 2 - POPOVER_WIDTH / 2;

            const left = Math.min(
                Math.max(margin, centred),
                window.innerWidth - POPOVER_WIDTH - margin,
            );

            return { top, left };
        },

        onKeydown(event) {
            if (!this.active) {
                return;
            }

            if (event.key === 'Escape') {
                this.skip();
            }

            if (event.key === 'ArrowRight') {
                this.next();
            }

            if (event.key === 'ArrowLeft') {
                this.previous();
            }
        },
    };
}
