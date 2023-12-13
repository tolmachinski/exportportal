<form method="post" class="validateModal relative-b">
   <div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
			<tbody>
				<tr>
					<td class="w-100">Key</td>
					<td>
						<?php if(!isset($config)){?>
							<input type="text" name="key_config" class="validate[required]  w-100pr" value=""/>
						<?php }else{?>
							<?php echo $config['key_config']?>
						<?php }?>
					</td>
				</tr>
				<tr>
					<td class="w-100">Value</td>
					<td>
						<input type="text" name="value" class="validate[required] w-100pr" value="<?php echo (isset($config['value']) ? $config['value'] : '')?>"/>
					</td>
				</tr>
				<tr>
					<td class="w-100">Description</td>
					<td>
						<textarea name="description" class="validate[required]  w-100pr"><?php echo (isset($config['description']) ? $config['description'] : '')?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($config)){?> <input type="hidden" name="key_config" value="<?php echo $config['key_config']?>"/> <?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script>
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: '<?php echo __SITE_URL?>config/ajax_config_operation/<?php echo (isset($config) ? 'edit' : 'add')?>_config',
		data: $form.serialize(),
		beforeSend: function(){
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
</script>
