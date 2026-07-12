<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Support;

use Illuminate\Support\Facades\Storage;

/**
 * The address of an uploaded file, whichever disk it landed on.
 *
 * A public disk hands back a plain URL. A private one — an S3 or R2 bucket kept
 * closed, which is what you want for anything that is not marketing material —
 * is signed for a short while instead, so the file is reachable by the person
 * looking at the step and by nobody else.
 */
final class MediaUrl
{
    public static function resolve(?string $disk, ?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $disk ??= config('filament-onboarding.media.disk', 'public');

        $filesystem = Storage::disk($disk);

        if (!self::isPrivate($disk)) {
            return $filesystem->url($path);
        }

        try {
            return $filesystem->temporaryUrl(
                $path,
                now()->addMinutes((int) config('filament-onboarding.media.url_ttl', 30)),
            );
        } catch (\Throwable) {
            // Local disks cannot sign URLs. Falling back keeps the step usable
            // rather than showing a broken image.
            return $filesystem->url($path);
        }
    }

    private static function isPrivate(string $disk): bool
    {
        $configured = config('filament-onboarding.media.visibility', 'public');

        if ($configured === 'private') {
            return true;
        }

        return config("filesystems.disks.{$disk}.visibility") === 'private';
    }
}
