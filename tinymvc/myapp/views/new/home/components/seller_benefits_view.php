<?php
    $benefitsData = [
        "reversed"    => true,
        "atasType"    => "seller",
        "title" => translate('home_benefits_seller_title'),
        "link"  => [
            "text" => translate('home_benefits_seller_title_link'),
            "href" => __SITE_URL . "selling",
        ],
        "benefits"    => [
            [
                "icon"      => asset('public/build/images/index/benefits/icons/seller-benefits-one.svg'),
                "title"     => translate('home_benefits_seller_benefit_one_title'),
                "paragraph" => translate('home_benefits_seller_benefit_one_paragraph'),
            ],
            [
                "icon"      => asset('public/build/images/index/benefits/icons/seller-benefits-two.svg'),
                "title"     => translate('home_benefits_seller_benefit_two_title'),
                "paragraph" => translate('home_benefits_seller_benefit_two_paragraph'),
            ],
            [
                "icon"      => asset('public/build/images/index/benefits/icons/seller-benefits-three.svg'),
                "title"     => translate('home_benefits_seller_benefit_third_title'),
                "paragraph" => translate('home_benefits_seller_benefit_third_paragraph'),
            ],
            [
                "icon"      => asset('public/build/images/index/benefits/icons/seller-benefits-four.svg'),
                "title"     => translate('home_benefits_seller_benefit_four_title'),
                "paragraph" => translate('home_benefits_seller_benefit_four_paragraph'),
            ],
        ],
        "picture"     => [
            "desktop"    => asset('public/build/images/index/benefits/seller-benefits-d.jpg'),
            "tablet"     => asset('public/build/images/index/benefits/seller-benefits-t.jpg'),
            "mobile"     => asset('public/build/images/index/benefits/seller-benefits-m.jpg'),
            "desktop@2x" => asset('public/build/images/index/benefits/seller-benefits-d@2x.jpg'),
            "tablet@2x"  => asset('public/build/images/index/benefits/seller-benefits-t@2x.jpg'),
            "mobile@2x"  => asset('public/build/images/index/benefits/seller-benefits-m@2x.jpg'),
        ],
        "button"      => [
            "text" => translate('home_benefits_seller_benefit_button_text'),
            "href" => __SITE_URL . "register/seller",
        ],
    ];

    echo views()->display('new/home/components/benefits_view', ['benefitsData' => $benefitsData]);
