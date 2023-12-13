<?php if(!empty($users_orders)){?>
	<?php foreach($users_orders as $key => $user_order){
		$expire = (strtotime($user_order['status_countdown']) - time()) * 1000;?>
		<li class="order-users-list__item flex-card" data-order="<?php echo $user_order['id'];?>">
 			<?php if(have_right('buy_item') || have_right('manage_shipper_orders')){ 
					$user_name = $users_info[$user_order['id_seller']]['username'];
					$company_name = $companies_info[$user_order['id_seller']]['name_company'];
					$company_link = getCompanyURL($companies_info[$user_order['id_seller']]); ?>
			
				<div class="order-users-list__img image-card-center flex-card__fixed">
					<a class="link" href="<?php echo $company_link;?>" target="_blank">
						<img 
							class="image" 
							src="<?php echo getDisplayImageLink(array('{ID}' => $companies_info[$user_order['id_seller']]['id_company'], '{FILE_NAME}' => $companies_info[$user_order['id_seller']]['logo_company']), 'companies.main', array( 'thumb_size' => 1 ));?>" 
							alt="<?php echo $companies_info[$user_order['id_seller']]['name_company'];?>"/>
					</a>
				</div>

			<?php }else if(have_right('manage_seller_orders')){
					$user_name = $users_info[$user_order['id_buyer']]['username']; ?>

					<div class="order-users-list__img image-card-center flex-card__fixed">
						<a class="link" href="<?php echo __SITE_URL;?>usr/<?php echo strForURL($user_name).'-'.$user_order['id_buyer'];?>" target="_blank">
							<img class="image" src="<?php echo getDisplayImageLink(array('{ID}' => $user_order['id_buyer'], '{FILE_NAME}' => $users_info[$user_order['id_buyer']]['user_photo']), 'users.main', array( 'thumb_size' => 1, 'no_image_group' => $users_info[$user_order['id_buyer']]['user_group'] ));?>"/>
						</a>
					</div>
			<?php } ?>
			

			<div class="order-users-list__detail flex-card__float">
				<div class="order-users-list__number">
					<?php echo orderNumber($user_order['id']);?>

					<div class="flex-display">
						<?php if(in_array($user_order['status_alias'], array('shipping_in_progress', 'shipping_completed')) && $expire > 0 && $user_order['dispute_opened'] == 1){?>
							<i class="ep-icon ep-icon_circle fs-10 pt-3 mr-5 txt-red"></i>
						<?php } ?>
						
						<?php if((int)($user_order['request_auto_extend']) > 0){?>
							<i class="ep-icon ep-icon_circle fs-10 pt-3 mr-5 txt-orange"></i>
						<?php } ?>

						<i class="order-popover ep-icon <?php echo $user_order['icon_new']?>" data-placement="top" data-content="<?php echo $user_order['status']?>"></i>
					</div>
				</div>

				<div class="order-users-list__price">
					<?php $total = $user_order['final_price']+$user_order['ship_price'];
						echo get_price($total); ?>
				</div>

				<div class="order-users-list__date"><?php echo formatDate($user_order['order_date']);?></div>

				<div class="order-users-list__company">
					<?php if(have_right('buy_item') || have_right('manage_shipper_orders')){ ?>
						by <span class="link"><?php echo $company_name;?></span>
					<?php }else if(have_right('manage_seller_orders')){ ?>
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
            <span>0 orders found by this search.</span>
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