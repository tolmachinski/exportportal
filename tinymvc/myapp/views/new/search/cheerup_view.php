<div class="cheerup-b">
	<div class="cheerup-b__ttl"><?php echo translate('no_results_card_title');?></div>
	<div class="cheerup-b__search">
		<?php echo $cheerup_message ?? translate('no_results_card_subtitle');?>
	</div>

	<div class="cheerup-b__desc">
		<?php echo translate('no_results_card_contact_us_text');?>
	</div>

	<div class="row">
		<div class="col-12 col-md-7">
			<div class="cheerup-b__info-ttl"><?php echo translate('no_results_card_search_tips_block_title');?></div>

			<ul class="cheerup-b__list">
				<li class="cheerup-b__list-item"><?php echo translate('no_results_card_search_tips_li_1');?></li>
				<li class="cheerup-b__list-item"><?php echo translate('no_results_card_search_tips_li_2');?></li>
				<li class="cheerup-b__list-item"><?php echo translate('no_results_card_search_tips_li_3');?></li>
			</ul>
		</div>
		<div class="col-12 col-md-5">
			<div class="cheerup-b__info-ttl"><?php echo translate('no_results_card_contact_us_block_title');?></div>
			<a
                class="btn btn-primary pl-25 pr-25 fancybox.ajax fancyboxValidateModal"
                data-mw="800"
                data-title="<?php echo translate('no_results_card_contact_us_btn', null, true);?>"
                href="<?php echo __SITE_URL . 'contact/popup_forms/contact_us';?>">
                <?php echo translate('no_results_card_contact_us_btn');?>
            </a>
		</div>
	</div>
</div>
