/**
 * Guided tour runner.
 *
 * Lives on every panel page (the launcher hangs it off the body), listens for a
 * tour handed over by Livewire, and walks the subject through it — spotlighting
 * one element at a time, following the tour across pages when a step lives
 * somewhere else, and following the *subject* through a wizard when the element
 * a stop points at is not on screen yet.
 */

const STORAGE_KEY = 'filament-onboarding.tour';
const PREFIXES = ['@widget:', '@livewire:'];
const SPOTLIGHT_PADDING = 8;
const POPOVER_GAP = 14;
const POPOVER_MARGIN = 12;
const POPOVER_FALLBACK_WIDTH = 320;
const POPOVER_FALLBACK_HEIGHT = 180;
const ELEMENT_TIMEOUT = 3000;
const SCROLL_TIMEOUT = 800;
const TINY_ELEMENT = 8;

export default function onboardingTour() {
    return {
        active: false,
        stepKey: null,
        steps: [],
        index: 0,
        // The element a stop points at is not here yet: the subject is on
        // another step of a wizard, or a section is still closed.
        waiting: false,
        spotlight: { top: 0, left: 0, width: 0, height: 0, visible: false },
        popover: { top: 0, left: 0 },
        reposition: null,
        onPageShow: null,
        observer: null,
        observerQueued: false,
        renderToken: 0,
        repositionQueued: false,
        reportedIndex: null,
        pollTimers: [],

        init() {
            this.resume();

            window.addEventListener('onboarding-tour-start', (event) => {
                const detail = event.detail ?? {};

                this.start(detail.key, detail.steps ?? []);
            });

            // Scrolling and resizing move the element, so the spotlight has to
            // follow it. That is *all* they do: re-measure, in place.
            //
            // Rendering from here instead was two bugs in one. It scrolled the
            // subject back — render() pulls the element into view, and the check
            // for "in view" wants the whole element, so nudging the page one
            // pixel snapped it back to centre and the subject could not read
            // around the spotlight. And it reported to the server on every frame:
            // a two-second scroll was a hundred Livewire round-trips, each one an
            // UPDATE plus a re-render of every surface, for a stop that never
            // changed.
            this.reposition = () => {
                if (!this.active || this.repositionQueued) {
                    return;
                }

                this.repositionQueued = true;

                requestAnimationFrame(() => {
                    this.repositionQueued = false;

                    if (this.active) {
                        this.measure();
                    }
                });
            };

            window.addEventListener('resize', this.reposition, { passive: true });
            window.addEventListener('scroll', this.reposition, { passive: true, capture: true });

            // Coming back through the browser's history can restore this page
            // from the back-forward cache, script and all: init() does not run
            // again, so the tour would carry on exactly as it was — including on
            // a page it does not belong to. Ask the question again.
            this.onPageShow = (event) => {
                if (!event.persisted) {
                    return;
                }

                if (this.active) {
                    this.close();
                }

                this.resume();
            };

            window.addEventListener('pageshow', this.onPageShow);
        },

        destroy() {
            window.removeEventListener('resize', this.reposition);
            window.removeEventListener('scroll', this.reposition, { capture: true });
            window.removeEventListener('pageshow', this.onPageShow);
            this.stopPolling();
            this.stopObserving();
        },

        /**
         * A tour that crossed a page boundary picks up where it left off — and
         * only there.
         *
         * The page it was parked on is remembered with it. Land anywhere else —
         * the browser's back button, a link in the sidebar — and the tour is
         * over: carrying on would explain the profile page on top of the server
         * list, pointing at an element that is never going to show up.
         */
        resume() {
            const stored = sessionStorage.getItem(STORAGE_KEY);

            if (!stored) {
                return;
            }

            try {
                const { key, steps, index, path } = JSON.parse(stored);

                if (!key || !Array.isArray(steps) || steps.length === 0) {
                    return this.clearStorage();
                }

                if (path && path !== window.location.pathname) {
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
            // The element of this stop is not on screen: the way forward is the
            // form, not the tour. The button is disabled too — this guards the
            // arrow key.
            if (this.waiting) {
                return;
            }

            if (this.isLast) {
                return this.finish();
            }

            const target = this.index + 1;

            if (this.navigateIfNeeded(target)) {
                return;
            }

            this.index = target;

            // Asked to move on, so bring the application along if the stop knows
            // how: the next field may live on the next step of a wizard.
            this.advanceApplication();

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
            this.waiting = false;
            this.steps = [];
            this.index = 0;
            this.stepKey = null;
            this.reportedIndex = null;
            this.spotlight.visible = false;

            // A render already in flight must not act after the tour is gone: it
            // would scroll the page under a subject who just dismissed it.
            this.renderToken++;
            this.stopPolling();
            this.stopObserving();
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
                JSON.stringify({
                    key: this.stepKey,
                    steps: this.steps,
                    index,
                    path: target.pathname,
                }),
            );

            window.location.assign(target.toString());

            return true;
        },

        /**
         * The control that carries the application to this stop — a wizard's
         * "next", a tab, a disclosure.
         *
         * Clicked only when the subject asked to move on and the element is not
         * on screen: the tour nudges the application, it does not drive it.
         */
        advanceApplication() {
            const step = this.step;

            if (!step?.advance) {
                return;
            }

            if (step.selector && this.find(step.selector)) {
                return;
            }

            this.find(step.advance)?.click();
        },

        clearStorage() {
            sessionStorage.removeItem(STORAGE_KEY);
        },

        async render() {
            // Renders overlap: our own smooth scroll fires scroll events, which
            // fire more renders. Only the newest one may write the spotlight —
            // an older render finishing last would place it where the element
            // used to be.
            const token = ++this.renderToken;

            const step = this.step;

            if (!step) {
                return this.close();
            }

            this.persist();
            this.report();

            const found = step.selector ? await this.waitForElement(step.selector) : null;

            if (token !== this.renderToken) {
                return;
            }

            if (!found) {
                // Nothing to point at *yet*. The copy still reads, centred, and
                // the DOM is watched: the element may live on a step of a wizard
                // the subject has not reached, and the tour is content to wait —
                // a tour that gave up after three seconds could never walk
                // anybody through a multi-step form.
                this.waiting = Boolean(step.selector);
                this.spotlight.visible = false;

                await this.placePopover(null);

                if (step.selector) {
                    this.observeFor(step.selector);
                }

                return;
            }

            this.stopObserving();
            this.waiting = false;

            const element = this.resolveTarget(found);

            // Measure *after* the scroll has settled. A smooth scroll is
            // asynchronous: reading the rectangle on the next line gives the
            // position the element had before the page moved, and the spotlight
            // lands on whatever used to be there.
            const rect = await this.scrollIntoView(element);

            if (token !== this.renderToken) {
                return;
            }

            this.spotlight = {
                top: rect.top - SPOTLIGHT_PADDING,
                left: rect.left - SPOTLIGHT_PADDING,
                width: rect.width + SPOTLIGHT_PADDING * 2,
                height: rect.height + SPOTLIGHT_PADDING * 2,
                visible: true,
            };

            await this.placePopover(rect, step.placement ?? 'auto');
        },

        /**
         * Follow the element where it is now — no scrolling, no reporting, no
         * waiting. Called on scroll and resize, many times a second.
         */
        measure() {
            const step = this.step;

            if (!step?.selector || this.waiting) {
                return;
            }

            const found = this.find(step.selector);

            if (!found) {
                // It went away under us — a Livewire morph, a wizard step left
                // behind. Go through the full render, which knows how to wait.
                return this.render();
            }

            const rect = this.resolveTarget(found).getBoundingClientRect();

            this.spotlight = {
                top: rect.top - SPOTLIGHT_PADDING,
                left: rect.left - SPOTLIGHT_PADDING,
                width: rect.width + SPOTLIGHT_PADDING * 2,
                height: rect.height + SPOTLIGHT_PADDING * 2,
                visible: true,
            };

            this.placePopover(rect, step.placement ?? 'auto');
        },

        persist() {
            if (!this.active) {
                return;
            }

            sessionStorage.setItem(
                STORAGE_KEY,
                JSON.stringify({
                    key: this.stepKey,
                    steps: this.steps,
                    index: this.index,
                    path: window.location.pathname,
                }),
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

            // The server only cares which stop was reached. Saying it again
            // costs a round-trip, a database write, and a re-render of every
            // surface showing onboarding — so say it once.
            if (this.reportedIndex === this.index) {
                return;
            }

            this.reportedIndex = this.index;

            window.Livewire.dispatch('onboarding-tour-progress', {
                key: this.stepKey,
                index: this.index,
                total: this.steps.length,
            });
        },

        /**
         * What to draw the spotlight around.
         *
         * A styled toggle or radio hides its real input behind the control the
         * subject actually sees, so the element a selector matches can be a pixel
         * tall — the spotlight would be a dot beside the thing it means. Climb
         * until there is something worth pointing at.
         */
        resolveTarget(element) {
            // A field the subject can see is the thing to point at. Climbing from
            // a visible input finds its caption label — a sliver of text above
            // the field — which is precisely what must not be spotlighted.
            if (this.isSubstantial(element)) {
                return element;
            }

            // A hidden input (a styled toggle keeps its real checkbox sr-only)
            // is represented by its label: the whole row the subject reads.
            if (element instanceof HTMLInputElement) {
                const label = element.closest('label')
                    ?? (element.id ? document.querySelector(`label[for="${CSS.escape(element.id)}"]`) : null);

                if (label && this.isSubstantial(label)) {
                    return label;
                }
            }

            let node = element;

            for (let depth = 0; depth < 4 && node instanceof HTMLElement; depth++) {
                if (this.isSubstantial(node)) {
                    return node;
                }

                node = node.parentElement;
            }

            return element;
        },

        isSubstantial(element) {
            const rect = element.getBoundingClientRect();

            return rect.width >= TINY_ELEMENT && rect.height >= TINY_ELEMENT;
        },

        /**
         * Bring the element into view and hand back the rectangle it settles at.
         */
        scrollIntoView(element) {
            const rect = element.getBoundingClientRect();
            const isVisible = rect.top >= 0 && rect.bottom <= window.innerHeight;

            if (isVisible) {
                return Promise.resolve(rect);
            }

            element.scrollIntoView({
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                block: 'center',
                inline: 'nearest',
            });

            return this.whenStill(element);
        },

        /**
         * The rectangle, once it stops moving: two frames in the same place, or
         * the scroll ran out of patience.
         */
        whenStill(element) {
            return new Promise((resolve) => {
                const startedAt = performance.now();

                let previous = null;
                let stillFor = 0;

                const tick = () => {
                    const rect = element.getBoundingClientRect();

                    const moved = previous === null
                        || Math.abs(rect.top - previous.top) > 0.5
                        || Math.abs(rect.left - previous.left) > 0.5;

                    stillFor = moved ? 0 : stillFor + 1;
                    previous = rect;

                    if (stillFor >= 2 || performance.now() - startedAt > SCROLL_TIMEOUT) {
                        return resolve(element.getBoundingClientRect());
                    }

                    requestAnimationFrame(tick);
                };

                requestAnimationFrame(tick);
            });
        },

        /**
         * Watch the DOM until the tour and the subject are looking at the same
         * thing again.
         *
         * Two ways that happens: the element this stop points at turns up (the
         * subject reached the wizard step it lives on), or the subject moved
         * *past* it and an element from a later stop is on screen instead — in
         * which case the tour follows them, rather than explaining a form they
         * have already filled in.
         */
        observeFor(selector) {
            this.stopObserving();

            this.observer = new MutationObserver(() => {
                if (this.observerQueued) {
                    return;
                }

                this.observerQueued = true;

                requestAnimationFrame(() => {
                    this.observerQueued = false;

                    if (!this.active) {
                        return this.stopObserving();
                    }

                    if (this.find(selector)) {
                        this.stopObserving();

                        return this.render();
                    }

                    const ahead = this.firstStopOnScreenAhead();

                    if (ahead !== null) {
                        this.stopObserving();
                        this.index = ahead;

                        return this.render();
                    }
                });
            });

            // A wizard step is revealed by flipping a class, and a section by
            // flipping a style — neither inserts a node, so watching childList
            // alone would wait forever for something that already happened.
            this.observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['class', 'style', 'hidden'],
            });
        },

        /**
         * The nearest stop after this one whose element is already on screen.
         */
        firstStopOnScreenAhead() {
            for (let index = this.index + 1; index < this.steps.length; index++) {
                const step = this.steps[index];

                // A stop that lives on another page is not "on screen": getting
                // there is a navigation, not a nudge.
                if (step.url) {
                    return null;
                }

                if (step.selector && this.find(step.selector)) {
                    return index;
                }
            }

            return null;
        },

        stopObserving() {
            this.observer?.disconnect();
            this.observer = null;
            this.observerQueued = false;
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

                const startedAt = performance.now();

                const poll = setInterval(() => {
                    const element = this.find(selector);

                    if (element) {
                        this.clearPoll(poll);

                        return resolve(element);
                    }

                    if (performance.now() - startedAt > ELEMENT_TIMEOUT) {
                        this.clearPoll(poll);

                        resolve(null);
                    }
                }, 100);

                this.pollTimers.push(poll);
            });
        },

        clearPoll(timer) {
            clearInterval(timer);

            this.pollTimers = this.pollTimers.filter((pending) => pending !== timer);
        },

        stopPolling() {
            this.pollTimers.forEach(clearInterval);

            this.pollTimers = [];
        },

        /**
         * A CSS selector, or a Livewire component — "@widget:some-widget",
         * "@livewire:edit_password_form". Widgets all share one wrapper class,
         * and a form section rendered by a plugin has no hook of its own, so
         * both are found by the component they are rather than by CSS.
         */
        find(selector) {
            if (!selector) {
                return null;
            }

            if (!PREFIXES.some((prefix) => selector.startsWith(prefix))) {
                try {
                    return this.firstOnScreen(document.querySelectorAll(selector));
                } catch {
                    // A selector the panel can no longer parse: the tour carries
                    // on without a spotlight rather than throwing.
                    return null;
                }
            }

            const prefix = PREFIXES.find((candidate) => selector.startsWith(candidate));
            const component = selector.slice(prefix.length);

            // Livewire puts the component's name straight on the element.
            const named = this.firstOnScreen(
                document.querySelectorAll(`[wire\\:name="${CSS.escape(component)}"]`),
            );

            if (named) {
                return named;
            }

            // Older Livewire only carries it inside the snapshot.
            for (const element of document.querySelectorAll('[wire\\:snapshot]')) {
                try {
                    const snapshot = JSON.parse(element.getAttribute('wire:snapshot'));

                    if (snapshot?.memo?.name === component && this.isOnScreen(element)) {
                        return element;
                    }
                } catch {
                    continue;
                }
            }

            return null;
        },

        /**
         * @param {NodeListOf<Element>} elements
         */
        firstOnScreen(elements) {
            for (const element of elements) {
                if (this.isOnScreen(element)) {
                    return element;
                }
            }

            return null;
        },

        /**
         * Whether the subject can actually see this element right now.
         *
         * This is the load-bearing question of the whole runner, and answering it
         * with `querySelector` alone is wrong: **Filament does not remove the
         * steps of a wizard from the DOM** — it renders every one of them and
         * hides the inactive ones with `visibility: hidden; height: 0`. A
         * collapsed section is `display: none`. Both still match a selector.
         *
         * Take the match at face value and everything downstream breaks: the
         * spotlight is drawn around an invisible field (a 16px box in the corner
         * of the screen), `waiting` never engages, and the tour never nudges the
         * wizard forward because it believes the field is already there.
         *
         * So a match that cannot be seen is not a match.
         */
        isOnScreen(element) {
            if (!(element instanceof HTMLElement)) {
                return false;
            }

            // display: none, and anything with no box at all.
            if (element.getClientRects().length === 0) {
                return false;
            }

            const style = window.getComputedStyle(element);

            // visibility is inherited, so an inactive wizard pane hides its
            // fields with it — this is the check that catches Filament's wizard.
            return style.visibility !== 'hidden'
                && style.visibility !== 'collapse'
                && style.display !== 'none';
        },

        /**
         * Put the popover where it fits — measured, not guessed. The copy decides
         * how tall the box is, and a box positioned from a guess is the box whose
         * buttons end up off the screen.
         */
        async placePopover(rect, placement = 'auto') {
            await this.$nextTick();

            const popover = this.$refs.popover;
            const width = popover?.offsetWidth || POPOVER_FALLBACK_WIDTH;
            const height = popover?.offsetHeight || POPOVER_FALLBACK_HEIGHT;

            if (!rect) {
                this.popover = {
                    top: Math.max(POPOVER_MARGIN, (window.innerHeight - height) / 2),
                    left: Math.max(POPOVER_MARGIN, (window.innerWidth - width) / 2),
                };

                return;
            }

            const below = rect.bottom + POPOVER_GAP;
            const above = rect.top - height - POPOVER_GAP;

            const fitsBelow = below + height <= window.innerHeight - POPOVER_MARGIN;
            const fitsAbove = above >= POPOVER_MARGIN;

            let top;
            let left = rect.left + rect.width / 2 - width / 2;

            if (placement === 'top' && fitsAbove) {
                top = above;
            } else if (placement === 'bottom' && fitsBelow) {
                top = below;
            } else if (fitsBelow) {
                top = below;
            } else if (fitsAbove) {
                top = above;
            } else {
                // No room above or below: sit beside the element, on whichever
                // side has the space.
                const rightOf = rect.right + POPOVER_GAP;
                const leftOf = rect.left - width - POPOVER_GAP;

                left = rightOf + width <= window.innerWidth - POPOVER_MARGIN ? rightOf : leftOf;
                top = rect.top;
            }

            this.popover = {
                top: this.clamp(top, POPOVER_MARGIN, window.innerHeight - height - POPOVER_MARGIN),
                left: this.clamp(left, POPOVER_MARGIN, window.innerWidth - width - POPOVER_MARGIN),
            };
        },

        clamp(value, min, max) {
            return Math.min(Math.max(value, min), Math.max(min, max));
        },

        /**
         * The arrow keys move the tour — unless the subject is typing.
         *
         * A tour over a wizard is precisely the case where they are: pressing
         * left to move the caret inside a field would page the tour backwards,
         * and Escape — which closes a dropdown, clears a search, ends an IME
         * composition — would throw the whole tour away.
         */
        onKeydown(event) {
            if (!this.active || this.isTyping(event)) {
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

        isTyping(event) {
            if (event.isComposing) {
                return true;
            }

            const target = event.target;

            if (!(target instanceof HTMLElement)) {
                return false;
            }

            return target.isContentEditable
                || ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName);
        },
    };
}
