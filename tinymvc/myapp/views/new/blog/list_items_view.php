<div class="products-blog-wr">
    <div
        class="js-latest-products-slider products products--slider-full"
        data-slider-name="blog-latest-items"
    >
        <?php
            views('new/item/list_item_view', [
                'items'     => $last_items,
                'has_hover' => false,
                'isSmCard'  => true,
            ]);
        ?>
    </div>

    <div class="products-blog-wr__more">
        <a class="products-blog-wr__more-link" href="<?php echo __SITE_URL;?>items/featured"><?php echo translate('blog_latest_products_button_view_all');?></a>
    </div>
</div>

<?php
    echo dispatchDynamicFragment(
        "blog:slider-items",
        null,
        true
    );
?>

