
<div
    id="js-basket-similar-items-wr"
    class="basket-similar-items"
>
    <?php if (!empty($similarItems)) { ?>
        <div class="columns-content__ttl">
            <span><?php echo translate("basket_similar_items_ttl"); ?></span>
        </div>

        <div class="products-similar-basket-wr">
            <div
                id="js-basket-similar-products-slider"
                class="products products--slider-full"
                data-items-count="<?php echo $itemsCount; ?>"
            >
                <?php
                    views('new/item/list_item_view', [
                        'items'      => $similarItems,
                        'savedItems' => isset($savedItems) ? $savedItems : [],
                        'has_hover'  => false,
                        'isSmCard'   => true
                    ]);
                ?>
            </div>
        </div>
    <?php } ?>
</div>

<script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/slick/index.js'); ?>"></script>
