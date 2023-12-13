<?php
    $practicesGuideData = [
        'title'       => translate('home_best_practices_manufacturer_guide_title'),
        'description' => translate('home_best_practices_manufacturer_guide_desc'),
        'group'       => 'manufacturer',
        'image'       => [
            'desktop'    => asset('public/build/images/index/practices-guide/manufacturers.png'),
            'desktop@2x' => asset('public/build/images/index/practices-guide/manufacturers@2x.png'),
        ],
    ];

    views('new/home/components/practices_guide_view', ['data' => $practicesGuideData]);
