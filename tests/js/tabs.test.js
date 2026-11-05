import { describe, expect, it } from 'vitest';

import onboardingTabs from '../../resources/js/onboarding-tabs.js';

/**
 * The flow-tabs row scrolls on its own (CSS), with a hidden scrollbar and no
 * other hint that it does — this is the mouse-drag affordance that makes up
 * for that. What matters is the one thing a drag-to-scroll handler can get
 * wrong without anyone noticing until a demo: a plain click has to still
 * reach the tab button underneath it, and only a real drag may steal it.
 */
function tabs() {
    const component = onboardingTabs();

    component.$el = document.createElement('div');
    component.$el.scrollLeft = 0;

    return component;
}

function mousedown(x) {
    return { button: 0, pageX: x, preventDefault: () => {} };
}

function mousemove(x) {
    return { pageX: x, preventDefault: () => {} };
}

describe('dragging', () => {
    it('scrolls the row once the pointer moves past the threshold', () => {
        const component = tabs();

        component.start(mousedown(100));
        component.move(mousemove(94)); // 6px — past the 4px threshold

        expect(component.dragged).toBe(true);
        expect(component.$el.scrollLeft).toBe(6);
    });

    it('keeps scrolling as the pointer keeps moving', () => {
        const component = tabs();

        component.start(mousedown(100));
        component.move(mousemove(90));
        component.move(mousemove(70));

        expect(component.$el.scrollLeft).toBe(30);
    });

    it('does nothing before mousedown', () => {
        const component = tabs();

        component.move(mousemove(50));

        expect(component.dragged).toBe(false);
        expect(component.$el.scrollLeft).toBe(0);
    });

    it('ignores a right-click', () => {
        const component = tabs();

        component.start({ button: 2, pageX: 100 });
        component.move(mousemove(50));

        expect(component.down).toBe(false);
        expect(component.$el.scrollLeft).toBe(0);
    });
});

describe('telling a drag from a click', () => {
    it('never marks a plain click (no movement at all) as a drag', () => {
        const component = tabs();

        component.start(mousedown(100));
        component.end();

        expect(component.dragged).toBe(false);
    });

    it('stays below the threshold not a drag', () => {
        const component = tabs();

        component.start(mousedown(100));
        component.move(mousemove(98)); // 2px — under the 4px threshold
        component.end();

        expect(component.dragged).toBe(false);
        expect(component.$el.scrollLeft).toBe(0);
    });

    it('lets an un-dragged click straight through the guard', () => {
        const component = tabs();
        let stopped = false;

        const event = {
            preventDefault: () => (stopped = true),
            stopPropagation: () => (stopped = true),
        };

        component.guard(event);

        expect(stopped).toBe(false);
    });

    it('swallows the click that follows an actual drag', () => {
        const component = tabs();

        component.start(mousedown(100));
        component.move(mousemove(80));
        component.end();

        let stopped = false;

        component.guard({
            preventDefault: () => (stopped = true),
            stopPropagation: () => (stopped = true),
        });

        expect(stopped).toBe(true);

        // One swallowed click is the end of it — the next one, unrelated to
        // this drag, must not be eaten too.
        expect(component.dragged).toBe(false);
    });
});

describe('the dragging getter the template\'s :class reads', () => {
    it('is true only while a real drag is in progress', () => {
        const component = tabs();

        expect(component.dragging).toBe(false);

        component.start(mousedown(100));
        expect(component.dragging).toBe(false); // down, but not moved yet

        component.move(mousemove(80));
        expect(component.dragging).toBe(true);

        component.end();
        expect(component.dragging).toBe(false);
    });
});
