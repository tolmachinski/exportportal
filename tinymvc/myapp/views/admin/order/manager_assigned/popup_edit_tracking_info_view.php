<div class="wr-modal-b">
	<form class="relative-b validateModal" data-callback="edit_tracking_info">
		<div class="wr-form-content updateValidationErrorsPosition w-500">
			<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr mt-15 vam-table">
				<tr>
					<td class="w-100">
						Tracking info
					</td>
					<td>
			            <textarea class="validate[required] h-70 w-100pr" name="track_info" placeholder="Tracking info"><?php echo $order_info['tracking_info'];?></textarea>
					</td>
				</tr>
			</table>
		</div>
		<div class="wr-form-btns clearfix">
			<button class="btn btn-primary pull-right" type="submit"><i class="ep-icon ep-icon_ok"></i> Save</button>
			<input type="hidden" name="order" value="<?php echo $order_info['id'];?>"/>
		</div>
	</form>
</div>
<script>
	var edit_tracking_info = function(form){
		var $form = $(form);
		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL;?>order/ajax_order_operations/edit_tracking_info',
			data: $form.serialize(),
			beforeSend: function(){ showLoader('.wr-modal-b'); },
			dataType: 'json',
			success: function(resp){
				if (resp.mess_type == 'success') {
                    $(globalThis).trigger('order:success-edit-tracking-info');

                    try {
                        dt_redraw_callback();
                    } catch (error) {
                        // If the function was undefined
                    }

					closeFancyBox();
				}
				hideLoader('.wr-modal-b');
				systemMessages( resp.message, 'message-' + resp.mess_type );
			}
		});
	}
</script>
