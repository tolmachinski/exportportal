<div class="header container-1420">
    <?php echo widgetShowBanner('header_for_seller', '', 'header-slider', true); ?>
    <?php echo views()->display('new/home/components/headers/blocks/call_to_register_view', [
        'data' => [
            [
                'title'     => translate('home_header_title_add_items'),
                'paragraph' => translate('home_header_paragraph_add_items'),
                'link'      => get_static_url('items/choose_category'),
                'linkText'  => translate('home_header_link_add_items'),
            ],
            [
                'title'     => is_certified() ? translate('home_header_title_feature_items') : translate('home_header_title_become_certified'),
                'paragraph' => is_certified() ? translate('home_header_paragraph_feature_items') : translate('home_header_pararaph_become_certified'),
                'link'      => is_certified() ? get_static_url('items/my') : get_static_url('upgrade'),
                'linkText'  => is_certified() ? translate('home_header_link_feature_items') : translate('home_header_link_become_certified'),
            ],
            [
                'title'     => translate('home_header_title_find_partner'),
                'paragraph' => translate('home_header_paragraph_find_partner'),
                'link'      => get_static_url('b2b/reg'),
                'linkText'  => translate('home_header_link_find_partner'),
            ],
            [
                'title'     => translate('home_header_title_join_events'),
                'paragraph' => translate('home_header_paragraph_join_events'),
                'link'      => get_static_url('ep_events'),
                'linkText'  => translate('home_header_link_join_events'),
            ],
        ],
    ]); ?>
</div>
