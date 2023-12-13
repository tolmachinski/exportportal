<div>
	<ul class="js-community-list product-comments">
		<?php if(!empty($questions)){ ?>
			<?php views()->display('new/items_questions/item_view'); ?>
		<?php } else { ?>
			<li><div class="default-alert-b"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_questions_no_questions_label');?></div></li>
		<?php } ?>
	</ul>
</div>
