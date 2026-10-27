<?php

declare(strict_types = 1);

return [

    'checklist' => [
        'mark_done'             => 'Als erledigt markieren',
        'start_tour'            => 'Tour starten',
        'go'                    => 'Los',
        'skip'                  => 'Überspringen',
        'close'                 => 'Schließen',
        'dismiss'               => 'Ausblenden',
        'done'                  => 'Fertig',
        'completed_title'       => 'Alles bereit',
        'completed_description' => 'Alle Schritte sind erledigt. Willkommen.',
        'footer_note'           => 'Sie können das später fortsetzen.',
    ],

    'welcome' => [
        'begin' => 'Loslegen',
        'later' => 'Jetzt nicht',
        'never' => 'Nicht mehr anzeigen',
        'steps' => '{0}Noch nichts vorhanden|{1}1 Schritt — in Ihrem Tempo|[2,*]:count Schritte — in Ihrem Tempo',
        'back'  => 'Onboarding wieder einschalten',
        'off'   => 'Sie haben das Onboarding abgeschaltet. Checkliste und Fortschrittsring sind weg — diese Seite bleibt.',
    ],
    'tour' => [
        'blocked'  => 'Der Bildschirm ist noch nicht weitergekommen. Falls etwas Pflicht ist, füllen Sie es aus und drücken Sie erneut auf „Weiter" — die Tour folgt Ihnen dorthin.',
        'start'    => 'Anleitung ansehen',
        'choose'   => 'Welche Anleitung möchten Sie?',
        'begin'    => 'Starten',
        'waiting'  => 'Wartet darauf, auf der Seite zu erscheinen — füllen Sie das Formular weiter aus, die Tour folgt Ihnen.',
        'skip'     => 'Überspringen',
        'previous' => 'Zurück',
        'next'     => 'Weiter',
        'finish'   => 'Fertigstellen',
    ],

    'page' => [
        'collapse' => 'Diesen Weg einklappen',
        'expand'   => 'Diesen Weg aufklappen',
        'meta'     => [
            'completed' => ':count von :total erledigt',
            'remaining' => '{1}1 übrig|[2,*]:count übrig',
            'skipped'   => '{1}1 übersprungen|[2,*]:count übersprungen',
        ],
        'open_image'           => 'Bild öffnen: :title',
        'title'                => 'Erste Schritte',
        'subheading'           => 'Wo Sie stehen und was übrig ist.',
        'next'                 => 'Als Nächstes',
        'hidden'               => 'Ausgeblendet',
        'restore'              => 'Wieder anzeigen',
        'undo'                 => 'Rückgängig',
        'replay_tour'          => 'Nochmal ansehen',
        'replay_video'         => 'Nochmal ansehen',
        'open_again'           => 'Nochmal öffnen',
        'restart'              => 'Neu beginnen',
        'restarted'            => 'Weg neu gestartet',
        'restarted_reinstated' => ':count Schritt(e) sind direkt zurückgekommen: Sie gelten als erledigt, weil die Arbeit dahinter erledigt ist.',
        'restart_confirm'      => 'Diesen Weg neu beginnen? Was Sie abgehakt, übersprungen und angesehen haben, wird gelöscht.',
        'restart_note'         => 'Schritte, die sich von selbst erledigen, kommen direkt zurück: Sie antworten der App, nicht diesem Button.',

        'stats' => [
            'completed' => 'Erledigt',
            'remaining' => 'Übrig',
            'skipped'   => 'Übersprungen',
        ],

        'status' => [
            'completed' => 'Erledigt',
            'skipped'   => 'Übersprungen',
            'next'      => 'Als Nächstes',
            'pending'   => 'Zu tun',
        ],

        'completed_at'       => 'Erledigt :time',
        'tour_progress'      => 'Halt :reached von :total',
        'awaiting_condition' => 'Ergibt sich von selbst',
        'empty_title'        => 'Nichts zum Onboarding',
        'empty_description'  => 'Für Sie gibt es derzeit keinen Weg.',
    ],

    'media' => [
        'watch'   => 'Ansehen',
        'resume'  => 'Fortsetzen',
        'watched' => 'angesehen',
    ],

    'enums' => [

        'condition_type' => [
            'aggregate' => [
                'label'       => 'Zählt, was sie haben',
                'description' => 'Kunden, Rechnungen, Server — alles, was ihnen gehört. „Mindestens ein Kunde."',
            ],
            'attribute' => [
                'label'       => 'Fragt nach ihnen selbst',
                'description' => 'Eine Spalte direkt am Benutzer. „Ihre E-Mail ist bestätigt."',
            ],
        ],

        'condition_operator' => [
            'equals'                => 'ist',
            'not_equals'            => 'ist nicht',
            'greater_than'          => 'ist größer als',
            'greater_than_or_equal' => 'ist mindestens',
            'less_than'             => 'ist kleiner als',
            'less_than_or_equal'    => 'ist höchstens',
            'contains'              => 'enthält',
            'is_set'                => 'ist ausgefüllt',
            'is_empty'              => 'ist leer',
        ],
        'step_type' => [
            'task' => 'Aufgabe',
            'tour' => 'Tour',
        ],

        'media_type' => [
            'none'  => 'Keins',
            'image' => 'Bild',
            'video' => 'Video',
        ],

        'media_source' => [
            'upload' => [
                'label'       => 'Hochladen',
                'description' => 'Auf dem konfigurierten Speicher abgelegt (S3, R2, lokal).',
            ],
            'url' => [
                'label'       => 'Direkte URL',
                'description' => 'Eine anderswo gehostete Datei.',
            ],
            'youtube' => [
                'label'       => 'YouTube',
                'description' => 'Die Wiedergabezeit wird erfasst.',
            ],
            'vimeo' => [
                'label'       => 'Vimeo',
                'description' => 'Die Wiedergabezeit wird erfasst.',
            ],
            'embed' => [
                'label'       => 'Anbieter sonst (iframe)',
                'description' => 'Läuft ab, aber die Wiedergabezeit lässt sich nicht erfassen.',
            ],
        ],

        'modal_position' => [
            'center'       => 'Mitte',
            'top'          => 'Oben',
            'bottom'       => 'Unten',
            'top-left'     => 'Oben links',
            'top-right'    => 'Oben rechts',
            'bottom-left'  => 'Unten links',
            'bottom-right' => 'Unten rechts',
        ],

        'completion_mode' => [
            'manual' => [
                'label'       => 'Manuell',
                'description' => 'Der Benutzer hakt es selbst ab.',
            ],
            'condition' => [
                'label'       => 'Bedingung',
                'description' => 'Wird automatisch erledigt, sobald eine registrierte Prüfung besteht.',
            ],
            'visit' => [
                'label'       => 'Seitenbesuch',
                'description' => 'Wird erledigt, sobald der Benutzer eine URL erreicht.',
            ],
            'video' => [
                'label'       => 'Video ansehen',
                'description' => 'Wird erledigt, sobald genug des Videos angesehen wurde.',
            ],
            'programmatic' => [
                'label'       => 'Programmatisch',
                'description' => 'Nur Anwendungscode erledigt es.',
            ],
        ],
    ],

    'resource' => [
        'singular'   => 'Onboarding-Ablauf',
        'plural'     => 'Onboarding',
        'all_panels' => 'Alle Panels',

        'placeholders' => [
            'flow_title'       => 'Erste Schritte mit …',
            'flow_description' => 'Eine Zeile dazu, was der Benutzer davon bekommt.',
            'step_title'       => 'Verbinden Sie Ihren ersten Server',
            'step_description' => 'Ein oder zwei Zeilen. Sagen Sie warum, nicht nur was.',
        ],

        'no_steps_warning' => 'Dieser Weg hat keine Schritte, also sieht ihn niemand.',

        'empty' => [
            'heading'     => 'Noch keine Wege',
            'description' => 'Ein Weg ist eine Checkliste, durch die Ihre Benutzer gehen. Erstellen Sie einen und fügen Sie seine Schritte hinzu.',
        ],

        'sections' => [
            'overview'                => 'Überblick',
            'publishing_description'  => 'Wer diesen Weg sieht, wo und in welcher Reihenfolge.',
            'appearance_description'  => 'Wie der Weg in der Checkliste angekündigt wird.',
            'content_description'     => 'Was der Benutzer liest — in jeder Sprache, die Sie unterstützen.',
            'publishing'              => 'Veröffentlichung',
            'appearance'              => 'Erscheinungsbild',
            'rules'                   => 'Regeln',
            'destination'             => 'Ziel',
            'destination_description' => 'Wohin die Schaltfläche führt. Wählen Sie eine Seite des Panels; eine URL ist der Rückfall.',
            'content'                 => 'Inhalt',
            'settings'                => 'Einstellungen',
            'behaviour'               => 'Verhalten',
            'media'                   => 'Medien',
            'media_description'       => 'Ein Bild zum Anzeigen oder ein Video zum Ansehen. Öffnet sich in einem Modal über dem Panel.',
            'tour'                    => 'Tour',
            'tour_description'        => 'Die Elemente, die diese Tour der Reihe nach hervorhebt.',
        ],

        'steps' => [
            'title'             => 'Schritte',
            'empty_heading'     => 'Noch keine Schritte',
            'empty_description' => 'Fügen Sie den ersten Schritt hinzu. Ohne Schritte sieht diesen Weg niemand.',
        ],

        'fields' => [
            'title'                 => 'Titel',
            'description'           => 'Beschreibung',
            'key'                   => 'Schlüssel',
            'key_helper'            => 'Wird im Code verwendet. Lässt sich nicht ändern, ohne bestehenden Fortschritt zu zerstören.',
            'step_key_helper'       => 'Eindeutig innerhalb des Ablaufs. Wird von Onboarding::for($user)->complete(...) verwendet.',
            'panel'                 => 'Panel',
            'panel_helper'          => 'Leer lassen, um den Ablauf in jedem Panel zu zeigen.',
            'icon'                  => 'Symbol',
            'color'                 => 'Farbe',
            'sort_order'            => 'Reihenfolge',
            'is_active'             => 'Aktiv',
            'is_dismissible'        => 'Ausblendbar',
            'is_dismissible_helper' => 'Erlaubt dem Benutzer, die Checkliste dauerhaft auszublenden.',
            'steps'                 => 'Schritte',
            'updated_at'            => 'Aktualisiert am',
            'type'                  => 'Typ',
            'completion_mode'       => 'Erledigt durch',
            'condition'             => 'Bedingung',
            'condition_helper'      => 'Von der Anwendung registriert. Bereits erfüllte Schritte kommen als erledigt zurück.',
            'condition_none'        => 'Noch keine Bedingungen — schreiben Sie eine unter Onboarding → Bedingungen (oder führen Sie php artisan make:onboarding-condition aus), dann erscheint sie hier.',
            'visit_url'             => 'URL',
            'visit_url_helper'      => 'Unterstützt den Platzhalter *: /app/*/servers/create',
            'cta_label'             => 'Beschriftung der Schaltfläche',
            'cta_url'               => 'Eigene URL',
            'cta_url_helper'        => 'Nur wenn das Ziel nicht in der Liste steht. {tenant} wird ausgefüllt.',
            'cta_route'             => 'Ziel',
            'cta_route_helper'      => 'Eine Seite des Panels. Einer URL vorzuziehen: übersteht eine umbenannte Slug.',
            'is_required'           => 'Pflicht',
            'is_required_helper'    => 'Optionale Schritte können übersprungen werden.',
            'visibility'            => 'Sichtbar, wenn',
            'visibility_helper'     => 'Nur wen sehen es, die diese Bedingung besteht. Damit können Sie einen Schritt hinter einem Tarif oder einer Funktion verstecken.',
            'visibility_everyone'   => 'Alle',

            'media_type'             => 'Medien',
            'media_source'           => 'Quelle',
            'media_file'             => 'Datei',
            'media_url'              => 'URL',
            'media_url_helper'       => 'Fügen Sie den Link so ein, wie er ist: watch-, share- oder Kurz-URLs funktionieren alle.',
            'media_caption'          => 'Bildunterschrift',
            'modal_position'         => 'Position des Modals',
            'modal_position_helper'  => 'Ein Modal in einer Ecke lässt die Seite dahinter bedienbar.',
            'modal_position_default' => 'Standard des Panels',
            'video_threshold'        => 'Gilt als angesehen bei',
            'video_threshold_helper' => 'Prozentsatz, der den Schritt erledigt, wenn er durch Zusehen erledigt wird.',
            'is_active_helper'       => 'Inaktive Wege verschwinden aus allen Panels.',
            'sort_order_helper'      => 'Kleineres kommt zuerst.',
            'type_helper'            => 'Eine Aufgabe wird erledigt; eine Tour wird gegangen.',
            'completion_mode_helper' => 'Was diesen Schritt als erledigt markiert.',
        ],

        'tour' => [
            'optional'        => 'Überspringen, wenn es fehlt',
            'optional_helper' => 'Für etwas, das es noch nicht geben muss — ein Tag auf einer leeren Tabelle, ein Diagramm ohne Daten. Die Tour geht weiter, statt zu warten.',
            'advance'         => 'Weiter mit',
            'advance_helper'  => 'Das Bedienelement, das die Anwendung zu diesem Halt bringt — die Weiter-Schaltfläche eines Assistenten, ein Tab. Wird geklickt, wenn der Benutzer fortschreitet und das Element noch nicht auf dem Bildschirm ist.',
            'add'             => 'Halt hinzufügen',
            'target'          => 'Was hervorheben',
            'target_helper'   => 'Wird aus dem Panel selbst gelesen — die Felder des Formulars dieser Seite, ihre Schaltflächen, ihre Tabelle. Manche Formulare lassen sich von außen nicht lesen (solche, die vom bearbeiteten Datensatz oder vom Betrachter abhängen): ihre Felder werden nicht aufgeführt, und ein CSS-Selektor ist der Weg.',
            'targets'         => [
                'on_this_page' => 'Auf dieser Seite',
                'widgets'      => 'Widgets',
                'advanced'     => 'Erweitert',
                'custom'       => 'Ein eigener CSS-Selektor…',
                'table'        => 'Die Tabelle',
                'search'       => 'Das Suchfeld',
                'submit'       => 'Die Speichern-Schaltfläche',
                'column'       => 'Spalte: :label',
                'new_record'   => 'Die Schaltfläche „Neu: :label"',
            ],
            'selector'        => 'CSS-Selektor',
            'selector_helper' => 'Das Element, das hervorgehoben wird, z. B. [data-onboarding="create-server"]. Leer lassen, wenn ein Widget gewählt ist.',
            'widget'          => 'Widget',
            'widget_helper'   => 'Ein Widget des Panels. Findet sich auf der Seite von selbst — kein Selektor nötig.',
            'placement'       => 'Platzierung',
            'placements'      => [
                'auto'   => 'Automatisch',
                'top'    => 'Darüber',
                'bottom' => 'Darunter',
            ],
            'route'             => 'Seite',
            'route_helper'      => 'Nur wenn dieser Halt anderswo liegt. Die Tour navigiert dorthin und macht weiter.',
            'url'               => 'Eigene URL',
            'url_helper'        => 'Nur wenn die Seite nicht in der Liste steht.',
            'body'              => 'Text',
            'visibility'        => 'Sichtbar, wenn',
            'visibility_helper' => 'Leer lassen, um diesen Halt allen zu zeigen. Ein Halt, der auf eine Funktion zeigt, die der Tarif nicht hat, würde nichts hervorheben.',
        ],

        'targets' => [
            'resources'      => 'Ressourcen',
            'pages'          => 'Seiten',
            'tenant_profile' => 'Mandantenprofil',
            'page_names'     => [
                'index'  => 'Liste',
                'create' => 'Erstellen',
            ],
        ],
    ],

    'conditions' => [
        'singular'   => 'Bedingung',
        'plural'     => 'Bedingungen',
        'subheading' => 'Die Fragen, die Ihre Schritte über jemanden stellen können — hier geschrieben, nicht im Code.',
        'at_least'   => 'mindestens :count',

        'sections' => [
            'question'             => 'Die Frage',
            'question_description' => 'Was an jemandem wahr sein muss, damit der Schritt erledigt ist.',
            'naming'               => 'Wie sie heißt',
            'naming_description'   => 'Der Name, den beim Schreiben eines Schritts aus der Auswahlliste gewählt wird.',
        ],

        'fields' => [
            'type'                     => 'Was sie fragt',
            'model'                    => 'Was sie zählt',
            'model_helper'             => 'Was sie haben müssen: Kunden, Rechnungen, Server.',
            'minimum'                  => 'Mindestens',
            'minimum_helper'           => 'Wie viele. Fast immer eins.',
            'subject_column'           => 'Gehört ihnen über',
            'subject_column_helper'    => 'Die Spalte mit der ID der Person, die durch das Onboarding geht.',
            'scope_column'             => 'Und dem Mandanten über',
            'scope_column_helper'      => 'Nur wenn das Modell auf einen Mandanten eingegrenzt ist. Sonst leer lassen.',
            'scope_column_none'        => 'Nicht auf einen Mandanten eingegrenzt',
            'filters'                  => 'Nur die, bei denen…',
            'filters_helper'           => 'Optional. Ohne welche zählen alle.',
            'filters_attribute'        => 'Wahr, wenn…',
            'filters_attribute_helper' => 'Alle diese müssen zutreffen.',
            'add_filter'               => 'Regel hinzufügen',
            'column'                   => 'Feld',
            'operator'                 => 'Ist',
            'value'                    => 'Wert',
            'label'                    => 'Name',
            'label_helper'             => 'Wie sie im Schritt-Editor heißt.',
            'key_helper'               => 'Wie Schritte darauf Bezug nehmen. Lässt sich nicht mehr ändern, sobald Schritte sie nutzen.',
            'key_taken'                => 'Der Schlüssel [:key] gehört zu einer im Code registrierten Bedingung.',
            'is_active_helper'         => 'Eine inaktive Bedingung trifft nie zu.',
            'question'                 => 'Fragt',
        ],

        'placeholders' => [
            'label' => 'Hat einen Kunden hinzugefügt',
        ],

        'empty' => [
            'heading'     => 'Noch keine Bedingungen',
            'description' => 'Eine Bedingung ist das, was einen Schritt sich selbst erledigen lässt — auch für Menschen, die das vor Langem getan haben. Schreiben Sie hier eine oder führen Sie php artisan make:onboarding-condition für eine Frage aus, die ein Formular nicht stellen kann.',
        ],
    ],

];
