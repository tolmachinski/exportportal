<?php views()->display('new/sample_orders/samples_list_view', array('samples' => $samples ?? array(), 'is_seller' => $is_seller ?? false)); ?>

<li class="js-no-content" <?php if (!empty($samples)) { ?>style="display: none"<?php } ?>>
	<div class="info-alert-b">
		<i class="ep-icon ep-icon_info"></i>
		<strong>0 orders found by this search.</strong>
	</div>
</li>

<script>
	$(function(){
		$('.js-sample-status-info-popover').popover({ container: 'body', trigger: 'hover' });
	});
</script>