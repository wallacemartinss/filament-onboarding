<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Support;

/**
 * Resolves content authored in the panel into the locale the viewer reads.
 *
 * Content is stored as a locale map — ['pt_BR' => '...', 'en' => '...'] — but a
 * plain string is accepted too and passed through the translator, so a flow may
 * also point at a key from the application's own language files.
 */
final class TranslatableText
{
    public static function resolve(mixed $value, ?string $locale = null): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (is_string($value)) {
            return self::translateKey($value, $locale);
        }

        if (!is_array($value)) {
            return null;
        }

        $locale ??= app()->getLocale();

        foreach (self::candidates($locale) as $candidate) {
            if (filled($value[$candidate] ?? null)) {
                return (string) $value[$candidate];
            }
        }

        $first = collect($value)->first(fn ($text): bool => filled($text));

        return $first !== null ? (string) $first : null;
    }

    /**
     * Locales tried in order: the exact one, its variations, then the fallbacks.
     *
     * pt_BR yields pt_BR, pt-BR, pt — so content written for "pt" still reaches
     * a "pt_BR" reader, and vice versa.
     *
     * @return array<int, string>
     */
    public static function candidates(string $locale): array
    {
        $fallback = config('filament-onboarding.fallback_locale')
            ?? config('app.fallback_locale')
            ?? 'en';

        $variations = static fn (string $value): array => array_unique([
            $value,
            str_replace('_', '-', $value),
            str_replace('-', '_', $value),
            explode('_', str_replace('-', '_', $value))[0],
        ]);

        return array_values(array_unique([
            ...$variations($locale),
            ...$variations((string) $fallback),
        ]));
    }

    /**
     * A plain string is treated as a translation key; when the key is missing
     * the translator hands the string back unchanged, which is the literal we
     * want anyway.
     */
    private static function translateKey(string $value, ?string $locale = null): string
    {
        $translated = __($value, [], $locale);

        return is_string($translated) ? $translated : $value;
    }
}
