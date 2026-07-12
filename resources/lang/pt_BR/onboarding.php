<?php

declare(strict_types = 1);

return [

    'checklist' => [
        'mark_done'             => 'Marcar como concluído',
        'start_tour'            => 'Iniciar tour',
        'go'                    => 'Ir',
        'skip'                  => 'Pular',
        'close'                 => 'Fechar',
        'dismiss'               => 'Ocultar',
        'done'                  => 'Concluir',
        'completed_title'       => 'Tudo pronto',
        'completed_description' => 'Você concluiu todas as etapas. Boas-vindas.',
        'footer_note'           => 'Você pode continuar depois.',
    ],

    'tour' => [
        'skip'     => 'Pular',
        'previous' => 'Voltar',
        'next'     => 'Próximo',
        'finish'   => 'Concluir',
    ],

    'enums' => [
        'step_type' => [
            'task' => 'Tarefa',
            'tour' => 'Tour',
        ],

        'completion_mode' => [
            'manual' => [
                'label'       => 'Manual',
                'description' => 'O usuário marca a etapa como concluída.',
            ],
            'condition' => [
                'label'       => 'Condição',
                'description' => 'Concluída automaticamente quando uma verificação registrada passa.',
            ],
            'visit' => [
                'label'       => 'Visita de página',
                'description' => 'Concluída quando o usuário acessa uma URL.',
            ],
            'programmatic' => [
                'label'       => 'Programática',
                'description' => 'Somente o código da aplicação conclui a etapa.',
            ],
        ],
    ],

    'resource' => [
        'singular'   => 'Jornada de onboarding',
        'plural'     => 'Onboarding',
        'all_panels' => 'Todos os painéis',

        'sections' => [
            'content'          => 'Conteúdo',
            'settings'         => 'Configurações',
            'behaviour'        => 'Comportamento',
            'tour'             => 'Tour',
            'tour_description' => 'Os elementos que o tour destaca, na ordem.',
        ],

        'steps' => [
            'title' => 'Etapas',
        ],

        'fields' => [
            'title'                 => 'Título',
            'description'           => 'Descrição',
            'key'                   => 'Chave',
            'key_helper'            => 'Usada no código. Alterá-la invalida o progresso já registrado.',
            'step_key_helper'       => 'Única dentro da jornada. Usada por Onboarding::for($user)->complete(...).',
            'panel'                 => 'Painel',
            'panel_helper'          => 'Deixe vazio para exibir a jornada em todos os painéis.',
            'icon'                  => 'Ícone',
            'color'                 => 'Cor',
            'sort_order'            => 'Ordem',
            'is_active'             => 'Ativa',
            'is_dismissible'        => 'Pode ser ocultada',
            'is_dismissible_helper' => 'Permite que o usuário esconda a checklist definitivamente.',
            'steps'                 => 'Etapas',
            'updated_at'            => 'Atualizada em',
            'type'                  => 'Tipo',
            'completion_mode'       => 'Concluída por',
            'condition'             => 'Condição',
            'condition_helper'      => 'Registrada pela aplicação. Etapas já cumpridas voltam concluídas.',
            'visit_url'             => 'URL',
            'visit_url_helper'      => 'Aceita o coringa *: /app/*/servers/create',
            'cta_label'             => 'Texto do botão',
            'cta_url'               => 'URL personalizada',
            'cta_url_helper'        => 'Só quando o destino não estiver na lista. {tenant} é preenchido automaticamente.',
            'cta_route'             => 'Destino',
            'cta_route_helper'      => 'Uma página do painel. Preferível à URL: sobrevive à renomeação do slug.',
            'is_required'           => 'Obrigatória',
            'is_required_helper'    => 'Etapas opcionais podem ser puladas.',
        ],

        'tour' => [
            'add'             => 'Adicionar parada',
            'selector'        => 'Seletor CSS',
            'selector_helper' => 'O elemento a destacar, ex.: [data-onboarding="create-server"]. Deixe vazio se escolher um widget.',
            'widget'          => 'Widget',
            'widget_helper'   => 'Um widget do painel. É localizado sozinho na página — sem seletor.',
            'placement'       => 'Posição',
            'placements'      => [
                'auto'   => 'Automática',
                'top'    => 'Acima',
                'bottom' => 'Abaixo',
            ],
            'route'        => 'Página',
            'route_helper' => 'Apenas quando a parada fica em outra página. O tour navega até lá e continua.',
            'url'          => 'URL personalizada',
            'url_helper'   => 'Só quando a página não estiver na lista.',
            'body'         => 'Texto',
        ],

        'targets' => [
            'resources'  => 'Recursos',
            'pages'      => 'Páginas',
            'page_names' => [
                'index'  => 'listagem',
                'create' => 'criar',
            ],
        ],
    ],

];
