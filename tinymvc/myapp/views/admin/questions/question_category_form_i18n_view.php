<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr">
			<tr>
				<td>Language</td>
				<td>
					<?php if(empty($category_i18n)){?>
						<?php $translations_data = json_decode($category['translations_data'], true);?>
						<select class="form-control" name="lang_category">
							<?php foreach($tlanguages as $lang){?>
								<option value="<?php echo $lang['lang_iso2'];?>" <?php if(array_key_exists($lang['lang_iso2'], $translations_data)){echo 'disabled';}?>><?php echo $lang['lang_name'];?></option>
							<?php } ?>
						</select>
					<?php } else{?>
						<?php echo $lang_block['lang_name'];?>
					<?php }?>
				</td>
			</tr>
			<tr>
				<td class="w-120">Title</td>
				<td><input class="w-100pr" type="text" name="title_cat" value="<?php echo (!empty($category_i18n)) ? $category_i18n['title_cat'] : $category['title_cat'];?>"></td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($category_i18n)){?>
			<input type="hidden" name="id_category_i18n" value="<?php echo $category_i18n['id_category_i18n'];?>" />
		<?php } else{?>
			<input type="hidden" name="id_category" value="<?php echo $category['idcat'];?>" />
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
	function modalFormCallBack(form, data_table){
		var $form = $(form);
		var $wrform = $form.closest('.wr-modal-b');
		var fdata = $form.serialize();

		<?php if(!empty($category_i18n)){?>
			var url = '<?php echo __SITE_URL?>blogs/ajax_blogs_operation/edit_category_i18n';
		<?php }else{?>
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
				} else{
					$form.find('button[type=submit]').removeClass('disabled');
				}
			}
		});
	}
function modalFormCallBack(form, data_table){
	var $form = $(form);
	var fdata = $form.serialize();
	var $wrform = $form.closest('.wr-modal-b');

	<?php if(isset($category_i18n)){?>
		var url = "<?php echo __SITE_URL;?>community_questions/ajax_question_categories_operation/edit_category_i18n";
	<?php }else{?>
		var url = "<?php echo __SITE_URL;?>community_questions/ajax_question_categories_operation/create_category_i18n";
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
		success: function(resp){
			systemMessages( resp.message, 'message-' + resp.mess_type );

			if(resp.mess_type == 'success'){
				closeFancyBox();
				if(data_table != undefined)
					data_table.fnDraw();
			}else{
				$form.find('button[type=submit]').removeClass('disabled');
			}
		}
	});
}
</script>
