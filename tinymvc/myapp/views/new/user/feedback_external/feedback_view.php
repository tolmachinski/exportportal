<a class="btn btn-primary btn-panel-left mb-25 fancyboxSidebar fancybox" data-title="<?php echo translate('mobile_screen_sidebar_btn', null, true);?>" href="#main-flex-card__fixed-left">
	<i class="ep-icon ep-icon_items"></i>
	<?php echo translate('mobile_screen_sidebar_btn');?>
</a>

<div class="title-public  display-b_i pt-0">
	<h1 class="title-public__txt"><?php echo translate('seller_all_feedback_external_feedback_block_title');?></h1>

	<div class="ep-large-text">
		<p class="mb-0"><?php echo translate('seller_all_feedback_external_feedback_block_subtitle');?></p>
	</div>
</div>

<?php views()->display('new/user/feedback_external/list_view'); ?>

<?php if($count_feedbacks > 0){?>
	<div class="pt-10 flex-display flex-jc--sb flex-ai--c">
		<?php views()->display("new/paginator_view"); ?>
	</div>	
<?php }?>
