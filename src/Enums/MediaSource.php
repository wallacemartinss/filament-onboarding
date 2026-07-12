<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Enums;

use Filament\Support\Contracts\{HasDescription, HasLabel};

/**
 * Where the media lives. Uploads go to whatever disk the application configures
 * — S3, R2, local — and the rest is addressed by URL.
 */
enum MediaSource: string implements HasDescription, HasLabel
{
    /**
     * Uploaded through the panel and stored on the configured disk.
     */
    case Upload = 'upload';

    /**
     * A file elsewhere, addressed directly: a CDN image, an .mp4 on your own host.
     */
    case Url = 'url';

    case YouTube = 'youtube';

    case Vimeo = 'vimeo';

    /**
     * Any other provider, shown in an iframe. Nothing is known about what the
     * player is doing inside, so watch time cannot be tracked.
     */
    case Embed = 'embed';

    public function getLabel(): string
    {
        return __("filament-onboarding::onboarding.enums.media_source.{$this->value}.label");
    }

    public function getDescription(): string
    {
        return __("filament-onboarding::onboarding.enums.media_source.{$this->value}.description");
    }

    /**
     * Whether the player can report how much of the video was watched.
     */
    public function tracksWatchTime(): bool
    {
        return in_array($this, [self::Upload, self::Url, self::YouTube, self::Vimeo], true);
    }

    public function isUpload(): bool
    {
        return $this === self::Upload;
    }

    /**
     * Sources a video step may use.
     *
     * @return array<int, self>
     */
    public static function forVideo(): array
    {
        return [self::Upload, self::Url, self::YouTube, self::Vimeo, self::Embed];
    }

    /**
     * Sources an image step may use.
     *
     * @return array<int, self>
     */
    public static function forImage(): array
    {
        return [self::Upload, self::Url];
    }
}
