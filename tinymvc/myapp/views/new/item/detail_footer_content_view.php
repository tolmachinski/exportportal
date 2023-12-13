<?php
    $productsType = [
        'featured' => [
            'title'     => translate('item_detail_featured_items_slider_ttl_new'),
            'desc'      => translate('item_detail_featured_items_slider_desc'),
            'img'       => 'featured_items_bg',
            'products'  => $featuredItems,
            'btn_url'   => __SITE_URL . 'items/featured',
            'slider_wr' => 'js-featured-items-slider-wr',
            'name'      => 'featured-items',
        ],
        'popular' => [
            'title'     => translate('item_detail_popular_items_slider_ttl_new'),
            'desc'      => translate('item_detail_popular_items_slider_desc'),
            'img'       => 'popular_items_bg',
            'products'  => $mostPopularItems,
            'btn_url'   => __SITE_URL . 'items/popular',
            'slider_wr' => 'js-popular-items-slider-wr',
            'name'      => 'popular-items',
        ],
        'latest' => [
            'title'     => translate('item_detail_latest_items_slider_ttl_new'),
            'desc'      => translate('item_detail_latest_items_slider_desc'),
            'img'       => 'latest_items_bg',
            'products'  => $latestItems,
            'btn_url'   => __SITE_URL . 'items/latest',
            'slider_wr' => 'js-latest-items-slider-wr',
            'name'      => 'latest-items',
        ],
    ];
?>

<?php if (!empty($similarItems)) { ?>
    <section class="container-center-sm">
        <div class="similar-items">
            <h3 class="similar-items__title"><?php echo translate("item_detail_similar_items_ttl") ?></h2>

            <div
                id="js-similar-products-slider"
                class="products products--slider-full<?php echo isset($webpackData) ? ' loading' : '';  ?>"
                data-items-count="<?php echo count($similarItems); ?>"
                data-lazy-name="similar-items"
                data-item="<?php echo $item['id']; ?>"
                data-category="<?php echo $item['id_cat']; ?>"
            >
                <?php
                    if (!isset($webpackData)) {
                        views('new/item/list_item_view', ['items' => $similarItems, 'has_hover' => false]);
                    }
                ?>
            </div>

            <?php
                if (isset($webpackData)) {
                    views("new/partials/ajax_loader_view");
                }
            ?>
        </div>
    </section>
<?php } ?>

<section class="popular-products-s">
    <div class="popular-products-s__inner container-center-sm">
        <div class="popular-products-s__wr js-promo-items-slider-wr loading" data-lazy-name="promo-items">
            <?php
                if (!isset($webpackData) || !$isItemDetailPage) {
                    views('new/item/promo_products_slider_item_view', ['productsType' => $productsType, 'fetchItems' => true]);
                }
            ?>
        </div>

        <?php
            // no add to search page
            if (isset($webpackData) && $isItemDetailPage) {
                views('new/partials/ajax_loader_view');
            }
        ?>
    </div>
</section>

<section class="container-center-sm">
    <div class="promo-products-s">
        <?php
            foreach ($productsType as $item) {
                views('new/item/detail_promo_products_slider_view', ['item' => $item, 'isSmCard' => true,]);
            }
        ?>
    </div>
</section>

<?php if (!isset($webpackData)) { ?>
    <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/item/products-slider.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/slick/index.js'); ?>"></script>
<?php } ?>
