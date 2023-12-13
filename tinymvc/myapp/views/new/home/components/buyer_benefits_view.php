<?php
    $benefitsData = [
        "reversed"    => false,
        "atasType"    => "buyer",
        "title" => translate('home_benefits_buyer_title'),
        "link"  => [
            "text" => translate('home_benefits_buyer_title_link'),
            "href" => __SITE_URL . "buying",
        ],
        "benefits"    => [
            [
                "icon"      => asset('public/build/images/index/benefits/icons/buyer-benefits-one.svg'),
                "title"     => translate('home_benefits_buyer_benefit_one_title'),
                "paragraph" => translate('home_benefits_buyer_benefit_one_paragraph'),
            ],
            [
                "icon"      => asset('public/build/images/index/benefits/icons/buyer-benefits-two.svg'),
                "title"     => translate('home_benefits_buyer_benefit_two_title'),
                "paragraph" => translate('home_benefits_buyer_benefit_two_paragraph'),
            ],
            [
                "icon"      => asset('public/build/images/index/benefits/icons/buyer-benefits-three.svg'),
                "title"     => translate('home_benefits_buyer_benefit_third_title'),
                "paragraph" => translate('home_benefits_buyer_benefit_third_paragraph'),
            ],
            [
                "icon"      => asset('public/build/images/index/benefits/icons/buyer-benefits-four.svg'),
                "title"     => translate('home_benefits_buyer_benefit_four_title'),
                "paragraph" => translate('home_benefits_buyer_benefit_four_paragraph'),
            ],
        ],
        "picture"     => [
            "desktop"    => asset('public/build/images/index/benefits/buyer-benefits-d.jpg'),
            "tablet"     => asset('public/build/images/index/benefits/buyer-benefits-t.jpg'),
            "mobile"     => asset('public/build/images/index/benefits/buyer-benefits-m.jpg'),
            "desktop@2x" => asset('public/build/images/index/benefits/buyer-benefits-d@2x.jpg'),
            "tablet@2x"  => asset('public/build/images/index/benefits/buyer-benefits-t@2x.jpg'),
            "mobile@2x"  => asset('public/build/images/index/benefits/buyer-benefits-m@2x.jpg'),
        ],
        "button"      => [
            "text" => translate('home_benefits_buyer_benefit_button_text'),
            "href" => is_buyer() ? __SITE_URL . 'categories' : __SITE_URL . 'register/buyer',
        ],
    ];

    echo views()->display('new/home/components/benefits_view', ['benefitsData' => $benefitsData]);
