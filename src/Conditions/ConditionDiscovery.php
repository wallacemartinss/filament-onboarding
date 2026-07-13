<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Conditions;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Wallacemartinss\FilamentOnboarding\Contracts\OnboardingCondition;

/**
 * Finds the condition classes an application wrote, without being told about them.
 *
 * Filament discovers resources, pages and widgets by looking in a directory; a
 * condition is no different, and asking somebody to write the class *and* then
 * name it in a config file is asking them to say the same thing twice — the sort
 * of second step that is remembered in development and forgotten in the deploy
 * that matters.
 *
 * Drop a class in app/Onboarding/Conditions and it is in the panel's dropdown.
 * The key is the class name without its noise: HasClientCondition → has_client.
 * A class that would rather name itself says so with a `key()` method.
 */
final class ConditionDiscovery
{
    /**
     * @return array<string, class-string<OnboardingCondition>>
     */
    public static function discover(): array
    {
        if (!config('filament-onboarding.discovery.enabled', true)) {
            return [];
        }

        $path      = (string) config('filament-onboarding.discovery.path', app_path('Onboarding/Conditions'));
        $namespace = (string) config('filament-onboarding.discovery.namespace', 'App\\Onboarding\\Conditions');

        $filesystem = app(Filesystem::class);

        if (blank($path) || !$filesystem->isDirectory($path)) {
            return [];
        }

        $conditions = [];

        foreach ($filesystem->allFiles($path) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $class = rtrim($namespace, '\\') . '\\' . str_replace(
                [DIRECTORY_SEPARATOR, '.php'],
                ['\\', ''],
                $file->getRelativePathname(),
            );

            if (!class_exists($class) || !is_subclass_of($class, OnboardingCondition::class)) {
                continue;
            }

            if ((new \ReflectionClass($class))->isAbstract()) {
                continue;
            }

            $conditions[static::key($class)] = $class;
        }

        return $conditions;
    }

    /**
     * The key a class answers to: its own, if it says; otherwise the class name
     * in the shape the rest of the package uses — HasClientCondition → has_client.
     *
     * @param  class-string  $class
     */
    public static function key(string $class): string
    {
        if (method_exists($class, 'key')) {
            return (string) $class::key();
        }

        $name = class_basename($class);

        // Both endings are natural to write, and neither belongs in the key.
        $name = (string) Str::of($name)->beforeLast('Condition')->whenEmpty(fn () => Str::of(class_basename($class)));

        return Str::snake($name);
    }
}
