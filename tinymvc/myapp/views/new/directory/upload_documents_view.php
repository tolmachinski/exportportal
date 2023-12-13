<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal" action="<?php echo $action; ?>" id="company-upload-document--form">

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
                <?php echo addQaUniqueIdentifier("company_upload-document__form-textarea");?>
                name="comment"
                data-max="500"
                placeholder="Enter the comment"
            ><?php echo !empty($comment) ? $comment : null; ?></textarea>
            <div class="container-fluid-modal">
                <?php widgetEpdocsFileUploader('upload-document', 'uploaded_document', 'document', []); ?>
            </div>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary call-action" data-js-action="forms-btn:upload-files" <?php echo addQaUniqueIdentifier("company_upload-document__form-confirm-button")?> type="submit">Confirm</button>
            </div>
		</div>
	</form>
</div>

<?php echo dispatchDynamicFragment('documents:inline-upload-dialog', ['#company-upload-document--form']); ?>
