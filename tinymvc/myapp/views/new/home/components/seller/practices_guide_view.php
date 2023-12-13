<?php
    $practicesGuideData = [
        'title'       => translate('home_best_practices_seller_title'),
        'description' => translate('home_best_practices_seller_desc'),
        'group'       => 'seller',
        'image'       => [
            'desktop'    => asset('public/build/images/index/practices-guide/sellers.png'),
            'desktop@2x' => asset('public/build/images/index/practices-guide/sellers@2x.png'),
        ],
    ];

    views('new/home/components/practices_guide_view', ['data' => $practicesGuideData]);
