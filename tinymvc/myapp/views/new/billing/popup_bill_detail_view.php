<div class="modal-flex__form inputs-40">
    <div class="modal-flex__content">
        <div class="container-fluid-modal">
            <div class="row">
                <div class="col-12 col-md-6">
                    <label class="input-label txt-gray">Issue date</label>
                    <?php echo getDateFormat($bill_info['create_date']);?>
                </div>
                <div class="col-12 col-md-6">
                    <label class="input-label txt-gray">Pay due</label>
                    <?php echo getDateFormat($bill_info['due_date']);?>
                </div>
                <div class="col-12 col-md-6">
                    <label class="input-label txt-gray">Payment Status</label>
                    <?php echo $status[$bill_info['status']]['description'];?>
                </div>
                <div class="col-12 col-md-6">
                    <label class="input-label txt-gray"><?php echo translate('billing_documents_amount');?>, in USD</label>
                    <?php echo get_price($bill_info['balance'], false);?>
                </div>
                <div class="col-12 col-md-6">
                    <label class="input-label txt-gray">Type</label>
                    <?php echo translate('billing_documents_group_package');?>
                </div>
                <div class="col-12">
                    <label class="input-label txt-gray"><?php echo translate('billing_documents_description');?></label>
                    <?php echo $bill_info['bill_description'];?>
                </div>
                <?php if (!empty($transaction_details)) { ?>
                    <div class="col-12">
                        <label class="input-label txt-gray">Transaction details</label>
                        <table class="main-data-table dataTable mt-15">
                            <thead>
                                <tr>
                                    <th class="w-150"><?php echo translate('label_name');?></th>
                                    <th><?php echo translate('label_value');?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($transaction_details as $key => $detail) { ?>
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
                    </div>
                <?php } ?>

                <?php if(!empty($bill_info['note'])){?>
                    <div class="col-12">
                        <?php $bill_notes = array_reverse(json_decode('['.$bill_info['note'].']', true));?>
                        <label class="input-label txt-gray">General timeline</label>
                        <table class="main-data-table dataTable mt-15">
                            <thead>
                                <tr>
                                    <th class="w-150">Date</th>
                                    <th>Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($bill_notes as $bill_note){?>
                                <tr>
                                    <td class="w-115"><?php echo getDateFormat($bill_note['date_note']);?></td>
                                    <td><?php echo $bill_note['note'];?></td>
                                </tr>
                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                <?php }?>
            </div>
        </div>
    </div>
    <div class="modal-flex__btns">
        <div class="modal-flex__btns-right">
            <a class="btn btn-dark" href="<?php echo __SITE_URL;?>billing/invoice/<?php echo $bill_info['id_bill'];?>" target="_blank">
                <?php echo translate('billing_documents_download_invoice');?>
            </a>
        </div>

        <div class="modal-flex__btns-left">
            <?php if($bill_info['status'] == 'init'){ ?>
                <a class="btn btn-primary fancybox.ajax fancyboxValidateModal" data-title="<?php echo translate('langing_block_payment_header');?>" href="<?php echo __SITE_URL;?>payments/popups_payment/pay_bill/<?php echo $bill_info['id_bill'];?>" data-body-class="fancybox-position-ios-fix">
                    <?php echo translate('accreditation_pay_now');?>
                </a>
            <?php }?>
        </div>
    </div>
</div>

<script>
var billTables = $('.js-modau-bill-details .main-data-table');
var normalizeTables = function (tables) {
    if(tables.length !== 0){
        if($(window).width() < 768) {
            tables.addClass('main-data-table--mobile');
        } else {
            tables.removeClass('main-data-table--mobile');
        }
    }
};

mobileDataTable(billTables);
normalizeTables(billTables);
</script>
