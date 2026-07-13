import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import onboardingMedia from '../../resources/js/onboarding-media.js';

/**
 * The player, on the promise the README makes: watch time is real, it is kept
 * as the furthest point reached, and nothing is invented about a provider that
 * cannot be asked.
 *
 * No provider script is loaded here — the media under test is a plain file
 * whose mount goes nowhere without a <video> ref. What is being tested is the
 * accounting: which step the seconds land on, when the server is told, and
 * what happens when one video is opened over another.
 */
const components = [];

function media() {
    const component = onboardingMedia();

    component.$nextTick = (callback) => (callback ? callback() : Promise.resolve());
    component.$refs = {};

    components.push(component);

    return component;
}

function fileVideo(overrides = {}) {
    return {
        type: 'video',
        provider: 'file',
        url: '/videos/intro.mp4',
        trackable: true,
        position: 'bottom-right',
        watched: 0,
        ...overrides,
    };
}

beforeEach(() => {
    window.Livewire = { dispatch: vi.fn() };
});

afterEach(() => {
    components.splice(0).forEach((component) => component.teardown());

    delete window.Livewire;
});

describe('watch time', () => {
    it('keeps the furthest point reached, not the last one', () => {
        const component = media();

        component.media = fileVideo();
        component.stepKey = 'watch-intro';

        component.track(30, 100);
        component.track(10, 100); // rewound to rewatch a bit

        expect(component.seconds).toBe(30);
    });

    it('never counts past the end of the video', () => {
        const component = media();

        component.media = fileVideo();
        component.stepKey = 'watch-intro';

        component.track(500, 100);

        expect(component.seconds).toBe(100);
    });

    it('says it every few seconds, not on every tick', () => {
        const component = media();

        component.media = fileVideo();
        component.stepKey = 'watch-intro';

        component.track(1, 100);
        component.track(2, 100);
        component.track(3, 100);

        // timeupdate fires several times a second; one round trip is plenty.
        expect(window.Livewire.dispatch).toHaveBeenCalledOnce();

        // Closing is worth saying again, whatever the clock thinks.
        component.report(true);

        expect(window.Livewire.dispatch).toHaveBeenCalledTimes(2);
    });

    it('invents nothing about a provider it cannot ask', () => {
        const component = media();

        component.media = fileVideo({ provider: 'embed', trackable: false });
        component.stepKey = 'watch-somewhere-else';
        component.seconds = 60;
        component.duration = 100;

        component.report(true);

        expect(window.Livewire.dispatch).not.toHaveBeenCalled();
    });
});

describe('one video opened over another', () => {
    /**
     * A docked modal leaves the page usable behind it — which is the point —
     * so the subject can press "watch" on a second step while the first video
     * is still up. The player that is leaving must settle its own account: its
     * seconds belong to *its* step, and its poll must not survive into the
     * next video and write the old numbers under the new key.
     */
    it('reports the first video under its own key before switching', async () => {
        const component = media();

        // The first video, half-watched.
        component.open = true;
        component.stepKey = 'video-a';
        component.media = fileVideo();
        component.seconds = 42;
        component.duration = 100;

        await component.show({ key: 'video-b', media: fileVideo({ watched: 7 }) });

        expect(window.Livewire.dispatch).toHaveBeenCalledWith('onboarding-video-progress', {
            key: 'video-a',
            seconds: 42,
            duration: 100,
        });

        // And the new video starts from its own ledger, not the old one's.
        expect(component.stepKey).toBe('video-b');
        expect(component.seconds).toBe(7);
        expect(component.duration).toBe(0);
        expect(component.open).toBe(true);

        // The throttle was reset with the teardown: the new video's first
        // report must not be swallowed because the old one said something
        // a moment ago.
        expect(component.lastReportedAt).toBe(0);
    });

    it('stops the old poll so it cannot write under the new key', async () => {
        const component = media();

        component.open = true;
        component.stepKey = 'video-a';
        component.media = fileVideo();
        component.duration = 100;
        component.startPolling(() => ({ seconds: 99, duration: 100 }));

        await component.show({ key: 'video-b', media: fileVideo() });

        expect(component.poll).toBeNull();
    });
});

describe('closing', () => {
    it('says where the video was left once more on the way out', () => {
        const component = media();

        component.open = true;
        component.stepKey = 'watch-intro';
        component.media = fileVideo();
        component.seconds = 61.5;
        component.duration = 100;

        component.close();

        expect(window.Livewire.dispatch).toHaveBeenCalledWith('onboarding-video-progress', {
            key: 'watch-intro',
            seconds: 61.5,
            duration: 100,
        });

        expect(component.open).toBe(false);
        expect(component.media).toBeNull();
        expect(component.stepKey).toBeNull();
    });
});
