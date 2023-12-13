
<?php if(!empty($companies)){?>
	<?php foreach($companies as $company){ ?>
	<li class="order-users-list__item flex-card js-user-list-item" data-company="<?php echo $company['id_company']?>" data-seller="<?php echo $company['id_user']?>">

		<div class="order-users-list__img image-card2 flex-card__fixed">
			<span class="link">
				<img
					class="image"
					src="<?php echo getDisplayImageLink(array('{ID}' => $company['id_company'], '{FILE_NAME}' => $company['logo_company']), 'companies.main', array( 'thumb_size' => 1 ));?>"
					alt="<?php echo $company['name_company']?>"/>
			</span>
		</div>

		<div class="order-users-list__detail flex-card__float">
			<div class="order-users-list__number">
				<?php echo $company['name_company']?>
			</div>
			<div class="pt-5 pb-5">
				<?php echo $company['user_group_name'];?>
			</div>
			<div class="order-users-list__line">
				<span class="nr"><span class="nr-val"><?php echo count($items[$company['id_user']])?></span> item(s)</span>
			</div>
		</div>
	</li>
	<?php } ?>
<?php }else{ ?>
	<li class="default-alert-b"><i class="ep-icon ep-icon_remove-circle"></i> <span>No items in the basket.</span></li>
<?php } ?>
