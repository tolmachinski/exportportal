<div class="js-wr-modal wr-modal-flex inputs-40">
    <form
        class="modal-flex__form validateModal"
        data-callback="sellerLibraryPopupShareFormCallBack"
        action="<?php echo $action; ?>"
        <?php echo addQaUniqueIdentifier('popup__seller-library-share__form'); ?>
    >
        <div class="modal-flex__content">
            <div class="form-group">
                <label class="input-label input-label--required">
                    <?php echo translate('general_modal_share_field_message_label_text'); ?>
                </label>
                <textarea name="message"
                    id="js-share-document-mail-message"
                    class="validate[required,maxSize[1000]]"
                    data-max="1000"
                    placeholder="<?php echo translate('general_modal_share_field_message_placeholder_text', null, true); ?>"
                    <?php echo addQaUniqueIdentifier('popup__seller-library-share__form_message-textarea'); ?>
                ></textarea>
            </div>

            <input type="hidden" name="id" value="<?php echo $id_document; ?>"/>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button
                    class="btn btn-primary"
                    type="submit"
                    <?php echo addQaUniqueIdentifier('popup__seller-library-share__form_submit-btn'); ?>
                >
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
                    callFunction('callbackShareLibraryDocument', data);
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
        };

        var mailMessage = $('#js-share-document-mail-message');
        var counterOptions = {
            countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'}),
        };

        if(mailMessage.length) {
            mailMessage.textcounter(counterOptions);
        }

        window.sellerLibraryPopupShareFormCallBack = onSaveContent;
    });
</script>
