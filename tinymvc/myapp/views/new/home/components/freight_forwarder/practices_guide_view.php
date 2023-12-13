<?php

    $practicesGuideData = [
        'title'       => translate('home_best_practices_ff_title'),
        'description' => translate('home_best_practices_ff_desc'),
        'group'       => 'freight_forwarder',
        'image'       => [
            'desktop'    => asset('public/build/images/index/practices-guide/freight_forwarder.png'),
            'desktop@2x' => asset('public/build/images/index/practices-guide/freight_forwarder@2x.png'),
        ],
    ];

    views('new/home/components/practices_guide_view', ['data' => $practicesGuideData]);
