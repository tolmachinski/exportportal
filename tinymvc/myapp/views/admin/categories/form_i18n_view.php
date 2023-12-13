<form method="post" class="validateModal relative-b">
   <div class="wr-form-content w-850">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tbody>
				<tr >
					<td class="w-20pr">Breadcrumbs:</td>
					<td class="w-40pr">
						<?php
							if(!empty($category['breadcrumbs'])){
								foreach ($category['breadcrumbs'] as $bread){
									foreach ($bread as $cat_id => $cat_title){
										$out[] = $cat_title;
									}
								}
								echo implode('<span class="crumbs-delimiter fs-16 pr-5 pl-5">&raquo;</span>', $out);
							} elseif(!empty($category_i18n['breadcrumbs'])){
								foreach ($category_i18n['breadcrumbs'] as $bread){
									foreach ($bread as $cat_id => $cat_title){
										$out[] = $cat_title;
									}
								}
								echo implode('<span class="crumbs-delimiter fs-16 pr-5 pl-5">&raquo;</span>', $out);
							} else{
								echo '&mdash;';
							}
						?>
					</td>
                    <td>
                    </td>
				</tr>
				<tr>
					<td>Title:</td>
					<td><input type="text" name="name" value="<?php echo (isset($category_i18n))?$category_i18n['name']:$category['name'];?>" class="w-100pr validate[required,maxSize[255]]" /></td>
                    <td><?php echo $category['name'];?></td>
				</tr>
				<tr>
					<td>Language</td>
					<td>
						<?php if(empty($category_i18n)){?>
							<select class="form-control" name="lang_category">
								<?php foreach($tlanguages as $lang){?>
									<option value="<?php echo $lang['lang_iso2'];?>" <?php if(array_key_exists($lang['lang_iso2'], $category['translations_data'])){echo 'disabled';}?>><?php echo $lang['lang_name'];?></option>
								<?php } ?>
							</select>
						<?php } else{?>
							<?php echo $lang_category['lang_name'];?>
						<?php }?>
					</td>
                    <td></td>
				</tr>
				<tr>
					<td>Meta title:</td>
					<td><textarea name="title" class="w-100pr h-100 validate[maxSize[255]]" ><?php echo (isset($category_i18n))?$category_i18n['title']:$category['title']?></textarea></td>
                    <td> <?php echo $category['title'] ?></td>
				</tr>
				<tr>
					<td>H1:</td>
					<td><input type="text" name="h1" class="w-100pr validate[required,maxSize[50]]" value="<?php echo (isset($category_i18n))?$category_i18n['h1']:$category['h1']?>"/></td>
                    <td><?php echo $category["h1"] ?></td>
				</tr>
				<tr>
					<td>Meta description:</td>
					<td>
						<textarea name="description" class="w-100pr h-100" ><?php echo (isset($category_i18n))?$category_i18n['description']:$category['description']?></textarea>
					</td>
                    <td><?php echo $category['description']?></td>
				</tr>
				<tr>
					<td>Meta keywords:</td>
					<td>
						<textarea name="keywords" class="w-100pr h-100" ><?php echo (isset($category_i18n))?$category_i18n['keywords']:$category['keywords']?></textarea>
					</td>
                    <td><?php echo $category['keywords']?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($category_i18n)){?>
			<input type="hidden" name="category_id_i18n" value="<?php echo $category_i18n['category_id_i18n'];?>" />
		<?php } else{?>
			<input type="hidden" name="category_id" value="<?php echo $category['category_id'];?>" />
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL;?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script>
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL ?>categories/ajax_category_operation/<?php echo ((isset($category_i18n) ? "update" : "add"))?>_category_i18n',
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
				if(data_table != undefined)
					data_table.fnDraw(false);
			}else{

			}
		}
	});
}
</script>
