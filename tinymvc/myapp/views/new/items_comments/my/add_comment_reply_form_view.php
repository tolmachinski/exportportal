<div class="wr-modal-flex inputs-40">
    <form
        id="add-comment-reply--form"
        class="modal-flex__form validateModal"
        data-callback="itemsCommentsAddReplyFormCallBack"
        action="<?php echo $action; ?>"
    >
        <div class="modal-flex__content">
            <label class="input-label input-label--required">
                <?php echo translate('general_modal_comment_field_message_label_text'); ?>
            </label>
            <textarea name="description"
                data-max="500"
                class="validate[required,maxSize[500]] textcounter_comment js-limited-comment"
                placeholder="<?php echo translate('general_modal_comment_field_message_placeholder_text', null, true); ?>"></textarea>
            <input type="hidden" name="comment" value="<?php echo $id_comm; ?>">
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit" id="add-comment-reply--formaction--submit">
                    <?php echo translate('general_modal_button_save_text'); ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
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
                if(data.mess_type === 'success') {
                    if (isDialog) {
                        closeBootstrapDialog(form);
                    } else {
                        closeFancyBox();
                    }

                    callFunction('addCommentReplyCallback', data);
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
        };

        var form = $('#add-comment-reply--form');
        var isDialog = Boolean(~~parseInt('<?php echo (int) ($is_dialog ?? false); ?>', 10));
        var mailMessage = form.find('.js-limited-comment');
        var counterOptions = {
            countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'}),
        };

        if(mailMessage.length) {
            mailMessage.textcounter(counterOptions);
        }

        mix(globalThis, { itemsCommentsAddReplyFormCallBack: onSaveContent }, false);
    });
</script>
