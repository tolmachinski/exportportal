<form method="post" class="validateModal relative-b">
	<div class="wr-form-content w-700">
		<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr" >
			<tr>
				<td>Title</td>
				<td><input class="w-100pr validate[required,maxSize[150]]" type="text" name="title" value="<?php echo $link_storage['title']?>" /></td>
			</tr>
			<tr>
				<td class="w-120">Link</td>
				<td><input class="w-100pr validate[required,maxSize[255]]" type="text" name="link" value="<?php echo $link_storage['link']?>" /></td>
			</tr>
			<tr>
				<td>Description</td>
				<td>
					<textarea class="w-100pr h-150 validate[maxSize[500]]" name="description"><?php echo $link_storage['description']?></textarea>
				</td>
			</tr>
			<tr>
				<td>Country</td>
				<td>
					<select class="w-100pr" name="country">
						<option disabled="disabled" selected="selected">Select Country</option>
						<?php foreach($port_country as $conutry) { ?>
							<option value='<?php echo $conutry['id']?>' <?php if(isset($link_storage)) echo selected($conutry['id'], $link_storage['id_country']); ?>>
								<?php echo $conutry['country']?>
							</option>
						<?php } ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Paid</td>
				<td>
					<label><input class="validate[required]" type="radio" name="paid" <?php if(isset($link_storage)){ echo checked($link_storage['paid'], '1'); } ?> value="1"> Yes</label>
					<label><input class="validate[required]" type="radio" name="paid" <?php if(isset($link_storage)){ echo checked($link_storage['paid'], '0'); }else{ echo 'checked'; } ?> value="0"> No</label>
				</td>
			</tr>
			<tr>
				<td>Account info</td>
				<td>
					<textarea class="w-100pr h-100" name="account_info"><?php echo $account['account_info']?></textarea>
				</td>
			</tr>
		</table>
	</div>
	<div class="wr-form-btns clearfix">
		<?php if(isset($link_storage)){?>
			<input type="hidden" name="id_links_storage" value="<?php echo $link_storage['id_links_storage']?>" />
		<?php }?>
		<button class="pull-right btn btn-default" type="submit"><span class="ep-icon ep-icon_ok"></span> Save</button>
	</div>
</form>

<script type="text/javascript">
function modalFormCallBack(form, data_table){
	var $form = $(form);
	var $wrform = $form.closest('.wr-modal-b');
	var fdata = $form.serialize();

	<?php if(isset($link_storage)){?>
		var url = 'links_storage/ajax_links_storage_operation/update_link';
	<?php }else{?>
		var url = 'links_storage/ajax_links_storage_operation/create_link';
	<?php }?>

	$.ajax({
		type: 'POST',
		url: url,
		data: fdata,
		dataType: 'JSON',
		beforeSend: function(){
			showFormLoader($wrform, 'Save link...');
			$form.find('button[type=submit]').addClass('disabled');
		},
		success: function(resp){
			hideFormLoader($wrform);
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
