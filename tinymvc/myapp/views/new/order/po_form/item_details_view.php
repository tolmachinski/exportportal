<tr>
    <?php if($is_additional){?>
        <td data-title="Ordered item">
            <div class="grid-text">
				<div class="grid-text__item">
                    <?php echo cleanOutput($item['name']); ?>
                </div>
            </div>
        </td>
    <?php } else{?>
        <td data-title="Additional item">
            <div class="grid-text">
				<div class="grid-text__item">
                    <a class="order-detail__prod-link" href="<?php echo  makeItemUrl($item['id_item'], $item['name']); ?>" target="_blank">
                        <?php echo cleanOutput($item['name']); ?>
                    </a>
                </div>
            </div>
            <?php echo cleanOutput($item['detail_ordered']); ?>
        </td>
    <?php }?>
    <td data-title="Quantity">
        <?php echo cleanOutput($item['quantity']); ?>
    </td>
    <td data-title="Unit price">
        <?php if($item['unit_price'] > 0){?>
            $ <?php echo get_price($item['unit_price'], false); ?>
        <?php } else { ?>
            &mdash;
        <?php }?>
    </td>
    <td data-title="Amount" id="total-tr">
        <?php if($item['unit_price'] > 0){?>
            $ <?php echo get_price(($item['quantity'] * $item['unit_price']), false); ?>
        <?php } else{?>
            &mdash;
        <?php }?>
    </td>
</tr>