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

    'enums' => [
        'step_type' => [
            'task' => 'Tarea',
            'tour' => 'Recorrido',
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

        'sections' => [
            'content'          => 'Contenido',
            'settings'         => 'Configuración',
            'behaviour'        => 'Comportamiento',
            'tour'             => 'Recorrido',
            'tour_description' => 'Los elementos que destaca el recorrido, en orden.',
        ],

        'steps' => [
            'title' => 'Pasos',
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
            'cta_url'               => 'URL del botón',
            'cta_url_helper'        => '{tenant} y otros parámetros del panel se completan automáticamente.',
            'cta_route'             => 'Nombre de la ruta',
            'cta_route_helper'      => 'Tiene prioridad sobre la URL.',
            'is_required'           => 'Obligatorio',
            'is_required_helper'    => 'Los pasos opcionales se pueden omitir.',
        ],

        'tour' => [
            'add'             => 'Añadir parada',
            'selector'        => 'Selector CSS',
            'selector_helper' => 'El elemento a destacar, p. ej. [data-onboarding="create-server"].',
            'placement'       => 'Posición',
            'placements'      => [
                'auto'   => 'Automática',
                'top'    => 'Arriba',
                'bottom' => 'Abajo',
            ],
            'url'        => 'Página',
            'url_helper' => 'Solo cuando la parada está en otra página. El recorrido navega hasta allí.',
            'body'       => 'Texto',
        ],
    ],

];
