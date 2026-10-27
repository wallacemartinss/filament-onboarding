<?php

declare(strict_types = 1);

return [

    'checklist' => [
        'mark_done'             => 'Marquer comme terminé',
        'start_tour'            => 'Démarrer la visite',
        'go'                    => 'Aller',
        'skip'                  => 'Passer',
        'close'                 => 'Fermer',
        'dismiss'               => 'Masquer',
        'done'                  => 'Terminé',
        'completed_title'       => 'Tout est prêt',
        'completed_description' => 'Toutes les étapes sont terminées. Bienvenue.',
        'footer_note'           => 'Vous pourrez y revenir plus tard.',
    ],

    'welcome' => [
        'begin' => 'Commencer',
        'later' => 'Pas maintenant',
        'never' => 'Ne plus afficher',
        'steps' => '{0}Rien pour l\'instant|{1}1 étape, à votre rythme|[2,*]:count étapes, à votre rythme',
        'back'  => 'Réactiver l\'onboarding',
        'off'   => 'Vous avez désactivé l\'onboarding. La liste et l\'anneau de progression ont disparu — mais cette page reste.',
    ],
    'tour' => [
        'blocked'  => 'L\'écran n\'est pas encore passé à la suite. Si quelque chose est obligatoire, remplissez-le et appuyez de nouveau sur « Suivant » — la visite vous y suit.',
        'start'    => 'Voir le tutoriel',
        'choose'   => 'Quel tutoriel voulez-vous ?',
        'begin'    => 'Commencer',
        'waiting'  => 'En attente que cela apparaisse sur la page — continuez le formulaire et la visite vous suit.',
        'skip'     => 'Passer',
        'previous' => 'Retour',
        'next'     => 'Suivant',
        'finish'   => 'Terminer',
    ],

    'page' => [
        'collapse' => 'Replier ce parcours',
        'expand'   => 'Déplier ce parcours',
        'meta'     => [
            'completed' => ':count sur :total terminées',
            'remaining' => '{1}1 restante|[2,*]:count restantes',
            'skipped'   => '{1}1 passée|[2,*]:count passées',
        ],
        'open_image'           => 'Ouvrir l\'image : :title',
        'title'                => 'Premiers pas',
        'subheading'           => 'Où vous en êtes et ce qu\'il reste.',
        'next'                 => 'À suivre',
        'hidden'               => 'Masqué',
        'restore'              => 'Afficher de nouveau',
        'undo'                 => 'Annuler',
        'replay_tour'          => 'Revoir',
        'replay_video'         => 'Revoir',
        'open_again'           => 'Rouvrir',
        'restart'              => 'Recommencer',
        'restarted'            => 'Parcours relancé',
        'restarted_reinstated' => ':count étape(s) sont revenues directement : elles sont terminées parce que le travail derrière l\'est.',
        'restart_confirm'      => 'Relancer ce parcours ? Ce que vous avez coché, passé et regardé sera effacé.',
        'restart_note'         => 'Les étapes qui se terminent d\'elles-mêmes reviennent directement : elles répondent à l\'application, pas à ce bouton.',

        'stats' => [
            'completed' => 'Terminées',
            'remaining' => 'Restantes',
            'skipped'   => 'Passées',
        ],

        'status' => [
            'completed' => 'Terminée',
            'skipped'   => 'Passée',
            'next'      => 'À suivre',
            'pending'   => 'À faire',
        ],

        'completed_at'       => 'Terminée :time',
        'tour_progress'      => 'Étape :reached sur :total',
        'awaiting_condition' => 'Se termine toute seule',
        'empty_title'        => 'Rien à onboarder',
        'empty_description'  => 'Il n\'y a aucun parcours pour vous pour l\'instant.',
    ],

    'media' => [
        'watch'   => 'Regarder',
        'resume'  => 'Reprendre',
        'watched' => 'regardé',
    ],

    'enums' => [

        'condition_type' => [
            'aggregate' => [
                'label'       => 'Compte ce qu\'ils ont',
                'description' => 'Clients, factures, serveurs — tout ce qui leur appartient. « Au moins un client. »',
            ],
            'attribute' => [
                'label'       => 'Interroge la personne elle-même',
                'description' => 'Une colonne de l\'utilisateur lui-même. « Son e-mail est vérifié. »',
            ],
        ],

        'condition_operator' => [
            'equals'                => 'est',
            'not_equals'            => 'n\'est pas',
            'greater_than'          => 'est supérieur à',
            'greater_than_or_equal' => 'est au moins',
            'less_than'             => 'est inférieur à',
            'less_than_or_equal'    => 'est au plus',
            'contains'              => 'contient',
            'is_set'                => 'est rempli',
            'is_empty'              => 'est vide',
        ],
        'step_type' => [
            'task' => 'Tâche',
            'tour' => 'Visite',
        ],

        'media_type' => [
            'none'  => 'Aucun',
            'image' => 'Image',
            'video' => 'Vidéo',
        ],

        'media_source' => [
            'upload' => [
                'label'       => 'Téléverser',
                'description' => 'Conservé sur le disque configuré (S3, R2, local).',
            ],
            'url' => [
                'label'       => 'URL directe',
                'description' => 'Un fichier hébergé ailleurs.',
            ],
            'youtube' => [
                'label'       => 'YouTube',
                'description' => 'Le temps de visionnage est mesuré.',
            ],
            'vimeo' => [
                'label'       => 'Vimeo',
                'description' => 'Le temps de visionnage est mesuré.',
            ],
            'embed' => [
                'label'       => 'Autre fournisseur (iframe)',
                'description' => 'Se lit, mais le temps de visionnage ne peut pas être mesuré.',
            ],
        ],

        'modal_position' => [
            'center'       => 'Au centre',
            'top'          => 'En haut',
            'bottom'       => 'En bas',
            'top-left'     => 'En haut à gauche',
            'top-right'    => 'En haut à droite',
            'bottom-left'  => 'En bas à gauche',
            'bottom-right' => 'En bas à droite',
        ],

        'completion_mode' => [
            'manual' => [
                'label'       => 'Manuelle',
                'description' => 'L\'utilisateur la coche lui-même.',
            ],
            'condition' => [
                'label'       => 'Condition',
                'description' => 'Terminée automatiquement dès qu\'une vérification enregistrée aboutit.',
            ],
            'visit' => [
                'label'       => 'Visite de page',
                'description' => 'Terminée quand l\'utilisateur atteint une URL.',
            ],
            'video' => [
                'label'       => 'Visionnage de la vidéo',
                'description' => 'Terminée quand une bonne partie de la vidéo de l\'étape a été vue.',
            ],
            'programmatic' => [
                'label'       => 'Programmatique',
                'description' => 'Seul le code de l\'application la termine.',
            ],
        ],
    ],

    'resource' => [
        'singular'   => 'Parcours d\'onboarding',
        'plural'     => 'Onboarding',
        'all_panels' => 'Tous les panneaux',

        'placeholders' => [
            'flow_title'       => 'Démarrer avec …',
            'flow_description' => 'Une ligne sur ce que l\'utilisateur y gagne.',
            'step_title'       => 'Connectez votre premier serveur',
            'step_description' => 'Une ou deux lignes. Dites pourquoi, pas seulement quoi.',
        ],

        'no_steps_warning' => 'Ce parcours n\'a pas d\'étapes, donc personne ne le voit.',

        'empty' => [
            'heading'     => 'Aucun parcours pour l\'instant',
            'description' => 'Un parcours est une liste que vos utilisateurs parcourent. Créez-en un et ajoutez ses étapes.',
        ],

        'sections' => [
            'overview'                => 'Vue d\'ensemble',
            'publishing_description'  => 'Qui voit ce parcours, où et dans quel ordre.',
            'appearance_description'  => 'Comment le parcours est annoncé dans la liste.',
            'content_description'     => 'Ce que l\'utilisateur lit, dans chaque langue que vous prenez en charge.',
            'publishing'              => 'Publication',
            'appearance'              => 'Apparence',
            'rules'                   => 'Règles',
            'destination'             => 'Destination',
            'destination_description' => 'Où mène le bouton. Choisissez une page du panneau ; une URL reste le repli.',
            'content'                 => 'Contenu',
            'settings'                => 'Paramètres',
            'behaviour'               => 'Comportement',
            'media'                   => 'Médias',
            'media_description'       => 'Une image à afficher, ou une vidéo à regarder. S\'ouvre dans une modale au-dessus du panneau.',
            'tour'                    => 'Visite',
            'tour_description'        => 'Les éléments que cette visite met en évidence, dans l\'ordre.',
        ],

        'steps' => [
            'title'             => 'Étapes',
            'empty_heading'     => 'Aucune étape pour l\'instant',
            'empty_description' => 'Ajoutez la première étape. Sans étapes, ce parcours ne s\'affiche pour personne.',
        ],

        'fields' => [
            'title'                 => 'Titre',
            'description'           => 'Description',
            'key'                   => 'Clé',
            'key_helper'            => 'Utilisée dans le code. Ne peut pas être modifiée sans casser la progression existante.',
            'step_key_helper'       => 'Unique dans le parcours. Utilisée par Onboarding::for($user)->complete(...).',
            'panel'                 => 'Panneau',
            'panel_helper'          => 'Laissez vide pour afficher le parcours dans tous les panneaux.',
            'icon'                  => 'Icône',
            'color'                 => 'Couleur',
            'sort_order'            => 'Ordre',
            'is_active'             => 'Actif',
            'is_dismissible'        => 'Masquable',
            'is_dismissible_helper' => 'Permet à l\'utilisateur de masquer la liste définitivement.',
            'steps'                 => 'Étapes',
            'updated_at'            => 'Mis à jour le',
            'type'                  => 'Type',
            'completion_mode'       => 'Terminée par',
            'condition'             => 'Condition',
            'condition_helper'      => 'Enregistrée par l\'application. Les étapes déjà accomplies reviennent comme terminées.',
            'condition_none'        => 'Aucune condition pour l\'instant — écrivez-en une sous Onboarding → Conditions (ou lancez php artisan make:onboarding-condition), et elle apparaîtra ici.',
            'visit_url'             => 'URL',
            'visit_url_helper'      => 'Accepte le joker * : /app/*/servers/create',
            'cta_label'             => 'Libellé du bouton',
            'cta_url'               => 'URL personnalisée',
            'cta_url_helper'        => 'Uniquement quand la destination n\'est pas dans la liste. {tenant} est rempli automatiquement.',
            'cta_route'             => 'Destination',
            'cta_route_helper'      => 'Une page du panneau. Préférable à une URL : elle survit à un slug renommé.',
            'is_required'           => 'Obligatoire',
            'is_required_helper'    => 'Les étapes facultatives peuvent être passées.',
            'visibility'            => 'Visible quand',
            'visibility_helper'     => 'Seuls ceux qui remplissent cette condition la voient. Utilisez-la pour subordonner une étape à un forfait ou à une fonctionnalité.',
            'visibility_everyone'   => 'Tout le monde',

            'media_type'             => 'Médias',
            'media_source'           => 'Source',
            'media_file'             => 'Fichier',
            'media_url'              => 'URL',
            'media_url_helper'       => 'Collez le lien tel quel : les URL watch, share ou courtes fonctionnent.',
            'media_caption'          => 'Légende',
            'modal_position'         => 'Position de la modale',
            'modal_position_helper'  => 'Une modale dans un coin laisse la page utilisable derrière elle.',
            'modal_position_default' => 'Défaut du panneau',
            'video_threshold'        => 'Considérée comme vue à',
            'video_threshold_helper' => 'Pourcentage qui termine l\'étape, quand elle se termine par visionnage.',
            'is_active_helper'       => 'Les parcours inactifs disparaissent de tous les panneaux.',
            'sort_order_helper'      => 'Le plus petit vient en premier.',
            'type_helper'            => 'Une tâche s\'accomplit ; une visite se parcourt.',
            'completion_mode_helper' => 'Ce qui marque cette étape comme terminée.',
        ],

        'tour' => [
            'optional'        => 'Passer si elle n\'est pas là',
            'optional_helper' => 'Pour quelque chose qui peut ne pas encore exister — une étiquette sur un tableau vide, un graphique sans données. La visite avance au lieu d\'attendre.',
            'advance'         => 'Avancer avec',
            'advance_helper'  => 'Le contrôle qui amène l\'application à cette étape — le bouton « suivant » d\'un assistant, un onglet. Cliqué quand l\'utilisateur progresse et que l\'élément n\'est pas encore à l\'écran.',
            'add'             => 'Ajouter une étape',
            'target'          => 'Quoi mettre en évidence',
            'target_helper'   => 'Lu depuis le panneau lui-même — les champs du formulaire de cette page, ses boutons, sa table. Certains formulaires ne peuvent pas être lus de l\'extérieur (ceux qui dépendent de l\'enregistrement en cours d\'édition ou de qui regarde) : leurs champs ne seront pas listés, et un sélecteur CSS est la solution.',
            'targets'         => [
                'on_this_page' => 'Sur cette page',
                'widgets'      => 'Widgets',
                'advanced'     => 'Avancé',
                'custom'       => 'Un sélecteur CSS de mon choix…',
                'table'        => 'Le tableau',
                'search'       => 'Le champ de recherche',
                'submit'       => 'Le bouton d\'enregistrement',
                'column'       => 'Colonne : :label',
                'new_record'   => 'Le bouton « Nouveau :label »',
            ],
            'selector'        => 'Sélecteur CSS',
            'selector_helper' => 'L\'élément à mettre en évidence, p. ex. [data-onboarding="create-server"]. Laissez vide si un widget est choisi.',
            'widget'          => 'Widget',
            'widget_helper'   => 'Un widget du panneau. Trouvé tout seul sur la page — aucun sélecteur requis.',
            'placement'       => 'Placement',
            'placements'      => [
                'auto'   => 'Automatique',
                'top'    => 'Au-dessus',
                'bottom' => 'En dessous',
            ],
            'route'             => 'Page',
            'route_helper'      => 'Uniquement si cette étape se trouve ailleurs. La visite y navigue et continue.',
            'url'               => 'URL personnalisée',
            'url_helper'        => 'Uniquement si la page n\'est pas dans la liste.',
            'body'              => 'Texte',
            'visibility'        => 'Visible quand',
            'visibility_helper' => 'Laissez vide pour montrer cette étape à tout le monde. Une étape pointant vers une fonctionnalité que le forfait n\'a pas ne mettrait rien en évidence.',
        ],

        'targets' => [
            'resources'      => 'Ressources',
            'pages'          => 'Pages',
            'tenant_profile' => 'Profil du tenant',
            'page_names'     => [
                'index'  => 'liste',
                'create' => 'création',
            ],
        ],
    ],

    'conditions' => [
        'singular'   => 'Condition',
        'plural'     => 'Conditions',
        'subheading' => 'Les questions que vos étapes peuvent poser sur quelqu\'un — écrites ici, pas dans le code.',
        'at_least'   => 'au moins :count',

        'sections' => [
            'question'             => 'La question',
            'question_description' => 'Ce qui doit être vrai de quelqu\'un pour que l\'étape soit terminée.',
            'naming'               => 'Ce que ça s\'appelle',
            'naming_description'   => 'Le nom que choisit dans la liste déroulante la personne qui écrit une étape.',
        ],

        'fields' => [
            'type'                     => 'Ce qu\'elle demande',
            'model'                    => 'Ce qu\'elle compte',
            'model_helper'             => 'Ce qu\'ils doivent avoir : clients, factures, serveurs.',
            'minimum'                  => 'Au moins',
            'minimum_helper'           => 'Combien. Un, presque toujours.',
            'subject_column'           => 'Cela leur appartient via',
            'subject_column_helper'    => 'La colonne qui porte l\'id de la personne en cours d\'onboarding.',
            'scope_column'             => 'Et au tenant via',
            'scope_column_helper'      => 'Uniquement si le modèle est cantonné à un tenant. Sinon, laissez vide.',
            'scope_column_none'        => 'Non cantonné à un tenant',
            'filters'                  => 'Seulement ceux pour lesquels…',
            'filters_helper'           => 'Facultatif. Sans aucun, ils comptent tous.',
            'filters_attribute'        => 'Vrai quand…',
            'filters_attribute_helper' => 'Tout ceci doit être vrai.',
            'add_filter'               => 'Ajouter une règle',
            'column'                   => 'Champ',
            'operator'                 => 'Est',
            'value'                    => 'Valeur',
            'label'                    => 'Nom',
            'label_helper'             => 'Ce que ça s\'appelle dans l\'éditeur d\'étapes.',
            'key_helper'               => 'Comment les étapes y font référence. Ne peut plus être modifié une fois que des étapes l\'utilisent.',
            'key_taken'                => 'La clé [:key] appartient à une condition enregistrée dans le code.',
            'is_active_helper'         => 'Une condition inactive n\'est jamais remplie.',
            'question'                 => 'Demande',
        ],

        'placeholders' => [
            'label' => 'A ajouté un client',
        ],

        'empty' => [
            'heading'     => 'Aucune condition pour l\'instant',
            'description' => 'Une condition est ce qui permet à une étape de se terminer toute seule — y compris pour ceux qui ont fait la chose il y a longtemps. Écrivez-en une ici, ou lancez php artisan make:onboarding-condition pour une question qu\'un formulaire ne peut pas poser.',
        ],
    ],

];
