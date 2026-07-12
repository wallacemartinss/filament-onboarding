<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Support;

use Wallacemartinss\FilamentOnboarding\Enums\MediaSource;

/**
 * Turns the link somebody pasted into something a player can use.
 *
 * People paste whatever the address bar gave them — a watch URL, a share link, a
 * shorts link — so the id is dug out of any of them rather than demanding a
 * particular shape.
 */
final class VideoEmbed
{
    public static function youTubeId(string $url): ?string
    {
        $patterns = [
            '#youtu\.be/([\w-]{6,})#i',
            '#youtube\.com/watch\?(?:.*&)?v=([\w-]{6,})#i',
            '#youtube\.com/embed/([\w-]{6,})#i',
            '#youtube\.com/shorts/([\w-]{6,})#i',
            '#youtube\.com/live/([\w-]{6,})#i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches) === 1) {
                return $matches[1];
            }
        }

        // A bare id, pasted on its own.
        return preg_match('#^[\w-]{6,}$#', $url) === 1 ? $url : null;
    }

    public static function vimeoId(string $url): ?string
    {
        $patterns = [
            '#vimeo\.com/video/(\d+)#i',
            '#vimeo\.com/(\d+)#i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches) === 1) {
                return $matches[1];
            }
        }

        return preg_match('#^\d+$#', $url) === 1 ? $url : null;
    }

    /**
     * What the browser should load for this video.
     *
     * @return array{provider: string, src: string|null, id: string|null}|null
     */
    public static function forSource(MediaSource $source, ?string $url): ?array
    {
        if (blank($url)) {
            return null;
        }

        return match ($source) {
            MediaSource::YouTube => self::youTubeId($url) === null ? null : [
                'provider' => 'youtube',
                'id'       => self::youTubeId($url),
                'src'      => null,
            ],
            MediaSource::Vimeo => self::vimeoId($url) === null ? null : [
                'provider' => 'vimeo',
                'id'       => self::vimeoId($url),
                'src'      => null,
            ],
            MediaSource::Embed => [
                'provider' => 'embed',
                'id'       => null,
                'src'      => $url,
            ],
            default => [
                'provider' => 'file',
                'id'       => null,
                'src'      => $url,
            ],
        };
    }
}
