<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Concerns;

use Wallacemartinss\FilamentOnboarding\Support\TranslatableText;

/**
 * Resolves JSON columns that hold one value per locale.
 *
 * @property array<string, string> $translatable
 */
trait HasTranslatableColumns
{
    /**
     * The value of a translatable column in the current (or given) locale.
     */
    public function translate(string $column, ?string $locale = null): ?string
    {
        return TranslatableText::resolve($this->getAttribute($column), $locale);
    }

    /**
     * Every locale stored for a translatable column.
     *
     * @return array<string, string>
     */
    public function translations(string $column): array
    {
        $value = $this->getAttribute($column);

        if (is_array($value)) {
            return array_filter($value, fn ($text): bool => filled($text));
        }

        return filled($value) ? [app()->getLocale() => (string) $value] : [];
    }
}
