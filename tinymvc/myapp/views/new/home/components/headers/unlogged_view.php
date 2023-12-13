<div class="header container-1420">
    <?php echo widgetShowBanner('header_for_unlogged', '', 'header-slider', true); ?>
    <?php echo views()->display('new/home/components/headers/blocks/call_to_register_view', [
        'additionalChildClass'  => logged_in() ? ' js-require-logout-systmess' : '',
        'data'          => [
            [
                'title'     => translate('home_header_title_registration_buyer'),
                'paragraph' => translate('home_header_paragraph_registration_buyer'),
                'link'      => get_static_url('register/buyer'),
                'linkText'  => translate('home_header_link_registration_buyer'),
            ],
            [
                'title'     => translate('home_header_title_registration_seller'),
                'paragraph' => translate('home_header_paragraph_registration_seller'),
                'link'      => get_static_url('register/seller'),
                'linkText'  => translate('home_header_link_registration_seller'),
            ],
            [
                'title'     => translate('home_header_title_registration_manufacturer'),
                'paragraph' => translate('home_header_paragraph_registration_manufacturer'),
                'link'      => get_static_url('register/manufacturer'),
                'linkText'  => translate('home_header_link_registration_manufacturer'),
            ],
            [
                'title'     => translate('home_header_title_registration_shipper'),
                'paragraph' => translate('home_header_paragraph_registration_shipper'),
                'link'      => __SHIPPER_URL . 'register',
                'linkText'  => translate('home_header_link_registration_shipper'),
            ],
        ],
    ]); ?>
</div>
