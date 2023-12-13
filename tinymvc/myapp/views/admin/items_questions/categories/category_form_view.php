<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr">
			<tr>
				<td class="w-120">Title</td>
				<td>
					<input class="w-100pr" type="text" name="name_category" value="<?php echo $category['name_category'];?>">
				</td>
			</tr>
		</table>
	</div>

	<div class="wr-form-btns clearfix">
		<?php if(isset($category)){?>
		<input type="hidden" name="id_category" value="<?php echo $category['id_category'];?>">
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
function modalFormCallBack(form, data_table){
	var $form = $(form);
	var fdata = $form.serialize();

	<?php if(isset($category)){?>
		var url = "items_questions/ajax_question_operation/edit_category";
	<?php }else{?>
		var url = "items_questions/ajax_question_operation/create_category";
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
				if(data_table != undefined)
					data_table.fnDraw();
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
