<?php
    $data = [
        'title'       => translate('home_sell_smart_title'),
        'description' => translate('home_sell_smart_desc'),
        'group'       => 'manufacturer',
        'link'        => [
            'text' => translate('home_sell_smart_link'),
            'href' => get_static_url('items/choose_category'),
        ],
        'picture'     => [
            'desktop'    => asset('public/build/images/index/sell-smart/sell-smart-d.jpg'),
            '1200'       => asset('public/build/images/index/sell-smart/sell-smart-1200.jpg'),
            'tablet'     => asset('public/build/images/index/sell-smart/sell-smart-t.jpg'),
            'mobile'     => asset('public/build/images/index/sell-smart/sell-smart-m.jpg'),
            'desktop@2x' => asset('public/build/images/index/sell-smart/sell-smart-d@2x.jpg'),
            '1200@2x'    => asset('public/build/images/index/sell-smart/sell-smart-1200@2x.jpg'),
            'tablet@2x'  => asset('public/build/images/index/sell-smart/sell-smart-t@2x.jpg'),
            'mobile@2x'  => asset('public/build/images/index/sell-smart/sell-smart-m@2x.jpg'),
        ],
    ];

    views('new/home/components/promo_card_view', ['data' => $data]);
