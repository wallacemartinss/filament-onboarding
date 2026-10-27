<?php

declare(strict_types = 1);

namespace Wallacemartinss\FilamentOnboarding\Support;

use Filament\Facades\Filament;
use Filament\Forms\Components\{Builder, Field, Hidden, Repeater};
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Resources\Pages\ManageRecords;
use Filament\Resources\Resource;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Wallacemartinss\FilamentOnboarding\FilamentOnboardingPlugin;

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
     * The schema a page's form is cached under, and so the first part of the
     * id of every field in it.
     */
    private const FORM = 'form';

    /**
     * Where the form of the first action a page mounts is rendered: the modal
     * of a "New" button on a list page that creates without leaving it.
     */
    private const FIRST_ACTION_MODAL = 'mountedActionSchema0';

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
            'field' => filled($value) ? static::fieldSelector(...static::fieldAndSchema($value)) : null,

            'action' => match ($value) {
                // Create pages say `create`, edit pages say `save`. One stop can
                // ride both, and only one of them is ever on the page.
                'submit' => '[wire\:target="create"], [wire\:target="save"]',
                // The "New" button of a list page that creates in a modal
                // (ManageRecords): an action, not a link to a create page.
                'create' => '[wire\:click="mountAction(\'create\')"]',
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
     * A field, whole — its label and its input, and not merely the box you type
     * in. A spotlight around an input with its label left out in the cold
     * explains half of what it is pointing at.
     *
     * What a stop stores is the field's *name*; what the markup wears is its
     * *state path*. Those are the same on a flat form — `title` renders
     * `form.title` — and not on one that nests. Any `Group`, `Section`, `Tab`
     * or wizard step given a state path of its own prefixes everything under
     * it, so a field still called `title` comes out as `form.details.title`.
     * Matching the name as the whole path finds nothing on such a form, and
     * the stop stalls on a field that is right there on the screen.
     *
     * So both are matched: the exact path first, then any path ending in the
     * name. The dot is part of what is matched, so a longer name cannot be
     * caught by a shorter one — `form.details.subtitle` does not end in
     * `.title`.
     *
     * A form that repeats the same field under several paths matches under
     * each, which is correct rather than ambiguous: the runner takes the first
     * match the subject can actually see, and a pane that is not open is
     * `display: none`. The stop follows the subject to whichever one they
     * open.
     *
     * The `:has()` finds the wrapper; the id is the fallback for a field whose
     * label is hidden, and the runner climbs from there. A Select has no id at
     * all, which is why the label is what both hang off.
     */
    private static function fieldSelector(string $name, string $schema = self::FORM): string
    {
        return sprintf(
            '.fi-fo-field:has(label[for="%2$s.%1$s"]), '
            . '.fi-fo-field:has(label[for^="%2$s."][for$=".%1$s"]), '
            . '[id="%2$s.%1$s"], '
            . '[id^="%2$s."][id$=".%1$s"]',
            $name,
            $schema,
        );
    }

    /**
     * A field's name and the schema it lives in. A stored `field:status` is
     * in the page's `form`, as it has always been; a field of any other schema
     * says which — `field:client_id@applicationForm` on a page with several
     * forms, `field:name@mountedActionSchema0` in the modal of an action —
     * because the schema's name is the first part of every id under it.
     *
     * @return array{0: string, 1: string}
     */
    private static function fieldAndSchema(string $value): array
    {
        $name   = Str::beforeLast($value, '@');
        $schema = Str::contains($value, '@') ? Str::afterLast($value, '@') : self::FORM;

        return [$name, filled($schema) ? $schema : self::FORM];
    }

    /**
     * What is on the page this stop lives on — its fields, its buttons, its table.
     *
     * @return array<string, string>
     */
    private static function onThisPage(?string $routeName, ?string $panelId): array
    {
        $page = static::resolvePage($routeName, $panelId);

        if ($page !== null) {
            /** @var class-string<Resource> $resource */
            $resource = $page['resource'];

            return static::inPanel($page['panel'], fn (): array => match ($page['page']) {
                'index'          => static::listPage($resource, $page['panel']),
                'create', 'edit' => static::formPage($resource, $page['page']),
                default          => [],
            });
        }

        $customPage = static::resolveCustomPage($routeName, $panelId);

        if ($customPage !== null) {
            return static::inPanel($customPage['panel'], fn (): array => static::customPage($customPage['page']));
        }

        return [];
    }

    /**
     * Reads a page the way its own panel would build it. Journeys are usually
     * written in another panel (an admin one), and a form that asks which panel
     * it is on — for a URL, a tenant-aware option, a guard — would otherwise be
     * answered with the wrong one, and lose its fields.
     *
     * @template T
     *
     * @param  \Closure(): T  $callback
     * @return T
     */
    private static function inPanel(Panel $panel, \Closure $callback): mixed
    {
        $current = static::safely(fn (): ?Panel => Filament::getCurrentPanel());
        $tenant  = Filament::getTenant();

        Filament::setCurrentPanel($panel);

        // A tenant panel read from outside it has no tenant, and a form that
        // reads the tenant's data while it is built cannot be built without one.
        // The panel may name one to be read as (->targetsTenant()).
        if ($tenant === null && $panel->hasTenancy()) {
            $sample = static::safely(function () use ($panel): ?Model {
                $plugin = $panel->getPlugin(FilamentOnboardingPlugin::make()->getId());

                return $plugin instanceof FilamentOnboardingPlugin ? $plugin->resolveTargetsTenant() : null;
            });

            if ($sample !== null) {
                Filament::setTenant($sample, isQuiet: true);
            }
        }

        try {
            return $callback();
        } finally {
            Filament::setCurrentPanel($current);
            Filament::setTenant($tenant, isQuiet: true);
        }
    }

    /**
     * The fields of a custom page — a settings screen, a profile — by the
     * labels they wear. A page may hold several forms, each a method that takes
     * a Schema and gives one back (`form`, `applicationForm`…); each is built
     * the way the page itself builds it, and its fields are named with the
     * schema they live in.
     *
     * Only the methods the application wrote are asked: Filament's own pages
     * declare schemas of their own (the header, the content that embeds the
     * forms), and those carry no field that the forms do not.
     *
     * @param  class-string<Page>  $page
     * @return array<string, string>
     */
    private static function customPage(string $page): array
    {
        $livewire = static::safely(fn (): mixed => app($page));

        if (!$livewire instanceof HasSchemas) {
            return [];
        }

        $options = [];

        foreach (static::schemaMethods($page) as $schema) {
            $fields = static::safely(fn (): array => static::fields(
                $livewire->getSchema($schema)?->getComponents() ?? [],
            )) ?? [];

            foreach ($fields as $target => $label) {
                $options[$schema === self::FORM ? $target : "{$target}@{$schema}"] = $label;
            }
        }

        if (filled($options)) {
            $options['action:submit'] = __('filament-onboarding::onboarding.resource.tour.targets.submit');
        }

        return $options;
    }

    /**
     * The schemas a page's own class defines: public methods that take a Schema
     * and give one back, written outside Filament.
     *
     * @param  class-string<Page>  $page
     * @return list<string>
     */
    private static function schemaMethods(string $page): array
    {
        $names = [];

        foreach ((new \ReflectionClass($page))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isStatic() || $method->getNumberOfParameters() !== 1 || $method->getName() === 'content') {
                continue;
            }

            if (str_starts_with($method->getDeclaringClass()->getName(), 'Filament\\')) {
                continue;
            }

            $parameter = $method->getParameters()[0]->getType();
            $return    = $method->getReturnType();

            if (!$parameter instanceof \ReflectionNamedType || !$return instanceof \ReflectionNamedType) {
                continue;
            }

            if ($parameter->getName() === Schema::class && $return->getName() === Schema::class) {
                $names[] = $method->getName();
            }
        }

        return $names;
    }

    /**
     * A custom page of a panel, by its route name.
     *
     * @return array{page: class-string<Page>, panel: Panel}|null
     */
    private static function resolveCustomPage(?string $routeName, ?string $panelId): ?array
    {
        if (blank($routeName)) {
            return null;
        }

        foreach (static::panels($panelId) as $panel) {
            foreach ($panel->getPages() as $page) {
                if (!is_subclass_of($page, Page::class)) {
                    continue;
                }

                if (static::safely(fn (): string => $page::getRouteName($panel)) === $routeName) {
                    return ['page' => $page, 'panel' => $panel];
                }
            }
        }

        return null;
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

        // A list page that creates in a modal (ManageRecords) has no create page
        // to link to: its "New" button is an action, and the form it opens is
        // the resource's own form, rendered in that action's modal.
        if (!array_key_exists('create', $resource::getPages())) {
            return [...$options, ...static::createModal($resource)];
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
     * The "New" button of a list page that creates in a modal, and the fields
     * of that modal — the resource's form, under the modal's schema.
     *
     * @param  class-string<Resource>  $resource
     * @return array<string, string>
     */
    private static function createModal(string $resource): array
    {
        $index = static::safely(fn (): ?string => ($resource::getPages()['index'] ?? null)?->getPage());

        if ($index === null || !is_subclass_of($index, ManageRecords::class)) {
            return [];
        }

        $label = static::safely(fn (): string => (string) $resource::getModelLabel()) ?? class_basename($resource);

        $options = [
            'action:create' => __('filament-onboarding::onboarding.resource.tour.targets.new_record', ['label' => $label]),
        ];

        $fields = static::safely(function () use ($resource, $index): array {
            return static::fields($resource::form(Schema::make(app($index)))->getComponents());
        }) ?? [];

        foreach ($fields as $target => $fieldLabel) {
            $options[$target . '@' . self::FIRST_ACTION_MODAL] = $fieldLabel;
        }

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
    private static function formPage(string $resource, string $page): array
    {
        $fields = static::safely(function () use ($resource, $page): array {
            return static::fields($resource::form(static::schemaFor($resource, $page))->getComponents());
        }) ?? [];

        if (filled($fields)) {
            $fields['action:submit'] = __('filament-onboarding::onboarding.resource.tour.targets.submit');
        }

        return $fields;
    }

    /**
     * A form built against the component that will render it.
     *
     * A schema with nothing behind it reads most forms and not all of them:
     * `Schema::getLivewire()` has a return type, so a form — or one field
     * inside one — that reaches for the component gets a TypeError rather than
     * a null. That costs the whole form its fields, and it costs them silently,
     * on exactly the resources that are worth touring: the ones whose form is
     * built out of enough parts that one of them asks who is rendering it.
     *
     * The resource's own page is the component Filament itself would use, and
     * it is already what the table side asks for its columns. If it cannot be
     * had, the bare schema is still tried — some forms read fine without one.
     *
     * @param  class-string<Resource>  $resource
     */
    private static function schemaFor(string $resource, string $page): Schema
    {
        $livewire = static::safely(function () use ($resource, $page): ?HasSchemas {
            $registration = $resource::getPages()[$page] ?? null;

            if ($registration === null) {
                return null;
            }

            $component = app($registration->getPage());

            return $component instanceof HasSchemas ? $component : null;
        });

        return Schema::make($livewire);
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
