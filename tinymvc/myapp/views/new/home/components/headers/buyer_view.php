<div class="header container-1420">
    <?php echo widgetShowBanner('header_for_buyer', '', 'header-slider', true); ?>
    <?php echo views()->display('new/home/components/headers/blocks/call_to_register_view', [
        'data' => [
            [
                'title'     => translate('home_header_title_view_best_sellers'),
                'paragraph' => translate('home_header_paragraph_view_best_sellers'),
                'link'      => __SITE_URL . 'items/popular',
                'linkText'  => translate('home_header_link_view_best_sellers'),
            ],
            [
                'title'     => translate('home_header_title_latest_items'),
                'paragraph' => translate('home_header_paragraph_latest_items'),
                'link'      => __SITE_URL . 'items/latest',
                'linkText'  => translate('home_header_link_latest_items'),
            ],
            [
                'title'                 => translate('home_header_title_shop_safely'),
                'paragraph'             => translate('home_header_paragraph_shop_safely'),
                'link'                  => __SITE_URL . 'buying',
                'linkText'              => translate('home_header_link_shop_safely'),
            ],
            [
                'title'     => translate('home_header_title_join_events'),
                'paragraph' => translate('home_header_paragraph_buyer_join_events'),
                'link'      => __SITE_URL . 'ep_events',
                'linkText'  => translate('home_header_link_join_events'),
            ],
        ],
    ]); ?>
</div>
