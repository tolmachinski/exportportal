<?php if(!empty($users_po)){?>
	<?php foreach($users_po as $key => $user_po_item){?>
		<li class="order-users-list__item flex-card" data-po="<?php echo $user_po_item['id_po'];?>">

			<div class="order-users-list__img image-card3 flex-card__fixed">
				<a class="link" href="<?php echo __SITE_URL;?>prototype/item/<?php echo $user_po_item['id_prototype'];?>" target="_blank">
                    <img class="image" src="<?php echo getDisplayImageLink(array('{ID}' => $user_po_item['id_prototype'], '{FILE_NAME}' => $user_po_item['image']), 'items.prototype', array( 'thumb_size' => 1 ));?>" alt="<?php echo $user_po_item['title'];?>"/>
				</a>
			</div>

			<div class="order-users-list__detail flex-card__float">
				<div class="order-users-list__number">
					<?php echo orderNumber($user_po_item['id_po']);?>

					<i class="order-popover ep-icon ep-icon_<?php echo $status_array[$user_po_item['status']]['icon_new'];?>" data-placement="top" data-content="<?php echo $status_array[$user_po_item['status']]['title'];?>"></i>
				</div>

				<?php if(!empty($user_po_item['price']) && $user_po_item['price'] != '0.00'){?>
				<div class="order-users-list__price">
					<?php echo get_price($user_po_item['price']);?> x <?php echo $user_po_item['quantity'];?>
				</div>
				<?php }?>

				<div class="order-users-list__date"><?php echo formatDate($user_po_item['change_date']);?></div>

				<div class="order-users-list__company">
					<?php if(have_right('buy_item')){
						$company_name = $companies_info[$user_po_item['id_seller']]['name_company'];
					?>
						by <span class="link"><?php echo $company_name;?></span>
					<?php }else if(have_right('sell_item')){
						$user_name = $users_list[$user_po_item['id_buyer']]['username']; ?>
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
            <span>0 Producing Requests found by this search.</span>
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
