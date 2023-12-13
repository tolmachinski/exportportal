<?php

    $methodsList = [
        [
            'title'       => translate('home_methods_of_selling_first_title'),
            'description' => translate('home_methods_of_selling_first_desc'),
            'picture'     => [
                'desktop'    => asset('public/build/images/index/selling-methods/sample-order-d.jpg'),
                'tablet'     => asset('public/build/images/index/selling-methods/sample-order-t.jpg'),
                'mobile'     => asset('public/build/images/index/selling-methods/sample-order-m.jpg'),
                'desktop@2x' => asset('public/build/images/index/selling-methods/sample-order-d@2x.jpg'),
                'tablet@2x'  => asset('public/build/images/index/selling-methods/sample-order-t@2x.jpg'),
                'mobile@2x'  => asset('public/build/images/index/selling-methods/sample-order-m@2x.jpg'),
            ],
        ],
        [
            'title'       => translate('home_methods_of_selling_second_title'),
            'description' => translate('home_methods_of_selling_second_desc'),
            'picture'     => [
                'desktop'    => asset('public/build/images/index/selling-methods/send-offer-d.jpg'),
                'tablet'     => asset('public/build/images/index/selling-methods/send-offer-t.jpg'),
                'mobile'     => asset('public/build/images/index/selling-methods/send-offer-m.jpg'),
                'desktop@2x' => asset('public/build/images/index/selling-methods/send-offer-d@2x.jpg'),
                'tablet@2x'  => asset('public/build/images/index/selling-methods/send-offer-t@2x.jpg'),
                'mobile@2x'  => asset('public/build/images/index/selling-methods/send-offer-m@2x.jpg'),
            ],
        ],
        [
            'title'       => translate('home_methods_of_selling_third_title'),
            'description' => translate('home_methods_of_selling_third_desc'),
            'picture'     => [
                'desktop'    => asset('public/build/images/index/selling-methods/get-estimate-d.jpg'),
                'tablet'     => asset('public/build/images/index/selling-methods/get-estimate-t.jpg'),
                'mobile'     => asset('public/build/images/index/selling-methods/get-estimate-m.jpg'),
                'desktop@2x' => asset('public/build/images/index/selling-methods/get-estimate-d@2x.jpg'),
                'tablet@2x'  => asset('public/build/images/index/selling-methods/get-estimate-t@2x.jpg'),
                'mobile@2x'  => asset('public/build/images/index/selling-methods/get-estimate-m@2x.jpg'),
            ],
        ],
        [
            'title'       => translate('home_methods_of_selling_fourth_title'),
            'description' => translate('home_methods_of_selling_fourth_desc'),
            'picture'     => [
                'desktop'    => asset('public/build/images/index/selling-methods/producing-request-d.jpg'),
                'tablet'     => asset('public/build/images/index/selling-methods/producing-request-t.jpg'),
                'mobile'     => asset('public/build/images/index/selling-methods/producing-request-m.jpg'),
                'desktop@2x' => asset('public/build/images/index/selling-methods/producing-request-d@2x.jpg'),
                'tablet@2x'  => asset('public/build/images/index/selling-methods/producing-request-t@2x.jpg'),
                'mobile@2x'  => asset('public/build/images/index/selling-methods/producing-request-m@2x.jpg'),
            ],
        ],
        [
            'title'       => translate('home_methods_of_selling_fifth_title'),
            'description' => translate('home_methods_of_selling_fifth_desc'),
            'picture'     => [
                'desktop'    => asset('public/build/images/index/selling-methods/inquiry-d.jpg'),
                'tablet'     => asset('public/build/images/index/selling-methods/inquiry-t.jpg'),
                'mobile'     => asset('public/build/images/index/selling-methods/inquiry-m.jpg'),
                'desktop@2x' => asset('public/build/images/index/selling-methods/inquiry-d@2x.jpg'),
                'tablet@2x'  => asset('public/build/images/index/selling-methods/inquiry-t@2x.jpg'),
                'mobile@2x'  => asset('public/build/images/index/selling-methods/inquiry-m@2x.jpg'),
            ],
        ],
    ];

    views('new/home/components/methods_of_selling_or_buying_view', [
        'methodsList'  => $methodsList,
        'sectionTitle' => translate('home_methods_of_buying_header_title'),
    ]);
