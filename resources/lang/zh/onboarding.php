<?php

declare(strict_types = 1);

return [

    'checklist' => [
        'mark_done'             => '标记为已完成',
        'start_tour'            => '开始导览',
        'go'                    => '前往',
        'skip'                  => '跳过',
        'close'                 => '关闭',
        'dismiss'               => '不再显示',
        'done'                  => '完成',
        'restart'               => '重新开始',
        'restart_confirm'       => '这将重置您已完成的所有步骤和导览 — 要重新开始吗？',
        'completed_title'       => '全部就绪',
        'completed_description' => '所有步骤都已完成，欢迎使用。',
        'footer_note'           => '稍后还能从这里回到它。',
    ],

    'welcome' => [
        'begin' => '开始',
        'later' => '以后再说',
        'never' => '不要再显示',
        'steps' => '{0}这里还什么都没有|{1}共 1 个步骤，按自己的节奏来|[2,*]共 :count 个步骤，按自己的节奏来',
        'back'  => '重新启用引导',
        'off'   => '你已关闭引导。清单和进度环消失了——但这个页面还在。',
    ],
    'tour' => [
        'blocked'  => '界面还没往前推进。如果需要先做点什么，完成后再点一次「下一步」——导览会跟上你。',
        'start'    => '观看教程',
        'choose'   => '你想看哪个教程？',
        'begin'    => '开始',
        'waiting'  => '等待它在页面中出现——继续填写表单，导览会跟上你。',
        'skip'     => '跳过',
        'previous' => '上一步',
        'next'     => '下一步',
        'finish'   => '完成',
    ],

    'page' => [
        'collapse' => '收起此流程',
        'expand'   => '展开此流程',
        'meta'     => [
            'completed' => '已完成 :count / :total',
            'remaining' => '{1}还剩 1 个|[2,*]还剩 :count 个',
            'skipped'   => '{1}跳过 1 个|[2,*]跳过 :count 个',
        ],
        'open_image'           => '打开图片：:title',
        'title'                => '开始',
        'subheading'           => '你现在的位置，以及还剩什么。',
        'next'                 => '下一项',
        'hidden'               => '已隐藏',
        'restore'              => '重新显示',
        'undo'                 => '撤销',
        'replay_tour'          => '再看一次导览',
        'replay_video'         => '再看一次',
        'open_again'           => '再次打开',
        'restart'              => '重新开始',
        'restarted'            => '流程已重启',
        'restarted_reinstated' => '有 :count 个步骤立即恢复：它们已完成，因为背后的工作已经做完了。',
        'restart_confirm'      => '要重新开始这个流程吗？你标记过、跳过和看过的内容都会被清除。',
        'restart_note'         => '自动完成的步骤会立即恢复：它们响应的是应用，而不是这个按钮。',

        'stats' => [
            'completed' => '已完成',
            'remaining' => '剩余',
            'skipped'   => '已跳过',
        ],

        'status' => [
            'completed' => '已完成',
            'skipped'   => '已跳过',
            'next'      => '下一项',
            'pending'   => '待处理',
        ],

        'completed_at'       => '完成于 :time',
        'tour_progress'      => '第 :reached 站，共 :total 站',
        'awaiting_condition' => '自动完成',
        'empty_title'        => '暂无内容可接入',
        'empty_description'  => '目前没有适合你的流程。',
    ],

    'media' => [
        'watch'   => '观看',
        'resume'  => '继续观看',
        'watched' => '已看完',
    ],

    'enums' => [

        'condition_type' => [
            'aggregate' => [
                'label'       => '统计他们的数据',
                'description' => '客户、发票、服务器——任何属于他们的东西。「至少有一个客户。」',
            ],
            'attribute' => [
                'label'       => '查询他们的字段',
                'description' => '用户自身的某个列。「他的邮箱已验证。」',
            ],
        ],

        'condition_operator' => [
            'equals'                => '等于',
            'not_equals'            => '不等于',
            'greater_than'          => '大于',
            'greater_than_or_equal' => '大于等于',
            'less_than'             => '小于',
            'less_than_or_equal'    => '小于等于',
            'contains'              => '包含',
            'is_set'                => '已填写',
            'is_empty'              => '为空',
        ],
        'step_type' => [
            'task' => '任务',
            'tour' => '导览',
        ],

        'media_type' => [
            'none'  => '无',
            'image' => '图片',
            'video' => '视频',
        ],

        'media_source' => [
            'upload' => [
                'label'       => '上传',
                'description' => '保存在你配置的磁盘（S3、R2 或本地）。',
            ],
            'url' => [
                'label'       => '直链',
                'description' => '存放在别处的文件。',
            ],
            'youtube' => [
                'label'       => 'YouTube',
                'description' => '观看时长可统计。',
            ],
            'vimeo' => [
                'label'       => 'Vimeo',
                'description' => '观看时长可统计。',
            ],
            'embed' => [
                'label'       => '其他来源（iframe）',
                'description' => '可以播放，但无法统计观看时长。',
            ],
        ],

        'modal_position' => [
            'center'       => '居中',
            'top'          => '顶部',
            'bottom'       => '底部',
            'top-left'     => '左上',
            'top-right'    => '右上',
            'bottom-left'  => '左下',
            'bottom-right' => '右下',
        ],

        'completion_mode' => [
            'manual' => [
                'label'       => '手动',
                'description' => '由用户自己勾选。',
            ],
            'condition' => [
                'label'       => '条件',
                'description' => '登记的检查通过后自动完成。',
            ],
            'visit' => [
                'label'       => '访问页面',
                'description' => '用户到达指定地址时完成。',
            ],
            'video' => [
                'label'       => '视频观看',
                'description' => '视频观看达到阈值后完成。',
            ],
            'programmatic' => [
                'label'       => '程序触发',
                'description' => '只有应用代码才能完成它。',
            ],
        ],
    ],

    'resource' => [
        'singular'   => '引导流程',
        'plural'     => '引导流程',
        'all_panels' => '所有面板',

        'placeholders' => [
            'flow_title'       => '从 … 开始',
            'flow_description' => '用一句话说明用户会得到什么。',
            'step_title'       => '连接你的第一台服务器',
            'step_description' => '一两句话。说明为什么，而不只是做什么。',
        ],

        'no_steps_warning' => '这个流程没有步骤，因此没人会看到它。',

        'empty' => [
            'heading'     => '还没有流程',
            'description' => '流程是用户要走完的一份清单。创建一个，然后为它添加步骤。',
        ],

        'sections' => [
            'overview'                => '概览',
            'publishing_description'  => '谁会看到这个流程、显示在哪里、以什么顺序。',
            'appearance_description'  => '流程在列表中的呈现方式。',
            'content_description'     => '用户会读到的内容，支持你使用的每一种语言。',
            'publishing'              => '发布',
            'appearance'              => '外观',
            'rules'                   => '规则',
            'destination'             => '目标',
            'destination_description' => '按钮跳到哪里。选择面板中的页面；地址是备选方案。',
            'content'                 => '内容',
            'settings'                => '设置',
            'behaviour'               => '行为',
            'media'                   => '媒体',
            'media_description'       => '用于展示的图片，或用于观看的视频。会在面板上方的新窗口中打开。',
            'tour'                    => '导览',
            'tour_description'        => '这个导览要高亮的元素，按顺序排列。',
        ],

        'steps' => [
            'title'             => '步骤',
            'empty_heading'     => '还没有步骤',
            'empty_description' => '先添加第一个步骤。没有步骤，这个流程就不会显示给任何人。',
        ],

        'fields' => [
            'title'                 => '标题',
            'description'           => '描述',
            'key'                   => '标识',
            'key_helper'            => '在代码中使用。修改会破坏已有的进度记录。',
            'step_key_helper'       => '在流程内唯一。可通过 Onboarding::for($user)->complete(...) 使用。',
            'panel'                 => '面板',
            'panel_helper'          => '留空则在整个流程的所有面板中显示。',
            'icon'                  => '图标',
            'color'                 => '颜色',
            'sort_order'            => '顺序',
            'is_active'             => '启用',
            'is_dismissible'        => '可关闭',
            'is_dismissible_helper' => '允许用户永久隐藏这个清单。',
            'steps'                 => '步骤',
            'updated_at'            => '更新时间',
            'type'                  => '类型',
            'completion_mode'       => '完成方式',
            'condition'             => '条件',
            'condition_helper'      => '由应用登记。已经满足的步骤会直接恢复为已完成。',
            'condition_none'        => '还没有条件——在「引导 → 条件」下写一个（或运行 php artisan make:onboarding-condition），它就会出现在这里。',
            'visit_url'             => '地址',
            'visit_url_helper'      => '支持通配符 *：/app/*/servers/create',
            'cta_label'             => '按钮文字',
            'cta_url'               => '自定义地址',
            'cta_url_helper'        => '仅当目标不在列表中时使用。{tenant} 会自动填充。',
            'cta_route'             => '目标',
            'cta_route_helper'      => '面板中的页面。优先于地址：slug 变化后依然有效。',
            'is_required'           => '必填',
            'is_required_helper'    => '可选步骤可以跳过。',
            'visibility'            => '显示条件',
            'visibility_helper'     => '只有满足该条件的人才会看到它。可以用它把步骤藏在某项套餐或能力之后。',
            'visibility_everyone'   => '所有人',

            'media_type'             => '媒体',
            'media_source'           => '来源',
            'media_file'             => '文件',
            'media_url'              => '地址',
            'media_url_helper'       => '直接粘贴链接：watch、share 或短链接都可以。',
            'media_caption'          => '说明文字',
            'modal_position'         => '弹窗位置',
            'modal_position_helper'  => '角落弹窗会把后面的页面露出来。',
            'modal_position_default' => '面板默认值',
            'video_threshold'        => '视为已观看',
            'video_threshold_helper' => '观看到什么百分比就算完成。',
            'is_active_helper'       => '未启用的流程会从所有面板中消失。',
            'sort_order_helper'      => '数字小的排在前面。',
            'type_helper'            => '任务是需要去完成的事；导览是带着走一遍。',
            'completion_mode_helper' => '是什么把这个步骤标记为已完成。',
        ],

        'tour' => [
            'optional'        => '不存在则跳过',
            'optional_helper' => '用于可能还不存在的东西——空表格标签、没有数据的图表。导览会直接继续，而不是干等。',
            'advance'         => '继续方式',
            'advance_helper'  => '把应用带到这一站的控件——向导里的「下一步」按钮、某个标签页。当用户向前走而该元素还不在屏幕上时才点击。',
            'add'             => '添加站点',
            'target'          => '要高亮什么',
            'target_helper'   => '直接取自面板本身——该页面的表单字段、它的按钮、它的表格。有些表单无法从外部读取（依赖当前记录或当前用户的字段）：它们的字段不会出现在列表里，这时用 CSS 选择器。',
            'targets'         => [
                'on_this_page' => '本页内容',
                'widgets'      => '组件',
                'advanced'     => '高级',
                'custom'       => '自定义 CSS 选择器…',
                'table'        => '表格',
                'search'       => '搜索框',
                'submit'       => '保存按钮',
                'column'       => '列：:label',
                'new_record'   => '「新建 :label」按钮',
            ],
            'selector'        => 'CSS 选择器',
            'selector_helper' => '要高亮的元素，例如 [data-onboarding="create-server"]。选择组件时留空。',
            'widget'          => '组件',
            'widget_helper'   => '面板中的组件。就在页面上，不需要选择器。',
            'placement'       => '位置',
            'placements'      => [
                'auto'   => '自动',
                'top'    => '上方',
                'bottom' => '下方',
            ],
            'route'             => '页面',
            'route_helper'      => '仅当这一站位于别处时使用。导览会跳到那里并继续。',
            'url'               => '自定义地址',
            'url_helper'        => '仅当该页面不在列表中时使用。',
            'body'              => '文字',
            'visibility'        => '显示条件',
            'visibility_helper' => '留空即对所有人显示。指向未开通能力的站点不会高亮任何东西。',
        ],

        'targets' => [
            'resources'      => '资源',
            'pages'          => '页面',
            'tenant_profile' => '租户资料',
            'page_names'     => [
                'index'  => '列表',
                'create' => '新建',
            ],
        ],
    ],

    'conditions' => [
        'singular'   => '条件',
        'plural'     => '条件',
        'subheading' => '你的步骤可以向某人提出的问题——写在这里，而不是代码里。',
        'at_least'   => '至少 :count',

        'sections' => [
            'question'             => '问题',
            'question_description' => '某个人必须满足什么，这个步骤才算完成。',
            'naming'               => '命名',
            'naming_description'   => '创建步骤时从下拉列表中选择的名称。',
        ],

        'fields' => [
            'type'                     => '询问什么',
            'model'                    => '统计什么',
            'model_helper'             => '他们必须拥有的东西：客户、发票、服务器。',
            'minimum'                  => '至少',
            'minimum_helper'           => '数量。几乎总是 1。',
            'subject_column'           => '通过什么关联他们',
            'subject_column_helper'    => '保存正在接入流程的用户标识的列。',
            'scope_column'             => '并按什么区分',
            'scope_column_helper'      => '仅当模型限定到租户时使用。否则留空。',
            'scope_column_none'        => '不限定租户',
            'filters'                  => '仅统计…',
            'filters_helper'           => '可选。不填任何条件时统计全部。',
            'filters_attribute'        => '满足…时',
            'filters_attribute_helper' => '所有条件都需成立。',
            'add_filter'               => '添加规则',
            'column'                   => '字段',
            'operator'                 => '是',
            'value'                    => '值',
            'label'                    => '名称',
            'label_helper'             => '在步骤编辑器中显示的名称。',
            'key_helper'               => '步骤引用它的方式。步骤已在使用后不能再修改。',
            'key_taken'                => '标识 [:key] 已被代码中登记的条件占用。',
            'is_active_helper'         => '未启用的条件永远不会通过。',
            'question'                 => '询问',
        ],

        'placeholders' => [
            'label' => '添加客户',
        ],

        'empty' => [
            'heading'     => '还没有条件',
            'description' => '条件让步骤能够自动完成——即使对方在很久之前就做过这件事。在这里写一个，或运行 php artisan make:onboarding-condition 来处理表单无法提出的问题。',
        ],
    ],

];
