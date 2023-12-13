<?php
$finished_statuses = array(
    'order_completed',
    'late_payment',
    'canceled_by_buyer',
    'canceled_by_seller',
    'canceled_by_ep'
);
?>

<div class="wr-form-content w-700">
    <ul class="bills-list-b">
        <?php $total_refund = 0;?>
        <?php $balance_paid = 0;?>
        <?php $amount_confirmed = 0;?>
        <?php foreach($bills as $item){?>
        <?php
            $balance_paid += $item['amount'];
            if($item['status'] == 'confirmed'){
                $amount_confirmed += $item['amount'];
            }
        ?>
        <li id="bill-<?php echo $item['id_bill'];?>-block" class="relative-b">
            <div class="clearfix">
            <?php if($item['name_type'] == 'order'){?>
                <p class="type-b">Order bill</p>
            <?php }else{?>
                <p class="type-b">Shipping bill</p>
            <?php }?>
                <p class="status-b <?php echo $status[$item['status']]['class']?>">
                    <i class="ep-icon ep-icon_<?php echo $status[$item['status']]['icon']?>"></i>
                    <?php echo $status[$item['status']]['description'];?>
                </p>
                <p class="date-b">
                    <?php echo formatDate($item['create_date'], 'm/d/Y');?>
                </p>
            </div>

            <div class="row">
                <div class="col-xs-3 pr-0 mt-5">
                    <strong class="total-amount-b txt-blue lh-30">
                        Amount: $ <?php echo get_price($item['balance'], false);?>
                    </strong>
                </div>
                <div class="col-xs-3 pr-0 mt-5 text-left">
                    <strong class="txt-orange lh-30">
                        <span class="lh-30">Paid:</span>
                        <?php if($item['status'] == 'paid'){?>
                        <span class="total_paid_amount lh-30">$ <?php echo get_price($item['amount'], false);?></span>
                        <div class="amount-edit-<?php echo $item['id_bill'];?> pull-right" style="display: none;">
                            <input class="w-110" type="text" name="amount" value="<?php echo $item['amount'];?>">
                            <a class="btn-confirm-amount confirm-dialog" data-callback="confirm_new_amount" title="Confirm new amount" data-message="Are you sure you want to change this payment amount?" data-bill="<?php echo $item['id_bill'];?>">
                                <i class="ep-icon ep-icon_ok txt-green lh-30 fs-16"></i>
                            </a>
                            <a class=" call-function" data-callback="cancel_amount" title="Cancel edit" data-bill="<?php echo $item['id_bill'];?>">
                                <i class="ep-icon ep-icon_remove txt-red lh-30 fs-16"></i>
                            </a>
                        </div>
                        <span class="cur-pointer btn-edit-amount call-function" data-callback="edit_amount" data-bill="<?php echo $item['id_bill'];?>">
                            <i class="ep-icon ep-icon_pencil txt-blue fs-16 lh-30"></i>
                        </span>
                        <?php } else{?>
                            <span class="lh-30">$ <?php echo get_price($item['amount'], false);?></span>
                        <?php }?>
                    </strong>
                </div>
                <div class="col-xs-3 pl-0 mt-5 text-left">
                    <strong class="txt-green lh-30">
                        <span class="lh-30">Confirmed:</span>
                        <?php if($item['status'] == 'confirmed'){?>
                        <span class="lh-30">$ <?php echo get_price($item['amount'], false);?></span>
                        <?php } else{?>
                            <span class="lh-30">$ 0.00</span>
                        <?php }?>
                    </strong>
                </div>
                <div class="col-xs-3 pl-0 text-right mt-5">
                    <strong class="lh-30">
                        <?php $bill_balance = $item['balance'] - $item['amount'];?>
                        <?php if($bill_balance < 0){?>
                            <?php $total_refund += $bill_balance;?>
                            <?php if(in_array($order_info['status_alias'], $finished_statuses)){?>
                                <?php if(!$item['refund_bill_request']){?>
                                    <span class="lh-30 txt-red">To refund: $ <?php echo get_price(-$bill_balance, false);?></span>
                                <?php } else{?>
                                    <span class="lh-30 txt-gray-light">Refunded: $ <?php echo get_price(-$bill_balance, false);?></span>
                                <?php }?>
                            <?php } else{?>
                                <span class="lh-30 txt-red">To refund: $ <?php echo get_price(-$bill_balance, false);?></span>
                            <?php }?>
                        <?php } else{?>
                            <span class="lh-30">Balance:</span>
                            <span class="lh-30">$ <?php echo get_price($bill_balance, false);?></span>
                        <?php }?>
                    </strong>
                </div>
            </div>
            <div class="clearfix">
                <div class="pull-left lh-22 mt-5">Bill <?php echo orderNumber($item['id_bill']);?></div>
                <?php if(in_array($item['status'], array('paid','confirmed','unvalidated'))){?>
                    <div class="btn btn-default btn-xs pull-right mt-5 toogle_bill_detail" data-toggle="bill-detai-<?php echo $item['id_bill'];?>"><i class="ep-icon ep-icon_visible lh-16"></i> Details</div>
                <?php }?>
                <?php if($item['status'] == 'paid'){ ?>
                    <a class="btn btn-danger btn-xs pull-right mr-5 mt-5 fancybox.ajax fancyboxValidateModal" data-title="Decline Bill" href="<?php echo __SITE_URL;?>billing/popup_forms/decline_bill/<?php echo $item['id_bill'];?>" title="Decline Bill">Decline</a>
                    <a class="btn btn-success btn-xs pull-right mr-5 mt-5 confirm-dialog btn-bill-confirm" href="#" data-callback="confirm_bill" data-message="Are you sure you want to confirm this payment?" data-bill="<?php echo $item['id_bill'];?>" href="#">Confirm</a>
                <?php }?>
                <?php if($item['amount'] > $item['balance'] && in_array($order_info['status_alias'], $finished_statuses)){?>
                    <?php if(!$item['refund_bill_request']){?>
                        <a class="btn btn-danger fancybox.ajax fancyboxValidateModal btn-xs pull-right mt-5 mr-5" href="<?php echo __SITE_URL;?>external_bills/popup_forms/refund_form/<?php echo $item['id_bill'];?>" data-title="Refund the user" title="Refund the user"><i class="ep-icon ep-icon_dollar-circle"></i> Refund the user</a>
                    <?php } else{?>
                        <a class="btn btn-default btn-xs pull-right mt-5 mr-5 fancybox.ajax fancyboxValidateModal" href="<?php echo __SITE_URL;?>external_bills/popup_forms/notice/<?php echo $item['refund_bill_request'];?>" data-title="Notes"><i class="ep-icon ep-icon_notice"></i> Refund detail</a>
                    <?php }?>
                <?php }?>
				<?php if(!empty($item['payment_form'])){?>
					<a class="btn btn-default btn-xs pull-right mt-5 mr-5" href="<?php echo __SITE_URL . 'payments/save_bill_document/' . $item['id_bill'];?>" title="Download payment form" target="_blank"><i class="ep-icon ep-icon_upload"></i> Payment form</a>
				<?php }?>
            </div>

            <div class="additional-desc ml-0" id="bill-detai-<?php echo $item['id_bill'];?>" style="display:none;">
                <p><strong>Description:</strong> <?php echo $item['bill_description'];?></p>
                <?php $pay_detail = unserialize($item['pay_detail']);?>
                <?php if(!empty($pay_detail)){?>
                <table class="data table-striped mt-15 w-100pr">
                    <caption><i class="ep-icon ep-icon_dollar-circle"></i> Payment details</caption>
                    <thead>
                        <tr>
                            <th class="w-150">Name</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($pay_detail as $detail){?>
                        <tr>
                            <td><?php echo $detail['label']; ?></td>
                            <td><?php echo $detail['value']; ?></td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table>
                <?php }?>
                <?php if(!empty($item['note'])){?>
                <?php $bill_notes = array_reverse(json_decode('['.$item['note'].']', true));?>
                <table class="data table-striped mt-15 w-100pr">
                    <caption><i class="ep-icon ep-icon_clock"></i> <strong>Bill timeline</strong></caption>
                    <thead>
                        <tr>
                            <th class="w-150">Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($bill_notes as $bill_note){?>
                        <tr>
                            <td class="w-115"><?php echo formatDate($bill_note['date_note'], 'm/d/Y H:i');?></td>
                            <td><?php echo $bill_note['note'];?></td>
                        </tr>
                        <?php }?>
                    </tbody>
                </table>
                <?php }?>
            </div>
        </li>

        <?php }?>
    </ul>
</div>
<div class="wr-form-btns w-700 mt-0 clearfix">
    <?php $balance_total = ($order_info['final_price'] + $order_info['ship_price']); ?>
    <div class="row">
        <div class="col-xs-3 pr-0 text-left">
            <strong>
                Total: <span>$ <?php echo get_price($balance_total, false); ?></span>
            </strong>
        </div>
        <div class="col-xs-3 pr-0 text-left">
            <strong>
                Paid: <span class="total_paid_by_order">$ <?php echo get_price($balance_paid, false); ?></span>
            </strong>
        </div>
        <div class="col-xs-3 pl-0 text-left">
            <strong>
                Confirmed: <span>$ <?php echo get_price($amount_confirmed, false); ?></span>
            </strong>
        </div>
        <div class="col-xs-3 pl-0 text-right">
            <strong>
                <?php $remain_balance = $balance_total - $amount_confirmed;?>
                <?php if($remain_balance < 0){?>
                    Balance: $ 0.00
                <?php } else{?>
                    Balance: $ <?php echo get_price($remain_balance, false); ?>
                <?php }?>
            </strong>
        </div>
        <?php if($total_refund < 0){?>
            <div class="col-xs-12 mt-10 text-right">
                <span class="pull-left txt-gray-light fs-12 lh-16">*The refund operation is disponible after the order will be finished.</span>
                <strong>
                    <span class="txt-red">*Total refund: $ <?php echo get_price(-$total_refund, false); ?></span>
                </strong>
            </div>
        <?php }?>
        <div class="col-xs-12 mt-10">
            <?php if(empty($order_info['external_bills_requests'])){?>
            <a href="<?php echo __SITE_URL;?>external_bills/popup_forms/add_form/order/<?php echo $order_info['id']?>" class="fancyboxValidateModal fancybox.ajax btn btn-success pull-right <?php if(!in_array($order_info['status_alias'], $finished_statuses)){ echo 'disabled';}?>" data-title="Create external bills">
                Create external bills
            </a>
            <?php } else{?>
            <a href="<?php echo __SITE_URL;?>external_bills/popup_forms/add_form/order/<?php echo $order_info['id']?>" class="fancyboxValidateModal fancybox.ajax btn btn-default pull-right" data-title="View external bills">
                View external bills
            </a>
            <?php }?>
        </div>
    </div>
</div>

<script>
    var edit_amount = function(btn){
        var $this = $(btn);
        var bill = $this.data('bill');
        $this.hide().siblings('.total_paid_amount').hide().siblings('.amount-edit-'+bill).show();
    }

    var cancel_amount = function(btn){
        var $this = $(btn);
        var $edit_block = $this.parent();
        var bill = $this.data('bill');
        $edit_block.hide().siblings('.total_paid_amount').show().siblings('.btn-edit-amount').show();
    }

    var confirm_new_amount = function(opener){
        var $this = $(opener);
        var bill = $this.data('bill');
        var $thisParent = $this.closest('.amount-edit-'+bill);
        var amount = $this.siblings('input[name=amount]').val();
        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL;?>billing/ajax_bill_operations/change_amount',
            data: { bill : bill, amount: amount },
            dataType: 'JSON',
            success: function(resp){
                systemMessages( resp.message, 'message-' + resp.mess_type );

                if(resp.mess_type == 'success'){
                    $thisParent.hide();
                    $thisParent.siblings('.total_paid_amount').html(resp.paid).show();
                    $thisParent.siblings('.btn-edit-amount').show();
                    $('.total_paid_by_order').html(resp.total_paid);
                    $('.total_balance_by_order').html(resp.total_balance);
                }
            }
        });
    }
</script>
