<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
    <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr vam-table">
        <tbody>
			<tr>
				<td class="w-120">Domain Name</td>
				<td>
					<input class="w-100pr validate[required,custom[url]]" type="text" name="domain" value="<?php echo (isset($api_key['domain']) ? $api_key['domain'] : '')?>" placeholder="enter domain name"/>
				</td>
			</tr>
			<?php if(isset($api_key['api_key'])){?>
			<tr>
				<td>Api key</td>
				<td><?php echo $api_key['api_key']?></td>
			</tr>
			<?php }?>
			<?php if(isset($api_key['registered'])){?>
			<tr>
				<td>Registered</td>
				<td><?php echo $api_key['registered']?></td>
			</tr>
			<?php }?>
			<tr>
				<td>Title</td>
				<td>
					<input class="w-100pr validate[required]" type="text" name="title_client" value="<?php echo (isset($api_key['title_client']) ? $api_key['title_client'] : '')?>" placeholder="enter title" />
				</td>
			</tr>
			<tr>
				<td>Description</td>
				<td>
					<textarea class="w-100pr h-120 validate[required]" type="text" name="description" placeholder="Enter description"><?php echo (isset($api_key['description_client']) ? $api_key['description_client'] : '')?></textarea>
				</td>
			</tr>
			<?php if(isset($api_key['moderated'])){?>
            <tr>
				<td>Moderated state</td>
				<td>
				    <select name="moderated" class="w-100pr">
                        <option value="0" <?php echo ($api_key['moderated'] == 0 ? 'selected="selected"' : '')?>>Unmoderated</option>
                        <option value="1" <?php echo ($api_key['moderated'] == 1 ? 'selected="selected"' : '')?>>Moderated</option>
                    </select>
				</td>
			</tr>
			<?php }?>
			<?php if(isset($api_key['enable'])){?>
            <tr>
				<td>State</td>
				<td>
                    <select name="enable" class="w-100pr">
                        <option value="0" <?php echo ($api_key['enable'] == 0 ? 'selected="selected"' : '')?>>Disable</option>
                        <option value="1" <?php echo ($api_key['enable'] == 1 ? 'selected="selected"' : '')?>>Enable</option>
                    </select>
				</td>
			</tr>
			<?php }?>
		</tbody>
	</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($api_key['id_key'])){?>
			<input type="hidden" name="id_key" value="<?php echo $api_key['id_key']?>">
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>
<script type="text/javascript">
function modalFormCallBack(form, data_table){
	var $form = $(form);
	$.ajax({
		type: 'POST',
		url: "api_keys/ajax_api_keys_operation/" + ($form.hasClass('edit-api') ? 'edit' : 'insert'),
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
