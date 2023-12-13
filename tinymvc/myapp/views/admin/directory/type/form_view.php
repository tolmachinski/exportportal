<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr" >
			<tr>
				<td class="w-130">Name type</td>
				<td><input type="text" name="name" class="w-100pr validate[required]" value="<?php echo $type_info['name_type']?>" /></td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($type_info)){?>
			<input type="hidden" name="id_type" value="<?php echo $type_info['id_type']?>" />
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
function modalFormCallBack(form){
	var $form = $(form);
	var fdata = $form.serialize();

	<?php if(isset($type_info)){?>
		var url = 'directory/ajax_company_operations/update_company_type';
	<?php }else{?>
		var url = 'directory/ajax_company_operations/create_company_type';
	<?php }?>

	$.ajax({
		type: 'POST',
		url: url,
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();
				<?php if(isset($type_info)){?>
					collbackUpdateCompanyType(resp);
				<?php }else{?>
					collbackAddCompanyType(resp);
				<?php }?>
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
