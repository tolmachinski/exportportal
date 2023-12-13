<form class="validateModal relative-b">
    <div class="wr-form-content updateValidationErrorsPosition w-500">
        <table class="data table-striped w-100pr mt-15 vam-table" cellpadding=0 cellspacing=0>
            <tr>
                <td class="w-70">
                    <label>Date</label>
                </td>
                <td>
                    <?php echo formatDate($request_info['date_create']);?>
                </td>
            </tr>
            <tr>
                <td>
                    <label>Extend for N day(s) <span class="fs-10">(Min 1 day, max 90 days)</span>:</label>
                </td>
                <td>
                    <input type="text" class="validate[required,custom[positive_integer],min[1],max[90]] w-100pr" name="extend_days" placeholder="Number of days to extend" value="<?php echo $request_info['days'];?>">
                </td>
            </tr>
            <tr>
                <td>
                    <label>Reason</label>
                </td>
                <td>
                    <?php echo $request_info['extend_reason'];?>
                </td>
            </tr>
            <tr>
                <td>
                    <label>Comment</label>
                </td>
                <td>
                    <textarea class="h-100 w-100pr textcounter_extend-request" data-max="500" name="extend_comment" placeholder="Comment"></textarea>
                </td>
            </tr>
        </table>
    </div>
    <div class="wr-form-btns clearfix">
        <a class="btn btn-danger pull-right confirm-dialog" data-message="Are you sure you want to decline this extend request?" href="#" data-callback="extend_action" data-action="decline"><i class="ep-icon ep-icon_remove lh-20"></i> Decline</a>
        <a class="btn btn-success pull-right mr-15 confirm-dialog" data-message="Are you sure you want to confirm this extend request?" href="#" data-callback="extend_action" data-action="confirm"><i class="ep-icon ep-icon_ok lh-20"></i> Confirm</a>
        <input type="hidden" name="id_extend" value="<?php echo $request_info['id_extend'];?>"/>
    </div>
</form>
<script>
	$(function(){
		$('.textcounter_extend-request').textcounter({
			countDown: true,
			countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
			countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
		});
	});
	var extend_action = function(opener){
        var $this = $(opener);
        var $form = $this.closest('form');
        var action = $this.data('action');
        <?php if ('bill' === $request_info['item_type']) {?>
            const triggeredAction = 'confirm' === action ? 'billing:success-confirm-extend-request' : 'billing:success-decline-extend-request';
        <?php } elseif ('order' === $request_info['item_type']) {?>
            const triggeredAction = 'confirm' === action ? 'order:success-confirm-extend-request' : 'order:success-decline-extend-request';
        <?php }?>

        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL;?>extend/ajax_operation/'+action,
            data: $form.serialize(),
            dataType: 'JSON',
            success: function(resp){
                systemMessages( resp.message, 'message-' + resp.mess_type );

                if(resp.mess_type == 'success'){
                    $(globalThis).trigger(triggeredAction);

                    try {
                        extend_action_callback();
                    } catch (error) {
                        // If the function was undefined
                    }

                    closeFancyBox();
                }
            }
        });
    }
</script>
