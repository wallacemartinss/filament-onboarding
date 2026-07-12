<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Wallacemartinss\FilamentOnboarding\Enums\{CompletionMode, MediaSource, MediaType, ModalPosition, StepType};
use Wallacemartinss\FilamentOnboarding\Facades\Onboarding;
use Wallacemartinss\FilamentOnboarding\Models\{OnboardingFlow, OnboardingStep};
use Wallacemartinss\FilamentOnboarding\Support\{MediaUrl, VideoEmbed};
use Wallacemartinss\FilamentOnboarding\Tests\Fixtures\Subject;
use Wallacemartinss\FilamentOnboarding\Tests\TestCase;

class MediaTest extends TestCase
{
    private Subject $subject;

    private OnboardingFlow $flow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = Subject::create(['name' => 'Ada']);

        $this->flow = OnboardingFlow::create([
            'key'       => 'journey',
            'title'     => ['en' => 'Journey'],
            'is_active' => true,
        ]);
    }

    public function test_it_digs_the_id_out_of_any_youtube_link(): void
    {
        $this->assertSame('dQw4w9WgXcQ', VideoEmbed::youTubeId('https://www.youtube.com/watch?v=dQw4w9WgXcQ'));
        $this->assertSame('dQw4w9WgXcQ', VideoEmbed::youTubeId('https://youtu.be/dQw4w9WgXcQ?t=42'));
        $this->assertSame('dQw4w9WgXcQ', VideoEmbed::youTubeId('https://www.youtube.com/shorts/dQw4w9WgXcQ'));
        $this->assertSame('347119375', VideoEmbed::vimeoId('https://vimeo.com/347119375'));
        $this->assertNull(VideoEmbed::youTubeId('https://example.com/nope'));
    }

    public function test_it_hands_the_player_what_it_needs(): void
    {
        $step = $this->videoStep();

        $media = $step->resolveMedia();

        $this->assertSame('video', $media['type']);
        $this->assertSame('youtube', $media['provider']);
        $this->assertSame('dQw4w9WgXcQ', $media['video_id']);
        $this->assertTrue($media['trackable']);
        $this->assertSame('bottom-right', $media['position']);
    }

    public function test_it_keeps_the_furthest_point_watched(): void
    {
        $this->videoStep();

        $onboarding = Onboarding::for($this->subject);

        $onboarding->recordVideoProgress('watch-intro', seconds: 60, duration: 200);
        $onboarding->recordVideoProgress('watch-intro', seconds: 20, duration: 200);

        $progress = Onboarding::for($this->subject)->flow('journey')->step('watch-intro')->videoProgress();

        $this->assertSame(60.0, $progress['seconds']);
        $this->assertSame(30, $progress['percent']);
    }

    public function test_watching_enough_of_it_completes_the_step(): void
    {
        $this->videoStep(['video_completion_threshold' => 90]);

        Onboarding::for($this->subject)->recordVideoProgress('watch-intro', seconds: 100, duration: 200);
        $this->assertFalse(Onboarding::for($this->subject)->flow('journey')->step('watch-intro')->isCompleted());

        Onboarding::for($this->subject)->recordVideoProgress('watch-intro', seconds: 185, duration: 200);
        $this->assertTrue(Onboarding::for($this->subject)->flow('journey')->step('watch-intro')->isCompleted());
    }

    public function test_a_video_step_completed_another_way_still_tracks_watch_time(): void
    {
        $this->videoStep(['completion_mode' => CompletionMode::Manual]);

        Onboarding::for($this->subject)->recordVideoProgress('watch-intro', seconds: 200, duration: 200);

        $step = Onboarding::for($this->subject)->flow('journey')->step('watch-intro');

        $this->assertFalse($step->isCompleted());
        $this->assertSame(100, $step->videoProgress()['percent']);
    }

    public function test_nothing_is_invented_about_an_unknown_embed(): void
    {
        $step = $this->videoStep([
            'media_source' => MediaSource::Embed,
            'media_url'    => 'https://player.example.com/embed/abc',
        ]);

        $this->assertFalse($step->resolveMedia()['trackable']);
        $this->assertSame('embed', $step->resolveMedia()['provider']);
    }

    public function test_an_uploaded_image_is_served_from_its_disk(): void
    {
        Storage::fake('public');

        $path = Storage::disk('public')->putFile('onboarding', UploadedFile::fake()->image('guide.png'));

        OnboardingStep::create([
            'flow_id'         => $this->flow->id,
            'key'             => 'see-the-map',
            'title'           => ['en' => 'See the map'],
            'completion_mode' => CompletionMode::Manual,
            'media_type'      => MediaType::Image,
            'media_source'    => MediaSource::Upload,
            'media_disk'      => 'public',
            'media_path'      => $path,
        ]);

        $state = Onboarding::for($this->subject)->flow('journey')->step('see-the-map');

        $this->assertTrue($state->hasImage());
        $this->assertStringContainsString($path, (string) $state->imageUrl());
    }

    public function test_a_tour_reports_the_stop_it_reached(): void
    {
        OnboardingStep::create([
            'flow_id'         => $this->flow->id,
            'key'             => 'tour',
            'type'            => StepType::Tour,
            'title'           => ['en' => 'Tour'],
            'completion_mode' => CompletionMode::Programmatic,
            'tour_steps'      => [
                ['selector' => '#a', 'title' => ['en' => 'One']],
                ['selector' => '#b', 'title' => ['en' => 'Two']],
                ['selector' => '#c', 'title' => ['en' => 'Three']],
                ['selector' => '#d', 'title' => ['en' => 'Four']],
            ],
        ]);

        Onboarding::for($this->subject)->recordTourProgress('tour', index: 1, total: 4);

        $step = Onboarding::for($this->subject)->flow('journey')->step('tour');

        $this->assertSame(25, $step->percentage());
        $this->assertSame(['reached' => 1, 'total' => 4], $step->tourProgress());
        $this->assertFalse($step->isCompleted());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function videoStep(array $attributes = []): OnboardingStep
    {
        return OnboardingStep::create([
            'flow_id'                    => $this->flow->id,
            'key'                        => 'watch-intro',
            'type'                       => StepType::Task,
            'title'                      => ['en' => 'Watch the intro'],
            'completion_mode'            => CompletionMode::Video,
            'media_type'                 => MediaType::Video,
            'media_source'               => MediaSource::YouTube,
            'media_url'                  => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'modal_position'             => ModalPosition::BottomRight,
            'video_completion_threshold' => 90,
            ...$attributes,
        ]);
    }

    /**
     * The checklist is rendered in the layout of every page, so a disk that
     * cannot build a URL must not be allowed to take the panel down over a
     * picture on a step.
     */
    public function test_a_disk_that_cannot_build_a_url_costs_the_image_not_the_page(): void
    {
        config()->set('filesystems.disks.no_urls', ['driver' => 'local', 'root' => sys_get_temp_dir()]);

        $this->assertNull(MediaUrl::resolve('there-is-no-such-disk', 'shot.png'));
    }

    /**
     * A private file was put somewhere closed on purpose. If the disk cannot sign
     * a URL, the answer is no URL — not a public one.
     */
    public function test_a_private_disk_that_cannot_sign_hands_out_nothing(): void
    {
        config()->set('filesystems.disks.closed', ['driver' => 'local', 'root' => sys_get_temp_dir(), 'visibility' => 'private']);

        $this->assertNull(MediaUrl::resolve('closed', 'secret.png'));
    }

    public function test_an_embed_that_is_code_rather_than_an_address_is_refused(): void
    {
        $this->assertNull(VideoEmbed::forSource(MediaSource::Embed, 'javascript:alert(1)'));
        $this->assertNull(VideoEmbed::forSource(MediaSource::Embed, 'data:text/html;base64,PHNjcmlwdD4='));

        $this->assertSame(
            'https://example.com/player',
            VideoEmbed::forSource(MediaSource::Embed, 'https://example.com/player')['src'],
        );
    }
}
