<div class="wr-modal-b">
    <form method="post" action="<?php echo $action; ?>" class="validateModal relative-b">
        <input type="hidden" name="language_iso2" value="<?php echo $language_key;?>">
        <input type="hidden" name="country_id" value="<?php echo $country_id;?>">
		<div class="modal-b__content pb-0 w-700">
			<div class="row">
                <div class="col-xs-12 initial-b">
                    <label class="modal-b__label">Language</label>
                    <div class="form-control">
                        <?php echo cleanOutput($language);?>
                    </div>
                </div>
                <div class="col-xs-12">
                    <label class="modal-b__label">Translation [EN]</label>
                    <div class="col-xs-12 pl-0 pr-0 mb-15">
                        <textarea class="form-control" readonly><?php echo cleanOutput($en_translation);?></textarea>
                    </div>
                    <div class="col-xs-12 pl-0 pr-0">
                        <textarea class="form-control validate[required,maxSize[255]]" name="translation"><?php echo cleanOutput($current_translation);?></textarea>
                    </div>
                </div>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <button class="btn btn-success pull-right" type="submit">
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
            }
        };

        showLoader(form);
        $.post(url, data, null, 'json').done(onRequestSuccess).fail(onRequestError).always(function() {
            hideLoader(form);
        });
    };
</script>
