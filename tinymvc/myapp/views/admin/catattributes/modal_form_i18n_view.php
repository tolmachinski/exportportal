<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
		<table cellpadding="5" cellspacing="0" class="data table-striped temp w-100pr">
			<tr>
				<td class="vam">Language</td>
				<td>
                    <?php $translation_data = json_decode($attribute['translation_data'], true);?>
                    <?php if(empty($lang_attr)) { ?>
                        <select class="form-control" name="lang_attr">
                            <?php $translation_data['en'] = 1;?>
                            <?php foreach($tlanguages as $lang){?>
                                <option value="<?php echo $lang['lang_iso2'];?>" <?php if(array_key_exists($lang['lang_iso2'], $translation_data)){echo 'disabled';}?>><?php echo $lang['lang_name'];?></option>
                            <?php } ?>
                        </select>
                    <?php } else { ?>
                        <?php echo $translation_data[$lang_attr]["lang_name"];?>
                        <input type="hidden" name="lang_attr" value="<?php echo $lang_attr; ?>"/>
                    <?php } ?>
				</td>
			</tr>
			<tr>
				<td class="vam w-100">Attribute name</td>
				<td>
					<input type="text" name="attr_name" value="<?php echo (isset($lang_attr, $translation_data[$lang_attr]))?$translation_data[$lang_attr]["attr_name"]:$attribute['attr_name'];?>" class="validate[required,maxSize[255]]"/>
				</td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="attribute" value="<?php echo $attribute['id'];?>" />
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL;?>catattr/ajax_attr_operation/update_i18n_attr',
		data: $form.serialize(),
		beforeSend: function () {
			showLoader($form);
		},
		dataType: 'json',
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );
			hideLoader($form);

			if(data.mess_type == 'success'){
				if(data_table != undefined){
					data_table.fnDraw(false);
				}

				closeFancyBox();
			}
		}
	});
}
</script>
