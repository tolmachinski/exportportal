<div class="wr-modal-b">
    <div id="in_modal_content">
        <?php views()->display('admin/payments/bills_list_view');?>
    </div>
</div>
<script>

    var reimburse_user = function(opener){
        var $this = $(opener);
        var bill = $this.data('bill');
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL . 'external_bills/ajax_external_bills_operation/add_request/reimburse';?>',
            data: { bill : bill },
            dataType: 'JSON',
            success: function(resp){
                systemMessages( resp.message, 'message-' + resp.mess_type );

                if (resp.mess_type == 'success') {
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
                if (data.mess_type == 'success') {
                    $('#in_modal_content').html(data.bills_list);
					dtMyOrders.fnDraw(false);
                    hideLoader(loader_element);
                }

                systemMessages( data.message, 'message-' + data.mess_type );
            }
        });
    }
</script>
