<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tbody>
				<tr>
					<td class="w-150">Visible</td>
					<td>
						<input type="radio" name="visible" <?php echo checked($category['visible'], 1);?> value="1" class="validate[required]"> Yes
						<input type="radio" name="visible" <?php echo checked($category['visible'], 0);?> value="0" class="validate[required]"> No
					</td>
				</tr>
				<tr>
					<td class="w-150">Category name</td>
					<td>
						<input class="w-100pr validate[required,maxSize[255]]" type="text" name="name" value="<?php echo $category['title_category']?>"/>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(!empty($category)){?>
			<input type="hidden" name="category" value="<?php echo $category['id_category']?>"/>
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript">
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>events/ajax_event_operation/admin_<?php if(isset($category)){?>edit<?php }else{?>add<?php }?>_category',
		data: $form.serialize(),
		beforeSend: function () {
			showLoader($form);
		},
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );

			if(data.mess_type == 'success'){
				closeFancyBox();
				if(data_table != undefined)
					data_table.fnDraw();
			}else{
				hideLoader($form);
			}
		}
	});
}
</script>
