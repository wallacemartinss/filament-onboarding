<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Support;

use Filament\Forms\Components\{Field, TextInput};
use Wallacemartinss\FilamentIconPicker\Forms\Components\IconPickerField;

/**
 * The icon field, as good as the host application allows.
 *
 * wallacemartinss/filament-icon-picker turns this into a visual picker over
 * every icon set installed. Without it the field is a plain text input, which
 * still accepts any Blade icon name — heroicon-o-server, phosphor-rocket, and
 * so on — so the package never *needs* the picker to work.
 */
final class IconInput
{
    public static function make(string $name): Field
    {
        if (!self::hasPicker()) {
            return TextInput::make($name)
                ->placeholder('heroicon-o-rocket-launch')
                ->maxLength(255);
        }

        return IconPickerField::make($name);
    }

    public static function hasPicker(): bool
    {
        return class_exists(IconPickerField::class);
    }
}
