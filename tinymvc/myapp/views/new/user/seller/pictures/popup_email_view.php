<div class=" js-wr-modalwr-modal-flex inputs-40">
    <form
        class="modal-flex__form validateModal"
        data-callback="sellerPicturesPopupEmailFormCallBack"
        action="<?php echo $action; ?>"
    >
        <div class="modal-flex__content">
            <div class="form-group">
                <label class="input-label input-label--required">
                    <?php echo translate('general_modal_send_mail_field_addresses_label_text'); ?>
                </label>
                <input type="text"
                    <?php echo addQaUniqueIdentifier('popup__email-picture__form_adresses-input'); ?>
                    name="emails"
                    class="validate[required,custom[noWhitespaces],custom[emailsWithWhitespaces],maxEmailsCount[<?php echo $max_emails; ?>]] mb-5"
                    placeholder="<?php echo translate('general_modal_send_mail_field_addresses_placeholder_text', null, true); ?>"/>
                <p class="fs-12 txt-red mb-15">* <?php echo translate('general_modal_send_mail_field_addresses_help_text'); ?></p>
            </div>
            <div class="form-group">
                <label class="input-label input-label--required">
                    <?php echo translate('general_modal_send_mail_field_message_label_text'); ?>
                </label>
                <textarea name="message"
                    <?php echo addQaUniqueIdentifier('popup__email-picture__form_message-textarea'); ?>
                    id="js-mail-pictures-mail-message"
                    class="validate[required,maxSize[500]]"
                    data-max="500"
                    placeholder="<?php echo translate('general_modal_send_mail_field_message_placeholder_text', null, true); ?>"></textarea>
            </div>

            <input type="hidden" name="id" value="<?php echo $id_picture; ?>"/>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" <?php echo addQaUniqueIdentifier('popup__email-picture__form_send-btn'); ?> type="submit">
                    <?php echo translate('general_modal_button_send_text'); ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script type="application/javascript">
    $(function(){
        var onSaveContent = function(formElement) {
            var form = $(formElement);
            var wrapper = form.closest('.js-wr-modal');
            var submitButton = form.find('button[type=submit]');
            var formData = form.serializeArray();
            var url = form.attr('action');
            var sendRequest = function (url, data) {
                return $.post(url, data, null, 'json');
            };
            var beforeSend = function() {
                showLoader(wrapper);
                submitButton.addClass('disabled');
            };
            var onRequestEnd = function() {
                hideLoader(wrapper);
                submitButton.removeClass('disabled');
            };
            var onRequestSuccess = function(data){
                hideLoader(wrapper);
                systemMessages(data.message, data.mess_type);
                if(data.mess_type === 'success'){
                    closeFancyBox();
                    callFunction('callbackMailPicture', data);
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
        };

        var mailMessage = $('#js-mail-pictures-mail-message');
        var counterOptions = {
            countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'}),
        };

        if(mailMessage.length) {
            mailMessage.textcounter(counterOptions);
        }

        window.sellerPicturesPopupEmailFormCallBack = onSaveContent;
    });
</script>
