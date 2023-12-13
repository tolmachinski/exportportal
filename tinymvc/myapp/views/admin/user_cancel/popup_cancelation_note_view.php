<div class="wr-modal-b">
	<form class="modal-b__form validateModal">
		<div class="modal-b__content w-700 mnh-100">
			<label class="modal-b__label">Note</label>
			<textarea class="validate[required] h-140" name="note" ><?php echo $request_info['note'];?></textarea>
		</div>
		<div class="modal-b__btns clearfix">
			<input type="hidden" name="request" value="<?php echo $request_info['idreq']; ?>">
			<button class="btn btn-primary pull-right" type="submit">Submit</button>
		</div>
	</form>
</div>

<script>
function modalFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.wr-modal-b');
	var fdata = $form.serialize();

	$.ajax({
		type: 'POST',
		url: __site_url + 'user_cancel/ajax_user_cancel_operation/save_requests_note',
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showFormLoader($wrform);
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();
				dtRequests.fnDraw();
			}else{
				hideFormLoader($wrform);
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
