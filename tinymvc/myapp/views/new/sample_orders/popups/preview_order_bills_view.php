<div class="modal-flex__form" id="sample-order-bills--form">
    <input type="hidden" value="<?php echo cleanOutput($order['id']); ?>">
    <div class="modal-flex__content">
        <ul class="bills-list">
            <?php foreach ($bills as $bill) { ?>
                <li class="bills-list__item">
                    <div class="bills-list__top">
                        <?php if ('sample_order' === $bill['name_type']) { ?>
                            <div class="bills-list__type">Sample Order bill</div>
                        <?php } else { ?>
                            <div class="bills-list__type">Shipping bill</div>
                        <?php } ?>

                        <div class="bills-list__status <?php echo cleanOutput($statuses[$bill['status']]['class']); ?>">
                            <i class="ep-icon ep-icon_<?php echo cleanOutput($statuses[$bill['status']]['icon']); ?>"></i>
                            <?php echo cleanOutput($statuses[$bill['status']]['description']); ?>
                        </div>
                        <div class="bills-list__date">
                            <?php echo cleanOutput(getDateFormatIfNotEmpty($bill['create_date'], \App\Common\DB_DATE_FORMAT, \App\Common\PUBLIC_DATE_FORMAT)); ?>
                        </div>
                    </div>

                    <div class="bills-list__bottom">
                        <div class="bills-list__price">
                            Amount: $ <?php echo cleanOutput(get_price($bill['balance'], false)); ?>
                        </div>
                        <div class="bills-list__price tal">
                            Paid: $ <?php echo cleanOutput(get_price($bill['amount'], false)); ?>
                        </div>
                        <div class="bills-list__price tal">
                            Confirmed:
                            <?php if ('confirmed' === $bill['status']) { ?>
                                $ <?php echo cleanOutput(get_price($bill['amount'], false)); ?>
                            <?php } else { ?>
                                $ 0.00
                            <?php } ?>
                        </div>
                        <div class="bills-list__price tar">
                            <?php if (!empty($bill['bill_balance']) && $bill['bill_balance']->isNegative()) { ?>
                                <span class="txt-red">*To refund:</span>
                                <span class="txt-red">$ <?php echo cleanOutput(get_price($bill['bill_balance'], false)); ?></span>
                            <?php } else { ?>
                                Balance: $ <?php echo cleanOutput(get_price($bill['bill_balance'], false)); ?>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="clearfix lh-18 mt-20">
                        <?php if (have_right('request_sample_order') && 'init' === $bill['status']) { ?>
                            <span class="btn btn-primary pull-right fancybox.ajax fancyboxValidateModal"
                                data-fancybox-href="<?php echo cleanOutput(getUrlForGroup("/payments/popups_payment/pay_bill/{$bill['id_bill']}")); ?>"
                                data-body-class="fancybox-position-ios-fix"
                                data-dashboard-class="inputs-40"
                                data-title="Payment">
                                Pay the bill
                            </span>
                        <?php } ?>

                        <?php if (in_array($bill['status'], array('paid', 'confirmed', 'unvalidated'))) { ?>
                            <div class="btn btn-primary pull-right toogle_bill_detail" data-toggle="#bill-detai-<?php echo cleanOutput($bill['id_bill']); ?>">
                                Details
                            </div>
                        <?php } ?>

                        <a class="btn btn-outline-dark pull-right mr-10"
                            href="<?php echo cleanOutput(getUrlForGroup("/billing/invoice/{$bill['id_bill']}")); ?>"
                            target="_blank">
                            Download Invoice
                        </a>
                    </div>

                    <div id="bill-detai-<?php echo cleanOutput($bill['id_bill']); ?>" class="pt-15" style="display:none;">
                        <p>
                            <strong>Description:</strong> <?php echo cleanOutput($bill['bill_description']); ?>
                        </p>
                        <?php if ($bill['pay_detail']) { ?>
                            <table class="table table-bordered table-hover mt-15">
                                <caption class="tac mb-5"><strong>Payment details</strong></caption>
                                <thead>
                                    <tr>
                                        <th class="w-150">Name</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bill['pay_detail'] as $key => $detail) { ?>
                                        <?php if ('payment_context' === $key) { ?>
                                            <?php continue; ?>
                                        <?php } ?>
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
                        <?php } ?>

                        <?php if (!empty($bill['note'])) {?>
                            <table class="table table-bordered table-hover mt-15">
                                <caption class="tac mb-5"><strong>Bill timeline</strong></caption>
                                <thead>
                                    <tr>
                                        <th class="w-150">Date</th>
                                        <th>Activity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_reverse($bill['note']) as $bill_note) { ?>
                                        <tr>
                                            <td class="w-115">
                                                <?php echo cleanOutput(
                                                    getDateFormatIfNotEmpty(
                                                        $bill_note['date_note'],
                                                        \App\Common\DB_DATE_FORMAT,
                                                        \App\Common\PUBLIC_DATETIME_FORMAT_INTERNATIONAL
                                                    )
                                                ); ?>
                                            </td>
                                            <td>
                                                <div class="grid-text">
                                                    <div class="grid-text__item">
                                                        <?php echo $bill_note['note']; ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } ?>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
    <div class="modal-flex__btns">
		<div class="container-fluid-modal w-100pr">
			<div class="row">
				<div class="col-6 col-md-3 txt-medium">
					<span class="txt-gray">Total:</span> <span class="display-b">$ <?php echo get_price($order['final_price'], false); ?></span>
				</div>
				<div class="col-6 col-md-3 txt-medium">
                    <span class="txt-gray">Paid:</span> <span class="total_paid_by_order display-b">$ <?php echo cleanOutput(get_price($paid_amount, false)); ?></span>
				</div>
				<div class="col-6 col-md-3 txt-medium">
                    <span class="txt-gray">Confirmed:</span> <span class="display-b">$ <?php echo cleanOutput(get_price($confirmed_amount, false)); ?></span>
				</div>
				<div class="col-6 col-md-3 txt-medium">
					<?php if ($remain_amount->isNegative()) {?>
						<span class="txt-gray">Balance:</span> <span class="display-b">$ 0.00</span>
					<?php } else { ?>
						<span class="txt-gray">Balance:</span> <span class="display-b">$ <?php echo cleanOutput(get_price($remain_amount, false)); ?></span>
					<?php } ?>
				</div>
			</div>
		</div>

		<?php if (!$refund_amount->isZero()) { ?>
			<div class="clearfix mt-10 tar">
				<span class="pull-left txt-gray-light fs-14 lh-16">*The user will be refunded after the order will be finished.</span>
				<span class="txt-red txt-medium">*Total refund: $ <?php echo cleanOutput(get_price($refund_amount, false)); ?></span>
			</div>
		<?php } ?>
    </div>
</div>

<script><?php echo getPublicScriptContent('plug/js/sample_orders/popups/bills.js', true); ?></script>
<script>
    $(function () {
		if (!('SampleOrderBillsPopupModule' in window)) {
			if (__debug_mode) {
				console.error(new SyntaxError("'SampleOrderBillsPopupModule' must be defined"));
			}

			return;
		}

		var purchaseOrderHandler = new SampleOrderBillsPopupModule(
            <?php echo json_encode(array(
                'isDialogPopup' => $is_dialog ?? false,
                'selectors'     => array(
                    'foem'          => '#sample-order-bills--form',
                    'detailsToggle' => '#sample-order-bills--form .toogle_bill_detail',
                ),
            )); ?>
        );

        mix(window, {
            modalFormCallBack: null,
            payment_callback: function (data) { purchaseOrderHandler.save(data.payment_data || {}); }
        }, false);
	});
</script>
