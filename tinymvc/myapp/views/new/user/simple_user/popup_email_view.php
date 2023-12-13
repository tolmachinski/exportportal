<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal"
        data-callback="simpleUserPopupEmailFormCallBack"
    >
		<div class="modal-flex__content">
			<?php global $tmvc;?>
			<label class="input-label input-label--required"><?php echo translate('email_user_form_email_label');?></label>
			<input type="text" <?php echo addQaUniqueIdentifier('popup__share__form_emails-input') ?> name="email" class="validate[required,custom[noWhitespaces],custom[emailsWithWhitespaces],maxEmailsCount[<?php echo config('email_this_max_email_count');?>]]" value="" placeholder="<?php echo translate('email_user_form_email_placeholder', null, true);?>"/>
			<p class="fs-12 txt-red"><?php echo translate('email_user_form_email_help_text');?></p>

			<label class="input-label input-label--required"><?php echo translate('email_user_form_message_label');?></label>
			<textarea name="message" <?php echo addQaUniqueIdentifier('popup__share__form_message-textarea') ?> class="validate[required,maxSize[1000]] js-textcounter-email-message" data-max="1000" placeholder="<?php echo translate('email_user_form_message_placeholder', null, true);?>"></textarea>
            <input type="hidden" name="user" value="<?php echo $id_user?>"/>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" <?php echo addQaUniqueIdentifier('popup__share__form_send-btn') ?> type="submit"><?php echo translate('email_user_form_submit_btn');?></button>
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

function simpleUserPopupEmailFormCallBack(form){
	var $form = $(form);
	var $wrform = $form.closest('.js-modal-flex');
	var fdata = $form.serialize();
	$.ajax({
		type: 'POST',
		url: 'user/ajax_send_email/email',
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showLoader($wrform, '<?php echo translate('sending_email_form_loader', null, true);?>');
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
