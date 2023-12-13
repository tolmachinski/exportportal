<form method="post" class="validateModal relative-b">
   <div class="wr-form-content w-700">
    <table cellpadding="5" cellspacing="0" class="data table-striped temp w-100pr">
		<tr>
			<td class="vam w-100">Language</td>
			<td>
				<?php $translation_data = json_decode($value['translation_data'], true);?>
				<?php if(empty($lang_value)) { ?>
					<select class="form-control" name="lang_value">
						<?php $translation_data['en'] = 1;?>
						<?php foreach($tlanguages as $lang){?>
							<option value="<?php echo $lang['lang_iso2'];?>" <?php if(array_key_exists($lang['lang_iso2'], $translation_data)){echo 'disabled';}?>><?php echo $lang['lang_name'];?></option>
						<?php } ?>
					</select>
				<?php } else { ?>
					<?php echo $translation_data[$lang_value]["lang_name"];?>
					<input type="hidden" name="lang_value" value="<?php echo $lang_value; ?>"/>
				<?php } ?>
			</td>
		</tr>
        <tr>
            <td class="vam">Value</td>
            <td><input type="text" name="value" class="validate[required]" value="<?php echo (isset($lang_value, $translation_data[$lang_value]))?$translation_data[$lang_value]["value"]:$value['value'];?>"/></td>
        </tr>
    </table>
	</div>	
	<div class="wr-form-btns clearfix">
		<input type="hidden" name="id" value="<?php echo $value['id'];?>" />
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL;?>catattr/ajax_attr_operation/update_value_i18n',
		data: $form.serialize(),
		dataType: 'json',
		beforeSend: function () {
			showLoader($form);
		},
		success: function(data){
			systemMessages( data.message, 'message-' + data.mess_type );
			hideLoader($form);

			if(data.mess_type == 'success'){
				dtCategoryAttribute.fnDraw(false);
				setTimeout(function(){
					$('.call-function[data-callback="get_attr_values"][data-attr="<?php echo $value['attribute'];?>"]').click();
				}, 100);
				closeFancyBox();
			}
		}
	});
}
</script>
