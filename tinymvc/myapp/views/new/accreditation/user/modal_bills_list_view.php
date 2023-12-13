<div class="modal-flex__form">
    <div class="modal-flex__content inputs-40">
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
					<div class="bills-list__type"><?php echo translate('billing_documents_group_package');?></div>
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
                        <?php echo translate('billing_documents_amount');?>: $ <?php echo get_price($item['balance'], false);?>
                    </div>
                    <div class="bills-list__price tal">
                        <?php echo translate('billing_documents_paid');?>: $ <?php echo get_price($item['amount'], false);?>
                    </div>
                    <div class="bills-list__price tal">
                        <?php echo translate('accreditation_documents_status_confirmed');?>:
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
                            <span class="txt-red">*<?php echo translate('billing_documents_refund');?>:</span>
                            <span class="txt-red">$ <?php echo get_price(-$bill_balance, false);?></span>
                        <?php } else{?>
                            <?php echo translate('billing_documents_balance')?>: $ <?php echo get_price($bill_balance, false);?>
                        <?php }?>
                    </div>
                </div>
				<div class="clearfix lh-18 mt-20">
					<?php if($item['status'] == 'init'){ ?>
						<a class="btn btn-primary pull-right fancybox.ajax fancyboxValidateModal" data-title="<?php echo translate('langing_block_payment_header');?>" href="<?php echo __SITE_URL;?>payments/popups_payment/pay_bill/<?php echo $item['id_bill'];?>" data-body-class="fancybox-position-ios-fix">
                            <?php echo translate('accreditation_pay_now');?>
                        </a>
					<?php }?>
                    <?php if(in_array($item['status'], array('paid','confirmed','unvalidated'))){?>
                        <div class="btn btn-primary pull-right toogle_bill_detail" data-toggle="bill-detai-<?php echo $item['id_bill'];?>">
                            <?php echo translate('billing_documents_details');?>
                        </div>
                    <?php }?>

                    <a class="btn btn-outline-dark pull-right mr-10" href="<?php echo __SITE_URL;?>billing/invoice/<?php echo $item['id_bill'];?>" target="_blank">
                        <?php echo translate('billing_documents_download_invoice');?>
                    </a>
				</div>

                <div id="bill-detai-<?php echo $item['id_bill'];?>" class="pt-15 pr-15" style="display:none;">
                    <p><strong><?php echo translate('billing_documents_description');?>:</strong> <?php echo $item['bill_description'];?></p>
                    <?php $pay_detail = unserialize($item['pay_detail']);?>
                    <?php if(!empty($pay_detail)){?>
                        <table class="table table-bordered table-hover mt-15">
                            <caption class="tac mb-5"><strong><?php echo translate('billing_documents_payment_details');?></strong></caption>
                            <thead>
                                <tr>
                                    <th class="w-150"><?php echo translate('label_name');?></th>
                                    <th><?php echo translate('label_value');?></th>
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
                        <table class="table table-bordered table-hover mt-15">
                            <caption class="tac mb-5"><strong><?php echo translate('billing_documents_bill_timeline');?></strong></caption>
                            <thead>
                                <tr>
                                    <th class="w-150"><?php echo translate('billing_documents_date');?></th>
                                    <th><?php echo translate('billing_documents_actions');?></th>
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
    <div class="modal-flex__btns">
        <?php $balance_total = $group_package['price']; ?>
        <div class="container-fluid-modal">
            <div class="row">
                <div class="col-6 col-md-3 txt-medium">
                    <span class="txt-gray">Total:</span> <span class="display-b">$ <?php echo get_price($balance_total, false); ?></span>
                </div>
                <div class="col-6 col-md-3 txt-medium">
                    <span class="txt-gray"><?php echo translate('billing_documents_paid');?>:</span> <span class="total_paid_by_order display-b">$ <?php echo get_price($balance_paid, false); ?></span>
                </div>
                <div class="col-6 col-md-3 txt-medium">
                    <span class="txt-gray"><?php echo translate('accreditation_documents_status_confirmed');?>:</span> <span>$ <?php echo get_price($amount_confirmed, false); ?></span>
                </div>
                <div class="col-6 col-md-3 txt-medium">
                    <?php $remain_balance = $balance_total - $amount_confirmed;?>
                    <?php if($remain_balance < 0){?>
                        <span class="txt-gray"><?php echo translate('billing_documents_balance')?>:</span> <span class="display-b">$ 0.00</span>
                    <?php } else{?>
                        <span class="txt-gray"><?php echo translate('billing_documents_balance')?>:</span> <span class="display-b">$ <?php echo get_price($remain_balance, false); ?></span>
                    <?php }?>
                </div>
            </div>

            <?php if($total_refund < 0){?>
                <div class="clearfix mt-10 tar">
                    <span class="pull-left txt-gray-light fs-14 lh-16">*<?php echo translate('billing_document_user_refunded');?></span>
                    <span class="txt-red txt-medium">*<?php echo translate('billing_document_total_refund');?>: $ <?php echo get_price(-$total_refund, false); ?></span>
                </div>
            <?php }?>
        </div>
    </div>
</div>

<script>
$('.toogle_bill_detail').click(function(e){
    e.preventDefault();
    var toggle_element = $(this).data('toggle');
    $('#'+toggle_element).toggle();

    $.fancybox.update();
});
</script>
