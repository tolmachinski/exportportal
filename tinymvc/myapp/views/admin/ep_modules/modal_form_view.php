<form method="post" class="validateModal relative-b">
   <div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tbody>
				<tr>
					<td>Name module</td>
					<td>
						<input type="text" name="name" class="validate[required,maxSize[255]] w-100pr" value="<?php echo ((isset($module['name_module']) ? $module['name_module'] : ''))?>" />
					</td>
				</tr>
				<tr>
					<td>Title module</td>
					<td>
						<input type="text" name="title" class="validate[required,maxSize[255]] w-100pr" value="<?php echo ((isset($module['title_module']) ? $module['title_module'] : ''))?>" />
					</td>
				</tr>
				</tr>
				<tr>
					<td>Group module</td>
					<td>
						<input type="text" name="group" class="validate[required,maxSize[1]]" value="<?php echo ((isset($module['group_module']) ? $module['group_module'] : ''))?>" />
					</td>
				</tr>
				<tr>
					<td>Description module</td>
					<td>
						<textarea name="text" class="w-100pr h-150 validate[required] module-text-block"><?php echo ((isset($module['description_module']) ? $module['description_module'] : ''))?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($module['id_module'])){?><input type="hidden" name="module" value="<?php echo $module['id_module'];?>"/><?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript" src="<?php echo __SITE_URL; ?>public/plug_admin/tinymce-4-3-10/tinymce.min.js"></script>
<script>
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL ?>ep_modules/ajax_ep_modules_operations/<?php echo ((isset($module) ? "edit" : "add"))?>_ep_module',
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
					data_table.fnDraw(false);
			}else{
				hideLoader($form);
			}
		}
	});
}
	$(function(){
		tinymce.init({
			selector:'.module-text-block',
			menubar: false,
			statusbar : false,
			height : 250,
			plugins: ["autolink lists link textcolor"],
			dialog_type : "modal",
			toolbar: "bold italic underline forecolor backcolor link | numlist bullist",
			resize: false
		});
	});
</script>
