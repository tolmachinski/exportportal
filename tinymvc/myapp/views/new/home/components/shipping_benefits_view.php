<?php
    $benefitsData = [
        "reversed"    => false,
        "atasType"    => "shipping",
        "title"       => translate('b2b_benefits_shipping_title'),
        "subTitle"    => translate('b2b_benefits_shipping_subtitle'),
        "benefits"    => [
            [
                "icon"      => asset('public/build/images/index/benefits/icons/shipping-benefits-one.svg'),
                "title"     => translate('b2b_benefits_shipping_benefit_first_title'),
                "paragraph" => translate('b2b_benefits_shipping_benefit_first_paragraph'),
            ],
            [
                "icon"      => asset('public/build/images/index/benefits/icons/shipping-benefits-two.svg'),
                "title"     => translate('b2b_benefits_shipping_benefit_second_title'),
                "paragraph" => translate('b2b_benefits_shipping_benefit_second_paragraph'),
            ],
            [
                "icon"      => asset('public/build/images/index/benefits/icons/shipping-benefits-three.svg'),
                "title"     => translate('b2b_benefits_shipping_benefit_third_title'),
                "paragraph" => translate('b2b_benefits_shipping_benefit_third_paragraph'),
            ],
        ],
        "picture"     => [
            "desktop"    => asset('public/build/images/index/benefits/shipping-benefits.jpg'),
            "tablet"     => asset('public/build/images/index/benefits/shipping-benefits-tablet.jpg'),
            "mobile"     => asset('public/build/images/index/benefits/shipping-benefits-mobile.jpg'),
            "desktop@2x" => asset('public/build/images/index/benefits/shipping-benefits@2x.jpg'),
            "tablet@2x"  => asset('public/build/images/index/benefits/shipping-benefits-tablet@2x.jpg'),
            "mobile@2x"  => asset('public/build/images/index/benefits/shipping-benefits-mobile@2x.jpg'),
        ],
        "button"      => [
            "text" => translate('b2b_benefits_shipping_benefit_button_text'),
            "href" => __SITE_URL . 'about',
        ],
    ];

    echo views()->display('new/home/components/benefits_view', ['benefitsData' => $benefitsData]);
