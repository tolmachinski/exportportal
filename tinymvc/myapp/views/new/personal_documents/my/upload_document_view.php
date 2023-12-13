<div class="js-modal-flex wr-modal-flex inputs-40">
	<form
        id="upload-document--form"
        class="modal-flex__form validateModal"
        data-callback="personalDocumentsUploadDocumentFormCallBack"
        action="<?php echo $action; ?>"
    >

        <input type="hidden" name="document" value="<?php echo $document['id_document']; ?>">
        <input type="hidden" name="user" value="<?php echo $document['id_user']; ?>">
        <input type="hidden" name="multiple" value="<?php echo $multiple; ?>">

		<div class="modal-flex__content">
        <?php if($multiple){?>
            <label class="input-label">Subtitle</label>
            <input type="text" name="subtitle" class="validate[minSize[3],maxSize[500]]" value="<?php echo !$another ? $document['subtitle'] : ''; ?>" placeholder="Enter the subtitle">
        <?php } ?>
            <label class="input-label">Comment</label>
            <textarea
                id="upload-document--formfield--comment"
                class="validate[maxSize[500]] textcounter-document_comment"
                <?php echo addQaUniqueIdentifier("upload-document__form-textarea");?>
                name="comment"
                data-max="500"
                placeholder="Enter the comment"
            ><?php echo !empty($comment) ? $comment : null; ?></textarea>
            <div class="container-fluid-modal">
                <?php widgetEpdocsFileUploader('upload-document', 'uploaded_document', 'document', $fileupload); ?>
            </div>
		</div>
		<div class="modal-flex__btns">
            <?php if(null !== $reference['from']) { ?>
                <?php views()->display('new/return_back_button_view', array('reference' => $reference['from'], 'id' => "document-versions--popup--reference")); ?>
            <?php } ?>
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" <?php echo addQaUniqueIdentifier("upload-document__form-confirm-button")?> type="submit">Confirm</button>
            </div>
		</div>
	</form>
</div>

<script>
    $(function() {
        var onSaveContent = function (formElement) {
            var form = $(formElement);
            var document = form.find('input[name=document]').val() || null;
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
            var onRequestSuccess = function(document, data){
                systemMessages(data.message, data.mess_type);
                if(data.mess_type === 'success'){
                    if (isReUpload) {
                        if (!data.isReUplodable && data.texts && data.texts.notReuplodable) {
                            systemMessages(data.texts.notReuplodable, 'warning');
                        }

                        callFunction('callbackReUploadDocument', document, data);
                    } else {
                        callFunction('callbackUploadDocument', document, data);
                    }
                    closeFancyBox();
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess.bind(null, document)).fail(onRequestError).always(onRequestEnd);
        };

        var isReUpload = Boolean(~~parseInt('<?php echo (int) $is_reupload; ?>', 10));
        var comment = $('#upload-document--formfield--comment');
        var counterOptions = {
            countDown: true,
            countDownTextBefore: translate_js({plug: 'textcounter', text: 'count_down_text_before'}),
            countDownTextAfter: translate_js({plug: 'textcounter', text: 'count_down_text_after'})
        };

        comment.textcounter(counterOptions);

        mix(window, { personalDocumentsUploadDocumentFormCallBack: onSaveContent }, false);
    });
</script>
