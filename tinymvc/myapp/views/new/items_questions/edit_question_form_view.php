<div class="js-modal-flex wr-modal-flex inputs-40">
    <form
        id="edit-item-question----form"
        class="modal-flex__form validateModal"
        data-callback="itemsQuestionsEditQuestionFormCallBack"
        action="<?php echo $action; ?>"
    >
        <input type="hidden" name="question" value="<?php echo $question_info["id_q"]; ?>"/>
	    <div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div class="row">
                    <div class="col-12">
                        <label class="input-label input-label--required"><?php echo translate('item_question_form_category_question_label');?></label>
                        <select name="name_category"
                            <?php echo addQaUniqueIdentifier("items-questions-my__edit_category-select_popup")?>
                            id="edit-item-question----formfield--category"
                            class="form-control validate[required]">
                            <option value="" selected disabled><?php echo translate('item_question_form_category_question_placeholder');?></option>
                            <?php foreach($question_categories as $category) {?>
                                <option value="<?php echo $category['id_category']; ?>"
                                    <?php echo isset($question_info) ? selected($question_info['id_category'], $category['id_category']) : '';?>>
                                    <?php echo $category['name_category'];?>
                                </option>
                            <?php }?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="input-label input-label--required"><?php echo translate('item_question_form_title_question_label');?></label>
                        <input type="text"
                            <?php echo addQaUniqueIdentifier("items-questions-my__edit_title-input_popup")?>
                            name="title"
                            maxlength="255"
                            id="edit-item-question----formfield--title"
                            class="validate[required,maxSize[255]]"
                            placeholder="<?php echo translate('item_question_form_title_question_placeholder', null, true);?>"
                            value="<?php echo isset($question_info) ? $question_info['title_question'] : '';?>">
                    </div>

                    <div class="col-12">
                        <label class="input-label input-label--required"><?php echo translate('item_question_form_text_question_label');?></label>
			            <textarea name="description"
                            <?php echo addQaUniqueIdentifier("items-questions-my__edit_text-textarea_popup")?>
                            data-max="500"
                            id="edit-item-question----formfield--text"
                            class="validate[required,maxSize[500]] textcounter-question"
                            placeholder="<?php echo translate('item_question_form_text_question_placeholder', null, true);?>"
                        ><?php echo isset($question_info) ? $question_info['question'] : '';?></textarea>
                    </div>

                    <div class="col-12">
                        <div class="mt-10">
                            <label class="custom-checkbox">
                                <input type="checkbox"
                                    value="1"
                                    name="answer"
                                    <?php echo isset($question_info) ? checked($question_info['notify'], 1) : '';?>/>
                                <span class="custom-checkbox__text"><?php echo translate('item_question_form_notify_about_answer_label');?></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
	    </div>
	    <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" <?php echo addQaUniqueIdentifier("items-questions-my__edit_save-btn_popup")?> type="submit"><?php echo translate('item_question_form_submit_btn');?></button>
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
                if(data.mess_type === 'success'){
                    callFunction('editQuestionCallback', data);
                    closeFancyBox();
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
        };

        var questionTextarea = $('#edit-item-question----formfield--text');
        var counterOptions = {
			countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
		};

        questionTextarea.textcounter(counterOptions);

        window.itemsQuestionsEditQuestionFormCallBack = onSaveContent;
	});
</script>
