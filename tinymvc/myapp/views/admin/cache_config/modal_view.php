<form method="post" class="validateModal relative-b cache_config_form <?php echo (isset($cache_config)) ? 'edit-config' : 'add-config'?>">
	<div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tbody>
				<tr>
					<td class="w-120">Key</td>
					<td>
						<input class="w-100pr validate[required,maxSize[50],custom[variableName]]" type="text" name="cache_key" value="<?php if(isset($cache_config['cache_key'])) echo $cache_config['cache_key']; ?>" placeholder="Enter key"/>
					</td>
				</tr>
				<tr>
					<td>Folder</td>
					<td>
						<input class="w-100pr validate[required,custom[variableName]]" type="text" name="folder" value="<?php if(isset($cache_config['folder'])) echo $cache_config['folder'];?>" placeholder="Enter folder"/>
					</td>
				</tr>
				<tr>
					<td>Time</td>
					<td>
						<input class="w-100pr validate[required]" type="text" name="cache_time" value="<?php if(isset($cache_config['cache_time'])) echo $cache_config['cache_time'];?>" />
					</td>
				</tr>
				<tr>
					<td>Description</td>
					<td>
						<textarea class="w-100pr h-120 validate[required,maxSize[250]]" type="text" name="description" placeholder="Enter description" ><?php if(isset($cache_config['description'])) echo $cache_config['description'];?></textarea>
					</td>
				</tr>
				<tr>
					<td>Enable</td>
					<td>
						<input type="radio" value="0" <?php echo checked($cache_config['enable'], 0)?> name="enable"/>Disable
						<input type="radio" value="1"<?php echo checked($cache_config['enable'], 1)?> name="enable"/>Enable
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($cache_config)){?>
				<input type="hidden" name="id_config" value="<?php echo $cache_config['id_config']?>">
				<input type="hidden" name="old_folder" value="<?php echo $cache_config['folder']?>">
			<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript">
	function modalFormCallBack(form, data_table){
		var $form = $(form);
		$.ajax({
            type: 'POST',
            url:"<?php echo __SITE_URL; ?>cache_config/ajax_operation/" + ($form.hasClass('edit-config') ? 'edit' : 'insert'),
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
