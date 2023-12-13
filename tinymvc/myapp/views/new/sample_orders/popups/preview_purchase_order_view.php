
<div class="wr-modal-flex inputs-40" id="purchase-order-details--form">
	<div class="modal-flex__form">
		<div class="modal-flex__content" id="purchase-order-details--form--wrapper">
            <?php views()->display('new/sample_orders/partials/purchase_orders_details_view', array(
                'order'          => $order ?? array(),
                'shipper'        => $shipper ?? null,
                'products'       => $products ?? array(),
                'purchase_order' => $po ?? array(),
            )); ?>
		</div>

		<?php if ($can_edit || $can_confirm) { ?>
			<div class="modal-flex__btns">
				<div class="modal-flex__btns-right">
                    <?php if ($can_edit) { ?>
                        <span class="btn btn-primary fancyboxValidateModal fancybox.ajax"
                            data-fancybox-href="<?php echo getUrlForGroup("sample_orders/popup_forms/edit_purchase_order/{$order['id']}"); ?>"
                            data-title="Edit Purchase Order (PO)">
                            Edit <span class="d-xs-none">Purchase Order</span> (PO)
                        </span>
					<?php } ?>

                    <?php if ($can_confirm) { ?>
                        <span class="btn btn-success confirm-dialog js-confirm-button"
                            data-message="Are you sure you want to confirm the Purchase Order?"
							data-callback="confirmPurchaseOrder"
							data-order="<?php echo cleanOutput($order['id']); ?>">
							Confirm <span class="d-xs-none">Purchase Order</span> (PO)
                        </span>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>

<script><?php echo getPublicScriptContent('plug/lodash-custom-4-17-5/lodash.custom.min.js', true); ?></script>
<script><?php echo getPublicScriptContent('plug/js/sample_orders/popups/purchase_order/preview.js', true); ?></script>
<script>
    $(function () {
		if (!('PreviewPurchaseOrderPopupModule' in window)) {
			if (__debug_mode) {
				console.error(new SyntaxError("'PreviewPurchaseOrderPopupModule' must be defined"));
			}

			return;
		}

        var canConfirm = Boolean(~~parseInt('<?php echo (int) $can_confirm; ?>', 10));
		var purchaseOrderHandler = new PreviewPurchaseOrderPopupModule(
			<?php echo json_encode(array(
				'confirmUrl'    => getUrlForGroup('/sample_orders/ajax_operations/confirm_purchase_order'),
				'canConfirm'    => $can_confirm,
				'isDialogPopup' => $is_dialog ?? false,
				'selectors'     => array(
					'form'           => '#purchase-order-details--form',
					'itemsTable'     => '#purchase-order--ordered-items',
					'detailsWrapper' => '#purchase-order-details--form--wrapper',
					'confirmButton'  => 'span.js-confirm-button',
				),
			)); ?>
		);

        if (canConfirm) {
            mix(window, { confirmPurchaseOrder: function () { purchaseOrderHandler.confirmPo(); } }, false);
        }
	});
</script>
