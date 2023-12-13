<?php views()->display('new/user/seller/company/company_menu_block'); ?>

<div class="detail-info">
	<div class="detail-info__ttl">
		<h1 class="detail-info__ttl-name" <?php echo addQaUniqueIdentifier('seller__detail_title'); ?>><?php echo translate('seller_about_page_about_company_block_title', array('{{COMPANY_NAME}}' => $company['name_company']));?></h1>
		<i class="ep-icon ep-icon_remove-stroke call-function" <?php echo addQaUniqueIdentifier('seller__about_detail_btn'); ?> data-callback="productDetailToggle"></i>
	</div>

	<div class="detail-info__toggle">
		<div class="ep-tinymce-text" <?php echo addQaUniqueIdentifier('seller__detail_text'); ?>>
			<?php echo empty($company['description_company']) ? '<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i>' . translate('seller_about_page_empty_company_description', null, true) . '</div>' : $company['description_company'];?>
		</div>
	</div>
</div>

<?php if(!empty($company['index_name'])){?>
	<div id="text_about_us" class="detail-info">
		<div class="detail-info__ttl">
			<h2 class="detail-info__ttl-name" <?php echo addQaUniqueIdentifier('seller__detail_title'); ?>>
				<?php echo translate('seller_about_page_about_company_block_title', array('{{COMPANY_NAME}}' => $company['name_company']));?>
			</h2>
			<i class="ep-icon ep-icon_remove-stroke call-function" <?php echo addQaUniqueIdentifier('seller__about_detail_btn'); ?> data-callback="productDetailToggle"></i>
		</div>

		<div class="detail-info__toggle">
			<div class="ep-tinymce-text" <?php echo addQaUniqueIdentifier('seller__detail_text'); ?> id="block_about_us">
				<?php if (!empty($about_page['text_about_us'])) {?>
					<?php echo $about_page['text_about_us'];?>
				<?php } else {?>
					<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_about_page_empty_some_company_field');?></div>
				<?php }?>
			</div>
		</div>
	</div>

	<div id="text_history" class="detail-info">
		<div class="detail-info__ttl">
			<h2 class="detail-info__ttl-name" <?php echo addQaUniqueIdentifier('seller__detail_title'); ?>>
				<?php echo translate('seller_home_page_company_history_block_title');?>
			</h2>
			<i class="ep-icon ep-icon_remove-stroke call-function" <?php echo addQaUniqueIdentifier('seller__about_detail_btn'); ?> data-callback="productDetailToggle"></i>
		</div>

		<div class="detail-info__toggle">
			<div class="ep-tinymce-text" <?php echo addQaUniqueIdentifier('seller__detail_text'); ?> id="block_history">
				<?php if (!empty($about_page['text_history'])) {?>
					<?php echo $about_page['text_history'];?>
				<?php } else {?>
					<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_about_page_empty_some_company_field');?></div>
				<?php }?>
			</div>
		</div>
	</div>

	<div id="text_what_we_sell" class="detail-info">
		<div class="detail-info__ttl">
			<h2 class="detail-info__ttl-name" <?php echo addQaUniqueIdentifier('seller__detail_title'); ?>>
				<?php echo translate('seller_home_page_products_services_company_block_title');?>
			</h2>
			<i class="ep-icon ep-icon_remove-stroke call-function" <?php echo addQaUniqueIdentifier('seller__about_detail_btn'); ?> data-callback="productDetailToggle"></i>
		</div>

		<div class="detail-info__toggle">
			<div class="ep-tinymce-text" <?php echo addQaUniqueIdentifier('seller__detail_text'); ?> id="block_what_we_sell">
				<?php if (!empty($about_page['text_what_we_sell'])){?>
					<?php echo $about_page['text_what_we_sell'];?>
				<?php } else{?>
					<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_about_page_empty_some_company_field');?></div>
				<?php } ?>
			</div>
		</div>
	</div>

	<div id="text_research_develop_abilities" class="detail-info">
		<div class="detail-info__ttl">
			<h2 class="detail-info__ttl-name" <?php echo addQaUniqueIdentifier('seller__detail_title'); ?>>
				<?php echo translate('seller_home_page_company_research_and_develop_abilities_block_title');?>
			</h2>
			<i class="ep-icon ep-icon_remove-stroke call-function" <?php echo addQaUniqueIdentifier('seller__about_detail_btn'); ?> data-callback="productDetailToggle"></i>
		</div>

		<div class="detail-info__toggle">
			<div class="ep-tinymce-text" <?php echo addQaUniqueIdentifier('seller__detail_text'); ?> id="block_research_develop_abilities">
				<?php if (!empty($about_page['text_research_develop_abilities'])) {?>
					<?php echo $about_page['text_research_develop_abilities'];?>
				<?php } else {?>
					<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_about_page_empty_some_company_field');?></div>
				<?php } ?>
			</div>
		</div>
	</div>

	<div id="text_development_expansion_plans" class="detail-info">
		<div class="detail-info__ttl">
			<h2 class="detail-info__ttl-name" <?php echo addQaUniqueIdentifier('seller__detail_title'); ?>>
				<?php echo translate('seller_home_page_company_development_expansion_plans_block_title');?>
			</h2>
			<i class="ep-icon ep-icon_remove-stroke call-function" <?php echo addQaUniqueIdentifier('seller__about_detail_btn'); ?> data-callback="productDetailToggle"></i>
		</div>

		<div class="detail-info__toggle">
			<div class="ep-tinymce-text" <?php echo addQaUniqueIdentifier('seller__detail_text'); ?> id="block_development_expansion_plans">
				<?php if (!empty($about_page['text_development_expansion_plans'])) {?>
					<?php echo $about_page['text_development_expansion_plans'];?>
				<?php } else {?>
					<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_about_page_empty_some_company_field');?></div>
				<?php }?>
			</div>
		</div>
	</div>

	<?php if ($company['user_group'] == 6) {?>
		<div id="text_prod_process_management" class="detail-info">
			<div class="detail-info__ttl">
				<h2 class="detail-info__ttl-name" <?php echo addQaUniqueIdentifier('seller__detail_title'); ?>>
					<?php echo translate('seller_home_page_company_production_process_management_block_title');?>
				</h2>
				<i class="ep-icon ep-icon_remove-stroke call-function" <?php echo addQaUniqueIdentifier('seller__about_detail_btn'); ?> data-callback="productDetailToggle"></i>
			</div>

			<div class="detail-info__toggle">
				<div class="ep-tinymce-text" <?php echo addQaUniqueIdentifier('seller__detail_text'); ?> id="block_prod_process_management">
					<?php if (!empty($about_page['text_prod_process_management'])) {?>
						<?php echo $about_page['text_prod_process_management'];?>
					<?php } else {?>
						<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_about_page_empty_some_company_field');?></div>
					<?php }?>
				</div>
			</div>
		</div>

		<div id="text_production_flow" class="detail-info">
			<div class="detail-info__ttl">
				<h2 class="detail-info__ttl-name" <?php echo addQaUniqueIdentifier('seller__detail_title'); ?>>
					<?php echo translate('seller_home_page_company_production_flow_block_title');?>
				</h2>
				<i class="ep-icon ep-icon_remove-stroke call-function" <?php echo addQaUniqueIdentifier('seller__about_detail_btn'); ?> data-callback="productDetailToggle"></i>
			</div>

			<div class="detail-info__toggle">
				<div class="ep-tinymce-text" <?php echo addQaUniqueIdentifier('seller__detail_text'); ?> id="block_production_flow">
					<?php if (!empty($about_page['text_production_flow'])) {?>
						<?php echo $about_page['text_production_flow'];?>
					<?php } else {?>
						<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_about_page_empty_some_company_field');?></div>
					<?php }?>
				</div>
			</div>
		</div>
	<?php }?>

	<?php if (!empty($about_page_additional)) {?>
		<?php foreach ($about_page_additional as $item) {?>
			<div class="ppersonal-about__item">
				<div id="" class="text-about-b">
					<div class="ppersonal-about__txt"></div>
				</div>
			</div>

			<div class="detail-info">
				<div class="detail-info__ttl">
					<h2 class="detail-info__ttl-name" <?php echo addQaUniqueIdentifier('seller__detail_title'); ?>>
						<?php echo $item['title_block'];?>
					</h2>
					<i class="ep-icon ep-icon_remove-stroke call-function" <?php echo addQaUniqueIdentifier('seller__about_detail_btn'); ?> data-callback="productDetailToggle"></i>
				</div>

				<div class="detail-info__toggle">
					<div class="ep-tinymce-text" <?php echo addQaUniqueIdentifier('seller__detail_text'); ?> id="block_<?php echo $item['id_block'];?>">
						<?php echo $item['text_block'];?>
					</div>
				</div>
			</div>
		<?php }?>
	<?php }?>
<?php }?>

<div class="detail-info">
	<div class="detail-info__ttl">
		<h2 class="detail-info__ttl-name" <?php echo addQaUniqueIdentifier('seller__detail_title'); ?>><?php echo translate('seller_home_page_company_overview_block_title');?></h2>
		<i class="ep-icon ep-icon_remove-stroke call-function" <?php echo addQaUniqueIdentifier('seller__about_detail_btn'); ?> data-callback="productDetailToggle"></i>
	</div>

	<div class="detail-info__toggle">
		<?php if (!empty($company['video_company'])) {?>
			<a class="wr-video-link fancybox.iframe fancyboxVideo" <?php echo addQaUniqueIdentifier('seller__wall-item_video-img'); ?> href="<?php echo get_video_link($company['video_company_code'], $company['video_company_source']);?>" data-title="<?php echo translate('seller_home_page_company_overview_block_title', null, true);?>">
				<div class="bg"><i class="ep-icon ep-icon_play"></i></div>
				<img class="image" src="<?php echo $videoImagePath;?>" alt="<?php echo $company['name_company'];?>">
			</a>
		<?php } else {?>
			<div class="info-alert-b" <?php echo addQaUniqueIdentifier('seller__detail_text'); ?>><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('seller_about_page_empty_company_video');?></div>
		<?php }?>
	</div>
</div>


