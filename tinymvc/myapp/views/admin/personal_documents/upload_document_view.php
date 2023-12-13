<div class="wr-modal-b inputs-40">
	<form class="modal-b__form validateModal" action="<?php echo $action; ?>" id="upload-document--form">
        <input type="hidden" name="document" value="<?php echo $document['id_document']; ?>">
        <input type="hidden" name="user" value="<?php echo $document['id_user']; ?>">
        <input type="hidden" name="multiple" value="<?php echo $multiple; ?>">

		<div class="modal-b__content w-700 mnh-195">
            <div class="container-fluid-modal">
                <div class="row">
                <?php if($multiple){?>
                    <div class="col-xs-12">
                        <label class="modal-b__label">Subtitle</label>
                        <input type="text"
                        <?php echo addQaUniqueIdentifier("admin-users__verification-upload-document-form__subtitle_input")?>
                        name="subtitle"
                        class="validate[minSize[3],maxSize[500]]"
                        value="<?php echo !$another ? $document['subtitle'] : ''; ?>"
                        placeholder="Enter the subtitle">
                    </div>
                <?php } ?>
                    <div class="col-xs-12">
                        <label class="modal-b__label">Comment</label>
                        <textarea name="comment"
                            <?php echo addQaUniqueIdentifier("admin-users__verification-upload-document-form__comment-textarea")?>
                            data-max="500"
                            id="upload-document--formfield--comment"
                            class="h-100 validate[maxSize[500]] textcounter-document_comment"
                            placeholder="Enter the comment"></textarea>
                    </div>

                    <div class="col-xs-12 mt-15" <?php echo addQaUniqueIdentifier("admin-users__verification-upload-document-form__upload-button-wrapper")?>>
                        <?php widgetEpdocsFileUploader('upload-document', 'uploaded_document', 'document', $fileupload, array(), array(), null, false, false); ?>
                    </div>
                </div>
            </div>
		</div>
		<div class="modal-b__btns clearfix">
            <?php if(null !== $reference['from']) { ?>
                <?php views()->display('admin/return_back_button_view', array('reference' => $reference['from'], 'id' => "upload-document--formaction--go-back")); ?>
            <?php } ?>
			<button class="btn btn-primary w-150 pull-right" type="submit" <?php echo addQaUniqueIdentifier("admin-users__verification-upload-document-form__confirm-button")?>>Confirm</button>
		</div>
	</form>
</div>

<script>
    $(function() {
        var onSaveContent = function(formElement) {
            var form = $(formElement);
            var wrapper = form.closest('.wr-modal-b');
            var submitButton = form.find('button[type=submit]');
            var formData = form.serializeArray();
            var url = form.attr('action');
            var sendRequest = function (url, data) {
                return $.post(url, data, null, 'json');
            };
            var beforeSend = function() {
                showFormLoader(wrapper);
                submitButton.addClass('disabled');
            };
            var onRequestEnd = function() {
                hideFormLoader(wrapper);
                submitButton.removeClass('disabled');
            };
            var onRequestSuccess = function(data){
                hideFormLoader(wrapper);
                systemMessages(data.message, data.mess_type);
                if(data.mess_type === 'success'){
                    callFunction('callbackUploadDocument', form.find('input[name=user]').val() || null, form.find('input[name=document]').val() || null, data);
                    if (goBackButton.length) {
                        goBackButton.trigger('click');
                    } else {
                        closeFancyBox();
                    }
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
        };

        var order = parseInt('<?php echo $order_detail['id']; ?>', 10);
        var goBackButton = $("#upload-document--formaction--go-back");
        var comment = $('#upload-document--formfield--comment');
        var counterOptions = {
            countDown: true,
            countDownTextBefore: translate_js({plug: 'textcounter', text: 'count_down_text_before'}),
            countDownTextAfter: translate_js({plug: 'textcounter', text: 'count_down_text_after'})
        };

        comment.textcounter(counterOptions);

        mix(window, {
            modalFormCallBack: onSaveContent
        }, false);
    });
</script>
