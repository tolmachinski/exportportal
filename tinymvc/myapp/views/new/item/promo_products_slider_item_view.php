<?php foreach ($productsType as $item) { ?>
    <div class="popular-products-s__item">
        <div class="popular-products-s__bg">
            <img
                class="image js-lazy"
                width="412"
                height="286"
                data-src="<?php echo asset('public/build/images/items/' . $item['img'] . '.jpg'); ?>"
                src="<?php echo getLazyImage(412, 286); ?>"
                alt="<?php echo $item['title']; ?>"
            >

            <div class="popular-products-s__info">
                <div class="popular-products-s__ttl"><?php echo $item['title']; ?></div>
                <div class="popular-products-s__desc"><?php echo $item['desc']; ?></div>
                <a class="popular-products-s__btn btn btn-primary" href="<?php echo $item['btn_url']; ?>">
                    <?php echo translate('item_detail_products_slider_btn'); ?>
                </a>
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
