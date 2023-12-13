<div id="fixed-rigth-block" class="fixed-rigth-block">
	<?php $fixed_rigth_block_src = 'new/fixed_right_block/';?>

	<?php tmvc::instance()->controller->view->display("{$fixed_rigth_block_src}explore_user_view");?>

    <?php tmvc::instance()->controller->view->display("{$fixed_rigth_block_src}main_chat_view");?>

    <?php tmvc::instance()->controller->view->display("{$fixed_rigth_block_src}zoho_ticket_view");?>

	<?php if(isset($metaTitle)){
		tmvc::instance()->controller->view->display("{$fixed_rigth_block_src}popup_select_share_view");
	}?>

	<?php tmvc::instance()->controller->view->display("{$fixed_rigth_block_src}scrollup_view");?>

    <?php tmvc::instance()->controller->view->display("{$fixed_rigth_block_src}click_to_call_btn_view");?>
</div>
