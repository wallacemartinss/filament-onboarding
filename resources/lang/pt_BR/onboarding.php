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
        'waiting'  => 'Esperando isso aparecer na tela — siga no formulário que o tutorial acompanha você.',
        'skip'     => 'Pular',
        'previous' => 'Voltar',
        'next'     => 'Próximo',
        'finish'   => 'Concluir',
    ],

    'page' => [
        'title'                => 'Primeiros passos',
        'subheading'           => 'Onde você está e o que falta.',
        'next'                 => 'A seguir',
        'hidden'               => 'Oculta',
        'restore'              => 'Mostrar novamente',
        'undo'                 => 'Desfazer',
        'replay_tour'          => 'Rever tour',
        'replay_video'         => 'Rever vídeo',
        'open_again'           => 'Abrir de novo',
        'restart'              => 'Refazer jornada',
        'restarted'            => 'Jornada reiniciada',
        'restarted_reinstated' => ':count etapa(s) voltaram concluídas: elas já estão feitas de verdade.',
        'restart_confirm'      => 'Refazer esta jornada? O que você marcou, pulou e assistiu será apagado.',
        'restart_note'         => 'Etapas que concluem sozinhas voltam concluídas: elas respondem ao sistema, não a este botão.',

        'stats' => [
            'completed' => 'Concluídas',
            'remaining' => 'Restantes',
            'skipped'   => 'Puladas',
        ],

        'status' => [
            'completed' => 'Concluída',
            'skipped'   => 'Pulada',
            'next'      => 'A seguir',
            'pending'   => 'A fazer',
        ],

        'completed_at'       => 'Concluída :time',
        'tour_progress'      => 'Parada :reached de :total',
        'awaiting_condition' => 'Conclui sozinha',
        'empty_title'        => 'Nada por aqui',
        'empty_description'  => 'Não há nenhuma jornada para você no momento.',
    ],

    'media' => [
        'watch'   => 'Assistir',
        'resume'  => 'Retomar',
        'watched' => 'assistido',
    ],

    'enums' => [
        'step_type' => [
            'task' => 'Tarefa',
            'tour' => 'Tour',
        ],

        'media_type' => [
            'none'  => 'Nenhuma',
            'image' => 'Imagem',
            'video' => 'Vídeo',
        ],

        'media_source' => [
            'upload' => [
                'label'       => 'Upload',
                'description' => 'Guardado no disco configurado (S3, R2, local).',
            ],
            'url' => [
                'label'       => 'URL direta',
                'description' => 'Um arquivo hospedado em outro lugar.',
            ],
            'youtube' => [
                'label'       => 'YouTube',
                'description' => 'O tempo assistido é registrado.',
            ],
            'vimeo' => [
                'label'       => 'Vimeo',
                'description' => 'O tempo assistido é registrado.',
            ],
            'embed' => [
                'label'       => 'Outro provedor (iframe)',
                'description' => 'Reproduz, mas o tempo assistido não pode ser registrado.',
            ],
        ],

        'modal_position' => [
            'center'       => 'Centro',
            'top'          => 'Topo',
            'bottom'       => 'Rodapé',
            'top-left'     => 'Canto superior esquerdo',
            'top-right'    => 'Canto superior direito',
            'bottom-left'  => 'Canto inferior esquerdo',
            'bottom-right' => 'Canto inferior direito',
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
            'video' => [
                'label'       => 'Assistir ao vídeo',
                'description' => 'Concluída quando o vídeo da etapa é assistido o suficiente.',
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

        'placeholders' => [
            'flow_title'       => 'Comece por aqui…',
            'flow_description' => 'Uma linha sobre o que o usuário ganha com isso.',
            'step_title'       => 'Conecte seu primeiro servidor',
            'step_description' => 'Uma ou duas linhas. Diga o porquê, não só o quê.',
        ],

        'no_steps_warning' => 'Esta jornada não tem etapas, então ninguém a vê.',

        'empty' => [
            'heading'     => 'Nenhuma jornada ainda',
            'description' => 'Uma jornada é uma checklist que seus usuários percorrem. Crie uma e adicione as etapas.',
        ],

        'sections' => [
            'overview'                => 'Visão geral',
            'publishing_description'  => 'Quem vê esta jornada, onde e em que ordem.',
            'appearance_description'  => 'Como a jornada se anuncia no checklist.',
            'content_description'     => 'O que o usuário lê, em cada idioma que você suporta.',
            'publishing'              => 'Publicação',
            'appearance'              => 'Aparência',
            'rules'                   => 'Regras',
            'destination'             => 'Destino',
            'destination_description' => 'Para onde o botão leva. Escolha uma página do painel; a URL é o plano B.',
            'content'                 => 'Conteúdo',
            'settings'                => 'Configurações',
            'behaviour'               => 'Comportamento',
            'media'                   => 'Mídia',
            'media_description'       => 'Uma imagem para mostrar ou um vídeo para assistir. Abre em um modal sobre o painel.',
            'tour'                    => 'Tour',
            'tour_description'        => 'Os elementos que o tour destaca, na ordem.',
        ],

        'steps' => [
            'title'             => 'Etapas',
            'empty_heading'     => 'Nenhuma etapa ainda',
            'empty_description' => 'Adicione a primeira etapa. Sem etapas, esta jornada não aparece para ninguém.',
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
            'visibility'            => 'Visível quando',
            'visibility_helper'     => 'Só quem passa nesta condição vê. Use para restringir uma etapa a um plano ou a um recurso.',
            'visibility_everyone'   => 'Todos',

            'media_type'             => 'Mídia',
            'media_source'           => 'Origem',
            'media_file'             => 'Arquivo',
            'media_url'              => 'URL',
            'media_url_helper'       => 'Cole o link como ele vem: watch, share ou URL curta funcionam.',
            'media_caption'          => 'Legenda',
            'modal_position'         => 'Posição do modal',
            'modal_position_helper'  => 'Um modal num canto deixa a página utilizável por trás dele.',
            'modal_position_default' => 'Padrão do painel',
            'video_threshold'        => 'Conta como assistido em',
            'video_threshold_helper' => 'Percentual que conclui a etapa, quando ela é concluída por assistir.',
            'is_active_helper'       => 'Jornadas inativas somem de todos os painéis.',
            'sort_order_helper'      => 'Menor aparece primeiro.',
            'type_helper'            => 'Tarefa se cumpre; tour se percorre.',
            'completion_mode_helper' => 'O que marca esta etapa como concluída.',
        ],

        'tour' => [
            'advance'         => 'Avançar com',
            'advance_helper'  => 'O controle que leva o app até esta parada — o botão de próximo de um wizard, uma aba. Clicado quando o usuário avança e o elemento ainda não está na tela.',
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
            'route'             => 'Página',
            'route_helper'      => 'Apenas quando a parada fica em outra página. O tour navega até lá e continua.',
            'url'               => 'URL personalizada',
            'url_helper'        => 'Só quando a página não estiver na lista.',
            'body'              => 'Texto',
            'visibility'        => 'Visível quando',
            'visibility_helper' => 'Vazio: a parada aparece para todos. Uma parada que aponta para um recurso fora do plano não destacaria nada.',
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
