<form method="post" class="validateModal relative-b">
   <div class="wr-form-content w-700 h-440">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tbody>
				<tr>
					<td class="w-100">Visible</td>
					<td>
						<input type="checkbox" name="visible" <?php echo checked(1, $ep_updates['visible'])?>/>
					</td>
				</tr>
				<tr>
					<td>Title</td>
					<td>
						<input type="text" name="title" class="validate[required] w-100pr" value="<?php echo ((isset($ep_updates['title']) ? $ep_updates['title'] : ''))?>" />
					</td>
				</tr>
				<tr>
					<td>Content</td>
					<td>
						<textarea class="w-100pr h-50 validate[required] tinymce" name="content"><?php echo ((isset($ep_updates['content']) ? $ep_updates['content'] : ''))?></textarea>
					</td>
				</tr>
				<tr>
					<td>Description</td>
					<td>
						<textarea class="w-100pr h-150 validate[required] blog-text-block" name="description" ><?php echo ((isset($ep_updates['description']) ? $ep_updates['description'] : ''))?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($ep_updates['id'])){?><input type="hidden" name="id" value="<?php echo $ep_updates['id'];?>"/><?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script>
$(document).ready(function(){

    tinymce.init({
        selector:'.tinymce',
        menubar: false,
        statusbar : false,
        height : 250,
        plugins: ["autolink lists link powerpaste"],
        dialog_type : "modal",
        style_formats: [
            {title: 'H3', block: 'h3'},
            {title: 'H4', block: 'h4'},
            {title: 'H5', block: 'h5'},
            {title: 'H6', block: 'h6'},
        ],
        toolbar: "styleselect | bold italic underline link | numlist bullist",
		resize: false,
		powerpaste_word_import: "clean", // optional
		powerpaste_html_import: "clean", // optional
		powerpaste_clean_filtered_inline_elements: "b,em,ul,ol,a,strong,h3,h4,h5,h6"
    });
});
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL ?>ep_updates/ajax_ep_updates_operations/<?php echo ((isset($ep_updates) ? "edit" : "add"))?>_ep_update',
		data: $form.serialize(),
		beforeSend: function () {
			showLoader($form);
		},
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
</script>
