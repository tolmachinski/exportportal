<ul class="js-community-list product-comments">
	<?php
		if (!empty($reviews)) {
			$additionals['reviews'] = $reviews;
			if (isset($helpful_reviews)) {
				$additionals['helpful_reviews'] = $helpful_reviews;
			}

			views()->display('new/users_reviews/item_view', $additionals);
		} else {
    ?>
		<li><div class="default-alert-b"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_reviews_no_ep_reviews');?></div></li>
    <?php }?>
</ul>
