<div class="js-modal-flex wr-modal-flex inputs-40">
    <form class="modal-flex__form validateModal" id="mail-event--form" action="<?php echo $action; ?>">
        <input type="hidden" name="id" value="<?php echo $id_event; ?>"/>
        <div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div class="row">
                    <div class="col-12">
                        <label class="input-label input-label--required">
                            <?php echo translate('general_modal_send_mail_field_addresses_label_text'); ?>
                        </label>
                        <input type="text"
                            name="emails"
                            id="mail-event--formfield--addresses"
                            class="validate[required,custom[noWhitespaces],custom[emailsWithWhitespaces],maxEmailsCount[<?php echo $max_emails; ?>]] mb-5"
                            placeholder="<?php echo translate('general_modal_send_mail_field_addresses_placeholder_text', null, true); ?>"/>
                        <p class="fs-12 txt-red mb-15">* <?php echo translate('general_modal_send_mail_field_addresses_help_text'); ?></p>
                    </div>
                    <div class="col-12">
                        <label class="input-label input-label--required">
                            <?php echo translate('general_modal_send_mail_field_message_label_text'); ?>
                        </label>
                        <textarea name="message"
                            id="mail-event--formfield--mail-message"
                            class="validate[required,maxSize[1000]]"
                            data-max="1000"
                            placeholder="<?php echo translate('general_modal_send_mail_field_message_placeholder_text', null, true); ?>"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit" id="mail-event--formaction--submit">
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
            var wrapper = form.closest('.js-modal-flex');
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
                    callFunction('callbackMailEvent', data);
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
        };

        var mailMessage = $('#mail-event--formfield--mail-message');
        var counterOptions = {
            countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'}),
        };

        if(mailMessage.length) {
            mailMessage.textcounter(counterOptions);
        }

        window.modalFormCallBack = onSaveContent;
    });
</script>
