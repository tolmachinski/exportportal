<?php views()->display('new/user/seller/company/company_menu_block'); ?>

<div class="title-public display-b_i pt-0">
	<h1 class="title-public__txt"><?php echo translate('seller_reviews_ep_reviews_block_title');?></h1>
	<div class="ep-large-text">
		<p class="mb-0"><?php echo translate('seller_reviews_ep_reviews_block_subtitle');?></p>
	</div>
</div>

<ul class="product-comments">
	<?php if (!empty($reviews_ep)) {?>
		<?php $additionals['reviews'] = $reviews_ep;?>
		<?php if (isset($helpful_reviews)) {?>
			<?php $additionals['helpful_reviews'] = $helpful_reviews;?>
		<?php }?>
		<?php views()->display('new/users_reviews/item_view', $additionals);?>
	<?php } else {?>
		<li><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_reviews_no_ep_reviews');?></div></li>
	<?php }?>
</ul>

<?php if ($count_reviews_ep > count($reviews_ep)) {?>
	<a class="btn btn-outline-dark mw-250 m-auto btn-block mt-25" href="<?php echo $base_company_url . '/reviews_ep';?>"><?php echo translate('seller_reviews_more_ep_reviews_btn');?></a>
<?php }?>

<div class="title-public display-b_i">
	<h1 class="title-public__txt"><?php echo translate('seller_reviews_external_reviews_block_title');?></h1>
	<div class="ep-large-text">
		<p class="mb-0"><?php echo translate('seller_reviews_external_reviews_block_subtitle');?></p>
	</div>
</div>

<ul class="product-comments">
	<?php if (!empty($reviews_external)) {?>
		<?php views()->display('new/user/reviews_external/item_view', array('reviews' => $reviews_external));?>
	<?php } else {?>
		<li><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_reviews_no_external_reviews');?></div></li>
	<?php }?>
</ul>

<?php if ($count_reviews_external > count($reviews_external)) {?>
	<a class="btn btn-outline-dark mw-250 m-auto btn-block mt-25" href="<?php echo $base_company_url . '/reviews_external';?>"><?php echo translate('seller_reviews_more_external_reviews_btn');?></a>
<?php }?>
