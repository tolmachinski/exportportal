<div class="wr-modal-b">
	<form class="relative-b validateModal" data-callback="edit_reason">
		<div class="wr-form-content updateValidationErrorsPosition w-500">
			<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr mt-15 vam-table">
				<tr>
					<td class="w-100">
						Reason
					</td>
					<td>
			            <input type="text" name="reason" class="validate[required] w-100pr" placeholder="Reason" value="<?php echo $reason['reason'];?>">
					</td>
				</tr>
                <tr>
					<td class="w-100">
						Message
					</td>
					<td>
			            <textarea name="message" class="validate[maxSize[300]] w-100pr"><?php echo $reason['message'];?></textarea>
					</td>
				</tr>
				<tr>
					<td class="w-100">
						Order statuses
					</td>
					<td>
			            <?php foreach($orders_status as $status){?>
			            <label class="w-50pr pull-left lh-14">
                            <input class="mt-0" type="checkbox" <?php if(in_array($status['id'], $relations)){ echo 'checked="checked"';}?> value="<?php echo $status['id'];?>" name="statuses[]">
			                <span><?php echo $status['status'];?></span>
			            </label>
                        <?php }?>
					</td>
				</tr>
			</table>
		</div>
		<div class="wr-form-btns clearfix">
			<button class="btn btn-primary pull-right" type="submit"><i class="ep-icon ep-icon_ok lh-20"></i> Save</button>
            <input type="hidden" name="id_reason" value="<?php echo $reason['id'];?>">
		</div>
	</form>
</div>
<script>
	var edit_reason = function(form){
		var $form = $(form);
		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL;?>order/ajax_order_operations/edit_reason',
			data: $form.serialize(),
			beforeSend: function(){ showLoader('.wr-modal-b'); },
			dataType: 'json',
			success: function(resp){
				if(resp.mess_type == 'success'){
					dt_redraw_callback();
					closeFancyBox();
				}
				hideLoader('.wr-modal-b');
				systemMessages( resp.message, 'message-' + resp.mess_type );
			}
		});
	}
</script>
