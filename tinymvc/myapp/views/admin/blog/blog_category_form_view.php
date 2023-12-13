<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tbody>
				<tr>
					<td class="w-120">Name category</td>
					<td>
						<input class="w-100pr validate[required]" type="text" name="name" value="<?php echo $category['name'] ?: '';?>"/>
					</td>
				</tr>
				<tr>
					<td class="w-120">Special link</td>
					<td>
						<input class="w-100pr validate[maxSize[50]]" type="text" name="special_link" value="<?php echo $category['special_link'] ?: '';?>"/>
					</td>
				</tr>
                <tr>
					<td class="w-120">H1</td>
					<td>
						<input class="w-100pr validate[required,maxSize[255]]" type="text" name="page_header" value="<?php echo $category['h1'] ?: '';?>"/>
					</td>
				</tr>
                <tr>
					<td class="w-120">Subtitle</td>
					<td>
						<input class="w-100pr validate[required,maxSize[255]]" type="text" name="page_subtitle" value="<?php echo $category['subtitle'] ?: '';?>"/>
					</td>
				</tr>
                <tr>
					<td class="w-120">Meta title</td>
					<td>
						<input class="w-100pr validate[required,maxSize[400]]" type="text" name="meta_title" value="<?php echo $category['meta_title'] ?: '';?>"/>
					</td>
				</tr>
                <tr>
					<td class="w-120">Meta description</td>
					<td>
						<textarea class="w-100pr validate[required,maxSize[500]]" type="text" name="meta_description"><?php echo $category['meta_description'] ?: '';?></textarea>
					</td>
				</tr>
                <tr>
					<td class="w-120">Meta keywords</td>
					<td>
						<input class="w-100pr validate[maxSize[500]]" type="text" name="meta_keywords" value="<?php echo $category['meta_keywords'] ?: '';?>"/>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if (!empty($category)) {?>
			<input type="hidden" name="category" value="<?php echo $category['id_category']?>"/>
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script>
	function modalFormCallBack(form, data_table){
		var $form = $(form);
		var $wrform = $form.closest('.wr-modal-b');
		var fdata = $form.serialize();

		<?php if (isset($category)) {?>
			var url = '<?php echo __SITE_URL?>blogs/ajax_blogs_operation/edit_category_blog';
		<?php }else{?>
			var url = '<?php echo __SITE_URL?>blogs/ajax_blogs_operation/save_category_blog';
		<?php }?>

		$.ajax({
			type: 'POST',
			url: url,
			data: fdata,
			dataType: 'JSON',
			beforeSend: function(){
				showFormLoader($wrform);
				// $form.find('button[type=submit]').addClass('disabled');
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
