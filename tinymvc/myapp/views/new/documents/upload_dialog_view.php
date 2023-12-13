<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal" id="upload-documents-inline--form" data-js-action="documents:inline-upload-dialog.upload">
		<div class="modal-flex__content">
            <?php if ($multiple) { ?>
                <label class="input-label">
                    <?php echo translate('verification_documents_inline_upload_form_subtitle_input_label', null, true); ?>
                </label>
                <input
                    id="upload-documents-inline--form-field--subtitle"
                    type="text"
                    name="subtitle"
                    class="validate[minSize[3],maxSize[500]]"
                    value="<?php echo cleanOutput($subtitle); ?>"
                    placeholder="<?php echo translate('verification_documents_inline_upload_form_subtitle_input_placeholder', null, true); ?>"
                    <?php echo addQaUniqueIdentifier('upload-documents-inline__form-field-subtitle-input'); ?>
                >
            <?php } ?>

            <label class="input-label">
                <?php echo translate('verification_documents_inline_upload_form_comment_input_label', null, true); ?>
            </label>
            <textarea
                id="upload-documents-inline--formfield--comment"
                class="validate[maxSize[500]] textcounter-document_comment"
                name="comment"
                data-max="500"
                placeholder="<?php echo translate('verification_documents_inline_upload_form_comment_input_placeholder', null, true); ?>"
                <?php echo addQaUniqueIdentifier('upload-documents-inline__form-field-comment-textarea'); ?>
            ></textarea>

            <div class="container-fluid-modal">
                <?php widgetEpdocsFileUploader('upload-document', 'document', 'document', $uploadOptions); ?>
            </div>
		</div>

		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button
                    id="upload-documents-inline--form-action--submit"
                    type="submit"
                    class="btn btn-primary"
                    <?php echo addQaUniqueIdentifier('upload-documents-inline__form-confirm-button'); ?>
                >
                    <?php echo translate('verification_documents_inline_upload_form_submit_button_text', null, true); ?>
                </button>
            </div>
		</div>
	</form>
</div>

<?php echo dispatchDynamicFragment('documents:inline-upload-dialog', ['#upload-documents-inline--form']); ?>
