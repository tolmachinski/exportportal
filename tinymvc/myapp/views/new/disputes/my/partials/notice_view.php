<strong>Comment:</strong> <?php echo cleanOutput($comment); ?>
<br><br>
<strong>The buyer:</strong> <?php echo cleanOutput($buyer_name); ?>
<br>
<strong>The seller:</strong> <?php echo  cleanOutput($seller_name); ?>
<?php if (null !== $shipper_name) { ?>
    <br>
    <strong>The freight forwarder:</strong> <?php echo  cleanOutput($shipper_name); ?>
<?php } ?>
<br>
<strong>The manager:</strong> <?php echo cleanOutput($manager_name); ?>
<br>
<strong>The order:</strong> <?php echo  cleanOutput($order_number); ?>
<?php if (null !== $ordered_item_title) { ?>
    <br>
    <strong>The ordered item:</strong> <?php echo  cleanOutput($ordered_item_title); ?>
<?php } ?>
<?php if (null !== $items_title) { ?>
    <br>
    <strong>The ordered item(s)</strong>: <?php echo  cleanOutput($items_title); ?>
<?php } ?>