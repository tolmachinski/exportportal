<ul class="product-comments">
	<?php if(!empty($feedbacks)){?>
		<?php views()->display('new/user/feedback_external/item_view');?>
	<?php }else{?>
		<li class="w-100pr p-0"><div class="info-alert-b no-feedback"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_all_feedback_no_external_feedback');?></div></li>
	<?php }?>
</ul>
