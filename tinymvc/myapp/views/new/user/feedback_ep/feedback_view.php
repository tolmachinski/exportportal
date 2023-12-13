<?php views()->display('new/users_feedbacks/feedback_scripts_view'); ?>

<a class="btn btn-primary btn-panel-left mb-25 fancyboxSidebar fancybox" data-title="<?php echo translate('mobile_screen_sidebar_btn', null, true);?>" href="#main-flex-card__fixed-left">
	<i class="ep-icon ep-icon_items"></i>
	<?php echo translate('mobile_screen_sidebar_btn');?>
</a>

<div class="title-public display-b_i pt-0">
	<div class="d-flex flex-d--r">
		<h1 class="title-public__txt"><?php echo translate('seller_all_feedback_ep_feedback_block_title');?></h1>

		<?php if(logged_in() && !empty($user_ordered_for_feedback)){?>
			<div class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
					<i class="ep-icon ep-icon_menu-circles"></i>
				</a>

				<div class="dropdown-menu">
					<a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="<?php echo translate('seller_ep_feedback_leave_feedback_tag_title', null, true);?>" href="<?php echo __SITE_URL . 'feedbacks/popup_forms/add_feedback/user/' . $company['id_user'];?>" title="<?php echo translate('seller_ep_feedback_leave_feedback_tag_title', null, true);?>">
						<i class="ep-icon ep-icon_star"></i>
						<?php echo translate('seller_ep_feedback_leave_feedback_btn');?>
					</a>
				</div>
			</div>
		<?php }?>
	</div>

	<div class="ep-large-text">
		<p class="mb-0"><?php echo translate('seller_all_feedback_ep_feedback_block_subtitle');?></p>
	</div>
</div>

<?php views()->display('new/users_feedbacks/list_view'); ?>

<?php if ($count_feedbacks > 0) {?>
	<div class="pt-10 flex-display flex-jc--sb flex-ai--c">
		<?php views()->display("new/paginator_view");?>
	</div>
<?php }?>
