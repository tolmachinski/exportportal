<?php
    $balance_paid = 0;
    $amount_confirmed = 0;
?>

<div class="wr-form-content w-700">
    <ul class="bills-list-b">
        <?php foreach($bills as $item){?>
            <li id="bill-<?php echo $item['id_bill'];?>-block" class="relative-b">
                <div class="clearfix">
                    <p class="type-b"><?php echo $item['name_type'] === 'order' ? 'Order' : ($item['name_type'] === 'sample_order' ? 'Sample order' : 'Shipping');?> bill</p>
                    <p class="status-b <?php echo $status[$item['status']]['class']?>">
                        <i class="ep-icon ep-icon_<?php echo $status[$item['status']]['icon']?>"></i>
                        <?php echo $status[$item['status']]['description'];?>
                    </p>
                    <p class="date-b">
                        <?php echo getDateFormat($item['create_date'], null, 'm/d/Y');?>
                    </p>
                </div>

                <div class="row">
                    <div class="col-xs-4 pr-0 mt-5">
                        <strong class="total-amount-b txt-blue lh-30">
                            Amount: $ <?php echo get_price($item['balance'], false);?>
                        </strong>
                    </div>
                    <div class="col-xs-4 pr-0 mt-5 text-left">
                        <strong class="txt-orange lh-30">
                            <span class="lh-30">Paid:</span>
                            <span class="lh-30">$ <?php echo get_price($item['amount'], false);?></span>
                        </strong>
                    </div>
                    <div class="col-xs-4 pl-0 mt-5 text-left">
                        <strong class="txt-green lh-30">
                            <span class="lh-30">Confirmed:</span>
                            <span class="lh-30"><?php echo '$' . ($item['status'] == 'confirmed' ? get_price($item['amount'], false) : '0.00');?></span>
                        </strong>
                    </div>
                </div>
                <div class="clearfix">
                    <div class="pull-left lh-22 mt-5"><?php echo 'Bill ' . orderNumber($item['id_bill']);?></div>
                    <?php if (in_array($item['status'], array('paid','confirmed','unvalidated'))) {?>
                        <div class="btn btn-default btn-xs pull-right mt-5 call-function" data-callback="toggle_bill_details" data-toggle="bill-detai-<?php echo $item['id_bill'];?>"><i class="ep-icon ep-icon_visible lh-16"></i> Details</div>
                    <?php }?>
                    <?php if ($item['status'] == 'paid') {?>
                        <a class="btn btn-danger btn-xs pull-right mr-5 mt-5 fancybox.ajax fancyboxValidateModal" data-title="Decline Bill" href="<?php echo __SITE_URL . 'billing/popup_forms/decline_bill/' . $item['id_bill'];?>" title="Decline Bill">Decline</a>
                        <a class="btn btn-success btn-xs pull-right mr-5 mt-5 confirm-dialog btn-bill-confirm" href="#" data-callback="confirm_bill" data-message="Are you sure you want to confirm this payment?" data-bill="<?php echo $item['id_bill'];?>" href="#">Confirm</a>
                    <?php }?>
                    <?php if (!empty($item['payment_form'])) {?>
                        <a class="btn btn-default btn-xs pull-right mt-5 mr-5" href="<?php echo __SITE_URL . 'payments/download_payment_form/' . $item['id_bill'];?>" title="Download payment form"><i class="ep-icon ep-icon_upload"></i> Payment form</a>
                    <?php }?>
                </div>

                <div class="additional-desc ml-0" id="bill-detai-<?php echo $item['id_bill'];?>" style="display:none;">
                    <p><strong>Description:</strong> <?php echo $item['bill_description'];?></p>
                    <?php $pay_detail = unserialize($item['pay_detail']);?>
                    <?php if (!empty($pay_detail)) {?>
                        <table class="data table-striped mt-15 w-100pr">
                            <caption><i class="ep-icon ep-icon_dollar-circle"></i> Payment details</caption>
                            <thead>
                                <tr>
                                    <th class="w-150">Name</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($pay_detail as $detail) {?>
                                <tr>
                                    <td><?php echo $detail['label']; ?></td>
                                    <td><?php echo $detail['value']; ?></td>
                                </tr>
                            <?php }?>
                            </tbody>
                        </table>
                    <?php }?>
                    <?php if (!empty($item['note'])) {?>
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
            <?php
                if (in_array($item['status'], array('paid', 'confirmed'))) {
                    $balance_paid += $item['amount'];
                }
                if ($item['status'] == 'confirmed'){
                    $amount_confirmed += $item['amount'];
                }
            ?>
        <?php }?>
    </ul>
</div>
<div class="wr-form-btns w-700 mt-0 clearfix">
    <div class="row">
        <div class="col-xs-4 pr-0 text-left">
            <strong>
                Total: <span>$ <?php echo get_price($item['balance'], false); ?></span>
            </strong>
        </div>
        <div class="col-xs-4 pr-0 text-left">
            <strong>
                Paid: <span class="total_paid_by_order">$ <?php echo get_price($balance_paid, false); ?></span>
            </strong>
        </div>
        <div class="col-xs-4 pl-0 text-left">
            <strong>
                Confirmed: <span>$ <?php echo get_price($amount_confirmed, false); ?></span>
            </strong>
        </div>
    </div>
</div>

<script>
    var toggle_bill_details = function(btn){
        var btn = $(btn);
        var toggle_element = btn.data('toggle');
        btn.toggleClass('active');

        $('#' + toggle_element).toggle();
        $.fancybox.reposition();
    }
</script>