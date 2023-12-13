<?php views()->display('new/users_reviews/reviews_scripts_view'); ?>
<a class="btn btn-primary btn-panel-left mb-25 fancyboxSidebar fancybox" data-title="<?php echo translate('mobile_screen_sidebar_btn', null, true);?>" href="#main-flex-card__fixed-left">
	<i class="ep-icon ep-icon_items"></i>
	<?php echo translate('mobile_screen_sidebar_btn');?>
</a>

<div class="title-public display-b_i pt-0">
	<div class="d-flex flex-d--r">
		<h1 class="title-public__txt"><?php echo translate('seller_reviews_ep_reviews_block_title');?></h1>

		<?php if (logged_in() && have_right('write_reviews') && !empty($user_ordered_items_for_reviews)) {?>
			<div class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
					<i class="ep-icon ep-icon_menu-circles"></i>
				</a>

				<div class="dropdown-menu">
					<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="<?php echo translate('seller_ep_reviews_add_review_btn_tag_title', null, true);?>" href="<?php echo __SITE_URL . 'reviews/popup_forms/add_review/' . $company['id_user'];?>" title="<?php echo translate('seller_ep_reviews_add_review_btn_tag_title', null, true);?>">
						<i class="ep-icon ep-icon_star"></i>
						<?php echo translate('seller_ep_reviews_add_review_btn');?>
					</a>
				</div>
			</div>
		<?php }?>
	</div>

	<div class="ep-large-text">
		<p class="mb-0"><?php echo translate('seller_reviews_ep_reviews_block_subtitle');?></p>
	</div>
</div>

<?php views()->display('new/users_reviews/list_view'); ?>

<?php if ($reviews_count > 0) {?>
	<div class="pt-10 flex-display flex-jc--sb flex-ai--c">
		<?php views()->display("new/paginator_view"); ?>
	</div>
<?php }?>
