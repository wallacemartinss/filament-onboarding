/**
 * Click-and-drag scrolling for the flow-tabs row (.fio-tabs, launcher
 * component — see resources/views/livewire/launcher.blade.php).
 *
 * The row already scrolls horizontally on its own (CSS: overflow-x: auto),
 * and that is deliberately all it used to need: a trackpad swipe, or
 * shift+wheel, just works. But its scrollbar is hidden by design (the
 * checklist is a small floating card, and a visible scrollbar across four
 * pill-shaped tabs reads as clutter, not a hint) — which means a subject on a
 * plain mouse has no sign the row scrolls at all. This adds the other common
 * way in: press and drag, the way a horizontal carousel usually works.
 *
 * A drag and a tab click start the same way — mousedown on a button — so the
 * two have to be told apart. The threshold below is that distinction: only
 * once the pointer has actually moved is it a drag, and only then does the
 * `click.capture` listener in the template stop the tab's own `wire:click`
 * from firing. A plain click, with no intervening movement, reaches the
 * button exactly as it always did.
 */

const DRAG_THRESHOLD = 4;

export default function onboardingTabs() {
    return {
        down: false,
        dragged: false,
        startX: 0,
        startScroll: 0,

        get dragging() {
            return this.down && this.dragged;
        },

        start(event) {
            // The left button only — a right-click or a trackpad two-finger
            // tap firing mousedown should not start a drag.
            if (event.button !== 0) {
                return;
            }

            this.down = true;
            this.dragged = false;
            this.startX = event.pageX;
            this.startScroll = this.$el.scrollLeft;
        },

        move(event) {
            if (!this.down) {
                return;
            }

            const delta = event.pageX - this.startX;

            if (!this.dragged && Math.abs(delta) > DRAG_THRESHOLD) {
                this.dragged = true;
            }

            if (!this.dragged) {
                return;
            }

            // Text selection and the browser's own drag gesture are the
            // default for a mousedown-then-move over inline content; without
            // this, dragging the tabs also highlights their labels.
            event.preventDefault();

            this.$el.scrollLeft = this.startScroll - delta;
        },

        end() {
            this.down = false;
        },

        /**
         * The click that follows a drag's mouseup is not a click on whatever
         * tab the pointer happens to be over — it is the drag ending. Caught
         * here, in the capture phase, before it reaches the tab button's own
         * `wire:click` and switches the flow nobody meant to switch to.
         */
        guard(event) {
            if (!this.dragged) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            this.dragged = false;
        },
    };
}
