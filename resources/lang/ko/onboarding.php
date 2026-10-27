<?php

declare(strict_types = 1);

return [

    'checklist' => [
        'mark_done'             => '완료로 표시',
        'start_tour'            => '투어 시작',
        'go'                    => '이동',
        'skip'                  => '건너뛰기',
        'close'                 => '닫기',
        'dismiss'               => '숨기기',
        'done'                  => '완료',
        'completed_title'       => '모든 준비가 끝났습니다',
        'completed_description' => '모든 단계를 완료했습니다. 환영합니다.',
        'footer_note'           => '나중에 이 화면으로 다시 돌아올 수 있습니다.',
    ],

    'welcome' => [
        'begin' => '시작하기',
        'later' => '나중에',
        'never' => '다시 표시하지 않기',
        'steps' => '{0}아직 아무것도 없습니다|{1}단계 1개, 나만의 속도로|[2,*]단계 :count개, 나만의 속도로',
        'back'  => '가이드를 다시 켜기',
        'off'   => '가이드를 껐습니다. 목록과 진행 링은 사라졌지만 이 페이지는 그대로입니다.',
    ],
    'tour' => [
        'blocked'  => '화면이 아직 다음으로 넘어가지 않았습니다. 먼저 할 게 있으면 마친 뒤 «다음»을 다시 눌러 주세요 — 투어가 따라갑니다.',
        'start'    => '동영상 보기',
        'choose'   => '어떤 동영상을 보시겠어요?',
        'begin'    => '시작',
        'waiting'  => '화면에 나타날 때까지 기다리는 중입니다 — 양식을 계속 작성하세요. 투어가 따라갑니다.',
        'skip'     => '건너뛰기',
        'previous' => '이전',
        'next'     => '다음',
        'finish'   => '완료',
    ],

    'page' => [
        'collapse' => '이 흐름 접기',
        'expand'   => '이 흐름 펼치기',
        'meta'     => [
            'completed' => ':total개 중 :count개 완료',
            'remaining' => '{1}1개 남음|[2,*]:count개 남음',
            'skipped'   => '{1}1개 건너뜀|[2,*]:count개 건너뜀',
        ],
        'open_image'           => '이미지 열기: :title',
        'title'                => '시작하기',
        'subheading'           => '지금 어디까지 왔고, 무엇이 남았는지.',
        'next'                 => '다음 항목',
        'hidden'               => '숨겨짐',
        'restore'              => '다시 표시',
        'undo'                 => '실행 취소',
        'replay_tour'          => '투어 다시 보기',
        'replay_video'         => '다시 보기',
        'open_again'           => '다시 열기',
        'restart'              => '처음부터 다시',
        'restarted'            => '흐름을 다시 시작했습니다',
        'restarted_reinstated' => ':count개 단계가 즉시 되돌아왔습니다. 뒤쪽 작업이 이미 끝났으므로 완료 상태입니다.',
        'restart_confirm'      => '이 흐름을 처음부터 다시 시작할까요? 체크한 항목, 건너뛴 항목, 본 동영상이 지워집니다.',
        'restart_note'         => '자동으로 완료되는 단계는 즉시 되돌아옵니다. 이 버튼이 아니라 애플리케이션에 반응하기 때문입니다.',

        'stats' => [
            'completed' => '완료',
            'remaining' => '남음',
            'skipped'   => '건너뜀',
        ],

        'status' => [
            'completed' => '완료',
            'skipped'   => '건너뜀',
            'next'      => '다음 항목',
            'pending'   => '대기 중',
        ],

        'completed_at'       => ':time에 완료',
        'tour_progress'      => ':total개 중 :reached번째',
        'awaiting_condition' => '자동으로 완료됨',
        'empty_title'        => '연결할 것이 없습니다',
        'empty_description'  => '지금 사용자에게 보여줄 흐름이 없습니다.',
    ],

    'media' => [
        'watch'   => '시청',
        'resume'  => '이어서 보기',
        'watched' => '시청함',
    ],

    'enums' => [

        'condition_type' => [
            'aggregate' => [
                'label'       => '소유한 것 세기',
                'description' => '고객, 인보이스, 서버 — 그들의 것. "고객이 1개 이상."',
            ],
            'attribute' => [
                'label'       => '필드 확인하기',
                'description' => '사용자 자신의 열. "이메일 인증 완료."',
            ],
        ],

        'condition_operator' => [
            'equals'                => '같음',
            'not_equals'            => '다름',
            'greater_than'          => '초과',
            'greater_than_or_equal' => '이상',
            'less_than'             => '미만',
            'less_than_or_equal'    => '이하',
            'contains'              => '포함',
            'is_set'                => '입력됨',
            'is_empty'              => '비어 있음',
        ],
        'step_type' => [
            'task' => '작업',
            'tour' => '투어',
        ],

        'media_type' => [
            'none'  => '없음',
            'image' => '이미지',
            'video' => '동영상',
        ],

        'media_source' => [
            'upload' => [
                'label'       => '업로드',
                'description' => '설정한 스토리지(S3, R2, 로컬)에 저장됩니다.',
            ],
            'url' => [
                'label'       => '직접 링크',
                'description' => '다른 곳에 보관된 파일.',
            ],
            'youtube' => [
                'label'       => 'YouTube',
                'description' => '시청 시간을 측정할 수 있습니다.',
            ],
            'vimeo' => [
                'label'       => 'Vimeo',
                'description' => '시청 시간을 측정할 수 있습니다.',
            ],
            'embed' => [
                'label'       => '기타(iframe)',
                'description' => '재생은 되지만 시청 시간을 측정할 수 없습니다.',
            ],
        ],

        'modal_position' => [
            'center'       => '가운데',
            'top'          => '위',
            'bottom'       => '아래',
            'top-left'     => '왼쪽 위',
            'top-right'    => '오른쪽 위',
            'bottom-left'  => '왼쪽 아래',
            'bottom-right' => '오른쪽 아래',
        ],

        'completion_mode' => [
            'manual' => [
                'label'       => '수동',
                'description' => '사용자가 직접 체크합니다.',
            ],
            'condition' => [
                'label'       => '조건',
                'description' => '등록된 검사가 통과하면 자동으로 완료됩니다.',
            ],
            'visit' => [
                'label'       => '페이지 방문',
                'description' => '사용자가 지정한 주소에 도달하면 완료됩니다.',
            ],
            'video' => [
                'label'       => '동영상 시청',
                'description' => '단계의 동영상을 충분히 보면 완료됩니다.',
            ],
            'programmatic' => [
                'label'       => '프로그래밍',
                'description' => '애플리케이션 코드만 완료시킬 수 있습니다.',
            ],
        ],
    ],

    'resource' => [
        'singular'   => '온보딩 흐름',
        'plural'     => '온보딩 흐름',
        'all_panels' => '모든 패널',

        'placeholders' => [
            'flow_title'       => '…부터 시작하기',
            'flow_description' => '사용자가 무엇을 얻게 되는지 한 줄로.',
            'step_title'       => '첫 서버 연결하기',
            'step_description' => '한두 줄. 무엇인지뿐 아니라 왜인지.',
        ],

        'no_steps_warning' => '이 흐름에는 단계가 없어서 누구에게도 보이지 않습니다.',

        'empty' => [
            'heading'     => '아직 흐름이 없습니다',
            'description' => '흐름은 사용자가 따라가는 목록입니다. 하나 만들고 단계를 추가하세요.',
        ],

        'sections' => [
            'overview'                => '개요',
            'publishing_description'  => '누가, 어디에서, 어떤 순서로 이 흐름을 보게 될지.',
            'appearance_description'  => '목록에서 흐름이 어떻게 보이는지.',
            'content_description'     => '사용자가 읽게 될 내용. 지원하는 모든 언어로.',
            'publishing'              => '게시',
            'appearance'              => '모양',
            'rules'                   => '규칙',
            'destination'             => '이동 경로',
            'destination_description' => '버튼이 어디로 가는지. 패널에서 페이지를 고르세요. 주소는 대체 수단입니다.',
            'content'                 => '콘텐츠',
            'settings'                => '설정',
            'behaviour'               => '동작',
            'media'                   => '미디어',
            'media_description'       => '보여 줄 이미지 또는 볼 동영상. 패널 위에 창으로 열립니다.',
            'tour'                    => '투어',
            'tour_description'        => '이 투어가 강조하는 요소를 순서대로.',
        ],

        'steps' => [
            'title'             => '단계',
            'empty_heading'     => '아직 단계가 없습니다',
            'empty_description' => '첫 단계를 추가하세요. 단계가 없으면 이 흐름은 누구에게도 보이지 않습니다.',
        ],

        'fields' => [
            'title'                 => '제목',
            'description'           => '설명',
            'key'                   => '키',
            'key_helper'            => '코드에서 사용합니다. 바꾸면 기존 진행 상황이 깨집니다.',
            'step_key_helper'       => '흐름 안에서 고유해야 합니다. Onboarding::for($user)->complete(...)로 사용합니다.',
            'panel'                 => '패널',
            'panel_helper'          => '비워 두면 모든 패널에 이 흐름이 표시됩니다.',
            'icon'                  => '아이콘',
            'color'                 => '색상',
            'sort_order'            => '순서',
            'is_active'             => '활성',
            'is_dismissible'        => '숨길 수 있음',
            'is_dismissible_helper' => '사용자가 목록을 영구적으로 숨길 수 있게 합니다.',
            'steps'                 => '단계',
            'updated_at'            => '업데이트됨',
            'type'                  => '유형',
            'completion_mode'       => '완료 기준',
            'condition'             => '조건',
            'condition_helper'      => '애플리케이션이 등록합니다. 이미 충족된 단계는 완료로 되돌아옵니다.',
            'condition_none'        => '아직 조건이 없습니다 — 온보딩 → 조건에서 만들거나 php artisan make:onboarding-condition을 실행하면 여기에 나타납니다.',
            'visit_url'             => '주소',
            'visit_url_helper'      => '와일드카드 * 지원: /app/*/servers/create',
            'cta_label'             => '버튼 문구',
            'cta_url'               => '사용자 지정 주소',
            'cta_url_helper'        => '대상이 목록에 없을 때만. {tenant}는 자동으로 채워집니다.',
            'cta_route'             => '이동 경로',
            'cta_route_helper'      => '패널의 페이지. 주소보다 좋습니다. slug가 바뀌어도 그대로 동작합니다.',
            'is_required'           => '필수',
            'is_required_helper'    => '선택 단계는 건너뛸 수 있습니다.',
            'visibility'            => '표시 조건',
            'visibility_helper'     => '이 조건을 만족하는 사람에게만 보입니다. 요금제나 기능 뒤에 단계를 감추는 데 쓰세요.',
            'visibility_everyone'   => '모두',

            'media_type'             => '미디어',
            'media_source'           => '소스',
            'media_file'             => '파일',
            'media_url'              => '주소',
            'media_url_helper'       => '링크를 그대로 붙여 넣으세요. watch, share, 짧은 링크 모두 동작합니다.',
            'media_caption'          => '캡션',
            'modal_position'         => '모달 위치',
            'modal_position_helper'  => '모서리에 두면 뒤쪽 페이지가 그대로 사용할 수 있습니다.',
            'modal_position_default' => '패널 기본값',
            'video_threshold'        => '시청으로 간주',
            'video_threshold_helper' => '얼마나 보면 완료로 볼지 백분율.',
            'is_active_helper'       => '비활성 흐름은 모든 패널에서 사라집니다.',
            'sort_order_helper'      => '작은 숫자가 먼저 옵니다.',
            'type_helper'            => '작업은 해내야 하는 일, 투어는 함께 걸어 보는 것.',
            'completion_mode_helper' => '무엇이 이 단계를 완료로 표시합니까.',
        ],

        'tour' => [
            'optional'        => '없으면 건너뛰기',
            'optional_helper' => '아직 없을 수 있는 것용 — 빈 탭, 데이터 없는 차트. 기다리지 않고 투어가 계속됩니다.',
            'advance'         => '진행 방식',
            'advance_helper'  => '애플리케이션을 이 단계로 이동시키는 컨트롤 — 마법사의 «다음» 버튼, 탭 등. 사용자가 앞으로 움직였고 해당 요소가 아직 화면에 없을 때 클릭합니다.',
            'add'             => '단계 추가',
            'target'          => '무엇을 강조할지',
            'target_helper'   => '패널에서 직접 읽습니다 — 이 페이지의 양식 필드, 버튼, 표. 일부 양식은 외부에서 읽을 수 없습니다(편집 중인 레코드나 현재 사용자에 의존하는 필드). 그런 필드는 목록에 나타나지 않으니 CSS 선택자를 쓰세요.',
            'targets'         => [
                'on_this_page' => '이 페이지',
                'widgets'      => '위젯',
                'advanced'     => '고급',
                'custom'       => '사용자 CSS 선택자…',
                'table'        => '표',
                'search'       => '검색창',
                'submit'       => '저장 버튼',
                'column'       => ':label 열',
                'new_record'   => '«:label 신규» 버튼',
            ],
            'selector'        => 'CSS 선택자',
            'selector_helper' => '강조할 요소. 예: [data-onboarding="create-server"]. 위젯을 선택하면 비워 두세요.',
            'widget'          => '위젯',
            'widget_helper'   => '패널의 위젯. 페이지에 이미 있으므로 선택자가 필요 없습니다.',
            'placement'       => '위치',
            'placements'      => [
                'auto'   => '자동',
                'top'    => '위',
                'bottom' => '아래',
            ],
            'route'             => '페이지',
            'route_helper'      => '이 단계가 다른 곳에 있을 때만. 투어가 그곳으로 이동해 계속합니다.',
            'url'               => '사용자 지정 주소',
            'url_helper'        => '해당 페이지가 목록에 없을 때만.',
            'body'              => '문안',
            'visibility'        => '표시 조건',
            'visibility_helper' => '비워 두면 모두에게 표시됩니다. 플랜에 없는 기능을 가리키는 단계는 아무것도 강조하지 않습니다.',
        ],

        'targets' => [
            'resources'      => '리소스',
            'pages'          => '페이지',
            'tenant_profile' => '테넌트 프로필',
            'page_names'     => [
                'index'  => '목록',
                'create' => '신규',
            ],
        ],
    ],

    'conditions' => [
        'singular'   => '조건',
        'plural'     => '조건',
        'subheading' => '단계가 누구에게 물을 수 있는 질문입니다 — 코드가 아니라 여기에 적으세요.',
        'at_least'   => '최소 :count',

        'sections' => [
            'question'             => '질문',
            'question_description' => '누군가가 무엇을 충족해야 단계가 완료로 봅니다.',
            'naming'               => '이름',
            'naming_description'   => '단계 만들 때 드롭다운에서 고른 이름.',
        ],

        'fields' => [
            'type'                     => '무엇을 묻는가',
            'model'                    => '무엇을 세는가',
            'model_helper'             => '그들이 가져야 할 것: 고객, 인보이스, 서버.',
            'minimum'                  => '최소',
            'minimum_helper'           => '개수. 거의 항상 1입니다.',
            'subject_column'           => '연결 기준 열',
            'subject_column_helper'    => '온보딩 중인 사용자의 식별자를 담고 있는 열.',
            'scope_column'             => '테넌트로 좁히는 열',
            'scope_column_helper'      => '모델이 테넌트로 제한된 경우에만. 그 외에는 비워 두세요.',
            'scope_column_none'        => '테넌트로 좁히지 않음',
            'filters'                  => '다음만 셀',
            'filters_helper'           => '선택 사항. 조건을 하나도 안 넣으면 전부 셉니다.',
            'filters_attribute'        => '조건',
            'filters_attribute_helper' => '모두 충족해야 합니다.',
            'add_filter'               => '규칙 추가',
            'column'                   => '필드',
            'operator'                 => '연산자',
            'value'                    => '값',
            'label'                    => '이름',
            'label_helper'             => '단계 편집기에 보이는 이름.',
            'key_helper'               => '단계가 이것을 참조하는 방식. 단계가 쓰기 시작한 뒤에는 바꿀 수 없습니다.',
            'key_taken'                => '키 [:key]는 코드에 등록된 조건이 사용 중입니다.',
            'is_active_helper'         => '비활성 조건은 절대 통과하지 않습니다.',
            'question'                 => '질문',
        ],

        'placeholders' => [
            'label' => '고객 추가',
        ],

        'empty' => [
            'heading'     => '아직 조건이 없습니다',
            'description' => '조건이 있어야 단계가 스스로 완료됩니다 — 아주 오래전에 해 둔 사람에게도요. 여기서 하나 만들거나, 양식으로 묻기 어려운 질문에는 php artisan make:onboarding-condition을 실행하세요.',
        ],
    ],

];
