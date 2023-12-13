<form method="post" class="validateModal relative-b">
   <div class="wr-form-content w-850">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tbody>
				<tr>
					<td class="w-100">Title block</td>
					<td>
						<input type="text" name="title_block" class="w-100pr validate[required,maxSize[500]]" value="<?php echo (isset($block_info)) ? $block_info['title_block'] : ''?>" />
					</td>
				</tr>
				<tr>
					<td>Short name</td>
					<td>
						<input type="text" name="short_name" class="w-100pr validate[required,maxSize[50]]" value="<?php echo (isset($block_info)) ? $block_info['short_name'] : ''?>" />
				 	<br/> sample: short_name
					</td>
				</tr>
				<tr>
					<td>Description block</td>
					<td>
						<textarea name="description_block" class="w-100pr h-50 validate[required,maxSize[255]]"><?php echo (isset($block_info)) ? $block_info['description_block'] : ''?></textarea>
					</td>
				</tr>
				<tr>
					<td>Text block</td>
					<td>
						<textarea id="text_block" name="text_block" class="w-100pr h-100 validate[required]"><?php echo (isset($block_info)) ? $block_info['text_block'] : ''?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($block_info)){?>
			<input type="hidden" name="id_block" value="<?php echo $block_info['id_block']?>" />
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
		url: '<?php echo __SITE_URL ?>text_block/ajax_text_block_operation/<?php echo ((isset($block_info) ? "edit" : "create"))?>_text_block',
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

tinymce.init({
	schema: 'html5',
	selector:'#text_block',
	menubar: false,
	statusbar : false,
	height : 250,
	plugins: ["autolink lists link textcolor media preview code"],
	dialog_type : "modal",
	toolbar: "code | formatselect | fontsizeselect | bold italic underline forecolor backcolor link | numlist bullist | alignleft aligncenter alignright alignjustify",
	fontsize_formats: '12px 14px 16px 18px 20px 22px 24px',
	resize: false
});
</script>
