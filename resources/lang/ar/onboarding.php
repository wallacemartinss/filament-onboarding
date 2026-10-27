<?php

declare(strict_types = 1);

return [

    'checklist' => [
        'mark_done'             => 'وضع علامة «تم»',
        'start_tour'            => 'ابدأ الجولة',
        'go'                    => 'انتقل',
        'skip'                  => 'تخطَّ',
        'close'                 => 'إغلاق',
        'dismiss'               => 'إخفاء',
        'done'                  => 'تم',
        'completed_title'       => 'كل شيء جاهز',
        'completed_description' => 'أكملت جميع الخطوات. أهلاً بك.',
        'footer_note'           => 'يمكنك العودة إلى هذا لاحقاً.',
    ],

    'welcome' => [
        'begin' => 'ابدأ',
        'later' => 'ليس الآن',
        'never' => 'لا تعرضه مرة أخرى',
        'steps' => '{0}لا يوجد شيء هنا بعد|{1}خطوة واحدة، على وتيرتك|[2,10]:count خطوات، على وتيرتك|[11,99]:count خطوة، على وتيرتك|[100,*]:count خطوة، على وتيرتك',
        'back'  => 'أعد تشغيل الإعداد الأولي',
        'off'   => 'أوقفتَ الإعداد الأولي. اختفت القائمة وحلقة التقدم — لكن هذه الصفحة باقية.',
    ],
    'tour' => [
        'blocked'  => 'لم تتقدّم الشاشة بعد. إذا كان هناك حقل مطلوب، املأه واضغط «التالي» مرة أخرى — الجولة تتبعك إلى هناك.',
        'start'    => 'شاهد الدليل',
        'choose'   => 'أي دليل تريد؟',
        'begin'    => 'ابدأ',
        'waiting'  => 'في انتظار ظهوره في الصفحة — أكمل تعبئة النموذج وستتبعك الجولة.',
        'skip'     => 'تخطَّ',
        'previous' => 'رجوع',
        'next'     => 'التالي',
        'finish'   => 'إنهاء',
    ],

    'page' => [
        'collapse' => 'اطوِ هذه الرحلة',
        'expand'   => 'افتح هذه الرحلة',
        'meta'     => [
            'completed' => ':count من :total مكتملة',
            'remaining' => '{1}بقيت واحدة|[2,*]:count بقيت',
            'skipped'   => '{1}تم تخطي واحدة|[2,*]:count تم تخطيها',
        ],
        'open_image'           => 'افتح الصورة: :title',
        'title'                => 'البداية',
        'subheading'           => 'أين أنت، وما المتبقّي.',
        'next'                 => 'التالي',
        'hidden'               => 'مخفي',
        'restore'              => 'إظهار من جديد',
        'undo'                 => 'تراجع',
        'replay_tour'          => 'شاهد من جديد',
        'replay_video'         => 'شاهد من جديد',
        'open_again'           => 'افتح من جديد',
        'restart'              => 'ابدأ من جديد',
        'restarted'            => 'أُعيد تشغيل الرحلة',
        'restarted_reinstated' => 'عاد :count خطوة مباشرةً: هي مكتملة لأن العمل خلفها قد أُنجز.',
        'restart_confirm'      => 'هل تريد إعادة تشغيل هذه الرحلة؟ سيُمسح ما علّمته وتخطّيت وشاهدته.',
        'restart_note'         => 'الخطوات التي تكتمل تلقائياً تعود مباشرةً: فهي تستجيب للتطبيق، لا لهذا الزر.',

        'stats' => [
            'completed' => 'مكتملة',
            'remaining' => 'متبقية',
            'skipped'   => 'متخطاة',
        ],

        'status' => [
            'completed' => 'مكتملة',
            'skipped'   => 'متخطاة',
            'next'      => 'التالي',
            'pending'   => 'قيد الانتظار',
        ],

        'completed_at'       => 'اكتملت :time',
        'tour_progress'      => 'المحطة :reached من :total',
        'awaiting_condition' => 'تكتمل تلقائياً',
        'empty_title'        => 'لا شيء للإعداد الأولي',
        'empty_description'  => 'لا توجد رحلة لك حالياً.',
    ],

    'media' => [
        'watch'   => 'شاهد',
        'resume'  => 'استئناف',
        'watched' => 'تمت مشاهدته',
    ],

    'enums' => [

        'condition_type' => [
            'aggregate' => [
                'label'       => 'يعدّ ما لديهم',
                'description' => 'العملاء والفواتير والخوادم — أي شيء يخصهم. «عميل واحد على الأقل».',
            ],
            'attribute' => [
                'label'       => 'يسأل عنهم هم',
                'description' => 'عمود في المستخدم نفسه. «تم التحقق من بريده الإلكتروني».',
            ],
        ],

        'condition_operator' => [
            'equals'                => 'يساوي',
            'not_equals'            => 'لا يساوي',
            'greater_than'          => 'أكبر من',
            'greater_than_or_equal' => 'لا يقل عن',
            'less_than'             => 'أصغر من',
            'less_than_or_equal'    => 'لا يزيد عن',
            'contains'              => 'يحتوي على',
            'is_set'                => 'مُعبّأ',
            'is_empty'              => 'فارغ',
        ],
        'step_type' => [
            'task' => 'مهمة',
            'tour' => 'جولة',
        ],

        'media_type' => [
            'none'  => 'لا شيء',
            'image' => 'صورة',
            'video' => 'فيديو',
        ],

        'media_source' => [
            'upload' => [
                'label'       => 'رفع',
                'description' => 'يُخزَّن على القرص المُهيَّأ (S3، R2، محلي).',
            ],
            'url' => [
                'label'       => 'رابط مباشر',
                'description' => 'ملف مُستضاف في مكان آخر.',
            ],
            'youtube' => [
                'label'       => 'YouTube',
                'description' => 'يتم قياس وقت المشاهدة.',
            ],
            'vimeo' => [
                'label'       => 'Vimeo',
                'description' => 'يتم قياس وقت المشاهدة.',
            ],
            'embed' => [
                'label'       => 'مزوّد آخر (iframe)',
                'description' => 'يعمل، لكن لا يمكن قياس وقت المشاهدة.',
            ],
        ],

        'modal_position' => [
            'center'       => 'في الوسط',
            'top'          => 'أعلى',
            'bottom'       => 'أسفل',
            'top-left'     => 'أعلى اليسار',
            'top-right'    => 'أعلى اليمين',
            'bottom-left'  => 'أسفل اليسار',
            'bottom-right' => 'أسفل اليمين',
        ],

        'completion_mode' => [
            'manual' => [
                'label'       => 'يدوي',
                'description' => 'يقوم المستخدم بعلامة عليها بنفسه.',
            ],
            'condition' => [
                'label'       => 'شرط',
                'description' => 'تكتمل تلقائياً عند نجاح فحص مسجّل.',
            ],
            'visit' => [
                'label'       => 'زيارة صفحة',
                'description' => 'تكتمل عندما يصل المستخدم إلى الرابط.',
            ],
            'video' => [
                'label'       => 'مشاهدة الفيديو',
                'description' => 'تكتمل بعد مشاهدة ما يكفي من فيديو الخطوة.',
            ],
            'programmatic' => [
                'label'       => 'برمجياً',
                'description' => 'يكمله كود التطبيق وحده.',
            ],
        ],
    ],

    'resource' => [
        'singular'   => 'مسار الإعداد الأولي',
        'plural'     => 'الإعداد الأولي',
        'all_panels' => 'كل اللوحات',

        'placeholders' => [
            'flow_title'       => 'البدء مع …',
            'flow_description' => 'سطر واحد عما يحصل عليه المستخدم من هذا.',
            'step_title'       => 'اربط أول خادم لك',
            'step_description' => 'سطر أو سطران. اشرح لماذا، لا ماذا فقط.',
        ],

        'no_steps_warning' => 'لا تحتوي هذه الرحلة على خطوات، فلا يراها أحد.',

        'empty' => [
            'heading'     => 'لا توجد رحلات بعد',
            'description' => 'الرحلة قائمة يمرّ بها مستخدموك. أنشئ واحدة وأضف خطواتها.',
        ],

        'sections' => [
            'overview'                => 'نظرة عامة',
            'publishing_description'  => 'من يرى هذه الرحلة، وأين، وبأي ترتيب.',
            'appearance_description'  => 'كيف يُعلَن عن الرحلة في القائمة.',
            'content_description'     => 'ما يقرأه المستخدم، بكل لغة تدعمها.',
            'publishing'              => 'النشر',
            'appearance'              => 'المظهر',
            'rules'                   => 'القواعد',
            'destination'             => 'الوجهة',
            'destination_description' => 'إلى أين يأخذ الزر المستخدم. اختر صفحة من اللوحة؛ الرابط هو الخيار الاحتياطي.',
            'content'                 => 'المحتوى',
            'settings'                => 'الإعدادات',
            'behaviour'               => 'السلوك',
            'media'                   => 'الوسائط',
            'media_description'       => 'صورة تُعرض، أو فيديو يُشاهد. يُفتح في نافذة فوق اللوحة.',
            'tour'                    => 'جولة',
            'tour_description'        => 'العناصر التي تُبرزها هذه الجولة، بالترتيب.',
        ],

        'steps' => [
            'title'             => 'الخطوات',
            'empty_heading'     => 'لا توجد خطوات بعد',
            'empty_description' => 'أضف الخطوة الأولى. بدون خطوات، لن تظهر هذه الرحلة لأحد.',
        ],

        'fields' => [
            'title'                 => 'العنوان',
            'description'           => 'الوصف',
            'key'                   => 'المفتاح',
            'key_helper'            => 'يُستخدم في الكود. لا يمكن تغييره دون كسر التقدّم الحالي.',
            'step_key_helper'       => 'فريد داخل المسار. يستخدمه Onboarding::for($user)->complete(...).',
            'panel'                 => 'اللوحة',
            'panel_helper'          => 'اتركه فارغاً لعرض المسار في كل اللوحات.',
            'icon'                  => 'الأيقونة',
            'color'                 => 'اللون',
            'sort_order'            => 'الترتيب',
            'is_active'             => 'نشط',
            'is_dismissible'        => 'قابل للإخفاء',
            'is_dismissible_helper' => 'يسمح للمستخدم بإخفاء القائمة نهائياً.',
            'steps'                 => 'الخطوات',
            'updated_at'            => 'آخر تحديث',
            'type'                  => 'النوع',
            'completion_mode'       => 'يكتمل بواسطة',
            'condition'             => 'الشرط',
            'condition_helper'      => 'مسجّل من التطبيق. الخطوات المحقّقة تعود مكتملة.',
            'condition_none'        => 'لا توجد شروط بعد — اكتب شرطاً في الإعداد الأولي ← الشروط (أو شغّل php artisan make:onboarding-condition)، فيظهر هنا.',
            'visit_url'             => 'رابط',
            'visit_url_helper'      => 'يدعم البدل *: /app/*/servers/create',
            'cta_label'             => 'نص الزر',
            'cta_url'               => 'رابط مخصّص',
            'cta_url_helper'        => 'فقط عندما لا تكون الوجهة في القائمة. يُملأ {tenant} تلقائياً.',
            'cta_route'             => 'الوجهة',
            'cta_route_helper'      => 'صفحة من اللوحة. أفضل من الرابط: تصمد عند تغيير المعرّف.',
            'is_required'           => 'مطلوب',
            'is_required_helper'    => 'يمكن تخطّي الخطوات الاختيارية.',
            'visibility'            => 'مرئي عندما',
            'visibility_helper'     => 'لا يراها إلا من يحقّق هذا الشرط. استخدمه لإخفاء خطوة خلف خطة أو ميزة.',
            'visibility_everyone'   => 'الجميع',

            'media_type'             => 'الوسائط',
            'media_source'           => 'المصدر',
            'media_file'             => 'الملف',
            'media_url'              => 'رابط',
            'media_url_helper'       => 'الصق الرابط كما هو: روابط watch أو share أو القصيرة تعمل كلها.',
            'media_caption'          => 'التعليق',
            'modal_position'         => 'موضع النافذة',
            'modal_position_helper'  => 'نافذة في الزاوية تترك الصفحة قابلة للاستخدام خلفها.',
            'modal_position_default' => 'افتراضي اللوحة',
            'video_threshold'        => 'تُعتبر مشاهدة عند',
            'video_threshold_helper' => 'النسبة التي تُكمل الخطوة، عندما تكتمل بالمشاهدة.',
            'is_active_helper'       => 'الرحلات غير النشطة تختفي من كل اللوحات.',
            'sort_order_helper'      => 'الأصغر أولاً.',
            'type_helper'            => 'المهمة تُنجَز؛ الجولة تُقطع.',
            'completion_mode_helper' => 'ما الذي يَعُدّ هذه الخطوة مكتملة.',
        ],

        'tour' => [
            'optional'        => 'تخطَّها إن لم تكن موجودة',
            'optional_helper' => 'لشيء قد لا يوجد بعد — وسم في جدول فارغ، رسم بياني بلا بيانات. تتابع الجولة بدل الانتظار.',
            'advance'         => 'التقدّم عبر',
            'advance_helper'  => 'العنصر الذي ينقل التطبيق إلى هذه المحطة — زر «التالي» في معالج، أو تبويب. يُنقر عندما يتقدّم المستخدم والعنصر ليس على الشاشة بعد.',
            'add'             => 'أضف محطة',
            'target'          => 'ما الذي نُبرزه',
            'target_helper'   => 'يُقرأ من اللوحة نفسها — حقول نموذج هذه الصفحة وأزرارها وجدولها. بعض النماذج لا يمكن قراءتها من الخارج (من يعتمد على السجل الذي يُحرَّر أو على من ينظر): لن تُدرج حقولها، والمُحدِّد CSS هو المخرج.',
            'targets'         => [
                'on_this_page' => 'في هذه الصفحة',
                'widgets'      => 'الأدوات',
                'advanced'     => 'متقدّم',
                'custom'       => 'مُحدِّد CSS خاص بي…',
                'table'        => 'الجدول',
                'search'       => 'مربع البحث',
                'submit'       => 'زر الحفظ',
                'column'       => 'عمود: :label',
                'new_record'   => 'زر «:label جديد»',
            ],
            'selector'        => 'مُحدِّد CSS',
            'selector_helper' => 'العنصر الذي نُبرزه، مثل [data-onboarding="create-server"]. اتركه فارغاً عند اختيار أداة.',
            'widget'          => 'أداة',
            'widget_helper'   => 'أداة من اللوحة. تُعثر عليها في الصفحة تلقائياً — لا حاجة إلى مُحدِّد.',
            'placement'       => 'الموضع',
            'placements'      => [
                'auto'   => 'تلقائي',
                'top'    => 'أعلى',
                'bottom' => 'أسفل',
            ],
            'route'             => 'الصفحة',
            'route_helper'      => 'فقط إذا كانت هذه المحطة في مكان آخر. تنتقل الجولة إلى هناك وتكمل.',
            'url'               => 'رابط مخصّص',
            'url_helper'        => 'فقط عندما لا تكون الصفحة في القائمة.',
            'body'              => 'النص',
            'visibility'        => 'مرئية عندما',
            'visibility_helper' => 'اتركه فارغاً لعرض هذه المحطة للجميع. محطة تشير إلى ميزة غير موجودة في الخطة لن تُبرز شيئاً.',
        ],

        'targets' => [
            'resources'      => 'المصادر',
            'pages'          => 'الصفحات',
            'tenant_profile' => 'ملف المستأجر',
            'page_names'     => [
                'index'  => 'قائمة',
                'create' => 'إنشاء',
            ],
        ],
    ],

    'conditions' => [
        'singular'   => 'الشرط',
        'plural'     => 'الشروط',
        'subheading' => 'الأسئلة التي يمكن لخطواتك أن تسألها عن شخص — مكتوبة هنا، لا في الكود.',
        'at_least'   => 'لا يقل عن :count',

        'sections' => [
            'question'             => 'السؤال',
            'question_description' => 'ما الذي يجب أن يكون صحيحاً في شخص ما حتى تُعدّ الخطوة مكتملة.',
            'naming'               => 'ما اسمه',
            'naming_description'   => 'الاسم الذي يختاره من القائمة من يكتب الخطوة.',
        ],

        'fields' => [
            'type'                     => 'ما الذي يسأل عنه',
            'model'                    => 'ما الذي يعدّ',
            'model_helper'             => 'ما الذي يجب أن يكون لديهم: عملاء، فواتير، خوادم.',
            'minimum'                  => 'لا يقل عن',
            'minimum_helper'           => 'كم. واحد، في الغالب.',
            'subject_column'           => 'ينتمي إليهم عبر',
            'subject_column_helper'    => 'العمود الذي يحمل مُعرّف من يمرّ بالإعداد الأولي.',
            'scope_column'             => 'ويتميّز إلى المستأجر عبر',
            'scope_column_helper'      => 'فقط عندما يكون النموذج مقيَّداً بمستأجر. وإلا فاتركه فارغاً.',
            'scope_column_none'        => 'غير مقيَّد بمستأجر',
            'filters'                  => 'فقط التي…',
            'filters_helper'           => 'اختياري. بدون أيّها، كلها تُحتسب.',
            'filters_attribute'        => 'صحيح عندما…',
            'filters_attribute_helper' => 'كل هذه يجب أن تتحقّق.',
            'add_filter'               => 'أضف قاعدة',
            'column'                   => 'الحقل',
            'operator'                 => 'هو',
            'value'                    => 'القيمة',
            'label'                    => 'الاسم',
            'label_helper'             => 'ما يُسمّى به في محرّر الخطوات.',
            'key_helper'               => 'كيف تشير إليه الخطوات. لا يمكن تغييره بعد أن تستخدمه خطوات.',
            'key_taken'                => 'المفتاح [:key] يعود إلى شرط مسجَّل في الكود.',
            'is_active_helper'         => 'الشرط غير النشط لا يتحقّق أبداً.',
            'question'                 => 'يسأل',
        ],

        'placeholders' => [
            'label' => 'أضاف عميلاً',
        ],

        'empty' => [
            'heading'     => 'لا توجد شروط بعد',
            'description' => 'الشرط هو ما يسمح لخطوة بأن تُكمل نفسها — حتى لمن فعل ذلك قبل زمن طويل. اكتب شرطاً هنا، أو شغّل php artisan make:onboarding-condition لسؤال لا يستطيع النموذج طرحه.',
        ],
    ],

];
