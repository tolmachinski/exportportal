<?php if(!empty($users_offers)){?>
	<?php foreach($users_offers as $key => $user_offer){?>
		<li class="order-users-list__item flex-card" data-offer="<?php echo $user_offer['id_offer'];?>">

			<div class="order-users-list__img flex-card__fixed image-card3">
				<a class="link" href="<?php echo __SITE_URL;?>item/<?php echo strForURL($products_list[$user_offer['id_item']]['title']).'-'.$user_offer['id_item'];?>" title="<?php echo $products_list[$user_offer['id_item']]['title'];?>" target="_blank">
					<?php
						$item_img_link = getDisplayImageLink(array('{ID}' => $user_offer['id_item'], '{FILE_NAME}' => $products_list[$user_offer['id_item']]['photo_name']), 'items.main', array( 'thumb_size' => 1 ));
					?>
					<img
						class="image"
						src="<?php echo $item_img_link;?>"
						alt="<?php echo $products_list[$user_offer['id_item']]['title'];?>"
					/>
				</a>
			</div>

			<div class="order-users-list__detail flex-card__float">
				<div class="order-users-list__number">
					<?php echo orderNumber($user_offer['id_offer']);?>

					<i class="order-popover ep-icon ep-icon_<?php echo $status_array[$user_offer['status']]['icon_new'];?>" data-placement="top" data-content="<?php echo $status_array[$user_offer['status']]['title'];?>"></i>
				</div>

				<div class="order-users-list__price">
					<?php echo get_price($user_offer['new_price']);?> x <?php echo $user_offer['quantity'];?>
				</div>

				<div class="order-users-list__date"><?php echo formatDate($user_offer['update_op']);?></div>

				<div class="order-users-list__company">
					<?php if(have_right('buy_item')){
						$company_name = $companies_info[$user_offer['id_seller']]['name_company'];
					?>
						by <span class="link"><?php echo $company_name;?></span>
					<?php }else if(have_right('manage_seller_offers')){
						$user_name = $users_list[$user_offer['id_buyer']]['username']; ?>
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
            <span>0 offers found by this search.</span>
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
