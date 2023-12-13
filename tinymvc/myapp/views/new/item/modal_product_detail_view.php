<div class="modal-flex__product-info flex-card">
    <div class="modal-flex__product-img flex-card__fixed image-card3">
        <span class="link">
            <?php
                $item_img_link = getDisplayImageLink(array('{ID}' => $item['id'], '{FILE_NAME}' => $photo[0]['photo_name']), 'items.main', array( 'thumb_size' => 1 ));
            ?>
            <img
                class="image"
                src="<?php echo $item_img_link;?>"
                alt="<?php echo cleanOutput($item['title']); ?>"
                title="<?php echo cleanOutput($item['title']); ?>"
            />
        </span>
    </div>

    <div class="modal-flex__product-text flex-card__float">
        <h4 class="modal-flex__product-name"><?php echo cleanOutput($item['title']); ?></h4>

        <div class="js-modal-flex-product-price modal-flex__product-price">
            Price:
            <?php
                $itemParams = [
                    'discount'      => $item['discount'],
                    'finalPrice'    => $item['final_price'],
                    'price'         => $item['price'],
                ];

                if (!empty($itemVariant)) {
                    $itemParams['discount'] = $itemVariant['discount'];
                    $itemParams['finalPrice'] = $itemVariant['final_price'];
                    $itemParams['price'] = $itemVariant['price'];
                }
            ?>

            <?php if($itemParams['discount'] > 0) { ?>
                <del><?php echo get_price($itemParams['price']); ?></del> (discount <?php echo cleanOutput($itemParams['discount']); ?>&#37;)
                <span><?php echo get_price($itemParams['finalPrice']); ?></span>
            <?php } else { ?>
                <span><?php echo get_price($itemParams['price']); ?></span>
            <?php } ?>
            <div class="pt-10 fs-10">*Real price for payment is $ <?php echo get_price($itemParams['finalPrice'], false); ?></div>
        </div>
    </div>
</div>
