<?php
    $benefitsData = [
        'reversed' => true,
        'atasType' => 'seller',
        'title'    => translate('home_certification_benefits_header_title'),
        'link'     => [
            'text' => translate('home_certification_benefits_header_title_link'),
            'href' => __SITE_URL . 'about/certification_and_upgrade_benefits',
        ],
        'benefits' => [
            [
                'icon'      => asset('public/build/images/index/benefits/icons/certification-benefits-one.svg'),
                'title'     => translate('home_certification_benefits_first_title'),
                'paragraph' => translate('home_certification_benefits_first_desc'),
            ],
            [
                'icon'      => asset('public/build/images/index/benefits/icons/certification-benefits-two.svg'),
                'title'     => translate('home_certification_benefits_second_title'),
                'paragraph' => translate('home_certification_benefits_second_desc'),
            ],
            [
                'icon'      => asset('public/build/images/index/benefits/icons/certification-benefits-three.svg'),
                'title'     => translate('home_certification_benefits_third_title'),
                'paragraph' => translate('home_certification_benefits_third_desc'),
            ],
            [
                'icon'      => asset('public/build/images/index/benefits/icons/certification-benefits-four.svg'),
                'title'     => translate('home_certification_benefits_fourth_title'),
                'paragraph' => translate('home_certification_benefits_fourth_desc'),
            ],
        ],
        'picture'  => [
            'desktop'    => asset('public/build/images/index/benefits/certification-benefits-d.jpg'),
            'tablet'     => asset('public/build/images/index/benefits/certification-benefits-t.jpg'),
            'mobile'     => asset('public/build/images/index/benefits/certification-benefits-m.jpg'),
            'desktop@2x' => asset('public/build/images/index/benefits/certification-benefits-d@2x.jpg'),
            'tablet@2x'  => asset('public/build/images/index/benefits/certification-benefits-t@2x.jpg'),
            'mobile@2x'  => asset('public/build/images/index/benefits/certification-benefits-m@2x.jpg'),
        ],
        'button'   => [
            'text' => is_certified() ? translate('home_certification_benefits_extend_upgrade_btn') : translate('home_certification_benefits_get_certified_btn'),
            'href' => is_certified() ? __SITE_URL . 'upgrade' : __SITE_URL . 'about/certification_and_upgrade_benefits',
        ],
    ];

    echo views()->display('new/home/components/benefits_view', ['benefitsData' => $benefitsData]);
