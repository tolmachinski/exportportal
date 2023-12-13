<?php

    $practicesGuideData = [
        'title'       => translate('home_best_practices_buyer_title'),
        'description' => translate('home_best_practices_buyer_desc'),
        'group'       => 'buyer',
        'image'       => [
            'desktop'    => asset('public/build/images/index/practices-guide/buyers.png'),
            'desktop@2x' => asset('public/build/images/index/practices-guide/buyers@2x.png'),
        ],
    ];

    views('new/home/components/practices_guide_view', ['data' => $practicesGuideData]);
