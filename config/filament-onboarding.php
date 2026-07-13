<?php

declare(strict_types = 1);

use Wallacemartinss\FilamentOnboarding\Models\{OnboardingCondition, OnboardingFlow, OnboardingFlowProgress, OnboardingPreference, OnboardingStep, OnboardingStepProgress};

return [

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    | Locales offered when writing flow and step content in the panel. Each
    | translatable column stores one value per locale and is resolved at
    | render time against the current application locale.
    |
    */

    'locales' => ['en'],

    /*
    | Locale used when the current one has no content. Defaults to the
    | application fallback locale.
    */

    'fallback_locale' => null,

    /*
    |--------------------------------------------------------------------------
    | Conditions
    |--------------------------------------------------------------------------
    |
    | A condition is the question a step asks about somebody — "have they added
    | a client yet?" — and there is nothing to put here for most of them.
    |
    | Three ways to have one, cheapest first:
    |
    |   1. Write it in the panel. Onboarding → Conditions → New. Covers "has at
    |      least N of X, matching some filters" and "this column of the user is
    |      set to that", which is most of them. No code, and no deploy.
    |
    |   2. Write a class. `php artisan make:onboarding-condition HasActivePlan`
    |      puts it in app/Onboarding/Conditions, where it is found on its own —
    |      for the questions a form cannot ask (an API call, a subscription).
    |
    |   3. List it here, or register it at runtime — for a condition that lives
    |      somewhere else, or comes from a package of your own:
    |
    |          'has_server' => \App\Onboarding\Conditions\HasServerCondition::class,
    |
    |          Onboarding::condition('has_server', fn (Model $subject) => ...);
    |
    | Code always wins a name clash: a condition written in the panel cannot take
    | a key that a class already answers to.
    |
    */

    'conditions' => [],

    /*
    | Where condition classes are found. Turn discovery off and register them by
    | hand above instead.
    */

    'discovery' => [
        'enabled'   => true,
        'path'      => null, // defaults to app_path('Onboarding/Conditions')
        'namespace' => 'App\Onboarding\Conditions',
    ],

    /*
    |--------------------------------------------------------------------------
    | The condition builder
    |--------------------------------------------------------------------------
    |
    | What an author may build a condition over, in the panel.
    |
    | `models` is an allowlist. Left empty, the application's own models
    | (app/Models) are offered — which is what an author expects, and what the
    | panel exposes anyway. Name them explicitly to narrow it:
    |
    |   'models' => [\App\Models\Client::class, \App\Models\Invoice::class],
    |
    | Nothing an author types reaches the database as SQL: the model comes from
    | this list, the column from that table's real columns, the operator from an
    | enum, and the value is bound.
    |
    */

    'conditions_builder' => [
        'models'    => [],
        'path'      => null, // defaults to app_path('Models')
        'namespace' => 'App\Models',

        // Whose columns an "attribute" condition asks about. Defaults to the
        // application's user model.
        'subject_model' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Flow definitions are read on every panel request, so they are cached and
    | flushed whenever a flow or step is written. Progress is never cached.
    |
    */

    'cache' => [
        'enabled' => true,
        'store'   => null,
        'ttl'     => 3600,
        'prefix'  => 'filament-onboarding',
    ],

    /*
    |--------------------------------------------------------------------------
    | Media
    |--------------------------------------------------------------------------
    |
    | Images and videos uploaded while authoring a step land on this disk — any
    | Laravel disk will do, including S3 and R2 (S3-compatible). A private disk
    | is signed at render time, so the file is never public.
    |
    */

    'media' => [
        'disk'       => 'public',
        'directory'  => 'onboarding',
        'visibility' => 'public', // 'private' signs a temporary URL instead
        'url_ttl'    => 30,       // minutes, for private disks

        /*
         * SVG is deliberately absent. An SVG is a document, and it can carry
         * script: harmless inside an <img>, but the file also sits at a URL on
         * your own origin, and opening it directly runs whatever is in it. Add it
         * back only if you are sure everyone who can author a step is someone you
         * would hand that to.
         */
        'accept' => [
            'image' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
            'video' => ['video/mp4', 'video/webm', 'video/ogg'],
        ],

        /*
         * In kilobytes. PHP has the last word: a 100 MB video will not arrive
         * unless upload_max_filesize and post_max_size say so too — the default
         * PHP install stops at 2 MB, and the upload simply fails.
         */
        'max_size' => [
            'image' => 5120,
            'video' => 102400,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Modal
    |--------------------------------------------------------------------------
    |
    | Where the media modal opens: center, top, bottom, top-left, top-right,
    | bottom-left, bottom-right. A step may override it; a docked modal leaves
    | the page usable behind it, so the subject can follow along.
    |
    */

    'modal' => [
        'position' => 'center',
    ],

    /*
    |--------------------------------------------------------------------------
    | Styles
    |--------------------------------------------------------------------------
    |
    | The checklist and tours ship with a self-contained stylesheet that borrows
    | the panel's colour variables. To make it your own:
    |
    |   php artisan vendor:publish --tag=filament-onboarding-styles
    |
    | then point `path` at the published copy and re-run `php artisan
    | filament:assets`. Set `enabled` to false to ship no CSS at all and style
    | the .fio-* classes from your own panel theme instead.
    |
    */

    'styles' => [
        'enabled' => true,
        'path'    => null, // resource_path('css/vendor/filament-onboarding/onboarding.css')
    ],

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    */

    'tables' => [
        'flows'         => 'onboarding_flows',
        'steps'         => 'onboarding_steps',
        'flow_progress' => 'onboarding_flow_progress',
        'step_progress' => 'onboarding_step_progress',
        'preferences'   => 'onboarding_preferences',
        'conditions'    => 'onboarding_conditions',
    ],

    'models' => [
        'flow'          => OnboardingFlow::class,
        'step'          => OnboardingStep::class,
        'flow_progress' => OnboardingFlowProgress::class,
        'step_progress' => OnboardingStepProgress::class,
        'preferences'   => OnboardingPreference::class,
        'condition'     => OnboardingCondition::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource
    |--------------------------------------------------------------------------
    |
    | Navigation of the flow management resource, registered on the panels that
    | call ->manageFlows() on the plugin.
    |
    */

    'resource' => [
        'navigation_group' => null,
        'navigation_sort'  => null,
        'navigation_icon'  => 'heroicon-o-map',
        'cluster'          => null,
    ],

];
