<div class="product-prev flex-card">
	<div class="product-prev__img flex-card__fixed image-card3">
		<a class="link" itemprop="url" href="<?php echo makeItemUrl($item['id'], $item['title']);?>">
			<?php
				$item_img_link = getDisplayImageLink(array('{ID}' => $item['id'], '{FILE_NAME}' => $item['photo_name']), 'items.main', array( 'thumb_size' => 3 ));
			?>
			<img class="image" itemprop="image" src="<?php echo $item_img_link;?>" alt="<?php echo $item['title']?>" data-item="<?php echo $item['id']?>"/>
		</a>
	</div>

	<div class="product-prev__detail flex-card__float">
		<h2 class="product-prev__ttl"><a class="link" href="<?php echo makeItemUrl($item['id'], $item['title']);?>"><?php echo cleanOutput($item['title']);?></a></h2>
		<div class="product-prev__price">
		<?php if ($item['discount']) { ?>
			<span class="product-prev__price-old mr-5"><?php echo get_price($item['price']);?></span>
		<?php } else { ?>
			<span class="product-prev__price-new mr-5"><?php echo get_price($item['final_price']);?></span>
			<div class="fs-12 pt-10">*Real price for payment is $ <span><?php echo get_price($item['final_price'], false);?></span></div>
		<?php } ?>
		</div>
		<div class="product-prev__quantity">Quantity <span class="fs-12 lh-18">(Min <?php echo $item['min_sale_q']?> , Max <?php echo $item['quantity']?> <?php echo $item['unit_name'] ?? '' ?>) </span></div>
	</div>
</div>
