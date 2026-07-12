<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Support;

use Illuminate\Support\Str;
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
            MediaSource::Embed => self::isSafeToFrame($url) ? [
                'provider' => 'embed',
                'id'       => null,
                'src'      => $url,
            ] : null,
            default => self::isSafeToFrame($url) ? [
                'provider' => 'file',
                'id'       => null,
                'src'      => $url,
            ] : null,
        };
    }

    /**
     * Whether this URL may be handed to an `<iframe>` or a `<video>`.
     *
     * YouTube and Vimeo never reach here — the browser rebuilds those from an id
     * against a hardcoded domain, so nothing an author types can escape the
     * provider. An "embed" is the opposite: whatever is written goes into a frame
     * as-is. Authors are trusted, but `javascript:` and `data:text/html` are not
     * addresses — they are code — and a step is not the place to run it.
     */
    private static function isSafeToFrame(string $url): bool
    {
        $scheme = Str::lower((string) parse_url(trim($url), PHP_URL_SCHEME));

        // A relative path (an uploaded file, a route) has no scheme, and is ours.
        if ($scheme === '') {
            return Str::startsWith(trim($url), '/');
        }

        return in_array($scheme, ['http', 'https'], strict: true);
    }
}
