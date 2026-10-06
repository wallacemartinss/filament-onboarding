<?php

declare(strict_types = 1);

return [

    'checklist' => [
        'mark_done'             => 'סמן כהושלם',
        'start_tour'            => 'התחל סיור',
        'go'                    => 'עבור',
        'skip'                  => 'דלג',
        'close'                 => 'סגור',
        'dismiss'               => 'הסתר',
        'done'                  => 'הושלם',
        'restart'               => 'התחל מחדש',
        'restart_confirm'       => 'פעולה זו תאפס כל שלב וסיור שכבר ראית — להתחיל מחדש?',
        'completed_title'       => 'הכול מוכן',
        'completed_description' => 'כל השלבים הושלמו. ברוך הבא.',
        'footer_note'           => 'אפשר לחזור לזה מאוחר יותר.',
    ],

    'welcome' => [
        'begin' => 'מתחילים',
        'later' => 'לא עכשיו',
        'never' => 'לא להציג שוב',
        'steps' => '{0}עדיין אין כאן כלום|{1}שלב אחד, בקצב שלך|[2,*]:count שלבים, בקצב שלך',
        'back'  => 'הפעלת ההטמעה מחדש',
        'off'   => 'כיבית את ההטמעה. הרשימה וטבעת ההתקדמות נעלמו — אבל העמוד הזה נשאר.',
    ],
    'tour' => [
        'blocked'  => 'המסך עדיין לא עבר הלאה. אם משהו נדרש, מלא אותו ולחץ שוב על «הבא» — הסיור ילך אחריך.',
        'start'    => 'צפה במדריך',
        'choose'   => 'איזה מדריך תרצה?',
        'begin'    => 'התחל',
        'waiting'  => 'ממתין שזה יופיע בעמוד — המשך למלא את הטופס והסיור ילך אחריך.',
        'skip'     => 'דלג',
        'previous' => 'חזרה',
        'next'     => 'הבא',
        'finish'   => 'סיים',
    ],

    'page' => [
        'collapse' => 'כווץ את המסע הזה',
        'expand'   => 'פתח את המסע הזה',
        'meta'     => [
            'completed' => ':count מתוך :total הושלמו',
            'remaining' => '{1}נותר אחד|[2,*]:count נותרו',
            'skipped'   => '{1}דולג אחד|[2,*]:count דולגו',
        ],
        'open_image'           => 'פתח את התמונה: :title',
        'title'                => 'מתחילים',
        'subheading'           => 'איפה אתה, ומה נשאר.',
        'next'                 => 'הבא בתור',
        'hidden'               => 'מוסתר',
        'restore'              => 'הצג שוב',
        'undo'                 => 'בטל',
        'replay_tour'          => 'צפה שוב',
        'replay_video'         => 'צפה שוב',
        'open_again'           => 'פתח שוב',
        'restart'              => 'התחל מחדש',
        'restarted'            => 'המסע הופעל מחדש',
        'restarted_reinstated' => ':count שלבים חזרו מיד: הם הושלמו כי העבודה שמאחוריהם נעשתה.',
        'restart_confirm'      => 'להתחיל את המסע מחדש? מה שסימנת, דילגת וצפית בו יימחק.',
        'restart_note'         => 'שלבים שמשלימים את עצמם חוזרים מיד: הם מגיבים לאפליקציה, לא לכפתור הזה.',

        'stats' => [
            'completed' => 'הושלמו',
            'remaining' => 'נותרו',
            'skipped'   => 'דולגו',
        ],

        'status' => [
            'completed' => 'הושלם',
            'skipped'   => 'דולג',
            'next'      => 'הבא בתור',
            'pending'   => 'לביצוע',
        ],

        'completed_at'       => 'הושלם :time',
        'tour_progress'      => 'תחנה :reached מתוך :total',
        'awaiting_condition' => 'משלים את עצמו',
        'empty_title'        => 'אין מה להטמיע',
        'empty_description'  => 'אין כרגע שום מסע עבורך.',
    ],

    'media' => [
        'watch'   => 'צפה',
        'resume'  => 'המשך',
        'watched' => 'נצפה',
    ],

    'enums' => [

        'condition_type' => [
            'aggregate' => [
                'label'       => 'סופר מה יש להם',
                'description' => 'לקוחות, חשבוניות, שרתים — כל דבר ששלהם. "לפחות לקוח אחד."',
            ],
            'attribute' => [
                'label'       => 'שואל עליהם',
                'description' => 'עמודה במשתמש עצמו. "האימייל שלו אומת."',
            ],
        ],

        'condition_operator' => [
            'equals'                => 'שווה ל',
            'not_equals'            => 'לא שווה ל',
            'greater_than'          => 'גדול מ',
            'greater_than_or_equal' => 'לפחות',
            'less_than'             => 'קטן מ',
            'less_than_or_equal'    => 'לכל היותר',
            'contains'              => 'מכיל',
            'is_set'                => 'מולא',
            'is_empty'              => 'ריק',
        ],
        'step_type' => [
            'task' => 'משימה',
            'tour' => 'סיור',
        ],

        'media_type' => [
            'none'  => 'ללא',
            'image' => 'תמונה',
            'video' => 'וידאו',
        ],

        'media_source' => [
            'upload' => [
                'label'       => 'העלאה',
                'description' => 'נשמר בדיסק שהוגדר (S3, R2, מקומי).',
            ],
            'url' => [
                'label'       => 'קישור ישיר',
                'description' => 'קובץ המאוחסן במקום אחר.',
            ],
            'youtube' => [
                'label'       => 'YouTube',
                'description' => 'זמן הצפייה נמדד.',
            ],
            'vimeo' => [
                'label'       => 'Vimeo',
                'description' => 'זמן הצפייה נמדד.',
            ],
            'embed' => [
                'label'       => 'ספק אחר (iframe)',
                'description' => 'משתחק, אבל זמן הצפייה אי אפשר למדוד.',
            ],
        ],

        'modal_position' => [
            'center'       => 'במרכז',
            'top'          => 'למעלה',
            'bottom'       => 'למטה',
            'top-left'     => 'למעלה משמאל',
            'top-right'    => 'למעלה מימין',
            'bottom-left'  => 'למטה משמאל',
            'bottom-right' => 'למטה מימין',
        ],

        'completion_mode' => [
            'manual' => [
                'label'       => 'ידני',
                'description' => 'המשתמש מסמן בעצמו.',
            ],
            'condition' => [
                'label'       => 'תנאי',
                'description' => 'משלים אוטומטית כאשר בדיקה רשומה עוברת.',
            ],
            'visit' => [
                'label'       => 'ביקור בעמוד',
                'description' => 'משלים כאשר המשתמש מגיע לכתובת.',
            ],
            'video' => [
                'label'       => 'צפייה בוידאו',
                'description' => 'משלים לאחר שצפו מספיק מהוידאו של השלב.',
            ],
            'programmatic' => [
                'label'       => 'תכנותי',
                'description' => 'רק קוד האפליקציה משלים אותו.',
            ],
        ],
    ],

    'resource' => [
        'singular'   => 'זרימת הטמעה',
        'plural'     => 'הטמעה',
        'all_panels' => 'כל הפאנלים',

        'placeholders' => [
            'flow_title'       => 'מתחילים עם …',
            'flow_description' => 'שורה אחת על מה שהמשתמש מקבל מזה.',
            'step_title'       => 'חבר את השרת הראשון שלך',
            'step_description' => 'שורה או שתיים. תגיד למה, לא רק מה.',
        ],

        'no_steps_warning' => 'למסע הזה אין שלבים, ולכן אף אחד לא רואה אותו.',

        'empty' => [
            'heading'     => 'אין עדיין מסעות',
            'description' => 'מסע הוא רשימה שהמשתמשים שלך עוברים בה. צור אחד והוסף לו שלבים.',
        ],

        'sections' => [
            'overview'                => 'סקירה',
            'publishing_description'  => 'מי רואה את המסע הזה, היכן ובאיזה סדר.',
            'appearance_description'  => 'איך המסע מוצג ברשימה.',
            'content_description'     => 'מה המשתמש קורא, בכל שפה שאתה תומך בה.',
            'publishing'              => 'פרסום',
            'appearance'              => 'מראה',
            'rules'                   => 'כללים',
            'destination'             => 'יעד',
            'destination_description' => 'לאן הכפתור מוביל. בחר עמוד מהפאנל; כתובת היא גיבוי.',
            'content'                 => 'תוכן',
            'settings'                => 'הגדרות',
            'behaviour'               => 'התנהגות',
            'media'                   => 'מדיה',
            'media_description'       => 'תמונה להצגה, או וידאו לצפייה. נפתח בחלון מעל הפאנל.',
            'tour'                    => 'סיור',
            'tour_description'        => 'הרכיבים שהסיור הזה מדגיש, לפי הסדר.',
        ],

        'steps' => [
            'title'             => 'שלבים',
            'empty_heading'     => 'אין עדיין שלבים',
            'empty_description' => 'הוסף את השלב הראשון. בלי שלבים, המסע הזה לא יופיע לאף אחד.',
        ],

        'fields' => [
            'title'                 => 'כותרת',
            'description'           => 'תיאור',
            'key'                   => 'מפתח',
            'key_helper'            => 'בשימוש בקוד. אי אפשר לשנות אותו בלי לשבור התקדמות קיימת.',
            'step_key_helper'       => 'ייחודי בתוך הזרימה. בשימוש על ידי Onboarding::for($user)->complete(...).',
            'panel'                 => 'פאנל',
            'panel_helper'          => 'השאר ריק כדי להציג את הזרימה בכל הפאנלים.',
            'icon'                  => 'סמל',
            'color'                 => 'צבע',
            'sort_order'            => 'סדר',
            'is_active'             => 'פעיל',
            'is_dismissible'        => 'ניתן להסתר',
            'is_dismissible_helper' => 'מאפשר למשתמש להסתיר את הרשימה לצמיתות.',
            'steps'                 => 'שלבים',
            'updated_at'            => 'עודכן בתאריך',
            'type'                  => 'סוג',
            'completion_mode'       => 'מושלם על ידי',
            'condition'             => 'תנאי',
            'condition_helper'      => 'נרשם על ידי האפליקציה. שלבים שכבר מולאו חוזרים כמושלמים.',
            'condition_none'        => 'אין עדיין תנאים — כתוב תנאי תחת ההטמעה ← תנאים (או הרץ php artisan make:onboarding-condition), והוא יופיע כאן.',
            'visit_url'             => 'כתובת',
            'visit_url_helper'      => 'תומך בתווית הג׳ם *: /app/*/servers/create',
            'cta_label'             => 'טקסט הכפתור',
            'cta_url'               => 'כתובת מותאמת',
            'cta_url_helper'        => 'רק כשהיעד אינו ברשימה. {tenant} ממולא אוטומטית.',
            'cta_route'             => 'יעד',
            'cta_route_helper'      => 'עמוד מהפאנל. עדיף על כתובת: שורד שינוי של slug.',
            'is_required'           => 'חובה',
            'is_required_helper'    => 'אפשר לדלג על שלבים רשותיים.',
            'visibility'            => 'גלוי כאשר',
            'visibility_helper'     => 'רק מי שעובר את התנאי הזה רואה אותו. השתמש בזה כדי להסתיר שלב מאחורי תוכנית או יכולת.',
            'visibility_everyone'   => 'כולם',

            'media_type'             => 'מדיה',
            'media_source'           => 'מקור',
            'media_file'             => 'קובץ',
            'media_url'              => 'כתובת',
            'media_url_helper'       => 'הדבק את הקישור כמו שהוא: קישורי watch, share או קיצרים עובדים.',
            'media_caption'          => 'כיתוב',
            'modal_position'         => 'מיקום החלון',
            'modal_position_helper'  => 'חלון בפינה משאיר את העמוד שמיש מאחוריו.',
            'modal_position_default' => 'ברירת המחדל של הפאנל',
            'video_threshold'        => 'נחשב נצפה ב־',
            'video_threshold_helper' => 'האחוז שמשלים את השלב, כשהוא משלים בצפייה.',
            'is_active_helper'       => 'מסעות לא פעילים נעלמים מכל הפאנלים.',
            'sort_order_helper'      => 'הקטן מגיע ראשון.',
            'type_helper'            => 'משימה מבוצעת; סיור עוברים.',
            'completion_mode_helper' => 'מה מסמן את השלב הזה כהושלם.',
        ],

        'tour' => [
            'optional'        => 'דלג אם אינו קיים',
            'optional_helper' => 'למשהו שאולי עדיין אינו קיים — תגית בטבלה ריקה, תרשים בלי נתונים. הסיור ממשיך במקום לחכות.',
            'advance'         => 'התקדם באמצעות',
            'advance_helper'  => 'הפקד שמוביל את האפליקציה לתחנה הזו — כפתור «הבא» של אשף, לשונית. נלחץ כאשר המשתמש מתקדם והרכיב עדיין לא על המסך.',
            'add'             => 'הוסף תחנה',
            'target'          => 'מה להדגיש',
            'target_helper'   => 'נקרא מהפאנל עצמו — שדות הטופס של העמוד הזה, הכפתורים שלו, הטבלה שלו. חלק מהטפסים אי אפשר לקרוא מבחוץ (כאלה שתלויים ברשומה הנערכת או במי שמסתכל): שדותיהם לא יופיעו ברשימה, ובורר CSS הוא הפתרון.',
            'targets'         => [
                'on_this_page' => 'בעמוד הזה',
                'widgets'      => 'וידג׳טים',
                'advanced'     => 'מתקדם',
                'custom'       => 'בורר CSS משלי…',
                'table'        => 'הטבלה',
                'search'       => 'תיבת החיפוש',
                'submit'       => 'כפתור השמירה',
                'column'       => 'עמודה: :label',
                'new_record'   => 'כפתור «:label חדש»',
            ],
            'selector'        => 'בורר CSS',
            'selector_helper' => 'הרכיב להדגשה, למשל [data-onboarding="create-server"]. השאר ריק כשבוחרים וידג׳ט.',
            'widget'          => 'וידג׳ט',
            'widget_helper'   => 'וידג׳ט של הפאנל. נמצא בעמוד בעצמו — אין צורך בבורר.',
            'placement'       => 'מיקום',
            'placements'      => [
                'auto'   => 'אוטומטי',
                'top'    => 'למעלה',
                'bottom' => 'למטה',
            ],
            'route'             => 'עמוד',
            'route_helper'      => 'רק אם התחנה הזו נמצאת במקום אחר. הסיור עובר לשם וממשיך.',
            'url'               => 'כתובת מותאמת',
            'url_helper'        => 'רק כשהעמוד אינו ברשימה.',
            'body'              => 'טקסט',
            'visibility'        => 'גלוי כאשר',
            'visibility_helper' => 'השאר ריק כדי להציג את התחנה לכולם. תחנה שמפנה ליכולת שאינה בתוכנית לא תדגיש דבר.',
        ],

        'targets' => [
            'resources'      => 'משאבים',
            'pages'          => 'עמודים',
            'tenant_profile' => 'פרופיל הדייר',
            'page_names'     => [
                'index'  => 'רשימה',
                'create' => 'יצירה',
            ],
        ],
    ],

    'conditions' => [
        'singular'   => 'תנאי',
        'plural'     => 'תנאים',
        'subheading' => 'השאלות שהשלבים שלך יכולים לשאול על מישהו — כתובות כאן, לא בקוד.',
        'at_least'   => 'לפחות :count',

        'sections' => [
            'question'             => 'השאלה',
            'question_description' => 'מה חייב להיות נכון אצל מישהו כדי שהשלב ייחשב מושלם.',
            'naming'               => 'מה שמו',
            'naming_description'   => 'השם שיוצר השלב בוחר מהרשימה הנפתחת.',
        ],

        'fields' => [
            'type'                     => 'מה הוא שואל',
            'model'                    => 'מה הוא סופר',
            'model_helper'             => 'מה שחייב להיות להם: לקוחות, חשבוניות, שרתים.',
            'minimum'                  => 'לפחות',
            'minimum_helper'           => 'כמה. כמעט תמיד אחד.',
            'subject_column'           => 'שייך להם באמצעות',
            'subject_column_helper'    => 'העמודה שמחזיקה את המזהה של מי שעובר את ההטמעה.',
            'scope_column'             => 'ולדייר באמצעות',
            'scope_column_helper'      => 'רק כשהמודל מוגבל לדייר. אחרת השאר ריק.',
            'scope_column_none'        => 'לא מוגבל לדייר',
            'filters'                  => 'רק כאלה שבהם…',
            'filters_helper'           => 'רשות. בלי אף אחד, כולם נספרים.',
            'filters_attribute'        => 'נכון כאשר…',
            'filters_attribute_helper' => 'כל אלה חייבים להתקיים.',
            'add_filter'               => 'הוסף כלל',
            'column'                   => 'שדה',
            'operator'                 => 'הוא',
            'value'                    => 'ערך',
            'label'                    => 'שם',
            'label_helper'             => 'מה שמו בעורך השלבים.',
            'key_helper'               => 'איך השלבים מפנים אליו. לא ניתן לשנות אחרי שהשלבים כבר משתמשים בו.',
            'key_taken'                => 'המפתח [:key] שייך לתנאי שנרשם בקוד.',
            'is_active_helper'         => 'תנאי לא פעיל לעולם אינו עובר.',
            'question'                 => 'שואל',
        ],

        'placeholders' => [
            'label' => 'הוסיף לקוח',
        ],

        'empty' => [
            'heading'     => 'אין עדיין תנאים',
            'description' => 'תנאי הוא מה שמאפשר לשלב להשלים את עצמו — גם למי שעשה את זה לפני זמן רב. כתוב כאן אחד, או הרץ php artisan make:onboarding-condition לשאלה שטופס לא יכול לשאול.',
        ],
    ],

];
