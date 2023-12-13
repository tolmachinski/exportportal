<form class="validateModal relative-b">
	<div class="wr-form-content w-700">
		<label class="modal-b__label">Reason for decline</label>
		<textarea class="validate[required,maxSize[255]] h-150" name="declined_reason" placeholder="Write here the reason ..."></textarea>
	</div>

	<div class="modal-b__btns clearfix">
		<input type="hidden" value="<?php echo $id_ereport;?>" name="id_ereport" />
		<input type="hidden" value="declined" name="status" />
		<button class="btn btn-primary pull-right" type="submit">Send</button>
	</div>
</form>
<script>

function modalFormCallBack($form, data_table){
	var fdata = $form.serialize();

	$.ajax({
		type: 'POST',
		url: 'cr_expense_reports/ajax_operations/change_status',
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showFormLoader($form, 'Sending ...');
			$form.find('button[type="submit"]').addClass('disabled');
		},
		success: function(resp){
			hideFormLoader($form);
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();
                data_table.fnDraw();
			}else{
				$form.find('button[type="submit"]').removeClass('disabled');
			}
		}
	});
}
</script>
