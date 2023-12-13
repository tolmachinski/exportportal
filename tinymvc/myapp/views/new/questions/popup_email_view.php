<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal"
        data-callback="questionsPopupEmailFormCallBack"
    >
		<div class="modal-flex__content">
			<label class="input-label input-label--required">Emails</label>
			<?php global $tmvc;?>
			<input type="text" name="emails" class="validate[required,custom[noWhitespaces],custom[emailsWithWhitespaces],maxEmailsCount[<?php echo $tmvc->my_config['email_this_max_email_count'];?>]]" value="" placeholder="Email address"/>
			<label class="input-label input-label--required">Message</label>
			<textarea class="validate[required,maxSize[1000]] js-textcounter-email-message" data-max="1000" name="message" placeholder="Message"></textarea>
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
$(function(){
	$('.js-textcounter-email-message').textcounter({
		countDown: true,
		countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
		countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
	});
});

function questionsPopupEmailFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.js-modal-flex');
	var fdata = $form.serialize();

	$.ajax({
		type: 'POST',
		url: 'community_questions/ajax_send_email/email',
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
