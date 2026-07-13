<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * The models a condition can be built over, and the columns they actually have.
 *
 * This is what makes a query authored in a form safe: the author never types a
 * table name or a column name — they pick from what is there. Anything not on
 * these lists cannot reach the database, whatever gets posted.
 *
 * The allowlist is `conditions_builder.models` in the config. Left empty, the
 * application's own models (app/Models) are offered, which is what an author
 * would expect and what an admin panel already exposes anyway.
 */
final class AppModels
{
    /**
     * @return array<class-string<Model>, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (static::allowed() as $class) {
            $options[$class] = Str::headline(class_basename($class));
        }

        asort($options);

        return $options;
    }

    /**
     * @return array<int, class-string<Model>>
     */
    public static function allowed(): array
    {
        /** @var array<int, class-string<Model>> $configured */
        $configured = (array) config('filament-onboarding.conditions_builder.models', []);

        if (filled($configured)) {
            return array_values(array_filter(
                $configured,
                fn (string $class): bool => class_exists($class) && is_subclass_of($class, Model::class),
            ));
        }

        return static::discover();
    }

    /**
     * Whether a model is one an author is allowed to build a condition over. The
     * evaluator asks this before it queries anything, so a record naming a model
     * that was later taken off the list simply stops passing.
     */
    public static function isAllowed(?string $class): bool
    {
        return filled($class) && in_array($class, static::allowed(), true);
    }

    /**
     * The columns of a model's table — what an author picks a filter over.
     *
     * @return array<string, string>
     */
    public static function columns(?string $class): array
    {
        if (!static::isAllowed($class)) {
            return [];
        }

        try {
            /** @var Model $model */
            $model = new $class();

            $columns = Schema::connection($model->getConnectionName())->getColumnListing($model->getTable());
        } catch (\Throwable) {
            // A table that is not there yet (a migration not run) must not take the
            // form down — it takes the dropdown down, which is a much smaller thing.
            return [];
        }

        return collect($columns)
            ->mapWithKeys(fn (string $column): array => [$column => $column])
            ->all();
    }

    /**
     * The columns that could hold the key of whoever is being onboarded — the
     * foreign keys. Offered first, because "belongs to this user" is the whole
     * question an aggregate condition asks.
     *
     * @return array<string, string>
     */
    public static function foreignKeys(?string $class): array
    {
        return collect(static::columns($class))
            ->filter(fn (string $column): bool => str_ends_with($column, '_id'))
            ->all();
    }

    /**
     * The columns of whoever is being onboarded — for a condition that asks about
     * the subject itself rather than about their rows elsewhere.
     *
     * @return array<string, string>
     */
    public static function subjectColumns(): array
    {
        $model = config('filament-onboarding.conditions_builder.subject_model')
            ?? config('auth.providers.users.model');

        if (!is_string($model) || !class_exists($model)) {
            return [];
        }

        try {
            /** @var Model $instance */
            $instance = new $model();

            $columns = Schema::connection($instance->getConnectionName())->getColumnListing($instance->getTable());
        } catch (\Throwable) {
            return [];
        }

        return collect($columns)
            ->mapWithKeys(fn (string $column): array => [$column => $column])
            ->all();
    }

    /**
     * @return array<int, class-string<Model>>
     */
    private static function discover(): array
    {
        $path      = (string) config('filament-onboarding.conditions_builder.path', app_path('Models'));
        $namespace = (string) config('filament-onboarding.conditions_builder.namespace', 'App\\Models');

        $filesystem = app(Filesystem::class);

        if (blank($path) || !$filesystem->isDirectory($path)) {
            return [];
        }

        $models = [];

        foreach ($filesystem->allFiles($path) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $class = rtrim($namespace, '\\') . '\\' . str_replace(
                [DIRECTORY_SEPARATOR, '.php'],
                ['\\', ''],
                $file->getRelativePathname(),
            );

            if (!class_exists($class) || !is_subclass_of($class, Model::class)) {
                continue;
            }

            if ((new \ReflectionClass($class))->isAbstract()) {
                continue;
            }

            $models[] = $class;
        }

        return $models;
    }
}
