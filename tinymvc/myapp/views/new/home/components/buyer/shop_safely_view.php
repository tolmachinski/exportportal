<?php

    $data = [
        'title'       => translate('home_shop_safely_title'),
        'description' => translate('home_shop_safely_desc'),
        'group'       => 'buyer',
        'link'        => [
            'text' => translate('home_shop_safely_link'),
            'href' => __SITE_URL . 'categories',
        ],
        'picture'     => [
            'desktop'    => asset('public/build/images/index/shop-safely/shop-safely-d.jpg'),
            '1200'       => asset('public/build/images/index/shop-safely/shop-safely-1200.jpg'),
            'tablet'     => asset('public/build/images/index/shop-safely/shop-safely-t.jpg'),
            'mobile'     => asset('public/build/images/index/shop-safely/shop-safely-m.jpg'),
            'desktop@2x' => asset('public/build/images/index/shop-safely/shop-safely-d@2x.jpg'),
            '1200@2x'    => asset('public/build/images/index/shop-safely/shop-safely-1200@2x.jpg'),
            'tablet@2x'  => asset('public/build/images/index/shop-safely/shop-safely-t@2x.jpg'),
            'mobile@2x'  => asset('public/build/images/index/shop-safely/shop-safely-m@2x.jpg'),
        ],
    ];

    views('new/home/components/promo_card_view', ['data' => $data]);
