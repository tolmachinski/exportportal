<?php views()->display('new/user/seller/company/company_menu_block'); ?>

<div class="title-public pt-0">
	<h1 class="title-public__txt"><?php echo translate('seller_followers_block_title');?></h1>
</div>

	<?php if (!empty($followers)) {?>
		<ul class="ppersonal-followers">
			<?php views()->display('new/followers/follower_item_view'); ?>
		</ul>
	<?php } else { ?>
		<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_followers_not_found_followers');?></div>
	<?php }?>

<div class="pt-10 flex-display flex-jc--sb flex-ai--c">
	<?php views()->display("new/paginator_view"); ?>
</div>
