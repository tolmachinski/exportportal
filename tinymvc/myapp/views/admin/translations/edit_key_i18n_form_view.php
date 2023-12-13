<div class="wr-modal-b">
    <form method="post" action="<?php echo $action; ?>" id="translation-localization--form" class="validateModal relative-b">
        <input type="hidden" name="language" id="translation-localization--form-input--lang" value="<?php echo $language['id_lang']; ?>">
		<div class="modal-b__content pb-0 w-700">
			<div class="row">
                <div class="col-xs-12 initial-b">
                    <label class="modal-b__label">Language</label>
                    <div class="form-control">
                        <?php echo $language['lang_name']; ?>
                    </div>
                </div>
                <div class="col-xs-12">
                    <label class="modal-b__label">Text</label>
                    <div class="col-xs-12 pl-0 pr-0 mb-15">
                        <textarea class="form-control" id="translation-localization--form-input--original" readonly><?php echo $original; ?></textarea>
                    </div>
                    <div class="col-xs-12 pl-0 pr-0">
                        <textarea class="form-control validate[required]" id="translation-localization--form-input--translation" name="translation"><?php echo $translated; ?></textarea>
                    </div>
                </div>
                <?php if(!empty($usage)) { ?>
                    <div class="col-xs-12">
                        <label class="modal-b__label">Usage example</label>
                        <div class="col-xs-12 pl-0 pr-0 mb-15">
                            <textarea class="form-control" readonly><?php echo $usage;?></textarea>
                        </div>
                    </div>
                <?php } ?>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <button class="btn btn-success pull-right" type="submit" id="translation-localization--form-action--submit">
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
