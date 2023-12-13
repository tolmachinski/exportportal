<?php if(!empty($users_inquiries)){?>
	<?php foreach($users_inquiries as $key => $user_inquiry){?>
		<li class="order-users-list__item flex-card" data-inquiry="<?php echo $user_inquiry['id_inquiry'];?>">

			<div class="order-users-list__img image-card3 flex-card__fixed">
				<a class="link" href="<?php echo __SITE_URL; ?>item/<?php echo strForURL($products_list[$user_inquiry['id_item']]['title']) . '-' . $user_inquiry['id_item']; ?>" title="<?php echo $products_list[$user_inquiry['id_item']]['title']; ?>" target="_blank">
					<?php
						$item_img_link = getDisplayImageLink(array('{ID}' => $user_inquiry['id_item'], '{FILE_NAME}' => $products_list[$user_inquiry['id_item']]['photo_name']), 'items.main', array( 'thumb_size' => 1 ));
					?>
					<img
						class="image"
						src="<?php echo $item_img_link; ?>"
						alt="<?php echo $products_list[$user_inquiry['id_item']]['title']; ?>"
					/>
				</a>
			</div>

			<div class="order-users-list__detail flex-card__float">
				<div class="order-users-list__number">
					<?php echo orderNumber($user_inquiry['id_inquiry']);?>

					<i class="order-popover ep-icon ep-icon_<?php echo $status_array[$user_inquiry['status']]['icon_new'];?>" data-placement="top" data-content="<?php echo $status_array[$user_inquiry['status']]['title'];?>"></i>
				</div>

				<div class="order-users-list__price">
					<?php echo get_price($user_inquiry['price']); ?> x <?php echo $user_inquiry['quantity']; ?>
				</div>

				<div class="order-users-list__date"><?php echo formatDate($user_inquiry['change_date']);?></div>

				<div class="order-users-list__company">
					<?php if(have_right('buy_item')){
						$company_name = $companies_info[$user_inquiry['id_seller']]['name_company'];
					?>
						by <span class="link"><?php echo $company_name;?></span>
					<?php }else if(have_right('manage_seller_inquiries')){
						$user_name = $users_list[$user_inquiry['id_buyer']]['username']; ?>
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
            <span>0 inquiries found by this search.</span>
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
