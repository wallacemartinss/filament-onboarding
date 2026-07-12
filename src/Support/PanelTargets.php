<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Support;

use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Widgets\{Widget, WidgetConfiguration};
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * What a panel actually contains: its resources, its pages, its widgets.
 *
 * Authoring a step used to mean typing "/app/{tenant}/servers/create" from
 * memory and finding out it was wrong when a user clicked it. These options feed
 * the panel's own dropdowns instead, so a destination can only be one that
 * exists, and it stays right when a slug is renamed — the route name is stored,
 * not the URL.
 */
final class PanelTargets
{
    /**
     * Every page a step can send the subject to, grouped for a Select.
     *
     * Pages that need a record (edit, view) are left out: onboarding has no
     * record to point them at.
     *
     * @return array<string, array<string, string>>
     */
    public static function pageOptions(?string $panelId = null): array
    {
        $groups = [];

        foreach (self::panels($panelId) as $panel) {
            $resourcePages = [];
            $customPages   = [];

            foreach ($panel->getResources() as $resource) {
                $resourcePages = [...$resourcePages, ...self::resourcePages($resource, $panel)];
            }

            foreach ($panel->getPages() as $page) {
                $routeName = self::safely(fn (): string => $page::getRouteName($panel));

                if ($routeName === null || !self::isReachable($routeName)) {
                    continue;
                }

                $customPages[$routeName] = self::safely(fn (): string => (string) $page::getNavigationLabel())
                    ?? Str::headline(class_basename($page));
            }

            $panelLabel = Str::headline($panel->getId());

            if (filled($resourcePages)) {
                $groups[$panelLabel . ' · ' . __('filament-onboarding::onboarding.resource.targets.resources')] = $resourcePages;
            }

            if (filled($customPages)) {
                $groups[$panelLabel . ' · ' . __('filament-onboarding::onboarding.resource.targets.pages')] = $customPages;
            }
        }

        return $groups;
    }

    /**
     * Widgets a tour can spotlight. A widget has no stable CSS selector — every
     * widget wears the same wrapper class — so it is addressed by the Livewire
     * component it is, and the tour runner finds it in the DOM.
     *
     * Both the widgets registered on the panel and the ones sitting in its
     * widget directories are offered: a widget attached to a page rather than to
     * the panel (Filament's `$isDiscovered = false`) is still a widget a tour
     * may want to point at.
     *
     * @return array<string, string>
     */
    public static function widgetOptions(?string $panelId = null): array
    {
        $options = [];

        foreach (self::panels($panelId) as $panel) {
            foreach ($panel->getWidgets() as $widget) {
                $class = $widget instanceof WidgetConfiguration ? $widget->widget : $widget;

                if (is_string($class)) {
                    $options[$class] = Str::headline(class_basename($class));
                }
            }

            foreach (self::widgetsInDirectories($panel) as $class) {
                $options[$class] = Str::headline(class_basename($class));
            }
        }

        asort($options);

        return $options;
    }

    /**
     * @return array<int, class-string<Widget>>
     */
    private static function widgetsInDirectories(Panel $panel): array
    {
        $directories = $panel->getWidgetDirectories();
        $namespaces  = $panel->getWidgetNamespaces();

        $filesystem = app(Filesystem::class);
        $widgets    = [];

        foreach ($directories as $index => $directory) {
            $namespace = $namespaces[$index] ?? null;

            if (blank($namespace) || !$filesystem->exists($directory)) {
                continue;
            }

            foreach ($filesystem->allFiles($directory) as $file) {
                $class = $namespace . '\\' . str_replace(
                    [DIRECTORY_SEPARATOR, '.php'],
                    ['\\', ''],
                    $file->getRelativePathname(),
                );

                if (!class_exists($class) || !is_subclass_of($class, Widget::class)) {
                    continue;
                }

                if ((new \ReflectionClass($class))->isAbstract()) {
                    continue;
                }

                $widgets[] = $class;
            }
        }

        return $widgets;
    }

    /**
     * The selector a Livewire component answers to, understood by the tour runner.
     *
     * What ends up in the DOM is the component's *name*, and a name is not always
     * the class: Filament widgets go by their class, but a component registered
     * under an alias (`edit_password_form`, say) goes by the alias. Asking
     * Livewire keeps the two in step — and makes any Livewire component
     * spotlightable, not just widgets.
     */
    public static function widgetSelector(string $componentClass): string
    {
        return '@widget:' . self::livewireName($componentClass);
    }

    public static function livewireName(string $componentClass): string
    {
        try {
            return app('livewire.factory')->resolveComponentName($componentClass);
        } catch (\Throwable) {
            return $componentClass;
        }
    }

    /**
     * @return array<string, string>
     */
    private static function resourcePages(string $resource, Panel $panel): array
    {
        if (!is_subclass_of($resource, Resource::class)) {
            return [];
        }

        $baseName = self::safely(fn (): string => $resource::getRouteBaseName($panel));

        if ($baseName === null) {
            return [];
        }

        $label = self::safely(fn (): string => (string) $resource::getPluralModelLabel())
            ?? Str::headline(class_basename($resource));

        $pages = [];

        foreach (array_keys($resource::getPages()) as $page) {
            $routeName = "{$baseName}.{$page}";

            if (!self::isReachable($routeName)) {
                continue;
            }

            $suffix = __("filament-onboarding::onboarding.resource.targets.page_names.{$page}");

            $pages[$routeName] = str_contains($suffix, 'onboarding.resource.targets')
                ? "{$label} — {$page}"
                : "{$label} — {$suffix}";
        }

        return $pages;
    }

    /**
     * A route onboarding can actually link to: it exists, and everything it
     * needs is something the panel already knows (a tenant), not a record.
     */
    private static function isReachable(string $routeName): bool
    {
        $route = Route::getRoutes()->getByName($routeName);

        if ($route === null) {
            return false;
        }

        $unknown = array_diff($route->parameterNames(), ['tenant']);

        return blank($unknown);
    }

    /**
     * @return array<int, Panel>
     */
    private static function panels(?string $panelId): array
    {
        $panels = Filament::getPanels();

        if ($panelId !== null) {
            $panel = $panels[$panelId] ?? null;

            return $panel instanceof Panel ? [$panel] : [];
        }

        return array_values($panels);
    }

    /**
     * Third-party resources and pages can throw while being labelled (a missing
     * translation, a tenant-dependent title). One of them must not take the
     * whole dropdown down with it.
     *
     * @template T
     *
     * @param  \Closure(): T  $callback
     * @return T|null
     */
    private static function safely(\Closure $callback): mixed
    {
        try {
            return $callback();
        } catch (\Throwable) {
            return null;
        }
    }
}
