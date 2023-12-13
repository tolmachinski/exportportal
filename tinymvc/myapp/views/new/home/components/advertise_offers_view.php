<?php
    $benefitsData = [
        'reversed' => false,
        'atasType' => 'seller',
        'title'    => translate('home_advertise_your_offers_header_title'),
        'subTitle' => translate('home_advertise_your_offers_header_subtitle'),
        'benefits' => [
            [
                'icon'      => asset('public/build/images/index/benefits/icons/increased-sales.svg'),
                'title'     => translate('home_advertise_your_offers_first_title'),
                'paragraph' => translate('home_advertise_your_offers_first_desc'),
            ],
            [
                'icon'      => asset('public/build/images/index/benefits/icons/more-recognition.svg'),
                'title'     => translate('home_advertise_your_offers_second_title'),
                'paragraph' => translate('home_advertise_your_offers_second_desc'),
            ],
            [
                'icon'      => asset('public/build/images/index/benefits/icons/boost-visibility.svg'),
                'title'     => translate('home_advertise_your_offers_third_title'),
                'paragraph' => translate('home_advertise_your_offers_third_desc'),
            ],
        ],
        'picture'  => [
            'desktop'    => asset('public/build/images/index/benefits/offers-benefits-d.jpg'),
            'tablet'     => asset('public/build/images/index/benefits/offers-benefits-t.jpg'),
            'mobile'     => asset('public/build/images/index/benefits/offers-benefits-m.jpg'),
            'desktop@2x' => asset('public/build/images/index/benefits/offers-benefits-d@2x.jpg'),
            'tablet@2x'  => asset('public/build/images/index/benefits/offers-benefits-t@2x.jpg'),
            'mobile@2x'  => asset('public/build/images/index/benefits/offers-benefits-m@2x.jpg'),
        ],
        'button'   => [
            'text' => translate('home_advertise_your_offers_btn'),
            'href' => is_certified() ? __SITE_URL . 'items/my' : __SITE_URL . 'upgrade',
        ],
    ];

    views('new/home/components/benefits_view', ['benefitsData' => $benefitsData]);
