/**
 * Media modal: the image a step shows, or the video it asks the subject to watch.
 *
 * Watch time is real, not guessed — an uploaded file reports through the video
 * element, YouTube through its IFrame API, Vimeo through its SDK. It is sent
 * back every few seconds and once more on the way out, so a video left half-way
 * is remembered as half-watched and picks up where it stopped.
 *
 * A provider we cannot ask (a bare iframe embed) simply plays; nothing is
 * invented about how much of it was seen.
 */

const REPORT_EVERY = 5000;
const YOUTUBE_API = 'https://www.youtube.com/iframe_api';
const VIMEO_API = 'https://player.vimeo.com/api/player.js';

export default function onboardingMedia() {
    return {
        open: false,
        stepKey: null,
        media: null,
        position: 'center',
        seconds: 0,
        duration: 0,
        player: null,
        poll: null,
        lastReportedAt: 0,

        init() {
            window.addEventListener('onboarding-media-open', (event) => {
                this.show(event.detail ?? {});
            });
        },

        destroy() {
            this.teardown();
        },

        get watchedPercentage() {
            if (!this.duration) {
                return 0;
            }

            return Math.min(100, Math.round((this.seconds / this.duration) * 100));
        },

        get isVideo() {
            return this.media?.type === 'video';
        },

        get isTrackable() {
            return Boolean(this.media?.trackable);
        },

        show(detail) {
            const media = detail.media;

            if (!media) {
                return;
            }

            this.stepKey = detail.key;
            this.media = media;
            this.position = media.position ?? 'center';
            this.seconds = Number(media.watched ?? 0);
            this.duration = 0;
            this.open = true;

            this.$nextTick(() => this.mount());
        },

        close() {
            this.report(true);
            this.teardown();

            this.open = false;
            this.media = null;
            this.stepKey = null;
        },

        mount() {
            if (!this.isVideo) {
                return;
            }

            if (this.media.provider === 'file') {
                return this.mountFile();
            }

            if (this.media.provider === 'youtube') {
                return this.mountYouTube();
            }

            if (this.media.provider === 'vimeo') {
                return this.mountVimeo();
            }
        },

        mountFile() {
            const video = this.$refs.video;

            if (!video) {
                return;
            }

            video.addEventListener('loadedmetadata', () => {
                this.duration = video.duration;

                // Pick the video back up where it was left, but never at the very
                // end — landing on the credits helps nobody.
                if (this.seconds > 0 && this.seconds < video.duration - 1) {
                    video.currentTime = this.seconds;
                }
            });

            video.addEventListener('timeupdate', () => {
                this.track(video.currentTime, video.duration);
            });

            video.addEventListener('ended', () => {
                this.track(video.duration, video.duration);
                this.report(true);
            });

            video.addEventListener('pause', () => this.report(true));
        },

        async mountYouTube() {
            await this.loadScript(YOUTUBE_API);
            await this.whenReady(() => window.YT?.Player);

            this.player = new window.YT.Player(this.$refs.youtube, {
                videoId: this.media.video_id,
                playerVars: {
                    rel: 0,
                    modestbranding: 1,
                    start: Math.floor(this.seconds),
                },
                events: {
                    onReady: (event) => {
                        this.duration = event.target.getDuration();
                        this.startPolling(() => ({
                            seconds: event.target.getCurrentTime(),
                            duration: event.target.getDuration(),
                        }));
                    },
                    onStateChange: (event) => {
                        // 0 = ended, 2 = paused: both are moments worth keeping.
                        if (event.data === 0 || event.data === 2) {
                            this.report(true);
                        }
                    },
                },
            });
        },

        async mountVimeo() {
            await this.loadScript(VIMEO_API);
            await this.whenReady(() => window.Vimeo?.Player);

            this.player = new window.Vimeo.Player(this.$refs.vimeo, {
                id: this.media.video_id,
                responsive: true,
            });

            if (this.seconds > 0) {
                this.player.setCurrentTime(this.seconds).catch(() => {});
            }

            this.player.on('timeupdate', (data) => {
                this.track(data.seconds, data.duration);
            });

            this.player.on('pause', () => this.report(true));
            this.player.on('ended', () => this.report(true));
        },

        startPolling(read) {
            this.stopPolling();

            this.poll = setInterval(() => {
                const { seconds, duration } = read();

                this.track(seconds, duration);
            }, 1000);
        },

        stopPolling() {
            if (this.poll) {
                clearInterval(this.poll);

                this.poll = null;
            }
        },

        track(seconds, duration) {
            if (!Number.isFinite(seconds) || !Number.isFinite(duration) || duration <= 0) {
                return;
            }

            this.seconds = Math.max(this.seconds, Math.min(seconds, duration));
            this.duration = duration;

            this.report(false);
        },

        /**
         * Reported on a timer rather than on every tick — a video fires timeupdate
         * several times a second, and none of those deserve a round trip.
         */
        report(force) {
            if (!this.isTrackable || !this.stepKey || !this.duration) {
                return;
            }

            const now = Date.now();

            if (!force && now - this.lastReportedAt < REPORT_EVERY) {
                return;
            }

            this.lastReportedAt = now;

            window.Livewire?.dispatch('onboarding-video-progress', {
                key: this.stepKey,
                seconds: Number(this.seconds.toFixed(1)),
                duration: Number(this.duration.toFixed(1)),
            });
        },

        teardown() {
            this.stopPolling();

            try {
                this.player?.destroy?.();
                this.player?.unload?.();
            } catch {
                // A player that is already gone needs no goodbye.
            }

            this.player = null;
            this.lastReportedAt = 0;
        },

        loadScript(src) {
            return new Promise((resolve, reject) => {
                if (document.querySelector(`script[src="${src}"]`)) {
                    return resolve();
                }

                const script = document.createElement('script');

                script.src = src;
                script.async = true;
                script.onload = () => resolve();
                script.onerror = () => reject(new Error(`Failed to load ${src}`));

                document.head.appendChild(script);
            });
        },

        /**
         * The provider's script is loaded, but the global it defines may take a
         * moment more (YouTube in particular).
         */
        whenReady(check, timeout = 5000) {
            return new Promise((resolve, reject) => {
                if (check()) {
                    return resolve();
                }

                const startedAt = Date.now();

                const interval = setInterval(() => {
                    if (check()) {
                        clearInterval(interval);

                        return resolve();
                    }

                    if (Date.now() - startedAt > timeout) {
                        clearInterval(interval);

                        reject(new Error('Player did not become available'));
                    }
                }, 50);
            });
        },
    };
}
