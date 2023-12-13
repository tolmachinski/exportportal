<div class="js-wr-modal wr-modal-flex inputs-40">
	<form
        class="modal-flex__form validateModal"
        data-callback="sellerNewsPopupEmailFormCallBack"
    >
		<div class="modal-flex__content">
            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('seller_news_emails_word'); ?></label>
                <input
                    class="form-control validate[required,custom[noWhitespaces],custom[emailsWithWhitespaces],maxEmailsCount[<?php echo config('email_this_max_email_count');?>]]"
                    type="text"
                    name="emails"
                    value=""
                    placeholder="<?php echo translate('seller_news_insert_emails_text', null, true); ?>"
                    <?php echo addQaUniqueIdentifier('popup__company-news__email-form_emails-input'); ?>
                />
                <p class="fs-12 txt-red mb-15">* <?php echo translate('general_modal_send_mail_field_addresses_help_text'); ?></p>
            </div>

            <div class="form-group">
                <label class="input-label input-label--required"><?php echo translate('seller_news_message_word'); ?></label>
                <textarea
                    class="validate[required,maxSize[500]] js-textcounter-email-message"
                    data-max="500"
                    name="message"
                    placeholder="<?php echo translate('seller_news_message_word', null, true); ?>"
                    <?php echo addQaUniqueIdentifier('popup__company-news__email-form_message-textarea'); ?>
                ></textarea>
            </div>

            <input type="hidden" name="id" value="<?php echo $id_news;?>"/>
		</div>
		<div class="modal-flex__btns">
			<div class="modal-flex__btns-right">
				<button
                    class="btn btn-primary"
                    type="submit"
                    <?php echo addQaUniqueIdentifier('popup__company-news__email-form__send-btn'); ?>
                >
                    <?php echo translate('seller_news_send_word'); ?>
                </button>
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

function sellerNewsPopupEmailFormCallBack(form, $caller_btn){
	var $form = $(form);
	var $wrform = $form.closest('.js-wr-modal');
	var fdata = $form.serialize();
	$.ajax({
		type: 'POST',
		url: 'seller_news/ajax_news_operations/email',
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showLoader($wrform, '<?php echo translate('sending_message_form_loader', null, true); ?>');
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
