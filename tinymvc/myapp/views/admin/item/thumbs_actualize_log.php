<div class="wr-form-content w-700 mh-700 mt-15 mb-15">
	<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr m-auto vam-table">
		<thead>
			<tr>
				<th>Action date</th>
				<th>Result</th>
			</tr>
		</thead>
		<tbody>
			<?php echo $content;?>
		</tbody>
	</table>
	<span id="fancybox_buttom"></span>
</div>
<div class="wr-form-btns clearfix">
	<button class="pull-right btn btn-default fancybox fancybox.ajax" href="<?php echo __SITE_URL?>items/popup_forms/thumbs_actualize_log" data-title="Actualize thumbs logs"><span class="ep-icon ep-icon_updates"></span> Refresh log</button>
</div>
<script>
	$(function(){
		scrollToElementModal('#fancybox_buttom', '.wr-form-content');
	});
</script>
