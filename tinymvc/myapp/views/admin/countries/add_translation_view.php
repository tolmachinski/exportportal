<div class="wr-modal-b">
    <form method="post" action="<?php echo $action; ?>" class="validateModal relative-b">
        <input type="hidden" name="country_id" value="<?php echo $country_id;?>">
		<div class="modal-b__content pb-0 w-700">
			<div class="row">
                <div class="col-xs-12 initial-b">
                    <label class="modal-b__label">Language</label>
                    <select class="form-control validate[required]" name="language_id">
                        <option selected disabled>Select language</option>
                        <?php foreach ($all_languages as $language) {?>
                            <option value="<?php echo $language['id_lang'];?>" <?php echo in_array($language['lang_iso2'], $translated_languages) ? 'disabled' : '';?>>
                                <?php echo cleanOutput($language['lang_name']);?>
                            </option>
                        <?php }?>
                    </select>
                </div>
                <div class="col-xs-12">
                    <label class="modal-b__label">Translation [EN]</label>
                    <div class="col-xs-12 pl-0 pr-0 mb-15">
                        <textarea class="form-control" readonly><?php echo cleanOutput($en_translation);?></textarea>
                    </div>
                    <div class="col-xs-12 pl-0 pr-0">
                        <textarea class="form-control validate[required,maxSize[255]]" name="translation"></textarea>
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
                if(dataGrid) {
                    $(dataGrid).DataTable().draw(false);
                }

                closeFancyBox();
            }
        };

        showLoader(form);
        $.post(url, data, null, 'json').done(onRequestSuccess).fail(onRequestError).always(function() {
            hideLoader(form);
        });
    };
</script>
