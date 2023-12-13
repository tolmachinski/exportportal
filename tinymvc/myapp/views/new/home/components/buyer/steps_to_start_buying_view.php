<?php

    $stepsData = [
        1 => [
            'title'       => translate('home_first_step_to_start_buying_title'),
            'description' => translate('home_first_step_to_start_buying_desc'),
            'picture'     => [
                'desktop'    => asset('public/build/images/index/start-buying-steps/first-step-d.jpg'),
                'tablet'     => asset('public/build/images/index/start-buying-steps/first-step-t.jpg'),
                'mobile'     => asset('public/build/images/index/start-buying-steps/first-step-m.jpg'),
                'desktop@2x' => asset('public/build/images/index/start-buying-steps/first-step-d@2x.jpg'),
                'tablet@2x'  => asset('public/build/images/index/start-buying-steps/first-step-t@2x.jpg'),
                'mobile@2x'  => asset('public/build/images/index/start-buying-steps/first-step-m@2x.jpg'),
            ],
        ],
        2 => [
            'title'       => translate('home_second_step_to_start_buying_title'),
            'description' => translate('home_second_step_to_start_buying_desc'),
            'picture'     => [
                'desktop'    => asset('public/build/images/index/start-buying-steps/second-step-d.jpg'),
                'tablet'     => asset('public/build/images/index/start-buying-steps/second-step-t.jpg'),
                'mobile'     => asset('public/build/images/index/start-buying-steps/second-step-m.jpg'),
                'desktop@2x' => asset('public/build/images/index/start-buying-steps/second-step-d@2x.jpg'),
                'tablet@2x'  => asset('public/build/images/index/start-buying-steps/second-step-t@2x.jpg'),
                'mobile@2x'  => asset('public/build/images/index/start-buying-steps/second-step-m@2x.jpg'),
            ],
        ],
        3 => [
            'title'       => translate('home_third_step_to_start_buying_title'),
            'description' => translate('home_third_step_to_start_buying_desc'),
            'picture'     => [
                'desktop'    => asset('public/build/images/index/start-buying-steps/third-step-d.jpg'),
                'tablet'     => asset('public/build/images/index/start-buying-steps/third-step-t.jpg'),
                'mobile'     => asset('public/build/images/index/start-buying-steps/third-step-m.jpg'),
                'desktop@2x' => asset('public/build/images/index/start-buying-steps/third-step-d@2x.jpg'),
                'tablet@2x'  => asset('public/build/images/index/start-buying-steps/third-step-t@2x.jpg'),
                'mobile@2x'  => asset('public/build/images/index/start-buying-steps/third-step-m@2x.jpg'),
            ],
        ],
        4 => [
            'title'       => translate('home_fourth_step_to_start_buying_title'),
            'description' => translate('home_fourth_step_to_start_buying_desc'),
            'picture'     => [
                'desktop'    => asset('public/build/images/index/start-buying-steps/fourth-step-d.jpg'),
                'tablet'     => asset('public/build/images/index/start-buying-steps/fourth-step-t.jpg'),
                'mobile'     => asset('public/build/images/index/start-buying-steps/fourth-step-m.jpg'),
                'desktop@2x' => asset('public/build/images/index/start-buying-steps/fourth-step-d@2x.jpg'),
                'tablet@2x'  => asset('public/build/images/index/start-buying-steps/fourth-step-t@2x.jpg'),
                'mobile@2x'  => asset('public/build/images/index/start-buying-steps/fourth-step-m@2x.jpg'),
            ],
        ],
        5 => [
            'title'       => translate('home_fifth_step_to_start_buying_title'),
            'description' => translate('home_fifth_step_to_start_buying_desc'),
            'picture'     => [
                'desktop'    => asset('public/build/images/index/start-buying-steps/fifth-step-d.jpg'),
                'tablet'     => asset('public/build/images/index/start-buying-steps/fifth-step-t.jpg'),
                'mobile'     => asset('public/build/images/index/start-buying-steps/fifth-step-m.jpg'),
                'desktop@2x' => asset('public/build/images/index/start-buying-steps/fifth-step-d@2x.jpg'),
                'tablet@2x'  => asset('public/build/images/index/start-buying-steps/fifth-step-t@2x.jpg'),
                'mobile@2x'  => asset('public/build/images/index/start-buying-steps/fifth-step-m@2x.jpg'),
            ],
        ],
    ];

    views('new/home/components/steps_to_start_view', [
        'steps'        => $stepsData,
        'sectionTitle' => translate('home_steps_to_start_buying_header_title'),
    ]);
