<div class="wr-modal-b">
    <div id="in_modal_content">
        <?php tmvc::instance()->controller->view->display('admin/order/manager_assigned/bills_list_view'); ?>
    </div>
	<form class="relative-b validateModal" id="in_modal_form" style="display:none;">
		<div class="wr-form-content updateValidationErrorsPosition">
			<table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr mt-15 vam-table">
				<tr>
					<td class="w-100">
						Reason
					</td>
					<td>
			            <textarea class="validate[required] h-150 w-100pr" name="reason" id="change_status_reason" placeholder="Reason"><?php echo $order_info['tracking_info'];?></textarea>
					</td>
				</tr>
			</table>
		</div>
		<div class="wr-form-btns clearfix">
			<a href="#" class="btn btn-primary pull-right ml-5 cancel_in_modal_form"><i class="ep-icon ep-icon_reply lh-20"></i> Return</a>
			<a href="#" class="btn btn-success pull-right confirm-dialog" id="form_submit_btn" ><i class="ep-icon ep-icon_ok lh-20"></i> Save</a>
		</div>
	</form>
</div>
<script>

    var reimburse_user = function(opener){
        var $this = $(opener);
        var bill = $this.data('bill');
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL;?>external_bills/ajax_external_bills_operation/add_request/reimburse',
            data: { bill : bill },
            dataType: 'JSON',
            success: function(resp){
                systemMessages( resp.message, 'message-' + resp.mess_type );

                if(resp.mess_type == 'success'){
                    $this.replaceWith('<a class="btn btn-default btn-xs pull-right mt-5 mr-5 fancybox.ajax fancyboxValidateModal" href="<?php echo __SITE_URL;?>external_bills/popup_forms/notice/'+resp.request+'" data-title="Notes"><i class="ep-icon ep-icon_notice"></i> Reimburse buyer detail</a>');
                }
            }
        });
    }
    var confirm_bill = function(opener){
        $change_status_btn = $(opener);
        var bill = $change_status_btn.data('bill');
        changeBillStatus(bill, 'confirm','');
    }

    function changeBillStatus(bill, status, reason){
        var $thisBtn = $change_status_btn;
        var loader_element = '.wr-modal-b';
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL;?>billing/ajax_bill_operations/'+status+'_bill',
            data: { bill : bill, reason:reason},
            dataType: 'json',
            beforeSend: function(){
                showLoader(loader_element);
            },
            success: function(data){
                if(data.mess_type == 'success'){
                    $('#in_modal_content').html(data.bills_list);

                    if(status == 'cancel'){
                        $change_status_btn = null;
                        $('#in_modal_content').show();
                        $('#in_modal_form').hide();
                        $('#in_modal_form')[0].reset();
                        $.fancybox.reposition();
                    }
					dtMyOrders.fnDraw();
                    hideLoader(loader_element);
                }
                systemMessages( data.message, 'message-' + data.mess_type );
            }
        });
    }
</script>
