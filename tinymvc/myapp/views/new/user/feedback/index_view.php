<?php views()->display('new/user/seller/company/company_menu_block'); ?>

<div class="detail-info">
	<div class="title-public display-b_i pt-0">
		<h1 class="title-public__txt"><?php echo translate('seller_all_feedback_ep_feedback_block_title');?></h1>
		<div class="ep-large-text">
			<p class="mb-0"><?php echo translate('seller_all_feedback_ep_feedback_block_subtitle');?></p>
		</div>
	</div>

	<ul class="product-comments">
		<?php
			$additionals = array();

			if (!empty($feedbacks_ep)) {
				$additionals['feedbacks'] = $feedbacks_ep;

				if (isset($helpful_feedbacks)) {
					$additionals['helpful_feedbacks'] = $helpful_feedbacks;
				}

				$additionals['feedback_written'] = isset($feedback_written) ? $feedback_written : false;

				if (isset($feedbacks_services)) {
					$additionals['feedbacks_services'] = $feedbacks_services;
				}
			}

			views()->display('new/users_feedbacks/item_view', $additionals);
		?>
	</ul>

	<?php if ($count_feedbacks_ep > count($feedbacks_ep)) {?>
		<a class="btn-block mw-250 m-auto btn btn-outline-dark mt-15" href="<?php echo $base_company_url . '/feedbacks_ep';?>"><?php echo translate('seller_all_feedback_more_ep_feedback_btn');?></a>
	<?php }?>
</div>

<div class="title-public display-b_i">
	<h1 class="title-public__txt"><?php echo translate('seller_all_feedback_external_feedback_block_title');?></h1>
	<div class="ep-large-text">
		<p class="mb-0"><?php echo translate('seller_all_feedback_external_feedback_block_subtitle');?></p>
	</div>
</div>

<ul class="product-comments">
	<?php if (!empty($feedbacks_external)) {?>
		<?php views()->display('new/user/feedback_external/item_view', array('feedbacks' => $feedbacks_external));?>
	<?php } else {?>
		<li class="w-100pr p-0"><div class="info-alert-b no-feedback"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_all_feedback_no_external_feedback');?></div></li>
	<?php }?>
</ul>

<?php if ($count_feedbacks_external > count($feedbacks_external)) {?>
	<a class="btn-block mw-250 m-auto btn btn-outline-dark mt-15" href="<?php echo $base_company_url . '/feedbacks_external';?>"><?php echo translate('seller_all_feedback_more_external_feedback_btn');?></a>
<?php }?>
