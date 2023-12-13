
<?php if (in_array($page_name, ['reviews', 'reviews_ep', 'reviews_external'])) {?>
    <?php views('new/item/header_reviews_view'); ?>
<?php } ?>

<div class="product-sidebar">
    <div class="title-public pt-0">
        <h2 class="title-public__txt">Sold by</h2>
        <?php if (is_certified((int) $item['user_group'])) { ?>
            <div class="product-sidebar__certified-block product-sidebar__certified-block--desktop">
                <div class="product-sidebar__certified-info-wrapper">
                    <div class="product-sidebar__certified-info">
                        <picture class="product-sidebar__certified-info-bg">
                            <img class="image js-lazy" src="<?php echo getLazyImage(530, 164); ?>" data-src="<?php echo asset("public/build/images/items/hover-info-bg.png"); ?>" data-srcset="<?php echo asset("public/build/images/items/hover-info-bg.png"); ?> 1x, <?php echo asset("public/build/images/items/hover-info-bg@2x.png"); ?> 2x" alt="Certified">
                        </picture>
                        <div class="product-sidebar__certified-header">
                            <div class="product-sidebar__certified-icon">
                                <img
                                    class="image js-lazy"
                                    src="<?php echo getLazyImage(84, 90); ?>"
                                    data-src="<?php echo asset("public/build/images/items/certified_popup-img.png"); ?>"
                                    data-srcset="<?php echo asset("public/build/images/items/certified_popup-img.png"); ?> 1x, <?php echo asset("public/build/images/items/certified_popup-img@2x.png"); ?> 2x"
                                    alt="Certified">
                            </div>
                        </div>
                        <h4 class="product-sidebar__certified-title"><?php echo translate('seller_item_page_certified_info_title', ['{GROUP_TYPE}' => is_manufacturer((int) $item['user_group']) ? 'manufacturer': 'seller']); ?></h4>
                        <p class="product-sidebar__certified-text">
                            <?php echo translate('seller_item_page_certified_info_text', [
                                '{{START_TAG}}'         => '<a class="display-ib" href="' . __SITE_URL . 'about/certification_and_upgrade_benefits' . '" target="_blank">',
                                '{{END_TAG}}'           => '</a>',
                                '{{GROUP_TYPE}}' => is_manufacturer((int) $item['user_group']) ? 'manufacturer': 'seller',
                            ]); ?>

                        </p>
                    </div>
                </div>
                <div class="product-sidebar__certified-body">
                    <div class="product-sidebar__certified-icon">
                        <img class="image js-lazy" src="<?php echo getLazyImage(18, 23); ?>" data-src="<?php echo asset("public/build/images/index/items/certified_user.png"); ?>" data-srcset="<?php echo asset("public/build/images/items/certified_user.png"); ?> 1x, <?php echo asset("public/build/images/items/certified_user@2x.png"); ?> 2x" alt="Certified">
                    </div>
                    <span><?php echo translate('seller_item_page_certified_badge') ?></span>
                </div>
            </div>
            <div class="product-sidebar__certified-block product-sidebar__certified-block--tablet">
                <?php
                    $modalSubTitle = translate(
                        'seller_item_page_certified_info_text', [
                            '{{START_TAG}}'  => '<a class="display-ib" href="' . __SITE_URL . 'about/certification_and_upgrade_benefits' . '" target="_blank">',
                            '{{END_TAG}}'    => '</a>',
                            '{{GROUP_TYPE}}' => is_manufacturer((int) $item['user_group']) ? 'manufacturer': 'seller',
                        ],
                        true
                    )?>
                <div
                    class="product-sidebar__certified-body call-function call-action"
                    data-callback="openCertifiedModal"
                    data-js-action="certified-modal:open"
                    data-title="<?php echo translate('seller_item_page_certified_info_title'); ?>"
                    data-sub-title="<?php echo $modalSubTitle; ?>"
                    data-icon-image="<?php echo asset("public/build/images/items/certified_popup-img.png"); ?>"
                >
                    <div class="product-sidebar__certified-icon">
                        <img class="image js-lazy" src="<?php echo getLazyImage(18, 23); ?>" data-src="<?php echo asset("public/build/images/index/items/certified_user.png"); ?>" data-srcset="<?php echo asset("public/build/images/items/certified_user.png"); ?> 1x, <?php echo asset("public/build/images/items/certified_user@2x.png"); ?> 2x" alt="Certified">
                    </div>
                    <span><?php echo translate('seller_item_page_certified_badge') ?></span>
                </div>
            </div>
        <?php } ?>
    </div>

    <?php views()->display('new/directory/list_item_view', ['item' => $company_user, 'distributor' => (int) $item['is_distributor']]); ?>
    <div class="product-sidebar__link">
        <?php echo $chatBtnByItem->button(); ?>
        <a class="link" href="<?php echo getCompanyURL($company_user) . '/products';?>"><span><?php echo translate('seller_product_sidebar_sellers_items_link_sold') ?></span> <i class="ep-icon ep-icon_arrow-line-right "></i></a>
    </div>

    <?php if (!isset($preview_item)) { ?>
        <div class="dn-md">

            <?php echo widgetShowBanner('item_detail_sidebar', 'promo-banner-wr--item-detail'); ?>

            <?php if (!empty($we_recommend)) { ?>
                <div class="title-public title-public--sidebar">
                    <h2 class="title-public__txt"><?php echo translate('seller_product_sidebar_we_recommend_link') ?></h2>
                </div>

                <div class="products-mini products-mini--item-detail">
                    <?php
                    foreach ($we_recommend as $key => $witem) {
                        $link_we_recommend = __SITE_URL . 'item/' . strForURL($witem['title']) . '-' . $witem['id'];
                        views()->display('new/item/list_mini_item_view', ['mini_link' => $link_we_recommend, 'mini_item' => $witem]);
                    }
                    ?>
                </div>
                <a class="btn btn-light btn-block btn-new16" href="<?php echo $more_recomended_link; ?>"><?php echo translate('seller_product_sidebar_we_recommend_see_more') ?></a>
            <?php } ?>

            <?php if (!empty($last_viewed_items)) { ?>
                <div class="title-public title-public--sidebar">
                    <h2 class="title-public__txt"><?php echo translate('seller_product_sidebar_last_viewed_ttl') ?></h2>
                </div>

                <ul id="last-viewed" class="products-mini products-mini--item-detail">
                    <?php foreach ($last_viewed_items as $key => $viewed_item) {
                        $link_last_viewed = __SITE_URL . 'item/' . strForURL($viewed_item['title']) . '-' . $key;
                        views()->display('new/item/list_mini_item_view', ['mini_link' => $link_last_viewed, 'mini_item' => $viewed_item]);
                    } ?>
                </ul>
            <?php } ?>
        </div>
    <?php } ?>
</div>

<?php if (!isset($webpackData)) { ?>
<script>
    var openCertifiedModal = function() {
        open_result_modal({
            type: 'certified',
            title: '<?php echo translate('seller_item_page_certified_info_title') ?>',
            subTitle: `<?php echo translate('seller_item_page_certified_info_text', ['{{START_TAG}}'  => '<a class="display-ib" href="' . __SITE_URL . 'about/certification_and_upgrade_benefits' . '" target="_blank">', '{{END_TAG}}' => '</a>', '{{GROUP_TYPE}}' => is_manufacturer((int) $item['user_group']) ? 'manufacturer': 'seller',]); ?>`,
            iconImage: '<?php echo asset("public/build/images/items/certified_popup-img.png"); ?>',
            iconModalType: 'iconImage',
            closable: true,
            closeByBg: true,
            classes: 'bootstrap-dialog--results-certified'
        });
    };
</script>
<?php } ?>
