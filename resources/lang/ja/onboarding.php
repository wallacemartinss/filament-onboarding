<?php

declare(strict_types = 1);

return [

    'checklist' => [
        'mark_done'             => '完了にする',
        'start_tour'            => 'ツアーを開始',
        'go'                    => '移動',
        'skip'                  => 'スキップ',
        'close'                 => '閉じる',
        'dismiss'               => '非表示',
        'done'                  => '完了',
        'restart'               => 'やり直す',
        'restart_confirm'       => '完了した手順やツアーがすべてリセットされます。やり直しますか？',
        'completed_title'       => 'すべて準備完了',
        'completed_description' => 'すべてのステップが完了しました。ようこそ。',
        'footer_note'           => 'あとからこの画面に戻れます。',
    ],

    'welcome' => [
        'begin' => 'はじめる',
        'later' => 'あとで',
        'never' => '今後表示しない',
        'steps' => '{0}まだ何もありません|{1}1 ステップ、あなたのペースで|[2,*]:count ステップ、あなたのペースで',
        'back'  => 'ガイドを再開する',
        'off'   => 'ガイドをオフにしました。リストとプログレスリングは消えますが、この画面は残ります。',
    ],
    'tour' => [
        'blocked'  => '画面はまだ先に進んでいません。何か必要なら入力してから「次へ」をもう一度押してください。ツアーがついていきます。',
        'start'    => '動画を見る',
        'choose'   => 'どの動画を見ますか？',
        'begin'    => '開始',
        'waiting'  => '画面に表示されるのを待っています。フォームを入力し続けてください。ツアーがついていきます。',
        'skip'     => 'スキップ',
        'previous' => '戻る',
        'next'     => '次へ',
        'finish'   => '完了',
    ],

    'page' => [
        'collapse' => 'このフローを折りたたむ',
        'expand'   => 'このフローを開く',
        'meta'     => [
            'completed' => ':total 個中 :count 個完了',
            'remaining' => '{1}残り 1 件|[2,*]残り :count 件',
            'skipped'   => '{1}スキップ 1 件|[2,*]スキップ :count 件',
        ],
        'open_image'           => '画像を開く：:title',
        'title'                => 'はじめる',
        'subheading'           => 'いまどこで、何が残っているか。',
        'next'                 => '次の項目',
        'hidden'               => '非表示',
        'restore'              => '再表示',
        'undo'                 => '元に戻す',
        'replay_tour'          => 'ツアーをもう一度見る',
        'replay_video'         => 'もう一度見る',
        'open_again'           => 'もう一度開く',
        'restart'              => '最初からやり直す',
        'restarted'            => 'フローを再起動しました',
        'restarted_reinstated' => ':count 個のステップが即座に戻りました。裏側の作業が完了しているので、完了扱いになっています。',
        'restart_confirm'      => 'このフローを最初からやり直しますか？チェック・スキップ・視聴した記録は消えます。',
        'restart_note'         => '自動で完了するステップはすぐに戻ります。反応するのはこのボタンではなくアプリケーションです。',

        'stats' => [
            'completed' => '完了',
            'remaining' => '残り',
            'skipped'   => 'スキップ',
        ],

        'status' => [
            'completed' => '完了',
            'skipped'   => 'スキップ',
            'next'      => '次の項目',
            'pending'   => '未着手',
        ],

        'completed_at'       => ':time に完了',
        'tour_progress'      => ':total 個中 :reached 個目',
        'awaiting_condition' => '自動で完了',
        'empty_title'        => 'オンボーディングするものがありません',
        'empty_description'  => 'いま対象者に表示できるフローはありません。',
    ],

    'media' => [
        'watch'   => '視聴',
        'resume'  => '続きから',
        'watched' => '視聴済み',
    ],

    'enums' => [

        'condition_type' => [
            'aggregate' => [
                'label'       => '件数を数える',
                'description' => '顧客、請求書、サーバーなど。彼らが持っているもの。「顧客が 1 件以上」。',
            ],
            'attribute' => [
                'label'       => '属性を調べる',
                'description' => 'ユーザー自身の列。「彼のメールは検証済み」。',
            ],
        ],

        'condition_operator' => [
            'equals'                => '等しい',
            'not_equals'            => '等しくない',
            'greater_than'          => 'より大きい',
            'greater_than_or_equal' => '以上',
            'less_than'             => 'より小さい',
            'less_than_or_equal'    => '以下',
            'contains'              => 'を含む',
            'is_set'                => '入力済み',
            'is_empty'              => '空',
        ],
        'step_type' => [
            'task' => 'タスク',
            'tour' => 'ツアー',
        ],

        'media_type' => [
            'none'  => 'なし',
            'image' => '画像',
            'video' => '動画',
        ],

        'media_source' => [
            'upload' => [
                'label'       => 'アップロード',
                'description' => '設定したストレージ（S3、R2、ローカル）に保存されます。',
            ],
            'url' => [
                'label'       => '直接リンク',
                'description' => '別の場所にホストされているファイル。',
            ],
            'youtube' => [
                'label'       => 'YouTube',
                'description' => '視聴時間を計測できます。',
            ],
            'vimeo' => [
                'label'       => 'Vimeo',
                'description' => '視聴時間を計測できます。',
            ],
            'embed' => [
                'label'       => 'その他（iframe）',
                'description' => '再生できますが、視聴時間は計測できません。',
            ],
        ],

        'modal_position' => [
            'center'       => '中央',
            'top'          => '上',
            'bottom'       => '下',
            'top-left'     => '左上',
            'top-right'    => '右上',
            'bottom-left'  => '左下',
            'bottom-right' => '右下',
        ],

        'completion_mode' => [
            'manual' => [
                'label'       => '手動',
                'description' => 'ユーザーが自分でチェックします。',
            ],
            'condition' => [
                'label'       => '条件',
                'description' => '登録済みのチェックが通ったら自動で完了します。',
            ],
            'visit' => [
                'label'       => 'ページ訪問',
                'description' => 'ユーザーが指定の URL に到達したら完了します。',
            ],
            'video' => [
                'label'       => '動画視聴',
                'description' => 'ステップの動画を十分に見たら完了します。',
            ],
            'programmatic' => [
                'label'       => 'プログラム',
                'description' => 'アプリケーションのコードだけが完了にできます。',
            ],
        ],
    ],

    'resource' => [
        'singular'   => 'オンボーディングフロー',
        'plural'     => 'オンボーディングフロー',
        'all_panels' => 'すべてのパネル',

        'placeholders' => [
            'flow_title'       => '…からはじめる',
            'flow_description' => 'ユーザーが得られるものを 1 行で。',
            'step_title'       => '最初のサーバーを接続する',
            'step_description' => '1〜2 行で。「何を」だけでなく「なぜ」を。',
        ],

        'no_steps_warning' => 'このフローにはステップがないため、誰にも表示されません。',

        'empty' => [
            'heading'     => 'フローはまだありません',
            'description' => 'フローはユーザーがたどるリストです。作成してステップを追加しましょう。',
        ],

        'sections' => [
            'overview'                => '概要',
            'publishing_description'  => '誰に、どこで、どの順番で見せるか。',
            'appearance_description'  => 'リストでの見え方。',
            'content_description'     => 'ユーザーが読む内容。対応しているすべての言語で。',
            'publishing'              => '公開',
            'appearance'              => '外観',
            'rules'                   => 'ルール',
            'destination'             => '遷移先',
            'destination_description' => 'ボタンがどこへ飛ぶか。パネルからページを選ぶ。URL は控えです。',
            'content'                 => 'コンテンツ',
            'settings'                => '設定',
            'behaviour'               => '動作',
            'media'                   => 'メディア',
            'media_description'       => '表示用の画像、または視聴用の動画。パネルの上にウィンドウで開きます。',
            'tour'                    => 'ツアー',
            'tour_description'        => 'このツアーがハイライトする要素を順番に。',
        ],

        'steps' => [
            'title'             => 'ステップ',
            'empty_heading'     => 'ステップはまだありません',
            'empty_description' => '最初のステップを追加してください。ステップがないフローは誰にも表示されません。',
        ],

        'fields' => [
            'title'                 => 'タイトル',
            'description'           => '説明',
            'key'                   => 'キー',
            'key_helper'            => 'コードで使用します。変更すると既存の進捗が壊れます。',
            'step_key_helper'       => 'フロー内で一意。Onboarding::for($user)->complete(...) で使用します。',
            'panel'                 => 'パネル',
            'panel_helper'          => '空欄ならすべてのパネルでフローが表示されます。',
            'icon'                  => 'アイコン',
            'color'                 => '色',
            'sort_order'            => '順序',
            'is_active'             => '有効',
            'is_dismissible'        => '非表示にできる',
            'is_dismissible_helper' => 'ユーザーがリストを完全に隠せるようにします。',
            'steps'                 => 'ステップ',
            'updated_at'            => '更新日',
            'type'                  => 'タイプ',
            'completion_mode'       => '完了の判定',
            'condition'             => '条件',
            'condition_helper'      => 'アプリケーションが登録します。すでに満たされているステップは完了として戻ります。',
            'condition_none'        => 'まだ条件がありません。オンボーディング → 条件 で作成するか、php artisan make:onboarding-condition を実行すると、ここに表示されます。',
            'visit_url'             => 'URL',
            'visit_url_helper'      => 'ワイルドカード * に対応しています：/app/*/servers/create',
            'cta_label'             => 'ボタン文言',
            'cta_url'               => 'カスタム URL',
            'cta_url_helper'        => '遷移先がリストにない場合のみ。{tenant} は自動で入ります。',
            'cta_route'             => '遷移先',
            'cta_route_helper'      => 'パネルのページ。URL より推奨。slug が変わっても壊れません。',
            'is_required'           => '必須',
            'is_required_helper'    => '任意ステップはスキップできます。',
            'visibility'            => '表示条件',
            'visibility_helper'     => 'この条件を満たす人にだけ表示されます。プランや機能の後ろにステップを隠すときに使います。',
            'visibility_everyone'   => '全員',

            'media_type'             => 'メディア',
            'media_source'           => 'ソース',
            'media_file'             => 'ファイル',
            'media_url'              => 'URL',
            'media_url_helper'       => 'リンクをそのまま貼り付けてください。watch、share、短縮 URL のいずれでも動きます。',
            'media_caption'          => 'キャプション',
            'modal_position'         => 'モーダルの位置',
            'modal_position_helper'  => '角に置くと、後ろのページが使えるままになります。',
            'modal_position_default' => 'パネルのデフォルト',
            'video_threshold'        => '視聴とみなす割合',
            'video_threshold_helper' => '視聴によって完了とするパーセント。',
            'is_active_helper'       => '無効なフローはすべてのパネルから消えます。',
            'sort_order_helper'      => '小さい数字が先に来ます。',
            'type_helper'            => 'タスクは実行する作業。ツアーは一緒に歩くものです。',
            'completion_mode_helper' => 'このステップを完了にするのは何か。',
        ],

        'tour' => [
            'optional'        => '無ければスキップ',
            'optional_helper' => 'まだ存在しないもの向け。空のタブやデータのないグラフを待たずに、ツアーは次へ進みます。',
            'advance'         => '進める操作',
            'advance_helper'  => 'このステップへアプリケーションを移動させるコントロール。ウィザードの「次へ」ボタン、タブなど。ユーザーが進み、要素がまだ画面に出ていないときにクリックします。',
            'add'             => 'ステップを追加',
            'target'          => '何をハイライト',
            'target_helper'   => 'パネル自身から読み取ります。そのページのフォーム欄、ボタン、表。外部から読めないフォームもあります（編集中のレコードや閲覧者に依存する欄）。その欄は一覧に出ないので、CSS セレクターを使ってください。',
            'targets'         => [
                'on_this_page' => 'このページ',
                'widgets'      => 'ウィジェット',
                'advanced'     => '詳細',
                'custom'       => '独自の CSS セレクター…',
                'table'        => '表',
                'search'       => '検索欄',
                'submit'       => '保存ボタン',
                'column'       => ':label 列',
                'new_record'   => '「:label を新規」ボタン',
            ],
            'selector'        => 'CSS セレクター',
            'selector_helper' => 'ハイライトする要素。例：[data-onboarding="create-server"]。ウィジェットを選んだら空欄。',
            'widget'          => 'ウィジェット',
            'widget_helper'   => 'パネルのウィジェット。ページ上にあるのでセレクターは不要。',
            'placement'       => '位置',
            'placements'      => [
                'auto'   => '自動',
                'top'    => '上',
                'bottom' => '下',
            ],
            'route'             => 'ページ',
            'route_helper'      => 'このステップが別の場所にある場合のみ。ツアーはそこへ移動して続きます。',
            'url'               => 'カスタム URL',
            'url_helper'        => 'そのページがリストにない場合のみ。',
            'body'              => 'テキスト',
            'visibility'        => '表示条件',
            'visibility_helper' => '空欄なら全員に表示されます。プランにない機能を指すステップは何もハイライトしません。',
        ],

        'targets' => [
            'resources'      => 'リソース',
            'pages'          => 'ページ',
            'tenant_profile' => 'テナント情報',
            'page_names'     => [
                'index'  => '一覧',
                'create' => '新規',
            ],
        ],
    ],

    'conditions' => [
        'singular'   => '条件',
        'plural'     => '条件',
        'subheading' => 'あなたのステップが相手に投げられる質問です。コードではなくここに書きます。',
        'at_least'   => ':count 以上',

        'sections' => [
            'question'             => '質問',
            'question_description' => 'その人が何かを満たしていればステップは完了します。',
            'naming'               => '命名',
            'naming_description'   => 'ステップ作成時にドロップダウンから選ぶ名前。',
        ],

        'fields' => [
            'type'                     => '何を尋ねるか',
            'model'                    => '何を数えるか',
            'model_helper'             => '彼らが持つべきもの：顧客、請求書、サーバー。',
            'minimum'                  => '最小値',
            'minimum_helper'           => '件数。だいたい 1 です。',
            'subject_column'           => '対象との結び付け',
            'subject_column_helper'    => 'オンボーディング中のユーザーの ID を保持する列。',
            'scope_column'             => 'テナントで絞る列',
            'scope_column_helper'      => 'モデルがテナントに限定されている場合のみ。それ以外は空欄。',
            'scope_column_none'        => 'テナントで絞らない',
            'filters'                  => 'のみ数える',
            'filters_helper'           => '任意。条件を 1 つも付けない場合はすべて数えます。',
            'filters_attribute'        => '条件',
            'filters_attribute_helper' => 'すべて満たす必要があります。',
            'add_filter'               => 'ルールを追加',
            'column'                   => '列',
            'operator'                 => 'が',
            'value'                    => '値',
            'label'                    => '名前',
            'label_helper'             => 'ステップエディタに表示される名前。',
            'key_helper'               => 'ステップがこれを参照する識別子。ステップが使用し始めた後は変更できません。',
            'key_taken'                => 'キー [:key] はコードで登録済みの条件が使用しています。',
            'is_active_helper'         => '無効な条件は決して通りません。',
            'question'                 => '質問',
        ],

        'placeholders' => [
            'label' => '顧客を追加',
        ],

        'empty' => [
            'heading'     => '条件はまだありません',
            'description' => '条件があることでステップは自動で完了します。ずっと前に済んでいる人にも。ここで作成するか、フォームでは聞けない質問には php artisan make:onboarding-condition を実行してください。',
        ],
    ],

];
