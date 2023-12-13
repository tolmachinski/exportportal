<?php views()->display('new/user/seller/company/company_menu_block'); ?>

<div class="title-public pt-0">
	<h1 class="title-public__txt"><?php echo translate('seller_products_main_block_title');?></h1>
</div>

<div class="seller-products-wr">
    <?php views('new/item/list_view', ['useLegacyCode' => true]); ?>
</div>

<div class="pt-40 flex-display flex-jc--sb flex-ai--c">
	<?php views("new/paginator_view"); ?>
</div>
