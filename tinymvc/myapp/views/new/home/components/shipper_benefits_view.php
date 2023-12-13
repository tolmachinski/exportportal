<?php
    $benefitsData = [
        "reversed"    => true,
        "atasType"    => "shipper",
        "title" => translate('home_benefits_shipper_title'),
        "link"  => [
            "text" => translate('home_benefits_shipper_title_link'),
            "href" => __SITE_URL . "shipper_description",
        ],
        "benefits"    => [
            [
                "icon"      => asset('public/build/images/index/benefits/icons/shipper-benefits-one.svg'),
                "title"     => translate('home_benefits_shipper_benefit_first_title'),
                "paragraph" => translate('home_benefits_shipper_benefit_first_paragraph'),
            ],
            [
                "icon"      => asset('public/build/images/index/benefits/icons/shipper-benefits-two.svg'),
                "title"     => translate('home_benefits_shipper_benefit_second_title'),
                "paragraph" => translate('home_benefits_shipper_benefit_second_paragraph'),
            ],
            [
                "icon"      => asset('public/build/images/index/benefits/icons/shipper-benefits-three.svg'),
                "title"     => translate('home_benefits_shipper_benefit_third_title'),
                "paragraph" => translate('home_benefits_shipper_benefit_third_paragraph'),
            ],
            [
                "icon"      => asset('public/build/images/index/benefits/icons/shipper-benefits-four.svg'),
                "title"     => translate('home_benefits_shipper_benefit_fourth_title'),
                "paragraph" => translate('home_benefits_shipper_benefit_fourth_paragraph'),
            ],
        ],
        "picture"     => [
            "desktop"    => asset('public/build/images/index/benefits/shipper-benefits-d.jpg'),
            "tablet"     => asset('public/build/images/index/benefits/shipper-benefits-t.jpg'),
            "mobile"     => asset('public/build/images/index/benefits/shipper-benefits-m.jpg'),
            "desktop@2x" => asset('public/build/images/index/benefits/shipper-benefits-d@2x.jpg'),
            "tablet@2x"  => asset('public/build/images/index/benefits/shipper-benefits-t@2x.jpg'),
            "mobile@2x"  => asset('public/build/images/index/benefits/shipper-benefits-m@2x.jpg'),
        ],
        "button"      => [
            "text" => translate('home_benefits_shipper_benefit_button_text'),
            "href" => __SHIPPER_URL . "register",
        ],
    ];

    echo views()->display('new/home/components/benefits_view', ['benefitsData' => $benefitsData]);
