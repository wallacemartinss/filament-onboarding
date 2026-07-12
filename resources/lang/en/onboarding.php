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

    'page' => [
        'title'                => 'Getting started',
        'subheading'           => 'Where you are, and what is left.',
        'next'                 => 'Up next',
        'hidden'               => 'Hidden',
        'restore'              => 'Show again',
        'undo'                 => 'Undo',
        'replay_tour'          => 'Watch again',
        'replay_video'         => 'Watch again',
        'open_again'           => 'Open again',
        'restart'              => 'Start over',
        'restarted'            => 'Journey restarted',
        'restarted_reinstated' => ':count step(s) came straight back: they are done because the work behind them is done.',
        'restart_confirm'      => 'Start this journey over? What you ticked, skipped and watched will be cleared.',
        'restart_note'         => 'Steps that complete on their own come straight back: they answer to the app, not to this button.',

        'stats' => [
            'completed' => 'Done',
            'remaining' => 'Left',
            'skipped'   => 'Skipped',
        ],

        'status' => [
            'completed' => 'Done',
            'skipped'   => 'Skipped',
            'next'      => 'Up next',
            'pending'   => 'To do',
        ],

        'completed_at'       => 'Done :time',
        'tour_progress'      => 'Stop :reached of :total',
        'awaiting_condition' => 'Completes on its own',
        'empty_title'        => 'Nothing to onboard',
        'empty_description'  => 'There is no journey for you right now.',
    ],

    'media' => [
        'watch'   => 'Watch',
        'resume'  => 'Resume',
        'watched' => 'watched',
    ],

    'enums' => [
        'step_type' => [
            'task' => 'Task',
            'tour' => 'Tour',
        ],

        'media_type' => [
            'none'  => 'None',
            'image' => 'Image',
            'video' => 'Video',
        ],

        'media_source' => [
            'upload' => [
                'label'       => 'Upload',
                'description' => 'Stored on the configured disk (S3, R2, local).',
            ],
            'url' => [
                'label'       => 'Direct URL',
                'description' => 'A file hosted elsewhere.',
            ],
            'youtube' => [
                'label'       => 'YouTube',
                'description' => 'Watch time is tracked.',
            ],
            'vimeo' => [
                'label'       => 'Vimeo',
                'description' => 'Watch time is tracked.',
            ],
            'embed' => [
                'label'       => 'Other provider (iframe)',
                'description' => 'Plays, but watch time cannot be tracked.',
            ],
        ],

        'modal_position' => [
            'center'       => 'Centre',
            'top'          => 'Top',
            'bottom'       => 'Bottom',
            'top-left'     => 'Top left',
            'top-right'    => 'Top right',
            'bottom-left'  => 'Bottom left',
            'bottom-right' => 'Bottom right',
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
            'video' => [
                'label'       => 'Watching the video',
                'description' => 'Completed once enough of the step\'s video has been watched.',
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

        'placeholders' => [
            'flow_title'       => 'Getting started with …',
            'flow_description' => 'One line on what the user gets out of this.',
            'step_title'       => 'Connect your first server',
            'step_description' => 'One or two lines. Say why, not just what.',
        ],

        'no_steps_warning' => 'This journey has no steps, so nobody sees it.',

        'empty' => [
            'heading'     => 'No journeys yet',
            'description' => 'A journey is a checklist your users walk through. Create one and add its steps.',
        ],

        'sections' => [
            'content_description'     => 'What the user reads, in each language you support.',
            'publishing'              => 'Publishing',
            'appearance'              => 'Appearance',
            'rules'                   => 'Rules',
            'destination'             => 'Destination',
            'destination_description' => 'Where the button takes the user. Pick a page of the panel; a URL is the fallback.',
            'content'                 => 'Content',
            'settings'                => 'Settings',
            'behaviour'               => 'Behaviour',
            'media'                   => 'Media',
            'media_description'       => 'An image to show, or a video to watch. Opens in a modal over the panel.',
            'tour'                    => 'Tour',
            'tour_description'        => 'The elements this tour spotlights, in order.',
        ],

        'steps' => [
            'title'             => 'Steps',
            'empty_heading'     => 'No steps yet',
            'empty_description' => 'Add the first step. Without steps, this journey shows up for nobody.',
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
            'cta_url'               => 'Custom URL',
            'cta_url_helper'        => 'Only when the destination is not in the list. {tenant} is filled in.',
            'cta_route'             => 'Destination',
            'cta_route_helper'      => 'A page of the panel. Preferred over a URL: it survives a renamed slug.',
            'is_required'           => 'Required',
            'is_required_helper'    => 'Optional steps can be skipped.',

            'media_type'             => 'Media',
            'media_source'           => 'Source',
            'media_file'             => 'File',
            'media_url'              => 'URL',
            'media_url_helper'       => 'Paste the link as it comes: watch, share or short URLs all work.',
            'media_caption'          => 'Caption',
            'modal_position'         => 'Modal position',
            'modal_position_helper'  => 'A modal in a corner leaves the page usable behind it.',
            'modal_position_default' => 'Panel default',
            'video_threshold'        => 'Counts as watched at',
            'video_threshold_helper' => 'Percentage that completes the step, when it is completed by watching.',
            'is_active_helper'       => 'Inactive journeys disappear from every panel.',
            'sort_order_helper'      => 'Lower comes first.',
            'type_helper'            => 'A task is done; a tour is walked.',
            'completion_mode_helper' => 'What marks this step as done.',
        ],

        'tour' => [
            'add'             => 'Add stop',
            'selector'        => 'CSS selector',
            'selector_helper' => 'The element to spotlight, e.g. [data-onboarding="create-server"]. Leave empty when a widget is picked.',
            'widget'          => 'Widget',
            'widget_helper'   => 'A widget of the panel. Found in the page on its own — no selector needed.',
            'placement'       => 'Placement',
            'placements'      => [
                'auto'   => 'Automatic',
                'top'    => 'Above',
                'bottom' => 'Below',
            ],
            'route'        => 'Page',
            'route_helper' => 'Only when this stop lives elsewhere. The tour navigates there and carries on.',
            'url'          => 'Custom URL',
            'url_helper'   => 'Only when the page is not in the list.',
            'body'         => 'Text',
        ],

        'targets' => [
            'resources'  => 'Resources',
            'pages'      => 'Pages',
            'page_names' => [
                'index'  => 'list',
                'create' => 'create',
            ],
        ],
    ],

];
