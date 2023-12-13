<?php
    $productsType = [
        'featured' => [
            'title'     => translate('search_page_featured_items_slider_ttl'),
            'desc'      => translate('search_page_featured_items_slider_desc'),
            'img'       => 'featured_items_bg',
            'products'  => $featuredItems,
            'btn_url'   => __SITE_URL . 'items/featured',
            'slider_wr' => 'js-featured-items-slider-wr',
            'name'      => 'featured-items',
        ],
        'popular' => [
            'title'     => translate('search_page_popular_items_slider_ttl'),
            'desc'      => translate('search_page_popular_items_slider_desc'),
            'img'       => 'popular_items_bg',
            'products'  => $mostPopularItems,
            'btn_url'   => __SITE_URL . 'items/popular',
            'slider_wr' => 'js-popular-items-slider-wr',
            'name'      => 'popular-items',
        ],
        'latest' => [
            'title'     => translate('search_page_latest_items_slider_ttl'),
            'desc'      => translate('search_page_latest_items_slider_desc'),
            'img'       => 'latest_items_bg',
            'products'  => $latestItems,
            'btn_url'   => __SITE_URL . 'items/latest',
            'slider_wr' => 'js-latest-items-slider-wr',
            'name'      => 'latest-items',
        ],
    ];
?>

<div class="popular-products-s popular-products-s--lg js-promo-items-slider-wr">
    <?php foreach ($productsType as $item) { ?>
        <div class="popular-products-s__item">
            <div class="popular-products-s__bg">
                <img
                    class="image"
                    width="375"
                    height="330"
                    src="<?php echo asset('public/build/images/items/' . $item['img'] . '.jpg'); ?>"
                    alt="<?php echo $item['title']; ?>"
                >

                <div class="popular-products-s__info">
                    <div class="popular-products-s__ttl"><?php echo $item['title']; ?></div>
                    <div class="popular-products-s__desc"><?php echo $item['desc']; ?></div>
                    <a class="popular-products-s__btn btn btn-primary" href="<?php echo $item['btn_url']; ?>"><?php echo translate('search_page_items_slider_btn'); ?></a>
                </div>
            </div>

            <div class="products-popular-wr">
                <div class="products products--slider-full">
                    <?php
                        views('new/item/list_item_view', [
                            'items'     => $item['products'],
                            'has_hover' => false,
                            'isSmCard'  => true,
                        ]);
                    ?>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<div class="promo-products-s">
    <?php foreach ($productsType as $item) { ?>
        <?php views()->display('new/item/detail_promo_products_slider_view', ['item' => $item]); ?>
    <?php } ?>
</div>
