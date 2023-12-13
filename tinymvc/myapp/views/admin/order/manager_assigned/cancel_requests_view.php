<div class="wr-form-content w-700 h-100pr">
    <table cellspacing="0" cellpadding="0" class="data table-striped table-bordered w-100pr mt-15 mb-15 vam-table">
        <thead>
            <tr role="row">
                <th class="w-80">Date</th>
                <th class="w-100">Member</th>
                <th class="mnw-100">Message</th>
                <th class="mnw-60 tac"></th>
            </tr>
        </thead>
        <tbody class="tabMessage">
            <?php foreach($cancel_requests as $cancel_request){?>
                <tr>
                    <td class="tac"><?php echo formatDate($cancel_request['create_date'], 'm/d/Y H:i:s A');?></td>
                    <td class="tac"><?php echo ucfirst($cancel_request['user_type']);?></td>
                    <td><?php echo $cancel_request['message'];?></td>
                    <td class="tac">
                        <?php if ($cancel_request['status'] == 'init') {?>
                            <?php if (is_my($order_info['ep_manager'])) {?>
                                <a class="ep-icon ep-icon_ok-circle txt-green confirm-dialog" data-message="Are you sure you want to confirm this request?" data-callback="change_cancel_request_status" data-request="<?php echo $cancel_request['id_request'];?>" data-order="<?php echo $cancel_request['id_order'];?>" data-status="accepted" href="#"></a>
                                <a class="ep-icon ep-icon_remove-circle txt-red confirm-dialog" data-message="Are you sure you want to decline this request?" data-callback="change_cancel_request_status" data-request="<?php echo $cancel_request['id_request'];?>" data-order="<?php echo $cancel_request['id_order'];?>" data-status="declined" href="#"></a>
                            <?php } else {?>
                                <span class="ep-icon ep-icon_ok-circle txt-green cur-pointer call-systmess" data-message="Only assigned Order Manager can perform this action." data-type="info"></span>
                                <span class="ep-icon ep-icon_remove-circle txt-red cur-pointer call-systmess" data-message="Only assigned Order Manager can perform this action." data-type="info"></span>
                            <?php }?>
                        <?php } elseif ($cancel_request['status'] == 'accepted') {?>
                            <span class="ep-icon ep-icon_ok"></span>
                        <?php } else {?>
                            <span class="ep-icon ep-icon_remove"></span>
                        <?php }?>
                    </td>
                </tr>
            <?php }?>
        </tbody>
    </table>
</div>

<?php if (is_my($order_info['ep_manager'])) {?>
    <script type="text/javascript">
        var change_cancel_request_status = function(opener){
            var $this = $(opener);
            var request = $this.data('request');
            var order = $this.data('order');
            var status = $this.data('status');
            $.ajax({
                type: 'POST',
                url: '<?php echo __SITE_URL;?>order/ajax_order_operations/change_cancel_request_status',
                data: { request : request, order : order, status : status},
                dataType: 'JSON',
                success: function(resp){
                    if(resp.mess_type == 'success'){
                        if(status == 'accepted')
                            $this.closest('td').html('<span class="ep-icon ep-icon_ok"></span>');
                        else
                            $this.closest('td').html('<span class="ep-icon ep-icon_remove"></span>');

                            try {
                                dt_redraw_callback();
                            } catch (err) {
                                <?php // this view is called also from the places which don't require DataTable redraw ?>
                            }
                    }
                    systemMessages( resp.message, 'message-' + resp.mess_type );
                }
            });
        }
    </script>
<?php }?>
