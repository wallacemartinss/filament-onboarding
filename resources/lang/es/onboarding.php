<?php

declare(strict_types = 1);

return [

    'checklist' => [
        'mark_done'             => 'Marcar como completado',
        'start_tour'            => 'Iniciar recorrido',
        'go'                    => 'Ir',
        'skip'                  => 'Omitir',
        'close'                 => 'Cerrar',
        'dismiss'               => 'Ocultar',
        'done'                  => 'Listo',
        'completed_title'       => 'Todo listo',
        'completed_description' => 'Completaste todos los pasos. Bienvenido.',
        'footer_note'           => 'Puedes continuar más tarde.',
    ],

    'tour' => [
        'skip'     => 'Omitir',
        'previous' => 'Atrás',
        'next'     => 'Siguiente',
        'finish'   => 'Finalizar',
    ],

    'page' => [
        'title'                => 'Primeros pasos',
        'subheading'           => 'Dónde estás y qué falta.',
        'next'                 => 'A continuación',
        'hidden'               => 'Oculto',
        'restore'              => 'Mostrar de nuevo',
        'undo'                 => 'Deshacer',
        'replay_tour'          => 'Ver de nuevo',
        'replay_video'         => 'Ver de nuevo',
        'open_again'           => 'Abrir de nuevo',
        'restart'              => 'Empezar de nuevo',
        'restarted'            => 'Ruta reiniciada',
        'restarted_reinstated' => ':count paso(s) volvieron completados: ya están hechos de verdad.',
        'restart_confirm'      => '¿Empezar esta ruta de nuevo? Lo que marcaste, omitiste y viste se borrará.',
        'restart_note'         => 'Los pasos que se completan solos vuelven completados: responden al sistema, no a este botón.',

        'stats' => [
            'completed' => 'Completados',
            'remaining' => 'Restantes',
            'skipped'   => 'Omitidos',
        ],

        'status' => [
            'completed' => 'Completado',
            'skipped'   => 'Omitido',
            'next'      => 'A continuación',
            'pending'   => 'Pendiente',
        ],

        'completed_at'       => 'Completado :time',
        'tour_progress'      => 'Parada :reached de :total',
        'awaiting_condition' => 'Se completa solo',
        'empty_title'        => 'Nada por aquí',
        'empty_description'  => 'No hay ningún recorrido para ti por ahora.',
    ],

    'media' => [
        'watch'   => 'Ver',
        'resume'  => 'Reanudar',
        'watched' => 'visto',
    ],

    'enums' => [
        'step_type' => [
            'task' => 'Tarea',
            'tour' => 'Recorrido',
        ],

        'media_type' => [
            'none'  => 'Ninguna',
            'image' => 'Imagen',
            'video' => 'Vídeo',
        ],

        'media_source' => [
            'upload' => [
                'label'       => 'Subida',
                'description' => 'Guardado en el disco configurado (S3, R2, local).',
            ],
            'url' => [
                'label'       => 'URL directa',
                'description' => 'Un archivo alojado en otro sitio.',
            ],
            'youtube' => [
                'label'       => 'YouTube',
                'description' => 'Se registra el tiempo visto.',
            ],
            'vimeo' => [
                'label'       => 'Vimeo',
                'description' => 'Se registra el tiempo visto.',
            ],
            'embed' => [
                'label'       => 'Otro proveedor (iframe)',
                'description' => 'Se reproduce, pero no se puede registrar el tiempo visto.',
            ],
        ],

        'modal_position' => [
            'center'       => 'Centro',
            'top'          => 'Arriba',
            'bottom'       => 'Abajo',
            'top-left'     => 'Arriba a la izquierda',
            'top-right'    => 'Arriba a la derecha',
            'bottom-left'  => 'Abajo a la izquierda',
            'bottom-right' => 'Abajo a la derecha',
        ],

        'completion_mode' => [
            'manual' => [
                'label'       => 'Manual',
                'description' => 'El usuario la marca como completada.',
            ],
            'condition' => [
                'label'       => 'Condición',
                'description' => 'Se completa automáticamente cuando pasa una verificación registrada.',
            ],
            'visit' => [
                'label'       => 'Visita de página',
                'description' => 'Se completa cuando el usuario llega a una URL.',
            ],
            'video' => [
                'label'       => 'Ver el vídeo',
                'description' => 'Se completa cuando se ha visto suficiente del vídeo del paso.',
            ],
            'programmatic' => [
                'label'       => 'Programática',
                'description' => 'Solo el código de la aplicación la completa.',
            ],
        ],
    ],

    'resource' => [
        'singular'   => 'Recorrido de incorporación',
        'plural'     => 'Incorporación',
        'all_panels' => 'Todos los paneles',

        'placeholders' => [
            'flow_title'       => 'Empieza por aquí…',
            'flow_description' => 'Una línea sobre lo que el usuario gana con esto.',
            'step_title'       => 'Conecta tu primer servidor',
            'step_description' => 'Una o dos líneas. Di el porqué, no solo el qué.',
        ],

        'no_steps_warning' => 'Esta ruta no tiene pasos, así que nadie la ve.',

        'empty' => [
            'heading'     => 'Aún no hay rutas',
            'description' => 'Una ruta es una lista que tus usuarios recorren. Crea una y añade sus pasos.',
        ],

        'sections' => [
            'content_description'     => 'Lo que el usuario lee, en cada idioma que soportas.',
            'publishing'              => 'Publicación',
            'appearance'              => 'Apariencia',
            'rules'                   => 'Reglas',
            'destination'             => 'Destino',
            'destination_description' => 'A dónde lleva el botón. Elige una página del panel; la URL es el plan B.',
            'content'                 => 'Contenido',
            'settings'                => 'Configuración',
            'behaviour'               => 'Comportamiento',
            'media'                   => 'Medios',
            'media_description'       => 'Una imagen para mostrar o un vídeo para ver. Se abre en un modal sobre el panel.',
            'tour'                    => 'Recorrido',
            'tour_description'        => 'Los elementos que destaca el recorrido, en orden.',
        ],

        'steps' => [
            'title'             => 'Pasos',
            'empty_heading'     => 'Aún no hay pasos',
            'empty_description' => 'Añade el primer paso. Sin pasos, esta ruta no aparece para nadie.',
        ],

        'fields' => [
            'title'                 => 'Título',
            'description'           => 'Descripción',
            'key'                   => 'Clave',
            'key_helper'            => 'Se usa en el código. Cambiarla invalida el progreso registrado.',
            'step_key_helper'       => 'Única dentro del recorrido. La usa Onboarding::for($user)->complete(...).',
            'panel'                 => 'Panel',
            'panel_helper'          => 'Déjalo vacío para mostrar el recorrido en todos los paneles.',
            'icon'                  => 'Icono',
            'color'                 => 'Color',
            'sort_order'            => 'Orden',
            'is_active'             => 'Activo',
            'is_dismissible'        => 'Se puede ocultar',
            'is_dismissible_helper' => 'Permite al usuario ocultar la lista definitivamente.',
            'steps'                 => 'Pasos',
            'updated_at'            => 'Actualizado el',
            'type'                  => 'Tipo',
            'completion_mode'       => 'Se completa por',
            'condition'             => 'Condición',
            'condition_helper'      => 'Registrada por la aplicación. Los pasos ya cumplidos vuelven completados.',
            'visit_url'             => 'URL',
            'visit_url_helper'      => 'Admite el comodín *: /app/*/servers/create',
            'cta_label'             => 'Texto del botón',
            'cta_url'               => 'URL personalizada',
            'cta_url_helper'        => 'Solo cuando el destino no está en la lista. {tenant} se completa automáticamente.',
            'cta_route'             => 'Destino',
            'cta_route_helper'      => 'Una página del panel. Preferible a la URL: sobrevive al cambio de slug.',
            'is_required'           => 'Obligatorio',
            'is_required_helper'    => 'Los pasos opcionales se pueden omitir.',

            'media_type'             => 'Medios',
            'media_source'           => 'Origen',
            'media_file'             => 'Archivo',
            'media_url'              => 'URL',
            'media_url_helper'       => 'Pega el enlace tal cual: watch, share o URL corta funcionan.',
            'media_caption'          => 'Leyenda',
            'modal_position'         => 'Posición del modal',
            'modal_position_helper'  => 'Un modal en una esquina deja la página utilizable detrás.',
            'modal_position_default' => 'Predeterminado del panel',
            'video_threshold'        => 'Cuenta como visto al',
            'video_threshold_helper' => 'Porcentaje que completa el paso, cuando se completa viéndolo.',
            'is_active_helper'       => 'Las rutas inactivas desaparecen de todos los paneles.',
            'sort_order_helper'      => 'El menor aparece primero.',
            'type_helper'            => 'Una tarea se cumple; un recorrido se recorre.',
            'completion_mode_helper' => 'Lo que marca este paso como completado.',
        ],

        'tour' => [
            'add'             => 'Añadir parada',
            'selector'        => 'Selector CSS',
            'selector_helper' => 'El elemento a destacar, p. ej. [data-onboarding="create-server"]. Déjalo vacío si eliges un widget.',
            'widget'          => 'Widget',
            'widget_helper'   => 'Un widget del panel. Se localiza solo en la página — sin selector.',
            'placement'       => 'Posición',
            'placements'      => [
                'auto'   => 'Automática',
                'top'    => 'Arriba',
                'bottom' => 'Abajo',
            ],
            'route'        => 'Página',
            'route_helper' => 'Solo cuando la parada está en otra página. El recorrido navega hasta allí y continúa.',
            'url'          => 'URL personalizada',
            'url_helper'   => 'Solo cuando la página no está en la lista.',
            'body'         => 'Texto',
        ],

        'targets' => [
            'resources'  => 'Recursos',
            'pages'      => 'Páginas',
            'page_names' => [
                'index'  => 'listado',
                'create' => 'crear',
            ],
        ],
    ],

];
