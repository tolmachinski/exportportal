<?php
    $benefitsData = [
        "reversed"    => false,
        "atasType"    => "manufacturer",
        "title" => translate('home_benefits_manufacturer_title'),
        "link"  => [
            "text" => translate('home_benefits_manufacturer_title_link'),
            "href" => __SITE_URL . "manufacturer_description",
        ],
        "benefits"    => [
            [
                "icon"      => asset('public/build/images/index/benefits/icons/manufacturer-benefits-one.svg'),
                "title"     => translate('home_benefits_manufacturer_benefit_one_title'),
                "paragraph" => translate('home_benefits_manufacturer_benefit_one_paragraph'),
            ],
            [
                "icon"      => asset('public/build/images/index/benefits/icons/manufacturer-benefits-two.svg'),
                "title"     => translate('home_benefits_manufacturer_benefit_two_title'),
                "paragraph" => translate('home_benefits_manufacturer_benefit_two_paragraph'),
            ],
            [
                "icon"      => asset('public/build/images/index/benefits/icons/manufacturer-benefits-three.svg'),
                "title"     => translate('home_benefits_manufacturer_benefit_third_title'),
                "paragraph" => translate('home_benefits_manufacturer_benefit_third_paragraph'),
            ],
            [
                "icon"      => asset('public/build/images/index/benefits/icons/manufacturer-benefits-four.svg'),
                "title"     => translate('home_benefits_manufacturer_benefit_four_title'),
                "paragraph" => translate('home_benefits_manufacturer_benefit_four_paragraph'),
            ],
        ],
        "picture"     => [
            "desktop"    => asset('public/build/images/index/benefits/manufacturer-benefits-d.jpg'),
            "tablet"     => asset('public/build/images/index/benefits/manufacturer-benefits-t.jpg'),
            "mobile"     => asset('public/build/images/index/benefits/manufacturer-benefits-m.jpg'),
            "desktop@2x" => asset('public/build/images/index/benefits/manufacturer-benefits-d@2x.jpg'),
            "tablet@2x"  => asset('public/build/images/index/benefits/manufacturer-benefits-t@2x.jpg'),
            "mobile@2x"  => asset('public/build/images/index/benefits/manufacturer-benefits-m@2x.jpg'),
        ],
        "button"      => [
            "text" => translate('home_benefits_manufacturer_benefit_button_text'),
            "href" => __SITE_URL . "register/manufacturer",
        ],
    ];

    echo views()->display('new/home/components/benefits_view', ['benefitsData' => $benefitsData]);
