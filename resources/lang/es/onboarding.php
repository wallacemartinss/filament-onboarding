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

    'welcome' => [
        'begin' => 'Empezar',
        'later' => 'Ahora no',
        'never' => 'No volver a mostrar',
        'steps' => '{0}Nada por aquí|{1}1 paso, a tu ritmo|[2,*]:count pasos, a tu ritmo',
        'back'  => 'Volver a activar el onboarding',
        'off'   => 'Desactivaste el onboarding. La lista y el círculo de progreso ya no aparecen, pero esta página sigue aquí.',
    ],
    'tour' => [
        'blocked'  => 'La pantalla aún no avanzó. Si falta completar algo, complétalo y pulsa siguiente otra vez — el tutorial te acompaña.',
        'start'    => 'Ver el tutorial',
        'choose'   => '¿Qué tutorial quieres ver?',
        'begin'    => 'Empezar',
        'waiting'  => 'Esperando a que aparezca en la pantalla — sigue en el formulario y el tutorial te acompaña.',
        'skip'     => 'Omitir',
        'previous' => 'Atrás',
        'next'     => 'Siguiente',
        'finish'   => 'Finalizar',
    ],

    'page' => [
        'collapse' => 'Plegar el recorrido',
        'expand'   => 'Abrir el recorrido',
        'meta'     => [
            'completed' => ':count de :total completadas',
            'remaining' => '{1}1 pendiente|[2,*]:count pendientes',
            'skipped'   => '{1}1 omitida|[2,*]:count omitidas',
        ],
        'open_image'           => 'Abrir la imagen: :title',
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

        'condition_type' => [
            'aggregate' => [
                'label'       => 'Cuenta algo que tiene',
                'description' => 'Clientes, facturas, servidores — cualquier cosa suya. "Al menos un cliente."',
            ],
            'attribute' => [
                'label'       => 'Pregunta sobre la persona',
                'description' => 'Una columna del propio usuario. "Su correo está verificado."',
            ],
        ],

        'condition_operator' => [
            'equals'                => 'es',
            'not_equals'            => 'no es',
            'greater_than'          => 'es más que',
            'greater_than_or_equal' => 'es al menos',
            'less_than'             => 'es menos que',
            'less_than_or_equal'    => 'es como máximo',
            'contains'              => 'contiene',
            'is_set'                => 'está completo',
            'is_empty'              => 'está vacío',
        ],
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
            'overview'                => 'Resumen',
            'publishing_description'  => 'Quién ve este recorrido, dónde y en qué orden.',
            'appearance_description'  => 'Cómo se anuncia el recorrido en la lista.',
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
            'condition_none'        => 'Aún no hay condiciones — escribe una en Onboarding → Condiciones (o ejecuta php artisan make:onboarding-condition) y aparecerá aquí.',
            'visit_url'             => 'URL',
            'visit_url_helper'      => 'Admite el comodín *: /app/*/servers/create',
            'cta_label'             => 'Texto del botón',
            'cta_url'               => 'URL personalizada',
            'cta_url_helper'        => 'Solo cuando el destino no está en la lista. {tenant} se completa automáticamente.',
            'cta_route'             => 'Destino',
            'cta_route_helper'      => 'Una página del panel. Preferible a la URL: sobrevive al cambio de slug.',
            'is_required'           => 'Obligatorio',
            'is_required_helper'    => 'Los pasos opcionales se pueden omitir.',
            'visibility'            => 'Visible cuando',
            'visibility_helper'     => 'Solo lo ve quien pasa esta condición. Úsalo para limitar un paso a un plan o a una función.',
            'visibility_everyone'   => 'Todos',

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
            'optional'        => 'Saltar si no está',
            'optional_helper' => 'Para algo que puede no existir aún: una etiqueta en una tabla vacía, un gráfico sin datos. El tutorial sigue en vez de esperar.',
            'advance'         => 'Avanzar con',
            'advance_helper'  => 'El control que lleva la app hasta esta parada — el botón siguiente de un wizard, una pestaña. Se pulsa cuando el usuario avanza y el elemento aún no está en pantalla.',
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
            'route'             => 'Página',
            'route_helper'      => 'Solo cuando la parada está en otra página. El recorrido navega hasta allí y continúa.',
            'url'               => 'URL personalizada',
            'url_helper'        => 'Solo cuando la página no está en la lista.',
            'body'              => 'Texto',
            'visibility'        => 'Visible cuando',
            'visibility_helper' => 'Vacío: la parada aparece para todos. Una parada que apunta a una función fuera del plan no destacaría nada.',
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

    'conditions' => [
        'singular'   => 'Condición',
        'plural'     => 'Condiciones',
        'subheading' => 'Las preguntas que tus pasos hacen sobre alguien — escritas aquí, no en el código.',
        'at_least'   => 'al menos :count',

        'sections' => [
            'question'             => 'La pregunta',
            'question_description' => 'Lo que tiene que ser cierto de alguien para que el paso esté hecho.',
            'naming'               => 'Cómo se llama',
            'naming_description'   => 'El nombre que elige quien escribe un paso.',
        ],

        'fields' => [
            'type'                     => 'Qué pregunta',
            'model'                    => 'Contando qué',
            'model_helper'             => 'Lo que tienen que tener: clientes, facturas, servidores.',
            'minimum'                  => 'Al menos',
            'minimum_helper'           => 'Cuántos. Uno, casi siempre.',
            'subject_column'           => 'Que les pertenece por',
            'subject_column_helper'    => 'La columna con el id de quien está siendo incorporado.',
            'scope_column'             => 'Y al inquilino por',
            'scope_column_helper'      => 'Solo cuando el modelo está limitado a un inquilino. Si no, déjalo vacío.',
            'scope_column_none'        => 'No está limitado a un inquilino',
            'filters'                  => 'Solo los que…',
            'filters_helper'           => 'Opcional. Sin ninguno, todos cuentan.',
            'filters_attribute'        => 'Cierto cuando…',
            'filters_attribute_helper' => 'Todas estas tienen que cumplirse.',
            'add_filter'               => 'Añadir regla',
            'column'                   => 'Campo',
            'operator'                 => 'Es',
            'value'                    => 'Valor',
            'label'                    => 'Nombre',
            'label_helper'             => 'Cómo aparece en el editor de pasos.',
            'key_helper'               => 'Cómo se refieren a ella los pasos. No la cambies una vez en uso.',
            'key_taken'                => 'La clave [:key] pertenece a una condición registrada en el código.',
            'is_active_helper'         => 'Una condición inactiva nunca se cumple.',
            'question'                 => 'Pregunta',
        ],

        'placeholders' => [
            'label' => 'Ha añadido un cliente',
        ],

        'empty' => [
            'heading'     => 'Aún no hay condiciones',
            'description' => 'Una condición es lo que permite que un paso se complete solo — incluso para quien ya lo hizo hace tiempo. Escribe una aquí, o ejecuta php artisan make:onboarding-condition para una pregunta que un formulario no puede hacer.',
        ],
    ],

];
