<div class="js-modal-flex wr-modal-flex inputs-40">
    <form
        id="leave-question-reply---reply----form"
        class="modal-flex__form validateModal"
        data-callback="itemsQuestionsleaveReplyFormCallBack"
        action="<?php echo $action; ?>"
    >
        <div class="modal-flex__content">
            <label class="input-label input-label--required"><?php echo translate('item_question_reply_form_text_label');?></label>
            <textarea name="response"
                <?php echo addQaUniqueIdentifier("items-questions-my__edit-reply_answer-textarea_popup")?>
                data-max="500"
                id="leave-question-reply--formfield--reply"
                class="validate[required,maxSize[500]] textcounter-question"
                placeholder="<?php echo translate('item_question_reply_form_text_placeholder', null, true);?>"><?php echo empty($question['reply']) ? '' : cleanOutput($question['reply']);?></textarea>
            <input type="hidden" name="question" value="<?php echo $question['id_q']; ?>" />
            <input type="hidden" name="link" value="<?php echo $item['url']; ?>" />
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary"
                    <?php echo addQaUniqueIdentifier("items-questions-my__edit-reply_submit-btn_popup")?>
                    type="submit">
                    <?php echo translate('item_question_reply_form_submit_btn');?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
$(function() {
    var onSaveContent = function(formElement) {
        var form = $(formElement);
        var wrapper = form.closest('.js-modal-flex');
        var submitButton = form.find('button[type=submit]');
        var formData = form.serializeArray();
        var url = form.attr('action');
        var sendRequest = function(url, data) {
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
        var onRequestSuccess = function(data) {
            hideLoader(wrapper);
            systemMessages(data.message, data.mess_type);
            if (data.mess_type === 'success') {
                callFunction(saveCallback, data);
                closeFancyBox();
            }
        };

        beforeSend();
        sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
    };

    var isEdit = Boolean(~~parseInt('<?php echo (int) !empty($question["reply"]); ?>'));
    var saveCallback = isEdit ? 'editQuestionReplyCallback' : 'addQuestionReplyCallback';
    var answerTextarea = $('#leave-question-reply--formfield--reply');
    var counterOptions = {
        countDown: true,
        countDownTextBefore: translate_js({
            plug: 'textcounter',
            text: 'count_down_text_before'
        }),
        countDownTextAfter: translate_js({
            plug: 'textcounter',
            text: 'count_down_text_after'
        })
    };

    answerTextarea.textcounter(counterOptions);

    window.itemsQuestionsleaveReplyFormCallBack = onSaveContent;
});
</script>
