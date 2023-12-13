<div class="header container-1420">
    <?php echo widgetShowBanner('header_for_ff', '', 'header-slider', true); ?>
    <?php views()->display('new/home/components/headers/blocks/call_to_register_view', [
        'data' => [
            [
                'title'     => translate('home_header_title_place_bid_today'),
                'paragraph' => translate('home_header_paragraph_place_bid_today'),
                'link'      => get_static_url('orders_bids/upcoming'),
                'linkText'  => translate('home_header_link_place_bid_today'),
            ],
            [
                'title'     => translate('home_header_title_educational_materials'),
                'paragraph' => translate('home_header_paragraph_educational_materials'),
                'link'      => __SHIPPER_URL . 'resources',
                'linkText'  => translate('home_header_link_educational_materials'),
            ],
            [
                'title'     => translate('home_header_title_trade_partnerships'),
                'paragraph' => translate('home_header_paragraph_trade_partnerships'),
                'link'      => get_static_url('about/partnership'),
                'linkText'  => translate('home_header_link_trade_partnerships'),
            ],
            [
                'title'     => translate('home_header_title_join_events_webinars'),
                'paragraph' => translate('home_header_paragraph_join_events_webinars'),
                'link'      => get_static_url('ep_events'),
                'linkText'  => translate('home_header_link_join_events_webinars'),
            ],
        ],
    ]); ?>
</div>
