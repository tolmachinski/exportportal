<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal"
        data-callback="officesPopupContactFormCallBack"
    >
		<div class="modal-flex__content">
			<label class="input-label input-label--required">Subject</label>
			<input class="validate[required]" type="text" name="subject" value="" placeholder="Subject"/>
			<label class="input-label input-label--required">Message</label>
			<textarea class="validate[required]" name="message" placeholder="Message"></textarea>
            <input type="hidden" name="office" value="<?php echo $id_office?>"/>
		</div>
		<div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<button class="btn btn-primary" type="submit">Submit</button>
			</div>
		</div>
	</form>
</div>
<script>
function officesPopupContactFormCallBack(form){
	var $form = $(form);
	var $wrModal = $form.closest('.js-modal-flex');
	var fdata = $form.serialize();

	$.ajax({
		type: 'POST',
		url: 'offices/ajax_offices_operation/contact_office',
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showLoader($wrModal);
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			hideLoader($wrModal);
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
