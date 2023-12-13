<div class="wr-modal-b">
    <form method="post" class="validateModal relative-b">
		<?php if(!empty($text_block_i18n)){?>
			<input type="hidden" name="id_block_i18n" value="<?php echo $text_block_i18n['id_block_i18n'];?>" />
		<?php } else{?>
            <input type="hidden" name="id_block" value="<?php echo $text_block['id_block'];?>" />
		<?php }?>
		<div class="modal-b__content pb-0 w-900 mh-700">
            <div class="row">
                <!-- languages -->
                <div class="col-xs-12 initial-b">
                    <label class="modal-b__label">Language</label>
                    <?php if(empty($text_block_i18n)){?>
                        <?php $translations_data = json_decode($text_block['translations_data'], true);?>
                        <select class="form-control" name="lang_id">
                            <option selected disabled>Select language</option>
                            <?php foreach($languages as $language){?>
                                <option value="<?php echo $language['id_lang'];?>" <?php if(array_key_exists($language['lang_iso2'], $translations_data)){echo 'disabled';}?>><?php echo $language['lang_name'];?></option>
                            <?php } ?>
                        </select>
                    <?php } else{?>
                        <div class="form-control">
                            <?php echo $language['lang_name'];?>
                        </div>
                    <?php }?>
                </div>

                <!-- title -->
                <div class="col-xs-12">
                    <label class="modal-b__label">Title block</label>
                    <div class="col-xs-12 pl-0 pr-0 mb-15">
						<input type="text"  class="form-control mn-100" value="<?php echo $text_block['title_block']?>" disabled />
                    </div>
                    <div class="col-xs-12 pl-0 pr-0">
						<input type="text" name="title_block" class="form-control mn-100 validate[required,maxSize[500]]" value="<?php echo empty($text_block_i18n) ? '' : $text_block_i18n['title_block']?>" />
                    </div>
                </div>

                <!-- Description block -->
                <div class="col-xs-12">
                    <label class="modal-b__label">Description block </label>
                    <div class="col-xs-12 pl-0 pr-0 mb-15">
						<textarea class="w-100pr h-75 mnh-75" disabled><?php echo $text_block['description_block']?></textarea>
                    </div>
                    <div class="col-xs-12 pl-0 pr-0 mb-15">
						<textarea name="description_block" class="w-100pr h-75 mnh-75 validate[required,maxSize[255]]"><?php echo isset($text_block_i18n) ? $text_block_i18n['description_block'] : ''?></textarea>
                    </div>
                </div>

                <!-- Text block -->
                <div class="col-xs-12">
                    <label class="modal-b__label">Text block</label>
                    <div class="col-xs-12 pl-0 pr-0 mb-15">
						<textarea id="translation--form-input--text-original" class="validate[required]" disabled><?php echo $text_block['text_block']?></textarea>
                    </div>
                    <div class="col-xs-12 pl-0 pr-0 mb-15">
						<textarea id="translation--form-input--text" name="text_block" class="validate[required]"><?php echo isset($text_block_i18n) ? $text_block_i18n['text_block'] : '' ?></textarea>
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

<script type="text/javascript" src="<?php echo __SITE_URL;?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script>
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL ?>text_block/ajax_text_block_operation/<?php echo ((isset($text_block_i18n) ? "edit" : "create"))?>_text_block_i18n',
		data: $form.serialize(),
		beforeSend: function () {
			showLoader($form);
		},
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );
			hideLoader($form);

			if(data.mess_type == 'success'){
				closeFancyBox();
				if(data_table != undefined){
					data_table.fnDraw(false);
                }
			}else{

			}
		}
	});
}

$(document).ready(function() {
    var previewOptions = {
        readonly: true,
        menubar: false,
        statusbar: false,
        height: 250,
        dialog_type: "modal",
        toolbar: false,
        resize: false
    };
    var editorOptions = {
        menubar: false,
        statusbar : false,
        height : 250,
        plugins: ["autolink lists link textcolor media preview"],
        dialog_type : "modal",
        toolbar: "fontsizeselect | bold italic underline forecolor backcolor link | numlist bullist | alignleft aligncenter alignright alignjustify",
        fontsize_formats: '12px 14px 16px 18px 20px 22px 24px',
        resize: false
    }

    tinymce.remove('#translation--form-input--answer');
    tinymce.remove('#translation--form-input--answer-original');
    tinymce.init($.extend({}, previewOptions, { selector: '#translation--form-input--text-original' }));
    tinymce.init($.extend({}, editorOptions, { selector: '#translation--form-input--text' }));
});
</script>
