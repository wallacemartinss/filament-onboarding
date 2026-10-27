<?php

declare(strict_types = 1);

return [

    'checklist' => [
        'mark_done'             => 'Tamamlandı işarələ',
        'start_tour'            => 'Turu başlat',
        'go'                    => 'Get',
        'skip'                  => 'Keç',
        'close'                 => 'Bağla',
        'dismiss'               => 'Gizlət',
        'done'                  => 'Hazır',
        'completed_title'       => 'Hər şey hazırdır',
        'completed_description' => 'Bütün addımlar tamamlandı. Xoş gəlmisiniz.',
        'footer_note'           => 'Bunu sonra davam edə bilərsiniz.',
    ],

    'welcome' => [
        'begin' => 'Başla',
        'later' => 'İndi deyil',
        'never' => 'Bunu bir daha göstərmə',
        'steps' => '{0}Hələlik heç nə yoxdur|{1}1 addım — öz sürətinizlə|[2,*]:count addım — öz sürətinizlə',
        'back'  => 'Onboarding-i yenidən aktivləşdir',
        'off'   => 'Onboarding söndürüldü. Siyahı və proqres halqası yox oldu — bu səhifə isə qalır.',
    ],
    'tour' => [
        'blocked'  => 'Ekran hələ irəliləməyib. Əgər mütləq sahə varsa, doldurun və yenidən "Növbəti" basın — tur sizi oraya çatdıracaq.',
        'start'    => 'Təlimatı bax',
        'choose'   => 'Hansı təlimatı istəyirsiniz?',
        'begin'    => 'Başla',
        'waiting'  => 'Səhifədə görünməsini gözləyir — formanı doldurmağa davam edin, tur sizi izləyəcək.',
        'skip'     => 'Keç',
        'previous' => 'Geri',
        'next'     => 'Növbəti',
        'finish'   => 'Bitir',
    ],

    'page' => [
        'collapse' => 'Bu səfəri yığ',
        'expand'   => 'Bu səfəri aç',
        'meta'     => [
            'completed' => ':total-dan :count tamamlandı',
            'remaining' => '{1}1 qalıb|[2,*]:count qalıb',
            'skipped'   => '{1}1 keçildi|[2,*]:count keçildi',
        ],
        'open_image'           => 'Şəkli aç: :title',
        'title'                => 'Başlamaq',
        'subheading'           => 'Haradasınız və nə qalıb.',
        'next'                 => 'Növbətdə',
        'hidden'               => 'Gizlidir',
        'restore'              => 'Yenidən göstər',
        'undo'                 => 'Geri al',
        'replay_tour'          => 'Yenidən bax',
        'replay_video'         => 'Yenidən bax',
        'open_again'           => 'Yenidən aç',
        'restart'              => 'Yenidən başla',
        'restarted'            => 'Səfər yenidən başladı',
        'restarted_reinstated' => ':count addım dərhal geri qayıtdı: onlar tamamlanıb, çünki arxalarındakı iş bitib.',
        'restart_confirm'      => 'Bu səfəri yenidən başlatsın? İşaretlədiyiniz, keçdiyiniz və baxdığınız şeylər təmizlənəcək.',
        'restart_note'         => 'Öz başına tamamlanan addımlar dərhal geri qayıdır: onlar bu düyməyə deyil, tətbiqə cavab verir.',

        'stats' => [
            'completed' => 'Tamamlanan',
            'remaining' => 'Qalan',
            'skipped'   => 'Keçilən',
        ],

        'status' => [
            'completed' => 'Tamamlandı',
            'skipped'   => 'Keçildi',
            'next'      => 'Növbətdə',
            'pending'   => 'Görüləcək',
        ],

        'completed_at'       => ':time tamamlandı',
        'tour_progress'      => ':total-dan :reached-ci dayanacaq',
        'awaiting_condition' => 'Özü tamamlanır',
        'empty_title'        => 'Onboarding üçün heç nə yoxdur',
        'empty_description'  => 'Hazırda sizin üçün heç bir səfər yoxdur.',
    ],

    'media' => [
        'watch'   => 'Bax',
        'resume'  => 'Davam et',
        'watched' => 'baxıldı',
    ],

    'enums' => [

        'condition_type' => [
            'aggregate' => [
                'label'       => 'Onların nəyəsi var — sayır',
                'description' => 'Müştərilər, fakturalar, serverlər — onlara aid hər şey. "Ən azı bir müştəri."',
            ],
            'attribute' => [
                'label'       => 'Onların özü barədə soruşur',
                'description' => 'İstifadəçinin öz sütunu. "E-poçt ünvanı təsdiqlənib."',
            ],
        ],

        'condition_operator' => [
            'equals'                => 'bərabərdir',
            'not_equals'            => 'bərabər deyil',
            'greater_than'          => 'daha çoxdur',
            'greater_than_or_equal' => 'ən azıdır',
            'less_than'             => 'dəfə azdır',
            'less_than_or_equal'    => 'ən çoxudur',
            'contains'              => 'içindədir',
            'is_set'                => 'doldurulub',
            'is_empty'              => 'boşdur',
        ],
        'step_type' => [
            'task' => 'Tapşırıq',
            'tour' => 'Tur',
        ],

        'media_type' => [
            'none'  => 'Yoxdur',
            'image' => 'Şəkil',
            'video' => 'Video',
        ],

        'media_source' => [
            'upload' => [
                'label'       => 'Yüklə',
                'description' => 'Tənzimlənmiş diskdə saxlanılır (S3, R2, lokal).',
            ],
            'url' => [
                'label'       => 'Birbaşa URL',
                'description' => 'Başqa yerdə yerləşən fayl.',
            ],
            'youtube' => [
                'label'       => 'YouTube',
                'description' => 'Baxma vaxtı izlənilir.',
            ],
            'vimeo' => [
                'label'       => 'Vimeo',
                'description' => 'Baxma vaxtı izlənilir.',
            ],
            'embed' => [
                'label'       => 'Başqa provayder (iframe)',
                'description' => 'Oynayır, amma baxma vaxtı izlənə bilmir.',
            ],
        ],

        'modal_position' => [
            'center'       => 'Mərkəzdə',
            'top'          => 'Yuxarıda',
            'bottom'       => 'Aşağıda',
            'top-left'     => 'Yuxarı solda',
            'top-right'    => 'Yuxarı sağda',
            'bottom-left'  => 'Aşağı solda',
            'bottom-right' => 'Aşağı sağda',
        ],

        'completion_mode' => [
            'manual' => [
                'label'       => 'Manual',
                'description' => 'İstifadəçi özü işarələyir.',
            ],
            'condition' => [
                'label'       => 'Şərt',
                'description' => 'Qeydə alınmış yoxlama uğurlu olduqda avtomatik tamamlanır.',
            ],
            'visit' => [
                'label'       => 'Səhifəyə ziyarət',
                'description' => 'İstifadəçi URL-ə çatanda tamamlanır.',
            ],
            'video' => [
                'label'       => 'Videonun izlənməsi',
                'description' => 'Addımın videosunun kifayət qədər hissəsi izləndikdə tamamlanır.',
            ],
            'programmatic' => [
                'label'       => 'Proqram vasitəsi ilə',
                'description' => 'Onu yalnız tətbiq kodu tamamlayır.',
            ],
        ],
    ],

    'resource' => [
        'singular'   => 'Onboarding axını',
        'plural'     => 'Onboarding',
        'all_panels' => 'Bütün panellər',

        'placeholders' => [
            'flow_title'       => '… ilə başlanğıc',
            'flow_description' => 'İstifadəçinin bundan nə qazandığına dair bir sətir.',
            'step_title'       => 'İlk serverinizi qoşun',
            'step_description' => 'Bir-iki sətir. Nə olduğunu deyin, niyə olduğunu da.',
        ],

        'no_steps_warning' => 'Bu səfərin heç bir addımı yoxdur, ona görə heç kim onu görmür.',

        'empty' => [
            'heading'     => 'Hələ səfər yoxdur',
            'description' => 'Səfər — istifadəçilərinizin keçdiyi bir siyahıdır. Birini yaradın və addımlarını əlavə edin.',
        ],

        'sections' => [
            'overview'                => 'İcmal',
            'publishing_description'  => 'Bu səfəri kim, harada və hansı ardıcıllıqda görür.',
            'appearance_description'  => 'Səfər siyahıda necə elan olunur.',
            'content_description'     => 'Dəstəklədiyiniz hər dil üçün istifadəçinin oxuduğu mətn.',
            'publishing'              => 'Nəşr',
            'appearance'              => 'Görünüş',
            'rules'                   => 'Qaydalar',
            'destination'             => 'Hədəf',
            'destination_description' => 'Düymə istifadəçini hara aparır. Paneldən bir səhifə seçin; URL ehtiyat variantdır.',
            'content'                 => 'Məzmun',
            'settings'                => 'Tənzimləmələr',
            'behaviour'               => 'Davranış',
            'media'                   => 'Media',
            'media_description'       => 'Göstərmək üçün şəkil və ya baxmaq üçün video. Panelin üzərində modalda açılır.',
            'tour'                    => 'Tur',
            'tour_description'        => 'Bu turun sıra ilə işıqladığı elementlər.',
        ],

        'steps' => [
            'title'             => 'Addımlar',
            'empty_heading'     => 'Hələ addım yoxdur',
            'empty_description' => 'İlk addımı əlavə edin. Addım olmadan bu səfər heç kimdə görünmür.',
        ],

        'fields' => [
            'title'                 => 'Başlıq',
            'description'           => 'Təsvir',
            'key'                   => 'Açar',
            'key_helper'            => 'Kodda istifadə olunur. Mövcud irəliləyişi pozmadan dəyişmək olmur.',
            'step_key_helper'       => 'Axın daxilində unikaldır. Onboarding::for($user)->complete(...) istifadə edir.',
            'panel'                 => 'Panel',
            'panel_helper'          => 'Axını bütün panellərdə göstərmək üçün boş buraxın.',
            'icon'                  => 'Nişan',
            'color'                 => 'Rəng',
            'sort_order'            => 'Sıra',
            'is_active'             => 'Aktiv',
            'is_dismissible'        => 'Bağlanıla bilər',
            'is_dismissible_helper' => 'İstifadəçiyə siyahını həmişəlik gizlətməyə imkan verir.',
            'steps'                 => 'Addımlar',
            'updated_at'            => 'Yenilənmə vaxtı',
            'type'                  => 'Növ',
            'completion_mode'       => 'Tamamlayan',
            'condition'             => 'Şərt',
            'condition_helper'      => 'Tətbiq tərəfindən qeydə alınır. Artıq yerinə yetirilmiş addımlar tamamlanmış kimi qayıdır.',
            'condition_none'        => 'Hələ şərt yoxdur — Onboarding → Şərtlər bölməsində birini yazın (və ya php artisan make:onboarding-condition əmrini icra edin), o da burada görünəcək.',
            'visit_url'             => 'URL',
            'visit_url_helper'      => '* ulğu işarəsini dəstəkləyir: /app/*/servers/create',
            'cta_label'             => 'Düymə mətni',
            'cta_url'               => 'Fərdi URL',
            'cta_url_helper'        => 'Yalnız hədəf siyahıda yoxdursa. {tenant} avtomatik doldurulur.',
            'cta_route'             => 'Hədəf',
            'cta_route_helper'      => 'Panelin bir səhifəsi. URL-ə nisbətən üstündür: slug dəyişsə də qalır.',
            'is_required'           => 'Məcburi',
            'is_required_helper'    => 'İstəyə bağlı addımlar keçilə bilər.',
            'visibility'            => 'Nə zaman görünür',
            'visibility_helper'     => 'Bu şərti keçən şəxslər görür. Addımı plan və ya funksiya arxasında saxlamaq üçün istifadə edin.',
            'visibility_everyone'   => 'Hamı',

            'media_type'             => 'Media',
            'media_source'           => 'Mənbə',
            'media_file'             => 'Fayl',
            'media_url'              => 'URL',
            'media_url_helper'       => 'Linki olduğu kimi yapışdırın: watch, share və ya qısa URL-lər işləyir.',
            'media_caption'          => 'Altyazı',
            'modal_position'         => 'Modal mövqeyi',
            'modal_position_helper'  => 'Küncdəki modal səhifəni arxasından istifadə oluna bilər saxlayır.',
            'modal_position_default' => 'Panelin standartı',
            'video_threshold'        => 'Baxılmış sayılır',
            'video_threshold_helper' => 'Addım baxmaqla tamamlandıqda onu tamamlayan faiz.',
            'is_active_helper'       => 'Qeyri-aktiv səfərlər bütün panellərdən yox olur.',
            'sort_order_helper'      => 'Kiçik olan əvvəl gəlir.',
            'type_helper'            => 'Tapşırıq yerinə yetirilir; tur keçilir.',
            'completion_mode_helper' => 'Bu addımı tamamlanmış sayan nədir.',
        ],

        'tour' => [
            'optional'        => 'Yoxdursa keç',
            'optional_helper' => 'Hələ mövcud olmaya bilən şeylər üçün — boş cədvəldə etiket, datasız qrafik. Tur gözləmək əvəzinə davam edir.',
            'advance'         => 'İrəliləmək üçün',
            'advance_helper'  => 'Tətbiqi bu dayanacağa çatıran idarəetmə — veryağın "növbəti" düyməsi, bir tab. İstifadəçi irəliləyib element hələ ekranda olmadıqda kliklənir.',
            'add'             => 'Dayanacaq əlavə et',
            'target'          => 'Nəyi işıqlamaq lazımdır',
            'target_helper'   => 'Panelin özündən oxunur — bu səhifənin forman sahələri, düymələri, cədvəli. Bəzi formalar xaricidən oxuna bilmir (redaktə olunan qeydə və ya baxanın şəxsinə görə işləyən): onların sahələri siyahıya düşməz və CSS seçicisi yolunu açır.',
            'targets'         => [
                'on_this_page' => 'Bu səhifədə',
                'widgets'      => 'Vidgetlər',
                'advanced'     => 'Qabaqlar',
                'custom'       => 'Öz CSS seçicim…',
                'table'        => 'Cədvəl',
                'search'       => 'Axtarma sahəsi',
                'submit'       => 'Yadda saxlama düyməsi',
                'column'       => 'Sütun: :label',
                'new_record'   => '"Yeni :label" düyməsi',
            ],
            'selector'        => 'CSS seçicisi',
            'selector_helper' => 'İşıqlanacaq element, məs. [data-onboarding="create-server"]. Vidget seçildikdə boş buraxın.',
            'widget'          => 'Vidget',
            'widget_helper'   => 'Panelin vidgeti. Səhifədə özü tapılır — seçici lazım deyil.',
            'placement'       => 'Yerləşmə',
            'placements'      => [
                'auto'   => 'Avtomatik',
                'top'    => 'Yuxarıda',
                'bottom' => 'Aşağıda',
            ],
            'route'             => 'Səhifə',
            'route_helper'      => 'Yalnız bu dayanacaq başqa yerdə yerləşirsə. Tur oraya gedib davam edir.',
            'url'               => 'Fərdi URL',
            'url_helper'        => 'Yalnız səhifə siyahıda yoxdursa.',
            'body'              => 'Mətn',
            'visibility'        => 'Nə zaman görünür',
            'visibility_helper' => 'Bütün şəxslərə göstərmək üçün boş buraxın. Planın dəstəkləmədiyi funksiyaya yönlənən dayanacaq heç nə işıqlamaz.',
        ],

        'targets' => [
            'resources'      => 'Resurslar',
            'pages'          => 'Səhifələr',
            'tenant_profile' => 'Tenant profili',
            'page_names'     => [
                'index'  => 'siyahı',
                'create' => 'yaradış',
            ],
        ],
    ],

    'conditions' => [
        'singular'   => 'Şərt',
        'plural'     => 'Şərtlər',
        'subheading' => 'Addımlarınızın bir şey barədə verə biləcəyi suallar — burada, kodda deyil.',
        'at_least'   => 'ən azı :count',

        'sections' => [
            'question'             => 'Sual',
            'question_description' => 'Addımın tamamlanması üçün kimdə nə doğru olmalıdır.',
            'naming'               => 'Necə adlandırılır',
            'naming_description'   => 'Addım yazan şəxsin açılan siyahıdan seçdiyi ad.',
        ],

        'fields' => [
            'type'                     => 'Nəyi soruşur',
            'model'                    => 'Nəni sayır',
            'model_helper'             => 'Onların malı olmalı şey: müştərilər, fakturalar, serverlər.',
            'minimum'                  => 'Ən azı',
            'minimum_helper'           => 'Neçə. Adətən bir.',
            'subject_column'           => 'Onlara aid olması üçün',
            'subject_column_helper'    => 'Onboarding keçirilən şəxsin id-sini saxlayan sütun.',
            'scope_column'             => 'Tenantə aid olması üçün isə',
            'scope_column_helper'      => 'Yalnız model tenantə görə sənədlənirsə. Əks halda boş buraxın.',
            'scope_column_none'        => 'Tenantə görə sənədlənməyib',
            'filters'                  => 'Yalnız harada…',
            'filters_helper'           => 'İstəyə bağlı. Heç biri olmadan hamısı sayılır.',
            'filters_attribute'        => 'Nə zaman doğrudur…',
            'filters_attribute_helper' => 'Bunların hamısı doğru olmalıdır.',
            'add_filter'               => 'Qayda əlavə et',
            'column'                   => 'Sahə',
            'operator'                 => 'Belədir',
            'value'                    => 'Dəyər',
            'label'                    => 'Ad',
            'label_helper'             => 'Addım redaktorunda necə adlandırılır.',
            'key_helper'               => 'Addımlar ona necə müraciət edir. Addımlar istifadə etdikdən sonra dəyişmək olmur.',
            'key_taken'                => '[:key] açarı kodda qeydə alınmış şərtə aiddir.',
            'is_active_helper'         => 'Qeyri-aktiv şərt heç vaxt keçmir.',
            'question'                 => 'Soruşur',
        ],

        'placeholders' => [
            'label' => 'Müştəri əlavə edib',
        ],

        'empty' => [
            'heading'     => 'Hələ şərt yoxdur',
            'description' => 'Şərt — addımın özünü tamamlaya bilməsini təmin edən şeydir, hətta uzun əvvəl bunu etmiş şəxslər üçün də. Burada birini yazın, və ya formanın soruşa bilmədiyi sual üçün php artisan make:onboarding-condition əmrini icra edin.',
        ],
    ],

];
