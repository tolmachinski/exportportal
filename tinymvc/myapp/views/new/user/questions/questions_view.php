<?php views()->display('new/user/seller/company/company_menu_block'); ?>

<script>
	function addQuestionReplyCallback(resp){
		_notifyContentChangeCallback();
	}
</script>

<div class="title-public pt-0">
	<h1 class="title-public__txt"><?php echo translate('seller_questions_block_title');?></h1>
</div>

<?php views()->display('new/items_questions/list_view'); ?>

<div class="pt-25 flex-display flex-jc--sb flex-ai--c">
	<?php views()->display("new/paginator_view"); ?>
</div>
