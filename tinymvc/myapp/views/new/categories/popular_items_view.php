<div class="container-center-sm">
    <div class="categories-popular-items">
        <div class="categories-popular-items__row">
            <div class="categories-popular-items__col">
                <div class="categories-popular-items__bg">
                    <picture class="display-b h-100pr">
                        <source
                            media="(max-width: 425px)"
                            srcset="<?php echo getLazyImage(320, 250); ?>"
                            data-srcset="<?php echo asset("public/build/images/categories/most_popular_bg-mobile.jpg"); ?>">
                        <source
                            media="(min-width: 768px) and (max-width: 1024px)"
                            srcset="<?php echo getLazyImage(408, 195); ?>"
                            data-srcset="<?php echo asset("public/build/images/categories/most_popular_bg-tablet.jpg"); ?>">
                        <img
                            class="image js-lazy"
                            width="570"
                            height="250"
                            src="<?php echo getLazyImage(570, 250); ?>"
                            data-src="<?php echo asset("public/build/images/categories/most_popular_bg.jpg"); ?>"
                            alt="<?php echo translate('popular_items_slider_img_alt'); ?>"
                        >
                    </picture>
                </div>
            </div>
            <div class="categories-popular-items__col categories-popular-items__col--right">
                <div class="categories-products-slider-heading">
                    <h2 class="categories-products-slider-heading__title"><?php echo translate('popular_items_slider_ttl'); ?></h2>
                    <p class="categories-products-slider-heading__subtitle"><?php echo translate('popular_items_slider_desc'); ?></p>
                </div>
            </div>
        </div>

        <div class="products-categories-popular-wr">
            <div
                class="products products--slider-full js-categories-popular-items-slider"
                data-items-count="<?php echo count($mostPopularItems); ?>"
                data-slider-name="popular-items"
            >
                <?php views()->display('new/item/list_item_view', ['items' => $mostPopularItems, 'has_hover' => false]); ?>
            </div>
        </div>

        <div class="tac">
            <a class="categories-popular-items__btn btn btn-new16 btn-primary" href="<?php echo __SITE_URL . 'items/popular'; ?>">
                <?php echo translate('popular_items_slider_btn'); ?>
            </a>
        </div>
    </div>
</div>
