<div class="modal-flex__form">
    <div class="modal-flex__content">
        <ul class="bills-list">
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
            <li class="bills-list__item">
                <div class="bills-list__top">
                    <?php if($item['name_type'] == 'order'){?>
                        <div class="bills-list__type">Order bill</div>
                    <?php }else{?>
                        <div class="bills-list__type">Shipping bill</div>
                    <?php }?>

                    <div class="bills-list__status <?php echo $status[$item['status']]['class']?>">
                        <i class="ep-icon ep-icon_<?php echo $status[$item['status']]['icon']?>"></i>
                        <?php echo $status[$item['status']]['description'];?>
                    </div>
                    <div class="bills-list__date">
                        <?php echo formatDate($item['create_date'], 'm/d/Y');?>
                    </div>
                </div>

                <div class="bills-list__bottom">
                    <div class="bills-list__price">
                        Amount: $ <?php echo get_price($item['balance'], false);?>
                    </div>
                    <div class="bills-list__price tal">
                        Paid: $ <?php echo get_price($item['amount'], false);?>
                    </div>
                    <div class="bills-list__price tal">
                        Confirmed:
                        <?php if($item['status'] == 'confirmed'){?>
                        $ <?php echo get_price($item['amount'], false);?>
                        <?php } else{?>
                            $ 0.00
                        <?php }?>
                    </div>
                    <div class="bills-list__price tar">
                        <?php $bill_balance = $item['balance'] - $item['amount'];?>
                        <?php if($bill_balance < 0){?>
                            <?php $total_refund += $bill_balance;?>
                            <span class="txt-red">*To refund:</span>
                            <span class="txt-red">$ <?php echo get_price(-$bill_balance, false);?></span>
                        <?php } else{?>
                            Balance: $ <?php echo get_price($bill_balance, false);?>
                        <?php }?>
                    </div>
                </div>

				<div class="clearfix lh-18 mt-20">
					<?php if(have_right('buy_item') && $item['status'] == 'init'){ ?>
						<a class="btn btn-primary pull-right fancybox.ajax fancyboxValidateModal" data-title="Payment" href="<?php echo __SITE_URL;?>payments/popups_payment/pay_bill/<?php echo $item['id_bill'];?>" data-body-class="fancybox-position-ios-fix" data-dashboard-class="inputs-40">Pay the bill</a>
					<?php }?>
                    <?php if(in_array($item['status'], array('paid','confirmed','unvalidated'))){?>
                        <div class="btn btn-primary pull-right toogle_bill_detail" data-toggle="bill-detai-<?php echo $item['id_bill'];?>">Details</div>
                    <?php }?>

					<a class="btn btn-outline-dark pull-right mr-10" href="<?php echo __SITE_URL;?>billing/invoice/<?php echo $item['id_bill'];?>" target="_blank">Download Invoice</a>
				</div>

                <div id="bill-detai-<?php echo $item['id_bill'];?>" class="pt-15" style="display:none;">
                    <p><strong>Description:</strong> <?php echo $item['bill_description'];?></p>
                    <?php $pay_detail = unserialize($item['pay_detail']);?>
                    <?php if(!empty($pay_detail)){?>
                        <table class="table table-bordered table-hover mt-15">
                            <caption class="tac mb-5"><strong>Payment details</strong></caption>
                            <thead>
                                <tr>
                                    <th class="w-150">Name</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($pay_detail as $key => $detail) { ?>
                                    <?php if ('payment_context' === $key) { continue; } ?>
                                    <tr>
                                        <td><?php echo cleanOutput($detail['label']); ?></td>
                                        <td>
                                            <div class="grid-text">
                                                <div class="grid-text__item">
                                                    <?php echo cleanOutput($detail['value']); ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    <?php }?>
                    <?php if(!empty($item['note'])){?>
                        <?php $bill_notes = array_reverse(json_decode('['.$item['note'].']', true));?>
                        <table class="table table-bordered table-hover mt-15">
                            <caption class="tac mb-5"><strong>Bill timeline</strong></caption>
                            <thead>
                                <tr>
                                    <th class="w-150">Date</th>
                                    <th>Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($bill_notes as $bill_note){?>
                                <tr>
                                    <td class="w-115"><?php echo formatDate($bill_note['date_note'], 'm/d/Y H:i');?></td>
                                    <td>
                                        <div class="grid-text">
                                            <div class="grid-text__item">
                                                <?php echo $bill_note['note'];?>
                                            </div>
                                        </div>
                                    </td>
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
    <div class="modal-flex__btns">
        <?php $balance_total = ($order_info['final_price'] + $order_info['ship_price']); ?>

		<div class="container-fluid-modal w-100pr">
			<div class="row">
				<div class="col-6 col-md-3 txt-medium">
					<span class="txt-gray">Total:</span> <span class="display-b">$ <?php echo get_price($balance_total, false); ?></span>
				</div>
				<div class="col-6 col-md-3 txt-medium">
                    <span class="txt-gray">Paid:</span> <span class="total_paid_by_order display-b">$ <?php echo get_price($balance_paid, false); ?></span>
				</div>
				<div class="col-6 col-md-3 txt-medium">
                    <span class="txt-gray">Confirmed:</span> <span class="display-b">$ <?php echo get_price($amount_confirmed, false); ?></span>
				</div>
				<div class="col-6 col-md-3 txt-medium">
					<?php $remain_balance = $balance_total - $amount_confirmed;?>
					<?php if($remain_balance < 0){?>
						<span class="txt-gray">Balance:</span> <span class="display-b">$ 0.00</span>
					<?php } else{?>
						<span class="txt-gray">Balance:</span> <span class="display-b">$ <?php echo get_price($remain_balance, false); ?></span>
					<?php }?>
				</div>
			</div>
		</div>

		<?php if($total_refund < 0){?>
			<div class="clearfix mt-10 tar w-100pr">
				<span class="pull-left txt-gray-light fs-14 lh-16">*The user will be refunded after the order will be finished.</span>
				<span class="txt-red txt-medium">*Total refund: $ <?php echo get_price(-$total_refund, false); ?></span>
			</div>
		<?php }?>
    </div>
</div>
<script>
$('.toogle_bill_detail').click(function(e){
    e.preventDefault();
    var toggle_element = $(this).data('toggle');
    $('#'+toggle_element).toggle();

    $.fancybox.update();
    //myRepositionFancybox();
});
</script>
