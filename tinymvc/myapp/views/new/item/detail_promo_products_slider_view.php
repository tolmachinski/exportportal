<div class="promo-products-s__item">
    <div class="promo-products-s__bg">
        <picture class="display-b h-100pr">
            <source
                media="(max-width: 475px)"
                srcset="<?php echo getLazyImage(365, 138); ?>"
                data-srcset="<?php echo asset('public/build/images/items/' . $item['img'] . '-mobile.jpg'); ?>"
            >
            <source
                media="(min-width: 768px) and (max-width: 991px)"
                srceset="<?php echo getLazyImage(930, 130); ?>"
                data-srcset="<?php echo asset('public/build/images/items/' . $item['img'] . '-tablet.jpg'); ?>"
            >
            <img
                class="image js-lazy"
                width="1170"
                height="158"
                src="<?php echo getLazyImage(1170, 158); ?>"
                data-src="<?php echo asset('public/build/images/items/' . $item['img'] . '-1200.jpg'); ?>"
                alt="<?php echo $item['title']; ?>"
            >
        </picture>

        <div class="promo-products-s__info">
            <div class="promo-products-s__ttl"><?php echo $item['title']; ?></div>
            <div class="promo-products-s__desc"><?php echo $item['desc']; ?></div>
        </div>
    </div>

    <div
        class="products products--slider-full <?php echo $item['slider_wr']; echo isset($webpackData) && $isItemDetailPage ? ' loading' : '';  ?>"
        data-lazy-name="<?php echo $item['name']; ?>"
        <?php if ($item['products']) {?>
            data-items-count="<?php echo count($item['products']); ?>"
        <?php } ?>
    >
        <?php
            if (!isset($webpackData) || !$isItemDetailPage) {
                views('new/item/list_item_view', ['items' => $item['products'], 'has_hover' => false]);
            }
        ?>
    </div>

    <a class="promo-products-s__btn btn btn-new16 btn-primary" href="<?php echo $item['btn_url']; ?>"><?php echo translate('item_detail_products_slider_btn'); ?></a>
    <?php
        // no add to search page
        if (isset($webpackData) && $isItemDetailPage) {
            views('new/partials/ajax_loader_view');
        }
    ?>
</div>
