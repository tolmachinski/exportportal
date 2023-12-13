<div class="wr-modal-b">
    <form action="<?php echo $action; ?>" class="modal-b__form validateModal">
		<div class="modal-b__content pb-0 w-700">
			<div class="row">
                <div class="col-xs-12 initial-b">
                    <label class="modal-b__label">Translation key</label>
                    <input type="text" class="form-control validate[required,maxSize[1000]]" name="translation_key">
                </div>
                <div class="col-xs-4">
                    <label class="modal-b__label">Select file</label>
                    <select name="file_name" class="form-control">
                        <option value="" disabled selected>Select file</option>
                        <?php foreach($file_names as $file_name){?>
                            <!-- TODO: Remove selected after task done -->
                            <option value="<?php echo $file_name;?>" <?php echo $file_name === "system_messages_lang.php" ? "selected" : "" ?>><?php echo $file_name;?></option>
                        <?php }?>
                        <option value="other">Other</option>
                        <!-- TODO: USE IT THEN ADD SYSTEM MESSAGES ON TASK -->
                        <!-- <option value="system_messages_lang.php" selected>system_messages_lang.php</option> -->
                    </select>
                </div>
				<div class="col-xs-4" id="new_file_name">
                    <label class="modal-b__label">New file name</label>
                    <input type="text" class="form-control" name="file_name" placeholder="New file name" disabled>
                </div>
                <div class="col-xs-4">
                    <label class="modal-b__label">File type</label>
                    <select name="file_type" class="form-control validate[required]">
                        <option value="php">PHP</option>
                        <option value="js">JS</option>
                    </select>
                </div>
                <div class="col-xs-12">
                    <label class="modal-b__label" >Select pages</label>
                    <select name="pages[]" class="form-control" id="new-key-selected-pages" multiple>
                        <option></option>
                        <?php foreach ($pages as $page_id => $page_name) { ?>
                            <option value="<?php echo $page_id; ?>"><?php echo $page_name; ?></option>
                        <?php } ?>
                        <option value="0">Used on all pages</option>
                    </select>
                </div>
                <div class="col-xs-12">
                    <label class="modal-b__label" >Add tag</label>
                    <select name="tags[]" class="form-control" id="new-key-selected-tags" multiple>
                        <option></option>
                        <?php foreach ($tags as $tagId => $tagName) { ?>
                            <option value="<?php echo $tagId; ?>"><?php echo $tagName; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-xs-12 mt-15">
                    <label class="modal-b__label" >Text</label>
                    <textarea class="form-control validate[required]" name="text"></textarea>
                </div>
                <div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Usage example</label>
                    <textarea class="form-control" name="usage"></textarea>
                </div>
                <?php if (isset($systmess)) { ?>
                    <div class="col-xs-12 mt-15">
                        <label class="lh-20"><input type="checkbox" checked name="is_systmess"> Is system message</label>
                    </div>
                <?php } ?>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <label class="lh-30 vam">
                <input type="checkbox" name="old"> Used only in old design
            </label>
            <button class="btn btn-success pull-right" type="submit" id="translation-key--form-action--submit">
                <span class="ep-icon ep-icon_ok"></span> Save
            </button>
        </div>
    </form>
</div>
<script>
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

    $(function(){
        $('#new-key-selected-pages').select2({
            width: '100%',
            multiple: true,
            placeholder: "Selected pages",
            minimumResultsForSearch: 2,
        });
        $('#new-key-selected-tags').select2({
            width: '100%',
            multiple: true,
            placeholder: "Selected tags",
            minimumResultsForSearch: 2,
        });
        $('select[name="file_name"]').on('change', function(){
            var option = $(this).val();
            $('input[name="file_name"]').val(option == 'other'?'':option).prop('disabled', option != 'other');
        });
    });
</script>
