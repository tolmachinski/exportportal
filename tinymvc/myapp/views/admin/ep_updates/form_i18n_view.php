<form id="add-ep-update-form" method="post" class="validateModal relative-b">
   <input type="hidden" name="id_ep_update_i18n" value="<?php if(!empty($ep_update_i18n)) echo $ep_update_i18n['id_ep_update_i18n']; ?>"/>
   <div class="wr-form-content w-700 h-440">
		<table cellspacing="0" cellpadding="0" class="data table-striped w-100pr vam-table">
			<tbody>
				<tr>
					<td class="w-100">Language</td>
					<td>
                        <?php $translations_data = json_decode($ep_update['translations_data'], true);
                            $translations_data = empty($translations_data) ? array() : $translations_data;
                        ?>
                        <?php if(empty($ep_update_i18n)) { ?>
                            <select name="ep_update_i18n_lang">
                                <?php $translations_data['en'] = true; ?>
                                <?php foreach($tlanguages as $tlanguage) { ?>
                                    <option value="<?php echo $tlanguage['lang_iso2']; ?>" <?php echo empty($translations_data[$tlanguage['lang_iso2']]) ? '': 'disabled'; ?>><?php echo $tlanguage['lang_name']; ?></option>
                                <?php } ?>
                            </select>
                        <?php } else { ?>
                            <input type="hidden" name="ep_update_i18n_lang" value="<?php echo $ep_update_i18n['ep_update_i18n_lang']; ?>"/>
                            <?php echo $translations_data[$ep_update_i18n['ep_update_i18n_lang']]['lang_name']; ?>
                        <?php }?>
					</td>
				</tr>
				<tr>
					<td>Title</td>
					<td>
                        <input type="text" class="w-100pr validate[required,maxSize[200]]" name="ep_update_i18n_title" value="<?php echo empty($ep_update_i18n) ? "" : $ep_update_i18n['ep_update_i18n_title']; ?>"/>
					</td>
				</tr>
				<tr>
					<td>Description</td>
					<td>
                        <textarea type="text" class="w-100pr h-150 validate[required,maxSize[500]]" name="ep_update_i18n_description"><?php echo empty($ep_update_i18n) ? "" : $ep_update_i18n['ep_update_i18n_description']; ?></textarea>
					</td>
				</tr>
				<tr>
					<td>Content</td>
					<td>
                        <textarea class="w-100pr h-150 validate[required] ep-update-i18n-text-block" name="ep_update_i18n_content"><?php echo empty($ep_update_i18n) ? "" : $ep_update_i18n['ep_update_i18n_content']; ?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script>
function modalFormCallBack(form, data_table){
	tinymce.triggerSave();
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>ep_updates/ajax_ep_updates_operations/<?php echo empty($ep_update_i18n) ? "save_ep_update_i18n" : "edit_ep_update_i18n" ?>/<?php echo $ep_update['id']?>',
		data: $(form).serialize(),
		beforeSend: function(){ showLoader($form); },
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );

			if(data.mess_type == 'success'){
				closeFancyBox();
				if(data_table != undefined)
					data_table.fnDraw();
			}else{
				hideLoader($form);
			}
		}
	});
}

$(document).ready(function(){
	tinymce.init({
		selector:'.ep-update-i18n-text-block',
		menubar: false,
		statusbar : false,
		height : 250,
		plugins: ["autolink lists link"],
		dialog_type : "modal",
        style_formats: [
            {title: 'H3', block: 'h3'},
            {title: 'H4', block: 'h4'},
            {title: 'H5', block: 'h5'},
            {title: 'H6', block: 'h6'},
        ],
        toolbar: "styleselect | bold italic underline link | numlist bullist",
		resize: false
	});
});
</script>
