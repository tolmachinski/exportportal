<div class="wr-modal-b">
    <form method="post" class="validateModal relative-b">
		<?php if (isset($category_i18n)) {?>
			<input type="hidden" name="id_category_i18n" value="<?php echo $category_i18n['id_category_i18n'];?>" />
		<?php } else {?>
			<input type="hidden" name="id_category" value="<?php echo $category['id_category'];?>" />
		<?php }?>
		<div class="modal-b__content pb-0 w-900">
            <div class="row">
                <div class="col-xs-12 initial-b">
                    <!-- language -->
                    <label class="modal-b__label">Language</label>
                    <?php if (empty($category_i18n)) {?>
                        <?php $translations_data = json_decode($category['translations_data'], true);?>
                        <select class="form-control" name="lang_category">
                            <option selected disabled>Select language</option>
                            <?php foreach ($tlanguages as $lang) {?>
                                <option value="<?php echo $lang['lang_iso2'];?>" <?php if(array_key_exists($lang['lang_iso2'], $translations_data)){echo 'disabled';}?>><?php echo $lang['lang_name'];?></option>
                            <?php } ?>
                        </select>
                    <?php } else{?>
                        <div class="form-control">
                            <?php echo $lang_block['lang_name'];?>
                        </div>
                    <?php }?>
                </div>
                <!-- name category -->
                <div class="col-xs-12">
                    <label class="modal-b__label">Name category</label>
                    <input class="form-control mnh-100 mb-15" type="text" value="<?php echo $category['name'];?>" disabled/>
                    <input class="form-control validate[required,maxSize[250]] mnh-100" type="text" name="name" value="<?php echo $category_i18n['name'] ?: '';?>"/>
                    <label class="modal-b__label">H1</label>
                    <input class="form-control validate[required,maxSize[255]]" type="text" name="page_header" value="<?php echo $category_i18n['h1'] ?: '';?>"/>
                    <label class="modal-b__label">Subtitle</label>
                    <input class="form-control validate[required,maxSize[255]]" type="text" name="page_subtitle" value="<?php echo $category_i18n['subtitle'] ?: '';?>"/>
                    <label class="modal-b__label">Meta title</label>
                    <input class="form-control validate[required,maxSize[400]]" type="text" name="meta_title" value="<?php echo $category_i18n['meta_title'] ?: '';?>"/>
                    <label class="modal-b__label">Meta description</label>
                    <textarea class="form-control validate[required,maxSize[500]]" type="text" name="meta_description"><?php echo $category_i18n['meta_description'] ?: '';?></textarea>
                    <label class="modal-b__label">Meta keywords</label>
                    <textarea class="form-control validate[maxSize[500]]" type="text" name="meta_keywords"><?php echo $category_i18n['meta_keywords'] ?: '';?></textarea>
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

<script>
	function modalFormCallBack(form, data_table){
		var $form = $(form);
		var $wrform = $form.closest('.wr-modal-b');
		var fdata = $form.serialize();

		<?php if (!empty($category_i18n)) {?>
			var url = '<?php echo __SITE_URL?>blogs/ajax_blogs_operation/edit_category_i18n';
		<?php } else {?>
			var url = '<?php echo __SITE_URL?>blogs/ajax_blogs_operation/add_category_i18n';
		<?php }?>

		$.ajax({
			type: 'POST',
			url: url,
			data: fdata,
			dataType: 'JSON',
			beforeSend: function(){
				showFormLoader($wrform);
				$form.find('button[type=submit]').addClass('disabled');
			},
			success: function(data){
				systemMessages( data.message, 'message-' + data.mess_type );
				hideLoader($wrform);
				if(data.mess_type == 'success'){
					closeFancyBox();
					if(data_table != undefined)
						data_table.fnDraw();
				}
			}
		});
	}
</script>
