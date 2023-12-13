<div class="wr-modal-b">
    <form class="modal-b__form validateModal">
		<div class="modal-b__content pb-0 w-700">
			<div class="row">
                <div class="col-xs-12">
                    <label class="modal-b__label">Translation key</label>
                    <div class="form-control h-100pr"><?php echo $translation_file["translation_key"];?></div>
                </div>
                <div class="col-xs-12">
                    <label class="modal-b__label" >Select pages</label>
                    <select name="pages[]" class="form-control" id="current-key-selected-pages" multiple>
                        <option></option>
                        <?php foreach ($pages as $page) { ?>
                            <option value="<?php echo $page['id']; ?>" <?php echo $page['selected'] ? 'selected' : ''; ?>><?php echo $page['name']; ?></option>
                        <?php } ?>
                    </select>
                    <?php foreach ($selected_pages as $id_page => $selected_page){?>
                        <input type="hidden" name="old_pages[]" value="<?php echo $id_page;?>">
                    <?php }?>
                </div>
                <div class="col-xs-12">
                    <label class="modal-b__label">Add tag</label>
                    <select name="tags[]" class="form-control" id="new-key-selected-tags" multiple>
                        <option></option>
                        <?php foreach ($tags as $tagId => $tagName) { ?>
                            <option value="<?php echo $tagId; ?>" <?php echo isset($oldTags[$tagId]) ? 'selected' : ''; ?>><?php echo $tagName; ?></option>
                        <?php } ?>
                    </select>
                    <?php foreach ($oldTags as $tagId => $tagName){?>
                        <input type="hidden" name="old_tags[]" value="<?php echo $tagId;?>">
                    <?php }?>
                </div>
                <div class="col-xs-12 mt-15">
                    <label class="modal-b__label" >Text</label>
                    <textarea class="form-control validate[required]" name="text"><?php echo $translation_file["translation_text"];?></textarea>
                </div>
                <div class="col-xs-12 mt-15">
                    <label class="modal-b__label">Usage example</label>
                    <textarea class="form-control" name="usage_example"><?php echo $translation_file["usage_example"];?></textarea>
                </div>
			</div>
		</div>
        <div class="modal-b__btns clearfix">
            <label class="lh-30 vam">
                <input type="checkbox" name="translation_old" <?php echo checked($translation_file["translation_old"], 1);?>> Used only in old design
            </label>
            <input type="hidden" name="id_key" value="<?php echo $translation_file["id_key"];?>">
            <button class="btn btn-success pull-right call-function" data-callback="translation_key_edit" type="button">Submit</button>
        </div>
    </form>
</div>
<script type="application/javascript">
	var translation_key_edit = function(btn){
        var $this = $(btn);
        var $form = $this.closest('form');
        $.ajax({
            url: '<?php echo __SITE_URL ?>translations/ajax_operations/translation_key_edit',
            type: 'POST',
            dataType: 'json',
            data: $form.serialize(),
            beforeSend: function () {
                showLoader($form);
            },
            success: function(data){
                systemMessages( data.message, 'message-' + data.mess_type );

                if(data.mess_type == 'success'){
                    translations_files_callback();
                    closeFancyBox();
                } else{
                    hideLoader($form);
                }
            }
        });
    }

    $(document).ready(function(){
        $('#current-key-selected-pages').select2({
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
    })
</script>
