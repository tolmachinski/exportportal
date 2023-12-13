<div class="js-modal-flex wr-modal-flex inputs-40">
    <form
        id="add-comment--form"
        class="modal-flex__form validateModal"
        data-callback="itemsCommentsAddCommentFormCallBack"
        data-js-action="items:add-comment-form.submit"
        action="<?php echo $action; ?>"
    >
        <input type="hidden" name="item" value="<?php echo $item; ?>"/>
        <div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div class="row">
                    <div class="col-12">
                        <label class="input-label input-label--required">
                            <?php echo translate('general_modal_comment_field_message_label_text'); ?>
                        </label>
                        <textarea name="description"
                            class="validate[required,maxSize[500]] textcounter_comment js-limited-comment"
                            data-max="500"
                            placeholder="<?php echo translate('general_modal_comment_field_message_placeholder_text', null, true); ?>"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit" id="add-comment--formaction--submit">
                    <?php echo translate('general_modal_button_save_text'); ?>
                </button>
            </div>
        </div>
    </form>
</div>

<?php if (!$webpackData) { ?>
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
                if(data.mess_type === 'success'){
                    if (isDialog) {
                        closeBootstrapDialog(form);
                    } else {
                        closeFancyBox();
                    }

                    callFunction('addCommentCallback', data);
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
        };

        var form = $('#add-comment--form');
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

        mix(globalThis, { itemsCommentsAddCommentFormCallBack: onSaveContent }, false);
    });
</script>
<?php } else {?>
    <?php echo dispatchDynamicFragment('items:add-comment', ['isDialog' => (int) ($is_dialog ?? false)]); ?>
<?php } ?>