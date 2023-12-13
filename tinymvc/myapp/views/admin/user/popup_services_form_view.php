<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered temp w-100pr" >
			<tr>
				<td class="w-140">Service title</td>
				<td><input type="text" name="s_title" class="validate[required, custom[onlyLetterSp]] w-100pr" value="<?php echo $edit_service['s_title'];?>"></td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($edit_service)){ ?>
			<input type="hidden" name="id_service" value="<?php echo $edit_service['id_service']?>">
		<?php } ?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
function modalFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.wr-modal-b');
	var fdata = $form.serialize();

	<?php if(isset($edit_service)){ ?>
		var url = 'admin/ajax_admin_operation/update_group_service';
	<?php }else{ ?>
		var url = 'admin/ajax_admin_operation/create_group_service';
	<?php } ?>

	$.ajax({
		type: 'POST',
		url: url,
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showFormLoader($wrform, 'Sending user service...');
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			hideFormLoader($wrform);
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();

				<?php if(isset($edit_service)){ ?>
					callbackUpdateService(resp);
				<?php }else{ ?>
					callbackCreateService(resp);
				<?php } ?>
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
