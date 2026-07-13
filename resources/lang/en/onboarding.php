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

    'welcome' => [
        'begin' => 'Get started',
        'later' => 'Not now',
        'never' => 'Do not show this again',
        'steps' => '{0}Nothing here yet|{1}1 step, at your own pace|[2,*]:count steps, at your own pace',
        'back'  => 'Turn onboarding back on',
        'off'   => 'You turned onboarding off. The checklist and the progress ring are gone — but this page stays.',
    ],
    'tour' => [
        'blocked'  => 'The screen has not moved on yet. If something is required, fill it in and press next again — the tour follows you there.',
        'start'    => 'View the tutorial',
        'choose'   => 'Which tutorial do you want?',
        'begin'    => 'Start',
        'waiting'  => 'Waiting for this to show up on the page — carry on with the form and the tour follows you.',
        'skip'     => 'Skip',
        'previous' => 'Back',
        'next'     => 'Next',
        'finish'   => 'Finish',
    ],

    'page' => [
        'collapse' => 'Fold this journey away',
        'expand'   => 'Open this journey',
        'meta'     => [
            'completed' => ':count of :total done',
            'remaining' => '{1}1 left|[2,*]:count left',
            'skipped'   => '{1}1 skipped|[2,*]:count skipped',
        ],
        'open_image'           => 'Open the image: :title',
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

        'condition_type' => [
            'aggregate' => [
                'label'       => 'Counts something they have',
                'description' => 'Clients, invoices, servers — anything of theirs. "At least one client."',
            ],
            'attribute' => [
                'label'       => 'Asks about them',
                'description' => 'A column on the user themselves. "Their email is verified."',
            ],
        ],

        'condition_operator' => [
            'equals'                => 'is',
            'not_equals'            => 'is not',
            'greater_than'          => 'is more than',
            'greater_than_or_equal' => 'is at least',
            'less_than'             => 'is less than',
            'less_than_or_equal'    => 'is at most',
            'contains'              => 'contains',
            'is_set'                => 'is filled in',
            'is_empty'              => 'is empty',
        ],
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
            'overview'                => 'Overview',
            'publishing_description'  => 'Who sees this journey, where, and in what order.',
            'appearance_description'  => 'How the journey is announced in the checklist.',
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
            'condition_none'        => 'No conditions yet — write one under Onboarding → Conditions (or run php artisan make:onboarding-condition), and it shows up here.',
            'visit_url'             => 'URL',
            'visit_url_helper'      => 'Supports the * wildcard: /app/*/servers/create',
            'cta_label'             => 'Button label',
            'cta_url'               => 'Custom URL',
            'cta_url_helper'        => 'Only when the destination is not in the list. {tenant} is filled in.',
            'cta_route'             => 'Destination',
            'cta_route_helper'      => 'A page of the panel. Preferred over a URL: it survives a renamed slug.',
            'is_required'           => 'Required',
            'is_required_helper'    => 'Optional steps can be skipped.',
            'visibility'            => 'Visible when',
            'visibility_helper'     => 'Only subjects this condition passes for see it. Use it to gate a step behind a plan or a feature.',
            'visibility_everyone'   => 'Everyone',

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
            'optional'        => 'Skip when it is not there',
            'optional_helper' => 'For something that may not exist yet — a tag on an empty table, a chart with no data. The tour moves on instead of waiting for it.',
            'advance'         => 'Advance with',
            'advance_helper'  => 'The control that carries the app to this stop — a wizard\'s next button, a tab. Clicked when the subject moves on and the element is not on screen yet.',
            'add'             => 'Add stop',
            'target'          => 'What to spotlight',
            'target_helper'   => 'Read from the panel itself — the fields of this page\'s form, its buttons, its table. Some forms cannot be read from the outside (one that leans on the record being edited, or on who is looking): its fields will not be listed, and a CSS selector is the way through.',
            'targets'         => [
                'on_this_page' => 'On this page',
                'widgets'      => 'Widgets',
                'advanced'     => 'Advanced',
                'custom'       => 'A CSS selector of my own…',
                'table'        => 'The table',
                'search'       => 'The search box',
                'submit'       => 'The save button',
                'column'       => 'Column: :label',
                'new_record'   => 'The "New :label" button',
            ],
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
            'route'             => 'Page',
            'route_helper'      => 'Only when this stop lives elsewhere. The tour navigates there and carries on.',
            'url'               => 'Custom URL',
            'url_helper'        => 'Only when the page is not in the list.',
            'body'              => 'Text',
            'visibility'        => 'Visible when',
            'visibility_helper' => 'Leave empty to show this stop to everyone. A stop pointing at a feature the plan lacks would spotlight nothing.',
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

    'conditions' => [
        'singular'   => 'Condition',
        'plural'     => 'Conditions',
        'subheading' => 'The questions your steps can ask about somebody — written here, not in code.',
        'at_least'   => 'at least :count',

        'sections' => [
            'question'             => 'The question',
            'question_description' => 'What has to be true of somebody for the step to be done.',
            'naming'               => 'What it is called',
            'naming_description'   => 'The name whoever writes a step picks from the dropdown.',
        ],

        'fields' => [
            'type'                     => 'What it asks',
            'model'                    => 'Counting what',
            'model_helper'             => 'The thing they have to have: clients, invoices, servers.',
            'minimum'                  => 'At least',
            'minimum_helper'           => 'How many. One, almost always.',
            'subject_column'           => 'That belongs to them through',
            'subject_column_helper'    => 'The column holding the id of whoever is being onboarded.',
            'scope_column'             => 'And to the tenant through',
            'scope_column_helper'      => 'Only when the model is scoped to a tenant. Leave empty otherwise.',
            'scope_column_none'        => 'Not scoped to a tenant',
            'filters'                  => 'Only the ones where…',
            'filters_helper'           => 'Optional. Without any, all of them count.',
            'filters_attribute'        => 'True when…',
            'filters_attribute_helper' => 'All of these have to hold.',
            'add_filter'               => 'Add a rule',
            'column'                   => 'Field',
            'operator'                 => 'Is',
            'value'                    => 'Value',
            'label'                    => 'Name',
            'label_helper'             => 'What it is called in the step editor.',
            'key_helper'               => 'How steps refer to it. Cannot be changed once steps use it.',
            'key_taken'                => 'The key [:key] belongs to a condition registered in code.',
            'is_active_helper'         => 'An inactive condition never passes.',
            'question'                 => 'Asks',
        ],

        'placeholders' => [
            'label' => 'Has added a client',
        ],

        'empty' => [
            'heading'     => 'No conditions yet',
            'description' => 'A condition is what lets a step complete itself — including for people who did the thing long ago. Write one here, or run php artisan make:onboarding-condition for a question a form cannot ask.',
        ],
    ],

];
