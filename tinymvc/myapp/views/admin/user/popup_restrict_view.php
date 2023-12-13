<form method="post" class="validateModal relative">
	<div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr">
			<tbody>
				<tr>
					<td class="w-140">Reason</td>
					<td><textarea name="notice" class="w-100pr h-100 validate[required]" placeholder="Please add a note about the status change for this user."></textarea></td>
				</tr>
			<tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="user" value="<?php echo $id_user;?>"/>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		url: '<?php echo __SITE_URL;?>users/ajax_operations/restrict_user',
		type: 'POST',
		data:  $form.serialize(),
		dataType: 'json',
		beforeSend: function(){ 
			showLoader($form); 
		},
		success: function(resp){
			systemMessages(resp.message, resp.mess_type );
			if(resp.mess_type == 'success'){
				closeFancyBox();
				if(data_table != undefined){
					data_table.fnDraw(false);
				}
			} else{
				systemMessages( data.message, 'message-' + data.mess_type );
				hideLoader($form);
			}
		}
	});
}
</script>
