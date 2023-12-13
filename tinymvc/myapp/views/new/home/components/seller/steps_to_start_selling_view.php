<?php
    $stepsData = [
        1 => [
            'title'       => translate('home_first_step_to_start_selling_title'),
            'description' => is_seller() ? translate('home_first_step_to_start_selling_description') : translate('home_first_step_to_start_selling_manufacturer_desc'),
            'link'        => [
                'text' => translate('home_first_step_to_start_selling_title_link'),
                'href' => __SITE_URL . 'usr/' . strForUrl(user_name_session()) . '-' . id_session(),
            ],
            'video'       => [
                'picture'     => [
                    'desktop'    => asset('public/build/images/index/start-steps/step1-d.jpg'),
                    'tablet'     => asset('public/build/images/index/start-steps/step1-t.jpg'),
                    'mobile'     => asset('public/build/images/index/start-steps/step1-m.jpg'),
                    'desktop@2x' => asset('public/build/images/index/start-steps/step1-d@2x.jpg'),
                    'tablet@2x'  => asset('public/build/images/index/start-steps/step1-t@2x.jpg'),
                    'mobile@2x'  => asset('public/build/images/index/start-steps/step1-m@2x.jpg'),
                ],
                'short_url'   => 'kJkCxgIeNFQ',
            ],
        ],
        2 => [
            'title'       => translate('home_second_step_to_start_selling_title'),
            'description' => translate('home_second_step_to_start_selling_description'),
            'link'        => [
                'text' => translate('home_second_step_to_start_selling_title_link'),
                'href' => get_static_url('items/choose_category'),
            ],
            'video'       => [
                'picture'     => [
                    'desktop'    => asset('public/build/images/index/start-steps/step2-d.jpg'),
                    'tablet'     => asset('public/build/images/index/start-steps/step2-t.jpg'),
                    'mobile'     => asset('public/build/images/index/start-steps/step2-m.jpg'),
                    'desktop@2x' => asset('public/build/images/index/start-steps/step2-d@2x.jpg'),
                    'tablet@2x'  => asset('public/build/images/index/start-steps/step2-t@2x.jpg'),
                    'mobile@2x'  => asset('public/build/images/index/start-steps/step2-m@2x.jpg'),
                ],
                'short_url'   => 'Z-BJhfVcp_4',
            ],
        ],
        3 => [
            'title'       => translate('home_third_step_to_start_selling_title'),
            'description' => translate('home_third_step_to_start_selling_description'),
            'link'        => [
                'text' => translate('home_third_step_to_start_selling_title_link'),
                'href' => get_static_url('items/choose_category'),
            ],
            'video'       => [
                'picture'     => [
                    'desktop'    => asset('public/build/images/index/start-steps/step3-d.jpg'),
                    'tablet'     => asset('public/build/images/index/start-steps/step3-t.jpg'),
                    'mobile'     => asset('public/build/images/index/start-steps/step3-m.jpg'),
                    'desktop@2x' => asset('public/build/images/index/start-steps/step3-d@2x.jpg'),
                    'tablet@2x'  => asset('public/build/images/index/start-steps/step3-t@2x.jpg'),
                    'mobile@2x'  => asset('public/build/images/index/start-steps/step3-m@2x.jpg'),
                ],
                'short_url'   => '5T8IGeCbgS4',
            ],
        ],
    ];

    views('new/home/components/steps_to_start_view', [
        'steps'        => $stepsData,
        'sectionTitle' => translate('home_steps_to_start_selling_header_title'),
    ]);
