<?php

declare(strict_types = 1);

return [

    'checklist' => [
        'mark_done'             => 'Tamamlandı olarak işaretle',
        'start_tour'            => 'Turu başlat',
        'go'                    => 'Git',
        'skip'                  => 'Atla',
        'close'                 => 'Kapat',
        'dismiss'               => 'Gizle',
        'done'                  => 'Bitti',
        'completed_title'       => 'Her şey hazır',
        'completed_description' => 'Tüm adımlar tamamlandı. Aramıza hoş geldin.',
        'footer_note'           => 'Buna daha sonra devam edebilirsin.',
    ],

    'welcome' => [
        'begin' => 'Başla',
        'later' => 'Şimdi değil',
        'never' => 'Bunu bir daha gösterme',
        'steps' => '{0}Burada henüz bir şey yok|{1}1 adım — kendi hızınızda|[2,*]:count adım — kendi hızınızda',
        'back'  => 'Onboarding\'i yeniden aç',
        'off'   => 'Onboarding kapatıldı. Liste ve ilerleme halkası gitti — bu sayfa kalıyor.',
    ],
    'tour' => [
        'blocked'  => 'Ekran henüz ilerlemedi. Zorunlu bir alan varsa doldurun ve tekrar "İleri"ye basın — tur sizi oraya götürür.',
        'start'    => 'Öğreticiyi izle',
        'choose'   => 'Hangi öğreticiyi istiyorsunuz?',
        'begin'    => 'Başla',
        'waiting'  => 'Sayfada görünmesi bekleniyor — formu doldurmaya devam edin, tur sizi takip eder.',
        'skip'     => 'Atla',
        'previous' => 'Geri',
        'next'     => 'İleri',
        'finish'   => 'Bitir',
    ],

    'page' => [
        'collapse' => 'Bu yolculuğu kapat',
        'expand'   => 'Bu yolculuğu aç',
        'meta'     => [
            'completed' => ':total içinden :count tamamlandı',
            'remaining' => '{1}1 kaldı|[2,*]:count kaldı',
            'skipped'   => '{1}1 atlandı|[2,*]:count atlandı',
        ],
        'open_image'           => 'Görseli aç: :title',
        'title'                => 'Başlarken',
        'subheading'           => 'Neredesiniz ve ne kaldı.',
        'next'                 => 'Sırada',
        'hidden'               => 'Gizli',
        'restore'              => 'Yeniden göster',
        'undo'                 => 'Geri al',
        'replay_tour'          => 'Yeniden izle',
        'replay_video'         => 'Yeniden izle',
        'open_again'           => 'Yeniden aç',
        'restart'              => 'Baştan başla',
        'restarted'            => 'Yolculuk yeniden başladı',
        'restarted_reinstated' => ':count adım doğrudan geri geldi: arkalarındaki iş bittiği için tamamlanmış sayılıyorlar.',
        'restart_confirm'      => 'Bu yolculuk yeniden başlatılsın mı? İşaretlediğiniz, atladığınız ve izlediğiniz her şey silinecek.',
        'restart_note'         => 'Kendiliğinden tamamlanan adımlar doğrudan geri gelir: bu düğmeye değil, uygulamaya hesap verirler.',

        'stats' => [
            'completed' => 'Tamamlanan',
            'remaining' => 'Kalan',
            'skipped'   => 'Atlanan',
        ],

        'status' => [
            'completed' => 'Tamamlandı',
            'skipped'   => 'Atlandı',
            'next'      => 'Sırada',
            'pending'   => 'Yapılacak',
        ],

        'completed_at'       => ':time tarihinde tamamlandı',
        'tour_progress'      => ':total duraktan :reached. sırada',
        'awaiting_condition' => 'Kendiliğinden tamamlanır',
        'empty_title'        => 'Onboarding edilecek bir şey yok',
        'empty_description'  => 'Şu anda sizin için bir yolculuk yok.',
    ],

    'media' => [
        'watch'   => 'İzle',
        'resume'  => 'Devam et',
        'watched' => 'izlendi',
    ],

    'enums' => [

        'condition_type' => [
            'aggregate' => [
                'label'       => 'Sahip olduklarını sayar',
                'description' => 'Müşteriler, faturalar, sunucular — onlara ait her şey. "En az bir müşteri."',
            ],
            'attribute' => [
                'label'       => 'Kendileri hakkında sorar',
                'description' => 'Kullanıcının kendi sütunu. "E-posta adresi doğrulanmış."',
            ],
        ],

        'condition_operator' => [
            'equals'                => 'eşittir',
            'not_equals'            => 'eşit değildir',
            'greater_than'          => 'daha büyüktür',
            'greater_than_or_equal' => 'en az',
            'less_than'             => 'daha küçüktür',
            'less_than_or_equal'    => 'en fazla',
            'contains'              => 'içerir',
            'is_set'                => 'doldurulmuş',
            'is_empty'              => 'boş',
        ],
        'step_type' => [
            'task' => 'Görev',
            'tour' => 'Tur',
        ],

        'media_type' => [
            'none'  => 'Yok',
            'image' => 'Görsel',
            'video' => 'Video',
        ],

        'media_source' => [
            'upload' => [
                'label'       => 'Yükle',
                'description' => 'Yapılandırılmış depoda saklanır (S3, R2, yerel).',
            ],
            'url' => [
                'label'       => 'Doğrudan URL',
                'description' => 'Başka bir yerde barındırılan dosya.',
            ],
            'youtube' => [
                'label'       => 'YouTube',
                'description' => 'İzlenme süresi takip edilir.',
            ],
            'vimeo' => [
                'label'       => 'Vimeo',
                'description' => 'İzlenme süresi takip edilir.',
            ],
            'embed' => [
                'label'       => 'Başka sağlayıcı (iframe)',
                'description' => 'Oynatır, ancak izlenme süresi takip edilemez.',
            ],
        ],

        'modal_position' => [
            'center'       => 'Ortada',
            'top'          => 'Üstte',
            'bottom'       => 'Altta',
            'top-left'     => 'Sol üstte',
            'top-right'    => 'Sağ üstte',
            'bottom-left'  => 'Sol altta',
            'bottom-right' => 'Sağ altta',
        ],

        'completion_mode' => [
            'manual' => [
                'label'       => 'Manuel',
                'description' => 'Kullanıcı kendisi işaretler.',
            ],
            'condition' => [
                'label'       => 'Koşul',
                'description' => 'Kayıtlı bir kontrol geçtiğinde otomatik tamamlanır.',
            ],
            'visit' => [
                'label'       => 'Sayfa ziyareti',
                'description' => 'Kullanıcı URL\'ye ulaştığında tamamlanır.',
            ],
            'video' => [
                'label'       => 'Videoyu izlemek',
                'description' => 'Adımın videosunun yeterli kısmı izlendiğinde tamamlanır.',
            ],
            'programmatic' => [
                'label'       => 'Programatik',
                'description' => 'Yalnızca uygulama kodu tamamlar.',
            ],
        ],
    ],

    'resource' => [
        'singular'   => 'Onboarding akışı',
        'plural'     => 'Onboarding',
        'all_panels' => 'Tüm paneller',

        'placeholders' => [
            'flow_title'       => '… ile başlangıç',
            'flow_description' => 'Kullanıcının bundan ne kazandığına dair tek satır.',
            'step_title'       => 'İlk sunucunuzu bağlayın',
            'step_description' => 'Bir-iki satır. Ne olduğunu değil, neden olduğunu söyleyin.',
        ],

        'no_steps_warning' => 'Bu yolculuğun adımı yok, bu yüzden kimse görmüyor.',

        'empty' => [
            'heading'     => 'Henüz yolculuk yok',
            'description' => 'Yolculuk, kullanıcılarınızın izlediği bir listedir. Bir tane oluşturun ve adımlarını ekleyin.',
        ],

        'sections' => [
            'overview'                => 'Genel bakış',
            'publishing_description'  => 'Bu yolculuğu kim, nerede ve hangi sırayla görür.',
            'appearance_description'  => 'Yolculuk listede nasıl duyurulur.',
            'content_description'     => 'Kullanıcının desteklediğiniz her dilde okuduğu metin.',
            'publishing'              => 'Yayın',
            'appearance'              => 'Görünüm',
            'rules'                   => 'Kurallar',
            'destination'             => 'Hedef',
            'destination_description' => 'Düğme kullanıcıyı nereye götürür. Panelden bir sayfa seçin; URL yedektir.',
            'content'                 => 'İçerik',
            'settings'                => 'Ayarlar',
            'behaviour'               => 'Davranış',
            'media'                   => 'Medya',
            'media_description'       => 'Gösterilecek bir görsel veya izlenecek bir video. Panelin üstünde bir modalda açılır.',
            'tour'                    => 'Tur',
            'tour_description'        => 'Bu turun sırayla vurguladığı öğeler.',
        ],

        'steps' => [
            'title'             => 'Adımlar',
            'empty_heading'     => 'Henüz adım yok',
            'empty_description' => 'İlk adımı ekleyin. Adım olmadan bu yolculuk kimseye görünmez.',
        ],

        'fields' => [
            'title'                 => 'Başlık',
            'description'           => 'Açıklama',
            'key'                   => 'Anahtar',
            'key_helper'            => 'Kodda kullanılır. Mevcut ilerlemeyi bozmadan değiştirilemez.',
            'step_key_helper'       => 'Akış içinde benzersizdir. Onboarding::for($user)->complete(...) kullanır.',
            'panel'                 => 'Panel',
            'panel_helper'          => 'Akışı tüm panellerde göstermek için boş bırakın.',
            'icon'                  => 'Simge',
            'color'                 => 'Renk',
            'sort_order'            => 'Sıra',
            'is_active'             => 'Etkin',
            'is_dismissible'        => 'Kapatılabilir',
            'is_dismissible_helper' => 'Kullanıcının listeyi kalıcı olarak gizlemesine izin verir.',
            'steps'                 => 'Adımlar',
            'updated_at'            => 'Güncellenme',
            'type'                  => 'Tür',
            'completion_mode'       => 'Tamamlayan',
            'condition'             => 'Koşul',
            'condition_helper'      => 'Uygulama tarafından kaydedilir. Zaten karşılanmış adımlar tamamlanmış olarak geri gelir.',
            'condition_none'        => 'Henüz koşul yok — Onboarding → Koşullar altında bir tane yazın (ya da php artisan make:onboarding-condition çalıştırın), burada görünür.',
            'visit_url'             => 'URL',
            'visit_url_helper'      => '* joker karakterini destekler: /app/*/servers/create',
            'cta_label'             => 'Düğme metni',
            'cta_url'               => 'Özel URL',
            'cta_url_helper'        => 'Yalnızca hedef listede yoksa. {tenant} otomatik doldurulur.',
            'cta_route'             => 'Hedef',
            'cta_route_helper'      => 'Panelin bir sayfası. URL\'ye göre tercih edilir: slug yeniden adlandırılsa da çalışır.',
            'is_required'           => 'Zorunlu',
            'is_required_helper'    => 'İsteğe bağlı adımlar atlanabilir.',
            'visibility'            => 'Ne zaman görünür',
            'visibility_helper'     => 'Yalnızca bu koşulu geçen kişiler görür. Bir adımı bir planın veya özelliğin arkasına almak için kullanın.',
            'visibility_everyone'   => 'Herkes',

            'media_type'             => 'Medya',
            'media_source'           => 'Kaynak',
            'media_file'             => 'Dosya',
            'media_url'              => 'URL',
            'media_url_helper'       => 'Bağlantıyı olduğu gibi yapıştırın: watch, share veya kısa URL\'ler çalışır.',
            'media_caption'          => 'Alt başlık',
            'modal_position'         => 'Modal konumu',
            'modal_position_helper'  => 'Köşedeki bir modal, sayfayı arkasından kullanılabilir bırakır.',
            'modal_position_default' => 'Panel varsayılanı',
            'video_threshold'        => 'Şu anda izlenmiş sayılır',
            'video_threshold_helper' => 'Adımı tamamlayan yüzde, izleyerek tamamlanıyorsa.',
            'is_active_helper'       => 'Etkin olmayan yolculuklar tüm panellerden kaybolur.',
            'sort_order_helper'      => 'Küçük olan önce gelir.',
            'type_helper'            => 'Görev yapılır; tur gezilir.',
            'completion_mode_helper' => 'Bu adımı tamamlanmış sayan şey.',
        ],

        'tour' => [
            'optional'        => 'Yoksa atla',
            'optional_helper' => 'Henüz var olmayabilecek bir şey için — boş bir tablodaki etiket, verisiz bir grafik. Tur beklerken ilerler.',
            'advance'         => 'İlerleme kontrolü',
            'advance_helper'  => 'Uygulamayı bu durağa taşıyan kontrol — bir sihirbazın "ileri" düğmesi, bir sekme. Kullanıcı ilerlediğinde ve öğe henüz ekranda olmadığında tıklanır.',
            'add'             => 'Durak ekle',
            'target'          => 'Neyi vurgulayalım',
            'target_helper'   => 'Panelin kendisinden okunur — bu sayfanın form alanları, düğmeleri, tablosu. Bazı formlar dışarıdan okunamaz (düzenlenen kayda veya bakan kişiye göre çalışanlar): alanları listelenmez ve CSS seçicisi yolunu açır.',
            'targets'         => [
                'on_this_page' => 'Bu sayfada',
                'widgets'      => 'Widget\'lar',
                'advanced'     => 'Gelişmiş',
                'custom'       => 'Kendi CSS seçicim…',
                'table'        => 'Tablo',
                'search'       => 'Arama kutusu',
                'submit'       => 'Kaydet düğmesi',
                'column'       => 'Sütun: :label',
                'new_record'   => '"Yeni :label" düğmesi',
            ],
            'selector'        => 'CSS seçicisi',
            'selector_helper' => 'Vurgulanacak öğe, örn. [data-onboarding="create-server"]. Widget seçildiğinde boş bırakın.',
            'widget'          => 'Widget',
            'widget_helper'   => 'Panelin bir widget\'ı. Sayfada kendiliğinden bulunur — seçici gerekmez.',
            'placement'       => 'Yerleşim',
            'placements'      => [
                'auto'   => 'Otomatik',
                'top'    => 'Üstte',
                'bottom' => 'Altta',
            ],
            'route'             => 'Sayfa',
            'route_helper'      => 'Yalnızca bu durak başka bir yerdeyse. Tur oraya gidip devam eder.',
            'url'               => 'Özel URL',
            'url_helper'        => 'Yalnızca sayfa listede yoksa.',
            'body'              => 'Metin',
            'visibility'        => 'Ne zaman görünür',
            'visibility_helper' => 'Bu durağı herkese göstermek için boş bırakın. Planın desteklemediği bir özelliğe işaret eden bir durak hiçbir şeyi vurgulamaz.',
        ],

        'targets' => [
            'resources'      => 'Kaynaklar',
            'pages'          => 'Sayfalar',
            'tenant_profile' => 'Tenant profili',
            'page_names'     => [
                'index'  => 'liste',
                'create' => 'oluştur',
            ],
        ],
    ],

    'conditions' => [
        'singular'   => 'Koşul',
        'plural'     => 'Koşullar',
        'subheading' => 'Adımlarınızın biri hakkında sorabileceği sorular — burada, kodda değil.',
        'at_least'   => 'en az :count',

        'sections' => [
            'question'             => 'Soru',
            'question_description' => 'Adımın tamamlanması için biri hakkında ne doğru olmalı.',
            'naming'               => 'Adı ne',
            'naming_description'   => 'Adım yazan kişinin açılır listeden seçtiği ad.',
        ],

        'fields' => [
            'type'                     => 'Neyi soruyor',
            'model'                    => 'Ne sayıyor',
            'model_helper'             => 'Sahip olmaları gereken şey: müşteriler, faturalar, sunucular.',
            'minimum'                  => 'En az',
            'minimum_helper'           => 'Kaç tane. Neredeyse her zaman bir.',
            'subject_column'           => 'Buna ait olması için',
            'subject_column_helper'    => 'Onboarding yapılan kişinin id\'sini tutan sütun.',
            'scope_column'             => 'Tenant\'a ait olması için ise',
            'scope_column_helper'      => 'Yalnızca model bir tenant\'a göre kapsandığında. Değilse boş bırakın.',
            'scope_column_none'        => 'Bir tenant\'a göre kapsanmıyor',
            'filters'                  => 'Yalnızca şunlarda…',
            'filters_helper'           => 'İsteğe bağlı. Hiçbiri yoksa hepsi sayılır.',
            'filters_attribute'        => 'Şu zaman doğru…',
            'filters_attribute_helper' => 'Bunların tümü geçerli olmalı.',
            'add_filter'               => 'Kural ekle',
            'column'                   => 'Alan',
            'operator'                 => 'Şudur',
            'value'                    => 'Değer',
            'label'                    => 'Ad',
            'label_helper'             => 'Adım düzenleyicide adı ne.',
            'key_helper'               => 'Adımlar buna nasıl atıfta bulunur. Adımlar kullandıktan sonra değiştirilemez.',
            'key_taken'                => '[:key] anahtarı kodda kayıtlı bir koşula ait.',
            'is_active_helper'         => 'Etkin olmayan bir koşul asla geçmez.',
            'question'                 => 'Soruyor',
        ],

        'placeholders' => [
            'label' => 'Bir müşteri ekledi',
        ],

        'empty' => [
            'heading'     => 'Henüz koşul yok',
            'description' => 'Koşul, bir adımın kendini tamamlayabilmesini sağlayan şeydir — bunu çok önce yapmış kişiler için de. Burada bir tane yazın ya da bir formun soramayacağı bir soru için php artisan make:onboarding-condition çalıştırın.',
        ],
    ],

];
