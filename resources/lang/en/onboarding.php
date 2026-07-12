<?php

declare(strict_types = 1);

return [

    'checklist' => [
        'mark_done'             => 'Mark as done',
        'start_tour'            => 'Start tour',
        'go'                    => 'Go',
        'skip'                  => 'Skip',
        'close'                 => 'Close',
        'dismiss'               => 'Hide',
        'done'                  => 'Done',
        'completed_title'       => 'You are all set',
        'completed_description' => 'Every step is done. Welcome aboard.',
        'footer_note'           => 'You can pick this up later.',
    ],

    'tour' => [
        'skip'     => 'Skip',
        'previous' => 'Back',
        'next'     => 'Next',
        'finish'   => 'Finish',
    ],

    'enums' => [
        'step_type' => [
            'task' => 'Task',
            'tour' => 'Tour',
        ],

        'completion_mode' => [
            'manual' => [
                'label'       => 'Manual',
                'description' => 'The user ticks it off themselves.',
            ],
            'condition' => [
                'label'       => 'Condition',
                'description' => 'Completed automatically when a registered check passes.',
            ],
            'visit' => [
                'label'       => 'Page visit',
                'description' => 'Completed when the user reaches a URL.',
            ],
            'programmatic' => [
                'label'       => 'Programmatic',
                'description' => 'Only application code completes it.',
            ],
        ],
    ],

    'resource' => [
        'singular'   => 'Onboarding flow',
        'plural'     => 'Onboarding',
        'all_panels' => 'All panels',

        'sections' => [
            'content'          => 'Content',
            'settings'         => 'Settings',
            'behaviour'        => 'Behaviour',
            'tour'             => 'Tour',
            'tour_description' => 'The elements this tour spotlights, in order.',
        ],

        'steps' => [
            'title' => 'Steps',
        ],

        'fields' => [
            'title'                 => 'Title',
            'description'           => 'Description',
            'key'                   => 'Key',
            'key_helper'            => 'Used in code. Cannot be changed without breaking existing progress.',
            'step_key_helper'       => 'Unique within the flow. Used by Onboarding::for($user)->complete(...).',
            'panel'                 => 'Panel',
            'panel_helper'          => 'Leave empty to show the flow in every panel.',
            'icon'                  => 'Icon',
            'color'                 => 'Colour',
            'sort_order'            => 'Order',
            'is_active'             => 'Active',
            'is_dismissible'        => 'Dismissible',
            'is_dismissible_helper' => 'Lets the user hide the checklist for good.',
            'steps'                 => 'Steps',
            'updated_at'            => 'Updated at',
            'type'                  => 'Type',
            'completion_mode'       => 'Completed by',
            'condition'             => 'Condition',
            'condition_helper'      => 'Registered by the application. Steps already fulfilled come back completed.',
            'visit_url'             => 'URL',
            'visit_url_helper'      => 'Supports the * wildcard: /app/*/servers/create',
            'cta_label'             => 'Button label',
            'cta_url'               => 'Button URL',
            'cta_url_helper'        => '{tenant} and other panel parameters are filled in.',
            'cta_route'             => 'Route name',
            'cta_route_helper'      => 'Takes precedence over the URL.',
            'is_required'           => 'Required',
            'is_required_helper'    => 'Optional steps can be skipped.',
        ],

        'tour' => [
            'add'             => 'Add stop',
            'selector'        => 'CSS selector',
            'selector_helper' => 'The element to spotlight, e.g. [data-onboarding="create-server"].',
            'placement'       => 'Placement',
            'placements'      => [
                'auto'   => 'Automatic',
                'top'    => 'Above',
                'bottom' => 'Below',
            ],
            'url'        => 'Page',
            'url_helper' => 'Only when this stop lives on another page. The tour navigates there.',
            'body'       => 'Text',
        ],
    ],

];
