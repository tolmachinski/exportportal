<div class="wr-modal-b">
	<form class="validateModal" data-callback="extend_item_time">
        <div class="wr-form-content updateValidationErrorsPosition w-500">
            <table class="data table-striped w-100pr mt-15 vam-table" cellpadding=0 cellspacing=0>
				<tr>
					<td class="w-120">
						Extend for N day(s) <span class="fs-10">(Min 1 day, max 90 days)</span>:
					</td>
					<td>
                        <input type="text" class="validate[required,custom[positive_integer],min[1],max[90]] w-100pr" name="extend_days" placeholder="Number of days to extend">
					</td>
				</tr>
				<tr>
					<td>
						Write the reason:
					</td>
					<td>
			            <textarea class="validate[required] h-100 w-100pr textcounter_extend-request" data-max="500" name="extend_reason" placeholder="The extend reason"></textarea>
					</td>
				</tr>
			</table>
		</div>
		<div class="wr-form-btns clearfix">
			<input type="hidden" name="id_extend_item" value="<?php echo $id_extend_item;?>"/>
			<input type="hidden" name="extend_type" value="<?php echo $extend_type;?>"/>
            <a class="btn btn-success pull-right confirm-dialog" data-message="Are you sure you want to extend the status time?" href="#" data-callback="extend_item_time"><i class="ep-icon ep-icon_ok lh-20"></i> Submit</a>
		</div>
	</form>
</div>
<script>
	$(function(){
		$('.textcounter_extend-request').textcounter({
			countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
		});
	});
	var extend_item_time = function(opener){
        var $this = $(opener);
        var $form = $this.closest('form');
		$.ajax({
			type: 'POST',
			url: '<?php echo __SITE_URL;?>extend/ajax_operation/extend_item',
			data: $form.serialize(),
			beforeSend: function(){ showLoader('.wr-modal-b'); },
			dataType: 'json',
			success: function(resp){
                if(resp.mess_type == 'success'){
                    <?php if ('bill' === $extend_type) {?>
                        $(globalThis).trigger('billing:success-extend-payment-time');
                    <?php } elseif ('order' === $extend_type) {?>
                        $(globalThis).trigger('order:success-extend-payment-time');
                    <?php }?>

                    try {
                        extend_action_callback();
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
