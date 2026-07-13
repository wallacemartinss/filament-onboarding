<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Support;

use Filament\Facades\Filament;
use Filament\Forms\Components\{Builder, Field, Hidden, Repeater};
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * What a tour stop can point at, offered as a list rather than asked for as CSS.
 *
 * Authoring a stop used to mean typing `[data-onboarding="client-submit"]` — which
 * says two things about whoever was expected to do it: that they know CSS, and
 * that they know a developer went and put that hook in the code. A journey is
 * supposed to be something product writes. Product does not know either of those.
 *
 * Most of it need never be typed. Filament's own markup is more predictable than
 * it looks, and the package already knows the panel:
 *
 *   a field   — every field of every form wears `label[for="form.<name>"]`, and
 *               the package can read a resource's form and list the fields by
 *               their labels. Including a Select, which has no id of its own.
 *   a button  — the one that saves carries `wire:target`, and the one that goes
 *               somewhere else is a *link to a route*, which the package has.
 *   the table — one class, on every list page there is.
 *
 * So the author picks "Status" and the package writes the selector.
 *
 * What is stored is the **choice**, not the selector — `field:status`, not
 * `.fi-fo-field:has(...)`. Same reason the package stores a route name and not a
 * URL: the markup underneath is Filament's to change, and a journey should
 * survive it being changed.
 *
 * The CSS box is still there, and it is still the answer for anything the panel
 * cannot name — a particular card, a paragraph, a third-party widget's internals.
 * That is what `data-onboarding` is *for*. It is now the exception rather than
 * the price of entry.
 */
final class SpotlightTargets
{
    /**
     * Everything this stop could point at, grouped for a Select.
     *
     * @return array<string, array<string, string>>
     */
    public static function options(?string $routeName, ?string $panelId = null): array
    {
        $groups = [];

        $onThisPage = static::onThisPage($routeName, $panelId);

        if (filled($onThisPage)) {
            $groups[__('filament-onboarding::onboarding.resource.tour.targets.on_this_page')] = $onThisPage;
        }

        $widgets = collect(PanelTargets::widgetOptions($panelId))
            ->mapWithKeys(fn (string $label, string $class): array => ['widget:' . $class => $label])
            ->all();

        if (filled($widgets)) {
            $groups[__('filament-onboarding::onboarding.resource.tour.targets.widgets')] = $widgets;
        }

        $groups[__('filament-onboarding::onboarding.resource.tour.targets.advanced')] = [
            'custom' => __('filament-onboarding::onboarding.resource.tour.targets.custom'),
        ];

        return $groups;
    }

    /**
     * The CSS the browser is actually handed. Resolved at render time, so a
     * journey written today survives Filament rearranging its markup tomorrow.
     *
     * @param  array<string, mixed>  $parameters  Fills {tenant} in a link's URL.
     */
    public static function selector(string $target, array $parameters = []): ?string
    {
        [$kind, $value] = array_pad(explode(':', $target, 2), 2, null);

        return match ($kind) {
            // The whole field — its label and its input — and not merely the box
            // you type in. A spotlight around an input with its label left out in
            // the cold explains half of what it is pointing at.
            //
            // The `:has()` finds the wrapper; the id is the fallback for a field
            // whose label is hidden, and the runner climbs from there. A Select
            // has no id at all, which is why the label is what both hang off.
            'field' => filled($value)
                ? sprintf('.fi-fo-field:has(label[for="form.%s"]), [id="form.%s"]', $value, $value)
                : null,

            'action' => match ($value) {
                // Create pages say `create`, edit pages say `save`. One stop can
                // ride both, and only one of them is ever on the page.
                'submit' => '[wire\:target="create"], [wire\:target="save"]',
                default  => null,
            },

            'part' => match ($value) {
                'table'  => '.fi-ta',
                'search' => '.fi-ta input[type="search"]',
                default  => null,
            },

            // Filament names a column's header cell after the column:
            // `fi-ta-header-cell-` . str($name)->camel()->kebab(). Matched with
            // `~=` rather than as a class, so a column called `user.name` — whose
            // class then holds a dot — needs no escaping to be found.
            'column' => filled($value)
                ? sprintf('[class~="fi-ta-header-cell-%s"]', Str::kebab(Str::camel($value)))
                : null,

            // A button that goes somewhere is a link to a route — and the route is
            // something this package already keeps hold of.
            'link' => filled($value) ? static::linkSelector($value, $parameters) : null,

            'widget' => filled($value) ? PanelTargets::widgetSelector($value) : null,

            default => null,
        };
    }

    /**
     * What is on the page this stop lives on — its fields, its buttons, its table.
     *
     * @return array<string, string>
     */
    private static function onThisPage(?string $routeName, ?string $panelId): array
    {
        $page = static::resolvePage($routeName, $panelId);

        if ($page === null) {
            return [];
        }

        /** @var class-string<Resource> $resource */
        $resource = $page['resource'];

        return match ($page['page']) {
            'index'          => static::listPage($resource, $page['panel']),
            'create', 'edit' => static::formPage($resource),
            default          => [],
        };
    }

    /**
     * @param  class-string<Resource>  $resource
     * @return array<string, string>
     */
    private static function listPage(string $resource, Panel $panel): array
    {
        $options = [
            'part:table'  => __('filament-onboarding::onboarding.resource.tour.targets.table'),
            'part:search' => __('filament-onboarding::onboarding.resource.tour.targets.search'),
        ];

        // A column is worth pointing at — "this is where the status lives" — and
        // Filament names each one in its own class.
        foreach (static::columns($resource) as $target => $label) {
            $options[$target] = __('filament-onboarding::onboarding.resource.tour.targets.column', ['label' => $label]);
        }

        // The "New client" button is not a button the page owns — it is a link to
        // the create page, which is a route, which we have.
        if (!array_key_exists('create', $resource::getPages())) {
            return $options;
        }

        $routeName = static::safely(fn (): string => $resource::getRouteBaseName($panel) . '.create');

        if ($routeName === null) {
            return $options;
        }

        $label = static::safely(fn (): string => (string) $resource::getModelLabel())
            ?? class_basename($resource);

        $options['link:' . $routeName] = __('filament-onboarding::onboarding.resource.tour.targets.new_record', [
            'label' => $label,
        ]);

        return $options;
    }

    /**
     * The columns of a resource's table, by the headings they wear.
     *
     * A table is built against the Livewire component that renders it, so this
     * asks the resource's own list page for one. It is the most fragile thing in
     * here, and it fails the way everything else in here fails: the columns are
     * not offered, and nothing else is any the worse.
     *
     * @param  class-string<Resource>  $resource
     * @return array<string, string>
     */
    private static function columns(string $resource): array
    {
        $columns = static::safely(function () use ($resource): array {
            $page = $resource::getPages()['index'] ?? null;

            if ($page === null) {
                return [];
            }

            /** @var \Filament\Tables\Contracts\HasTable $livewire */
            $livewire = app($page->getPage());

            return $resource::table(Table::make($livewire))->getColumns();
        }) ?? [];

        $options = [];

        foreach ($columns as $column) {
            $name = static::safely(fn (): string => $column->getName());

            if (blank($name)) {
                continue;
            }

            $label = static::safely(fn (): string => (string) $column->getLabel());

            $options['column:' . $name] = filled($label) ? $label : $name;
        }

        return $options;
    }

    /**
     * The fields of a form, by the labels they wear on the screen.
     *
     * A form is built to be rendered, not to be read about, and some of them will
     * not survive being asked what is in them outside of a request — one that
     * leans on the record being edited, or on who is looking. That is not a reason
     * to take the panel down over it: the fields are simply not offered, and the
     * CSS box is right there.
     *
     * @param  class-string<Resource>  $resource
     * @return array<string, string>
     */
    private static function formPage(string $resource): array
    {
        $fields = static::safely(function () use ($resource): array {
            return static::fields($resource::form(Schema::make())->getComponents());
        }) ?? [];

        if (filled($fields)) {
            $fields['action:submit'] = __('filament-onboarding::onboarding.resource.tour.targets.submit');
        }

        return $fields;
    }

    /**
     * @param  array<int, mixed>  $components
     * @return array<string, string>
     */
    private static function fields(array $components): array
    {
        $fields = [];

        foreach ($components as $component) {
            // A hidden input has no box on the screen. There is nothing to put a
            // spotlight around, and a stop pointing at one would dim the page and
            // point at the middle of it.
            if ($component instanceof Hidden) {
                continue;
            }

            if ($component instanceof Field) {
                $name = static::safely(fn (): string => $component->getName());

                if (blank($name)) {
                    continue;
                }

                $label = static::safely(fn (): string => (string) $component->getLabel());

                $fields['field:' . $name] = filled($label) ? $label : $name;

                // A repeater *is* worth pointing at — "this is where you add them"
                // — but the fields inside it are not: their names carry a row
                // number that belongs to the subject's data, and there is no
                // "the third one" to point at before somebody has three.
                if ($component instanceof Repeater || $component instanceof Builder) {
                    continue;
                }
            }

            if (method_exists($component, 'getDefaultChildComponents')) {
                $children = static::safely(fn (): array => $component->getDefaultChildComponents()) ?? [];

                $fields = [...$fields, ...static::fields($children)];
            }
        }

        return $fields;
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private static function linkSelector(string $routeName, array $parameters): ?string
    {
        $path = static::safely(fn (): ?string => parse_url(route($routeName, $parameters), PHP_URL_PATH));

        // `$=` and not `=`: the href in the markup is absolute, and the tenant is
        // already in the path we just built.
        return filled($path) ? sprintf('a[href$="%s"]', $path) : null;
    }

    /**
     * Which resource and which of its pages a route name belongs to.
     *
     * @return array{resource: class-string<Resource>, page: string, panel: Panel}|null
     */
    private static function resolvePage(?string $routeName, ?string $panelId): ?array
    {
        if (blank($routeName)) {
            return null;
        }

        foreach (static::panels($panelId) as $panel) {
            foreach ($panel->getResources() as $resource) {
                if (!is_subclass_of($resource, Resource::class)) {
                    continue;
                }

                $baseName = static::safely(fn (): string => $resource::getRouteBaseName($panel));

                if ($baseName === null) {
                    continue;
                }

                foreach (array_keys($resource::getPages()) as $page) {
                    if ("{$baseName}.{$page}" === $routeName) {
                        return ['resource' => $resource, 'page' => $page, 'panel' => $panel];
                    }
                }
            }
        }

        return null;
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
