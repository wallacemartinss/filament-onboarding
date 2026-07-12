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
 *
 * Nothing in here may throw. It is called while rendering the checklist, and the
 * checklist is rendered in the layout of every page: a disk that cannot build a
 * URL (there are drivers that simply do not) would take the whole panel down over
 * an image on a step. A step with no picture is a small disappointment. A panel
 * that will not load is not.
 */
final class MediaUrl
{
    public static function resolve(?string $disk, ?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $disk ??= config('filament-onboarding.media.disk', 'public');

        try {
            $filesystem = Storage::disk($disk);
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }

        if (self::isPrivate($disk)) {
            try {
                return $filesystem->temporaryUrl(
                    $path,
                    now()->addMinutes((int) config('filament-onboarding.media.url_ttl', 30)),
                );
            } catch (\Throwable $exception) {
                // A private disk that cannot sign is a misconfiguration, and the
                // one thing not to do about it is hand out a public URL — the
                // file was put somewhere closed on purpose. Say nothing, and say
                // it loudly in the log.
                report($exception);

                return null;
            }
        }

        try {
            return $filesystem->url($path);
        } catch (\Throwable $exception) {
            report($exception);

            return null;
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
