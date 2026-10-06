<?php

declare(strict_types = 1);

return [

    'checklist' => [
        'mark_done'             => 'Oznacz jako wykonane',
        'start_tour'            => 'Rozpocznij tour',
        'go'                    => 'Przejdź',
        'skip'                  => 'Pomiń',
        'close'                 => 'Zamknij',
        'dismiss'               => 'Ukryj',
        'done'                  => 'Gotowe',
        'restart'               => 'Zacznij od nowa',
        'restart_confirm'       => 'To zresetuje wszystkie ukończone kroki i wycieczki — zacząć od nowa?',
        'completed_title'       => 'Wszystko gotowe',
        'completed_description' => 'Wszystkie kroki wykonane. Witamy.',
        'footer_note'           => 'Możesz do tego wrócić później.',
    ],

    'welcome' => [
        'begin' => 'Zacznij',
        'later' => 'Nie teraz',
        'never' => 'Nie pokazuj więcej',
        'steps' => '{0}Na razie nic tu nie ma|{1}1 krok — w Twoim tempie|[2,4]:count kroki — w Twoim tempie|[5,*]:count kroków — w Twoim tempie',
        'back'  => 'Włącz onboarding z powrotem',
        'off'   => 'Wyłączyłeś onboarding. Lista i pierścień postępu zniknęły — ale ta strona zostaje.',
    ],
    'tour' => [
        'blocked'  => 'Ekran nie przeszedł jeszcze dalej. Jeśli coś jest wymagane, wypełnij to i ponownie naciśnij „Dalej" — tour podąży za Tobą.',
        'start'    => 'Zobacz samouczek',
        'choose'   => 'Który samouczek chcesz zobaczyć?',
        'begin'    => 'Zacznij',
        'waiting'  => 'Czeka, aż pojawi się na stronie — wypełniaj formularz, tour podąży za Tobą.',
        'skip'     => 'Pomiń',
        'previous' => 'Wstecz',
        'next'     => 'Dalej',
        'finish'   => 'Zakończ',
    ],

    'page' => [
        'collapse' => 'Zwiń tę ścieżkę',
        'expand'   => 'Rozwiń tę ścieżkę',
        'meta'     => [
            'completed' => 'Wykonano :count z :total',
            'remaining' => '{1}Pozostał 1|[2,4]Pozostały :count|[5,*]Pozostało :count',
            'skipped'   => '{1}Pominięty 1|[2,4]Pominięte :count|[5,*]Pominięto :count',
        ],
        'open_image'           => 'Otwórz obraz: :title',
        'title'                => 'Pierwsze kroki',
        'subheading'           => 'Gdzie jesteś i co zostało.',
        'next'                 => 'Następne',
        'hidden'               => 'Ukryte',
        'restore'              => 'Pokaż znowu',
        'undo'                 => 'Cofnij',
        'replay_tour'          => 'Obejrzyj ponownie',
        'replay_video'         => 'Obejrzyj ponownie',
        'open_again'           => 'Otwórz ponownie',
        'restart'              => 'Zacznij od nowa',
        'restarted'            => 'Ścieżka zrestartowana',
        'restarted_reinstated' => 'Kroki wróciły od razu: :count — są wykonane, bo praca za nimi jest zrobiona.',
        'restart_confirm'      => 'Zacząć tę ścieżkę od nowa? To, co odhaczyłeś, pominąłeś i obejrzałeś, zostanie wyczyszczone.',
        'restart_note'         => 'Kroki kończące się same wracają od razu: odpowiadają aplikacji, nie temu przyciskowi.',

        'stats' => [
            'completed' => 'Wykonane',
            'remaining' => 'Pozostałe',
            'skipped'   => 'Pominięte',
        ],

        'status' => [
            'completed' => 'Wykonany',
            'skipped'   => 'Pominięty',
            'next'      => 'Następny',
            'pending'   => 'Do zrobienia',
        ],

        'completed_at'       => 'Wykonano :time',
        'tour_progress'      => 'Przystanek :reached z :total',
        'awaiting_condition' => 'Kończy się sam',
        'empty_title'        => 'Nie ma czego onboardować',
        'empty_description'  => 'Nie masz teraz żadnej ścieżki.',
    ],

    'media' => [
        'watch'   => 'Obejrzyj',
        'resume'  => 'Wznów',
        'watched' => 'obejrzane',
    ],

    'enums' => [

        'condition_type' => [
            'aggregate' => [
                'label'       => 'Liczy to, co mają',
                'description' => 'Klienci, faktury, serwery — wszystko, co mają. „Co najmniej jeden klient".',
            ],
            'attribute' => [
                'label'       => 'Pyta o nich samych',
                'description' => 'Kolumna w samym użytkowniku. „Ich e-mail jest zweryfikowany".',
            ],
        ],

        'condition_operator' => [
            'equals'                => 'jest',
            'not_equals'            => 'nie jest',
            'greater_than'          => 'jest większe niż',
            'greater_than_or_equal' => 'jest co najmniej',
            'less_than'             => 'jest mniejsze niż',
            'less_than_or_equal'    => 'jest najwyżej',
            'contains'              => 'zawiera',
            'is_set'                => 'jest wypełnione',
            'is_empty'              => 'jest puste',
        ],
        'step_type' => [
            'task' => 'Zadanie',
            'tour' => 'Tour',
        ],

        'media_type' => [
            'none'  => 'Brak',
            'image' => 'Obraz',
            'video' => 'Wideo',
        ],

        'media_source' => [
            'upload' => [
                'label'       => 'Prześlij',
                'description' => 'Przechowywane na skonfigurowanym dysku (S3, R2, lokalnym).',
            ],
            'url' => [
                'label'       => 'Bezpośredni URL',
                'description' => 'Plik hostowany gdzie indziej.',
            ],
            'youtube' => [
                'label'       => 'YouTube',
                'description' => 'Czas oglądania jest mierzony.',
            ],
            'vimeo' => [
                'label'       => 'Vimeo',
                'description' => 'Czas oglądania jest mierzony.',
            ],
            'embed' => [
                'label'       => 'Inny dostawca (iframe)',
                'description' => 'Odtwarza się, ale czasu oglądania nie da się zmierzyć.',
            ],
        ],

        'modal_position' => [
            'center'       => 'Na środku',
            'top'          => 'U góry',
            'bottom'       => 'Na dole',
            'top-left'     => 'U góry po lewej',
            'top-right'    => 'U góry po prawej',
            'bottom-left'  => 'Na dole po lewej',
            'bottom-right' => 'Na dole po prawej',
        ],

        'completion_mode' => [
            'manual' => [
                'label'       => 'Ręcznie',
                'description' => 'Użytkownik odhacza je sam.',
            ],
            'condition' => [
                'label'       => 'Warunek',
                'description' => 'Kończy się automatycznie, gdy zarejestrowany test przejdzie.',
            ],
            'visit' => [
                'label'       => 'Odwiedzenie strony',
                'description' => 'Kończy się, gdy użytkownik otworzy adres URL.',
            ],
            'video' => [
                'label'       => 'Obejrzenie wideo',
                'description' => 'Kończy się, gdy obejrzano wystarczającą część wideo kroku.',
            ],
            'programmatic' => [
                'label'       => 'Programowo',
                'description' => 'Kończy je wyłącznie kod aplikacji.',
            ],
        ],
    ],

    'resource' => [
        'singular'   => 'Przebieg onboardingu',
        'plural'     => 'Onboarding',
        'all_panels' => 'Wszystkie panele',

        'placeholders' => [
            'flow_title'       => 'Pierwsze kroki z …',
            'flow_description' => 'Jedno zdanie o tym, co użytkownik z tego zyska.',
            'step_title'       => 'Podłącz swój pierwszy serwer',
            'step_description' => 'Jedno-dwa zdania. Powiedz dlaczego, nie tylko co.',
        ],

        'no_steps_warning' => 'Ta ścieżka nie ma kroków, więc nikt jej nie zobaczy.',

        'empty' => [
            'heading'     => 'Nie ma jeszcze ścieżek',
            'description' => 'Ścieżka to lista, przez którą przechodzą Twoi użytkownicy. Utwórz jedną i dodaj jej kroki.',
        ],

        'sections' => [
            'overview'                => 'Przegląd',
            'publishing_description'  => 'Kto widzi tę ścieżkę, gdzie i w jakiej kolejności.',
            'appearance_description'  => 'Jak ścieżka jest zapowiadana na liście.',
            'content_description'     => 'Co czyta użytkownik — w każdym języku, który obsługujesz.',
            'publishing'              => 'Publikowanie',
            'appearance'              => 'Wygląd',
            'rules'                   => 'Zasady',
            'destination'             => 'Cel',
            'destination_description' => 'Dokąd prowadzi przycisk. Wybierz stronę panelu; URL jest rozwiązaniem awaryjnym.',
            'content'                 => 'Treść',
            'settings'                => 'Ustawienia',
            'behaviour'               => 'Zachowanie',
            'media'                   => 'Media',
            'media_description'       => 'Obraz do pokazania albo wideo do obejrzenia. Otwiera się w oknie modalnym nad panelem.',
            'tour'                    => 'Tour',
            'tour_description'        => 'Elementy, które ten tour podświetla, po kolei.',
        ],

        'steps' => [
            'title'             => 'Kroki',
            'empty_heading'     => 'Nie ma jeszcze kroków',
            'empty_description' => 'Dodaj pierwszy krok. Bez kroków tej ścieżki nie zobaczy nikt.',
        ],

        'fields' => [
            'title'                 => 'Tytuł',
            'description'           => 'Opis',
            'key'                   => 'Klucz',
            'key_helper'            => 'Używany w kodzie. Nie można go zmienić bez zepsucia istniejącego postępu.',
            'step_key_helper'       => 'Unikalny w obrębie przebiegu. Używany przez Onboarding::for($user)->complete(...).',
            'panel'                 => 'Panel',
            'panel_helper'          => 'Zostaw puste, aby pokazać przebieg we wszystkich panelach.',
            'icon'                  => 'Ikona',
            'color'                 => 'Kolor',
            'sort_order'            => 'Kolejność',
            'is_active'             => 'Aktywny',
            'is_dismissible'        => 'Można ukryć',
            'is_dismissible_helper' => 'Pozwala użytkownikowi na stałe ukryć listę.',
            'steps'                 => 'Kroki',
            'updated_at'            => 'Zaktualizowano',
            'type'                  => 'Typ',
            'completion_mode'       => 'Kończy się przez',
            'condition'             => 'Warunek',
            'condition_helper'      => 'Rejestrowany przez aplikację. Już spełnione kroki wracają jako wykonane.',
            'condition_none'        => 'Nie ma jeszcze warunków — napisz jeden w sekcji Onboarding → Warunki (lub uruchom php artisan make:onboarding-condition), a pojawi się tutaj.',
            'visit_url'             => 'URL',
            'visit_url_helper'      => 'Obsługuje symbol wieloznaczny *: /app/*/servers/create',
            'cta_label'             => 'Etykieta przycisku',
            'cta_url'               => 'Własny URL',
            'cta_url_helper'        => 'Tylko gdy celu nie ma na liście. {tenant} jest uzupełniane.',
            'cta_route'             => 'Cel',
            'cta_route_helper'      => 'Strona panelu. Lepsze niż URL: przetrwa zmianę nazwy sluga.',
            'is_required'           => 'Wymagany',
            'is_required_helper'    => 'Kroki opcjonalne można pominąć.',
            'visibility'            => 'Widoczny, gdy',
            'visibility_helper'     => 'Widzą go tylko ci, którzy spełniają ten warunek. Użyj, aby zamknąć krok za planem lub funkcją.',
            'visibility_everyone'   => 'Wszyscy',

            'media_type'             => 'Media',
            'media_source'           => 'Źródło',
            'media_file'             => 'Plik',
            'media_url'              => 'URL',
            'media_url_helper'       => 'Wklej link tak, jak jest: adresy watch, share i krótkie działają.',
            'media_caption'          => 'Podpis',
            'modal_position'         => 'Pozycja okna modalnego',
            'modal_position_helper'  => 'Okno modalne w rogu zostawia stronę dostępną pod nim.',
            'modal_position_default' => 'Domyślne panelu',
            'video_threshold'        => 'Uznawane za obejrzane przy',
            'video_threshold_helper' => 'Procent kończący krok, gdy kończy się przez obejrzenie.',
            'is_active_helper'       => 'Nieaktywne ścieżki znikają ze wszystkich paneli.',
            'sort_order_helper'      => 'Mniejsze najpierw.',
            'type_helper'            => 'Zadanie się wykonuje; tour się przechodzi.',
            'completion_mode_helper' => 'Co oznacza ten krok jako wykonany.',
        ],

        'tour' => [
            'optional'        => 'Pomiń, jeśli jej nie ma',
            'optional_helper' => 'Do czegoś, czego może jeszcze nie być — etykiety na pustej tabeli, wykresu bez danych. Tour idzie dalej, zamiast czekać.',
            'advance'         => 'Przechodź przez',
            'advance_helper'  => 'Element sterujący, który prowadzi aplikację do tego przystanku — przycisk „Dalej" kreatora, zakładka. Kliknięty, gdy użytkownik idzie dalej, a elementu nie ma jeszcze na ekranie.',
            'add'             => 'Dodaj przystanek',
            'target'          => 'Co podświetlić',
            'target_helper'   => 'Odczytane z samego panelu — pola formularza tej strony, jej przyciski, jej tabeli. Niektórych formularzy nie da się odczytać z zewnątrz (tych, które zależą od edytowanego rekordu lub od tego, kto patrzy): ich pól nie będzie na liście, a wyjściem jest selektor CSS.',
            'targets'         => [
                'on_this_page' => 'Na tej stronie',
                'widgets'      => 'Widżety',
                'advanced'     => 'Zaawansowane',
                'custom'       => 'Własny selektor CSS…',
                'table'        => 'Tabela',
                'search'       => 'Pole wyszukiwania',
                'submit'       => 'Przycisk zapisu',
                'column'       => 'Kolumna: :label',
                'new_record'   => 'Przycisk „Nowy :label"',
            ],
            'selector'        => 'Selektor CSS',
            'selector_helper' => 'Element do podświetlenia, np. [data-onboarding="create-server"]. Zostaw puste, gdy wybrano widżet.',
            'widget'          => 'Widżet',
            'widget_helper'   => 'Widżet panelu. Znajdzie się na stronie sam — selektor nie jest potrzebny.',
            'placement'       => 'Umiejscowienie',
            'placements'      => [
                'auto'   => 'Automatycznie',
                'top'    => 'Powyżej',
                'bottom' => 'Poniżej',
            ],
            'route'             => 'Strona',
            'route_helper'      => 'Tylko gdy ten przystanek jest gdzie indziej. Tour przechodzi tam i kontynuuje.',
            'url'               => 'Własny URL',
            'url_helper'        => 'Tylko gdy strony nie ma na liście.',
            'body'              => 'Tekst',
            'visibility'        => 'Widoczny, gdy',
            'visibility_helper' => 'Zostaw puste, aby pokazać ten przystanek wszystkim. Przystanek wskazujący na funkcję nieobecną w planie niczego by nie podświetlił.',
        ],

        'targets' => [
            'resources'      => 'Zasoby',
            'pages'          => 'Strony',
            'tenant_profile' => 'Profil najemcy',
            'page_names'     => [
                'index'  => 'lista',
                'create' => 'tworzenie',
            ],
        ],
    ],

    'conditions' => [
        'singular'   => 'Warunek',
        'plural'     => 'Warunki',
        'subheading' => 'Pytania, które Twoje kroki mogą zadać o kimś — zapisane tutaj, nie w kodzie.',
        'at_least'   => 'co najmniej :count',

        'sections' => [
            'question'             => 'Pytanie',
            'question_description' => 'Co musi być prawdą o kimś, żeby krok był wykonany.',
            'naming'               => 'Jak się nazywa',
            'naming_description'   => 'Nazwa wybierana z listy przez osobę piszącą krok.',
        ],

        'fields' => [
            'type'                     => 'O co pyta',
            'model'                    => 'Co liczy',
            'model_helper'             => 'Co muszą mieć: klienci, faktury, serwery.',
            'minimum'                  => 'Co najmniej',
            'minimum_helper'           => 'Ile. Prawie zawsze jeden.',
            'subject_column'           => 'Należy do nich przez',
            'subject_column_helper'    => 'Kolumna przechowująca id osoby przechodzącej onboarding.',
            'scope_column'             => 'A do najemcy przez',
            'scope_column_helper'      => 'Tylko gdy model jest ograniczony do najemcy. W przeciwnym razie zostaw puste.',
            'scope_column_none'        => 'Nie ograniczony do najemcy',
            'filters'                  => 'Tylko te, dla których…',
            'filters_helper'           => 'Opcjonalne. Bez żadnego liczą się wszystkie.',
            'filters_attribute'        => 'Prawda, gdy…',
            'filters_attribute_helper' => 'Wszystkie te muszą zachodzić.',
            'add_filter'               => 'Dodaj regułę',
            'column'                   => 'Pole',
            'operator'                 => 'Jest',
            'value'                    => 'Wartość',
            'label'                    => 'Nazwa',
            'label_helper'             => 'Jak się nazywa w edytorze kroków.',
            'key_helper'               => 'Jak kroki się do niego odwołują. Nie można go zmienić, gdy kroki już go używają.',
            'key_taken'                => 'Klucz [:key] należy do warunku zarejestrowanego w kodzie.',
            'is_active_helper'         => 'Nieaktywny warunek nigdy nie zachodzi.',
            'question'                 => 'Pyta',
        ],

        'placeholders' => [
            'label' => 'Dodał klienta',
        ],

        'empty' => [
            'heading'     => 'Nie ma jeszcze warunków',
            'description' => 'Warunek to to, co pozwala krokowi skończyć się samemu — także tym, którzy zrobili to dawno temu. Napisz tutaj jeden albo uruchom php artisan make:onboarding-condition dla pytania, którego formularz nie zada.',
        ],
    ],

];
