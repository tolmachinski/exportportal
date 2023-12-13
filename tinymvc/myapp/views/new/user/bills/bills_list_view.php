<?php if(!empty($bills)){?>
	<?php foreach($bills as $key => $bills_item){?>
		<li class="order-users-list__item flex-card" data-bill="<?php echo $bills_item['id_bill'];?>">	
			<div class="order-users-list__detail flex-jc--sa flex-card__float">
				<div class="order-users-list__number">
					<?php echo orderNumber($bills_item['id_bill']);?>

					<div class="order-users-list__date txt-normal"><?php echo formatDate($bills_item[$status[$bills_item['status']]['date']]);?></div>
				</div>
				
				<div class="flex-display flex-jc--sb">
					<span class="flex-display flex-ai--c">
						<i class=" ep-icon ep-icon_<?php echo $types[$bills_item['name_type']]['icon_new'];?> mr-5 txt-gray"></i>
						<?php echo $types[$bills_item['name_type']]['description'];?>
					</span>

					<span class="flex-display flex-ai--c">
						<i class="ep-icon ep-icon_<?php echo $status[$bills_item['status']]['icon_new'];?> mr-5 txt-gray"></i>
						<?php echo $status[$bills_item['status']]['description'];?>
					</span>
				</div>
				
				<div class="order-users-list__price">
					<span class="txt-normal">Paid</span>
					$<?php echo $bills_item['amount'];?>
					<span class="txt-normal">form</span> $<?php echo $bills_item['balance'];?>
				</div>
			</div>
		</li>
	<?php }?>
<?php } else{?>
	<li>
        <div class="info-alert-b">
            <i class="ep-icon ep-icon_info-stroke"></i>
            <span>0 bills found by this search.</span>
        </div>
    </li>
<?php }?>
