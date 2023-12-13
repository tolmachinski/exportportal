<?php if (!empty($users_estimates)) { ?>
    <?php foreach ($users_estimates as $key => $user_estimate) { ?>
		<li class="order-users-list__item flex-card" data-estimate="<?php echo $user_estimate['id_request_estimate']; ?>">

			<div class="order-users-list__img image-card3 flex-card__fixed">
				<a class="link" href="<?php echo __SITE_URL; ?>item/<?php echo strForURL($products_list[$user_estimate['id_item']]['title']) . '-' . $user_estimate['id_item']; ?>" target="_blank">
					<?php
						$item_img_link = getDisplayImageLink(array('{ID}' => $user_estimate['id_item'], '{FILE_NAME}' => $user_estimate['photo_name']), 'items.main', array( 'thumb_size' => 1 ));
					?>
					<img
						class="image"
						src="<?php echo $item_img_link; ?>"
						alt="<?php echo $user_estimate['title']; ?>"
					/>
				</a>
			</div>

			<div class="order-users-list__detail flex-card__float">
				<div class="order-users-list__number">
					<?php echo orderNumber($user_estimate['id_request_estimate']); ?>

					<i class="order-popover ep-icon ep-icon_<?php echo $status_array[$user_estimate['status']]['icon_new'];?>" data-placement="top" data-content="<?php echo $status_array[$user_estimate['status']]['title'];?>"></i>
				</div>

				<?php if (!empty($user_estimate['price']) && $user_estimate['price'] != '0.00') { ?>
					<div class="order-users-list__price">
						<?php echo get_price($user_estimate['price']);?> x <?php echo $user_estimate['quantity'];?>
					</div>
				<?php } ?>

				<div class="order-users-list__date"><?php echo formatDate($user_estimate['update_date']);?></div>

				<div class="order-users-list__company">
					<?php if(have_right('buy_item')){
						$company_name = $companies_info[$user_estimate['id_seller']]['name_company'];
					?>
						by <span class="link"><?php echo $company_name;?></span>
					<?php }else if(have_right('sell_item')){
						$user_name = $users_list[$user_estimate['id_buyer']]['username']; ?>
						by <span class="link"><?php echo $user_name;?></span>
					<?php } ?>
				</div>
			</div>

		</li>
	<?php }?>
<?php } else{?>
	<li>
        <div class="info-alert-b">
            <i class="ep-icon ep-icon_info-stroke"></i>
            <span>0 estimates found by this search.</span>
        </div>
    </li>
<?php }?>

<script>
$(function(){
	$('.order-popover').popover({
		container: 'body',
		trigger: 'hover'
	});
});
</script>
