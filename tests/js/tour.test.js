import { beforeEach, describe, expect, it, vi } from 'vitest';

import onboardingTour from '../../resources/js/onboarding-tour.js';

/**
 * The runner, on the question everything else hangs off: is this element on the
 * screen or not?
 *
 * Filament renders **every** step of a wizard and hides the inactive ones with
 * `visibility: hidden; height: 0`. A collapsed section is `display: none`. Both
 * still answer to `querySelector`, so a runner that trusts the selector believes
 * a field is there while the subject is looking at a different step entirely —
 * and then spotlights nothing, never waits, and never nudges the form along.
 *
 * jsdom does no layout, so `getClientRects()` is stubbed per element: this is
 * about the decision the runner makes given a box, not about the box itself.
 */
function tour() {
    const component = onboardingTour();

    component.$nextTick = (callback) => (callback ? callback() : Promise.resolve());
    component.$refs = {};

    return component;
}

/**
 * Give an element a box (or take it away, the way `display: none` does).
 */
function withBox(element, box = { top: 10, left: 10, width: 200, height: 40 }) {
    element.getClientRects = () => (box ? [box] : []);
    element.getBoundingClientRect = () => ({
        top: box?.top ?? 0,
        left: box?.left ?? 0,
        width: box?.width ?? 0,
        height: box?.height ?? 0,
        bottom: (box?.top ?? 0) + (box?.height ?? 0),
        right: (box?.left ?? 0) + (box?.width ?? 0),
    });

    return element;
}

describe('find()', () => {
    beforeEach(() => {
        document.body.innerHTML = '';
    });

    it('finds an element the subject can see', () => {
        const field = withBox(document.createElement('input'));
        field.id = 'name';
        document.body.append(field);

        expect(tour().find('#name')).toBe(field);
    });

    it('does not find a field on a wizard step the subject has not reached', () => {
        // This is exactly Filament's markup: the pane stays in the DOM, hidden.
        const pane = document.createElement('div');
        pane.style.visibility = 'hidden';

        const field = withBox(document.createElement('input'), { top: 0, left: 0, width: 0, height: 0 });
        field.id = 'name';

        pane.append(field);
        document.body.append(pane);

        // visibility is inherited, which is what makes the field invisible too.
        vi.spyOn(window, 'getComputedStyle').mockReturnValue({ visibility: 'hidden', display: 'block' });

        expect(tour().find('#name')).toBeNull();

        vi.restoreAllMocks();
    });

    it('does not find an element inside a collapsed section', () => {
        const field = withBox(document.createElement('input'), null); // display: none has no boxes
        field.id = 'name';
        document.body.append(field);

        expect(tour().find('#name')).toBeNull();
    });

    it('walks past a hidden match to a visible one', () => {
        const hidden = withBox(document.createElement('button'), null);
        const visible = withBox(document.createElement('button'));

        hidden.className = 'target';
        visible.className = 'target';

        document.body.append(hidden, visible);

        expect(tour().find('.target')).toBe(visible);
    });

    it('carries on when the selector is nonsense rather than throwing', () => {
        expect(tour().find('[[[not-a-selector')).toBeNull();
    });
});

describe('the tour and the application walking together', () => {
    beforeEach(() => {
        document.body.innerHTML = '';
    });

    it('clicks the control that carries the app to a stop it cannot see', () => {
        const next = withBox(document.createElement('button'));
        next.id = 'next-step';
        document.body.append(next);

        const clicked = vi.fn();
        next.addEventListener('click', clicked);

        const component = tour();

        component.active = true;
        component.stepKey = 'a-tour';
        component.steps = [
            { selector: '#on-page', title: 'Here' },
            { selector: '#not-yet', advance: '#next-step', title: 'Over there' },
        ];
        component.index = 0;
        component.render = vi.fn();

        component.next();

        // The field of stop 2 is nowhere to be seen, so the tour presses the
        // wizard's own next button instead of pointing at nothing.
        expect(clicked).toHaveBeenCalledOnce();
    });

    it('does not press anything when the element is already on screen', () => {
        const field = withBox(document.createElement('input'));
        field.id = 'already-here';

        const next = withBox(document.createElement('button'));
        next.id = 'next-step';

        document.body.append(field, next);

        const clicked = vi.fn();
        next.addEventListener('click', clicked);

        const component = tour();

        component.active = true;
        component.steps = [
            { selector: '#first', title: 'One' },
            { selector: '#already-here', advance: '#next-step', title: 'Two' },
        ];
        component.index = 0;
        component.render = vi.fn();

        component.next();

        expect(clicked).not.toHaveBeenCalled();
    });

    it('refuses to move on while it is waiting for the form', () => {
        const component = tour();

        component.active = true;
        component.waiting = true;
        component.steps = [{ title: 'One' }, { title: 'Two' }];
        component.index = 0;
        component.render = vi.fn();

        component.next();

        // The way forward is the form, not the tour: paging through would walk
        // the popover across stops of nothing.
        expect(component.index).toBe(0);
        expect(component.render).not.toHaveBeenCalled();
    });

    it('follows the subject when they move the form past the current stop', () => {
        const ahead = withBox(document.createElement('input'));
        ahead.id = 'on-step-three';
        document.body.append(ahead);

        const component = tour();

        component.active = true;
        component.steps = [
            { selector: '#on-step-two' },
            { selector: '#on-step-three' },
        ];
        component.index = 0;

        expect(component.firstStopOnScreenAhead()).toBe(1);
    });

    it('does not follow a stop that lives on another page', () => {
        const component = tour();

        component.active = true;
        component.steps = [
            { selector: '#missing' },
            { selector: '#elsewhere', url: '/other/page' },
        ];
        component.index = 0;

        expect(component.firstStopOnScreenAhead()).toBeNull();
    });
});

describe('what to draw the spotlight around', () => {
    beforeEach(() => {
        document.body.innerHTML = '';
    });

    it('spotlights a visible field as it is, not its caption', () => {
        const field = withBox(document.createElement('input'));

        expect(tour().resolveTarget(field)).toBe(field);
    });

    it('spotlights the whole row of a toggle whose real input is hidden away', () => {
        // A styled toggle keeps its checkbox sr-only behind the control that is
        // actually on the screen — a 1px box next to the thing it means.
        const label = withBox(document.createElement('label'), { top: 0, left: 0, width: 400, height: 56 });
        const checkbox = withBox(document.createElement('input'), { top: 0, left: 0, width: 1, height: 1 });

        checkbox.type = 'checkbox';
        label.append(checkbox);
        document.body.append(label);

        expect(tour().resolveTarget(checkbox)).toBe(label);
    });
});

describe('keeping up with a page that moves on its own', () => {
    beforeEach(() => {
        document.body.innerHTML = '';
    });

    it('follows the element when the page grows under it', () => {
        // The real one: a file upload widget mounts, grows, and pushes the form
        // down. No scroll, no resize — and the old runner, which only listened
        // for those two, left the spotlight on the field above.
        const field = withBox(document.createElement('input'));
        field.id = 'email';
        document.body.append(field);

        const component = tour();

        component.active = true;
        component.steps = [{ selector: '#email' }];
        component.index = 0;
        component.target = field;

        component.measure();

        expect(component.spotlight.top).toBe(2); // 10 - 8 of padding

        // The upload widget mounted: everything below it moved down 88px.
        withBox(field, { top: 98, left: 10, width: 200, height: 40 });

        component.measure();

        expect(component.spotlight.top).toBe(90);
    });

    it('arms the watcher when it lands on a stop', async () => {
        const field = withBox(document.createElement('input'));
        field.id = 'email';
        document.body.append(field);

        const component = tour();

        component.active = true;
        component.stepKey = 'a-tour';
        component.steps = [{ selector: '#email' }];
        component.index = 0;

        await component.render();

        // This is the fix, and the thing that was missing: rendering a stop leaves
        // something behind that keeps up with the element. Without it the spotlight
        // is drawn once and never again, and the first widget that mounts late
        // moves the field out from under it.
        expect(component.target).toBe(field);
        expect(component.watchFrame).not.toBeNull();
    });

    it('writes nothing while the element stays put', () => {
        const field = withBox(document.createElement('input'));
        field.id = 'email';
        document.body.append(field);

        const component = tour();

        component.active = true;
        component.steps = [{ selector: '#email' }];
        component.index = 0;
        component.target = field;
        component.placePopover = vi.fn();

        component.measure();
        component.measure();
        component.measure();

        // One rectangle read per frame is cheap. Rewriting Alpine state per frame
        // is not, and this runs for as long as the tour is open.
        expect(component.placePopover).toHaveBeenCalledOnce();
    });

    it('goes back to waiting when the element leaves the page', () => {
        const field = withBox(document.createElement('input'));
        field.id = 'email';
        document.body.append(field);

        const component = tour();

        component.active = true;
        component.steps = [{ selector: '#email' }];
        component.index = 0;
        component.target = field;
        component.render = vi.fn();

        field.remove();

        component.measure();

        expect(component.render).toHaveBeenCalledOnce();
        expect(component.target).toBeNull();
    });

    it('never scrolls the subject from the watcher', () => {
        const field = withBox(document.createElement('input'), { top: -500, left: 0, width: 200, height: 40 });
        field.scrollIntoView = vi.fn();
        document.body.append(field);

        const component = tour();

        component.active = true;
        component.steps = [{ selector: '#email' }];
        component.index = 0;
        component.target = field;

        component.measure();

        // They scrolled away on purpose. The spotlight follows the element; the
        // page stays where they put it.
        expect(field.scrollIntoView).not.toHaveBeenCalled();
    });

    it('tells the server which stop was reached once, not once per frame', () => {
        const dispatch = vi.fn();

        window.Livewire = { dispatch };

        const component = tour();

        component.active = true;
        component.stepKey = 'a-tour';
        component.steps = [{ selector: '#a' }, { selector: '#b' }];
        component.index = 0;

        component.report();
        component.report();
        component.report();

        expect(dispatch).toHaveBeenCalledOnce();

        component.index = 1;
        component.report();

        expect(dispatch).toHaveBeenCalledTimes(2);

        delete window.Livewire;
    });

    it('lets no render act after the subject closes the tour', () => {
        const component = tour();

        component.active = true;
        component.stepKey = 'a-tour';
        component.steps = [{ selector: '#gone' }];
        component.index = 0;

        const before = component.renderToken;

        component.close();

        expect(component.renderToken).toBeGreaterThan(before);
        expect(component.pollTimers).toHaveLength(0);
        expect(component.watchFrame).toBeNull();
    });
});

describe('the keyboard', () => {
    beforeEach(() => {
        document.body.innerHTML = '';
    });

    it('does not page the tour while the subject is typing in the form', () => {
        const field = withBox(document.createElement('input'));
        document.body.append(field);

        const component = tour();

        component.active = true;
        component.steps = [{ selector: '#a' }, { selector: '#b' }];
        component.index = 0;
        component.render = vi.fn();
        component.close = vi.fn();

        // Moving the caret inside a field is not a request to move the tour.
        component.onKeydown({ key: 'ArrowRight', target: field });
        component.onKeydown({ key: 'ArrowLeft', target: field });
        component.onKeydown({ key: 'Escape', target: field });

        expect(component.index).toBe(0);
        expect(component.close).not.toHaveBeenCalled();
    });

    it('still walks the tour from the page itself', async () => {
        const component = tour();

        component.active = true;
        component.steps = [{ selector: '#a' }, { selector: '#b' }];
        component.index = 0;
        component.render = vi.fn();

        component.onKeydown({ key: 'ArrowRight', target: document.body });

        // next() asks the application whether it will go there before it moves,
        // so the answer lands a microtask later.
        await Promise.resolve();

        expect(component.index).toBe(1);
    });
});

describe('a form that refuses to move on', () => {
    beforeEach(() => {
        document.body.innerHTML = '';
    });

    /**
     * The wizard has required fields. The tour presses its next button, the form
     * puts "this field is required" on the screen and stays exactly where it was.
     * The tour used to walk on regardless — explaining the timezone, a wizard step
     * away, while the subject stared at the errors.
     */
    it('stays where the subject is when the form will not advance', async () => {
        const onScreen = withBox(document.createElement('input'));
        onScreen.id = 'ip-address';

        // The wizard's next button, wired to a form that refuses: nothing new
        // appears when it is pressed.
        const next = withBox(document.createElement('button'));
        next.id = 'next-step';

        document.body.append(onScreen, next);

        const component = tour();

        component.active = true;
        component.stepKey = 'a-tour';
        component.steps = [
            { selector: '#ip-address', title: 'IP' },
            { selector: '#timezone', advance: '#next-step', title: 'Timezone' },
        ];
        component.index = 0;
        component.target = onScreen;
        component.render = vi.fn();

        await component.next();

        expect(component.index).toBe(0);
        expect(component.blocked).toBe(true);
        expect(component.render).not.toHaveBeenCalled();
    });

    it('follows the subject the moment they fill the form in and it moves', async () => {
        const onScreen = withBox(document.createElement('input'));
        onScreen.id = 'ip-address';

        const next = withBox(document.createElement('button'));
        next.id = 'next-step';

        document.body.append(onScreen, next);

        const component = tour();

        component.active = true;
        component.stepKey = 'a-tour';
        component.steps = [
            { selector: '#ip-address' },
            { selector: '#timezone', advance: '#next-step' },
        ];
        component.index = 0;
        component.target = onScreen;
        component.render = vi.fn();

        await component.next();

        expect(component.blocked).toBe(true);

        // They fill it in, the wizard moves, and the field of the next stop is
        // suddenly there. The observer is watching for exactly this.
        const timezone = withBox(document.createElement('input'));
        timezone.id = 'timezone';
        document.body.append(timezone);

        await new Promise((resolve) => setTimeout(resolve, 50));

        expect(component.index).toBe(1);
        expect(component.blocked).toBe(false);
        expect(component.render).toHaveBeenCalled();
    });

    it('moves on when the form does what it is asked', async () => {
        const next = withBox(document.createElement('button'));
        next.id = 'next-step';

        // This button works: pressing it puts the next step's field on the page.
        next.addEventListener('click', () => {
            const timezone = withBox(document.createElement('input'));
            timezone.id = 'timezone';
            document.body.append(timezone);
        });

        document.body.append(next);

        const component = tour();

        component.active = true;
        component.stepKey = 'a-tour';
        component.steps = [
            { selector: '#provider' },
            { selector: '#timezone', advance: '#next-step' },
        ];
        component.index = 0;
        component.render = vi.fn();

        await component.next();

        expect(component.index).toBe(1);
        expect(component.blocked).toBe(false);
        expect(component.render).toHaveBeenCalledOnce();
    });
});

describe('pressing a control the way a person does', () => {
    beforeEach(() => {
        document.body.innerHTML = '';
    });

    /**
     * A Filament dropdown — the filter panel of a table, for one — opens on
     * `mousedown`, not on `click`. A tour that only called `.click()` waited
     * forever for a panel that was never going to open.
     */
    it('sends mousedown, not only click', async () => {
        const trigger = withBox(document.createElement('div'));
        trigger.id = 'filters';

        const seen = [];

        ['mousedown', 'mouseup', 'click'].forEach((type) => {
            trigger.addEventListener(type, () => seen.push(type));
        });

        // The panel only exists once the dropdown is open.
        trigger.addEventListener('mousedown', () => {
            const field = withBox(document.createElement('input'));
            field.id = 'tag-filter';
            document.body.append(field);
        });

        document.body.append(trigger);

        const component = tour();

        component.active = true;
        component.steps = [
            { selector: '#table' },
            { selector: '#tag-filter', advance: '#filters' },
        ];
        component.index = 0;
        component.render = vi.fn();

        await component.next();

        expect(seen).toEqual(['mousedown', 'mouseup', 'click']);
        expect(component.index).toBe(1);
        expect(component.blocked).toBe(false);
    });
});

describe('a stop about something that may not be there', () => {
    beforeEach(() => {
        document.body.innerHTML = '';
    });

    it('steps aside instead of stranding the tour on an empty page', async () => {
        const later = withBox(document.createElement('div'));
        later.id = 'always-here';
        document.body.append(later);

        const component = tour();

        component.active = true;
        component.stepKey = 'a-tour';
        component.steps = [
            // No tags on this account yet, so nothing to point at.
            { selector: '#no-tags-yet', optional: true },
            { selector: '#always-here' },
        ];
        component.index = 0;

        await component.render();

        expect(component.index).toBe(1);
        expect(component.waiting).toBe(false);
        expect(component.target).toBe(later);
    });
});

