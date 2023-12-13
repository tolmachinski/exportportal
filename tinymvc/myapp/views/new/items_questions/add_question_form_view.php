<div class="js-modal-flex wr-modal-flex inputs-40">
    <form
        id="add-item-question--form"
        class="modal-flex__form validateModal"
        data-callback="itemsQuestionsAddQuestionFormCallBack"
        action="<?php echo $action; ?>"
    >
        <input type="hidden" name="item" value="<?php echo $item["id"]; ?>"/>
	    <div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div class="row">
                    <div class="col-12">
                        <label class="input-label input-label--required">Category</label>
                        <select name="name_category"
                            id="add-item-question--formfield--category"
                            class="form-control validate[required]">
                            <option value="" selected disabled>Select category</option>
                            <?php if (!empty($question_categories)) { ?>
                                <?php foreach($question_categories as $category) { ?>
                                    <option value="<?php echo $category['id_category']; ?>">
                                        <?php echo cleanOutput($category['name_category']); ?>
                                    </option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="input-label input-label--required">Title</label>
                        <input type="text"
                            name="title"
                            maxlength="255"
                            id="add-item-question--formfield--title"
                            class="validate[required,maxSize[255]]"
                            placeholder="Enter the title">
                    </div>

                    <div class="col-12">
                        <label class="input-label input-label--required">Text</label>
			            <textarea name="description"
                            data-max="500"
                            id="add-item-question--formfield--text"
                            class="validate[required,maxSize[500]] textcounter-question"
                            placeholder="Write your question text here"
                        ></textarea>
                    </div>

                    <div class="col-12">
                        <div class="mt-10">
                            <label class="custom-checkbox">
                                <input type="checkbox"
                                    value="1"
                                    name="answer"/>
                                <span class="custom-checkbox__text">Notify when the seller answers</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
	    </div>
	    <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Save</button>
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
                    callFunction('addQuestionCallback', data);
                    closeFancyBox();
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
        };

        var questionTextarea = $('#add-item-question--formfield--text');
        var counterOptions = {
			countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
		};

        questionTextarea.textcounter(counterOptions);

        window.itemsQuestionsAddQuestionFormCallBack = onSaveContent;
	});
</script>
