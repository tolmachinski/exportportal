<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal"
        data-callback="questionsPopupShareFormCallBack"
    >
		<div class="modal-flex__content">
			<label class="input-label input-label--required">Message</label>
			<textarea class="validate[required]" name="message" placeholder="Message"></textarea>
            <input type="hidden" name="question" value="<?php echo $question;?>"/>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Submit</button>
            </div>
		</div>
	</form>
</div>

<script>
function questionsPopupShareFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.js-modal-flex');
	var fdata = $form.serialize();

	$.ajax({
		type: 'POST',
		url: 'community_questions/ajax_send_email/share',
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showLoader($wrform, 'Sending email...');
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			hideLoader($wrform);
			systemMessages( resp.message, resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
