<div class="wr-modal-b">
    <form method="post" action="<?php echo $action; ?>" id="accreditation-document-translation--form" class="validateModal relative-b">
        <input type="hidden" name="id" id="accreditation-document-translation--form-input--document" value="<?php echo $document['id_document']; ?>">
		<div class="modal-b__content pb-0 w-900">
			<div class="row">
                <div class="col-xs-12 initial-b">
                    <label class="modal-b__label">Language</label>
                    <select class="form-control validate[required]" name="language" id="accreditation-document-translation--form-input--language">
                        <option selected disabled>Select language</option>
                        <?php if(!empty($languages)){ ?>
                            <?php foreach($languages as $lang_id => $language){ ?>
                                <option value="<?php echo $lang_id; ?>" <?php echo in_array($language['lang_iso2'], $translations) ? 'disabled' : ''; ?>>
                                    <?php echo $language['lang_name']; ?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-xs-12">
                    <label class="modal-b__label">Title</label>
                    <div class="col-xs-12 pl-0 pr-0 mb-15">
                        <div class="form-control"><?php echo $document['document_title']; ?></div>
                    </div>
                    <div class="col-xs-12 pl-0 pr-0">
                        <input type="text"
                            name="title"
                            id="accreditation-documents-translation--form-input--title"
                            class="form-control validate[maxSize[250]]"
                            placeholder="Enter the title">
                    </div>
                </div>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <button class="btn btn-success pull-right" type="submit" id="accreditation-document-translation--form-action--submit">
                <span class="ep-icon ep-icon_ok"></span> Save
            </button>
        </div>
    </form>
</div>
<script type="text/javascript">
	var modalFormCallBack = function (formNode, dataGrid){
        var form = $(formNode);
        var url = form.attr('action');
        var data = form.serializeArray();
        var onRequestSuccess = function (response) {
            systemMessages(response.message, 'message-' + response.mess_type);
            if(response.mess_type == 'success'){
                closeFancyBox();
                if(dataGrid) {
                    $(dataGrid).DataTable().draw(false);
                }
            }
        };

        showLoader(form);
        $.post(url, data, null, 'json').done(onRequestSuccess).fail(onRequestError).always(function() {
            hideLoader(form);
        });
    };
</script>
