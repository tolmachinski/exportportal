<ul class="product-comments">
<?php if(!empty($reviews)){?>
	<?php views()->display('new/user/reviews_external/item_view', $additionals);?>
<?php }else{?>
	<li><div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_reviews_no_external_reviews');?></div></li>
<?php }?>
</ul>
