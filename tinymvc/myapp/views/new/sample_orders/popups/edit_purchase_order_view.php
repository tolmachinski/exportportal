<div class="wr-modal-flex inputs-40">
    <form
        id="purchase-order--form"
        class="modal-flex__form validateModal"
        data-callback="sampleOrdersPopupEditPurchaseOrderFormCallBack"
        autocomplete="off"
    >
        <input type="hidden" name="order" value="<?php echo cleanOutput($order['id']); ?>">

        <div class="modal-flex__content pt-25">
            <div class="container-fluid-modal">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <label class="input-label">Issue Date</label>
                        <p class="lh-40"><?php echo cleanOutput(date('m/d/Y')); ?></p>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="relative-b">
                            <label class="input-label input-label--required">Invoice Due Date</label>
                            <input type="text"
                                name="due_date"
                                class="validate[required] js-datepicker-validate js-due-date-datepicker"
                                value="<?php echo cleanOutput(getDateFormatIfNotEmpty($po['invoice']['due_date'], DATE_ATOM, 'm/d/Y', '')); ?>"
                                placeholder="Click to select date"
                                readonly>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="relative-b">
                            <label class="input-label input-label--required">PO Number</label>
                            <input type="text"
                                name="number"
                                maxlength="12"
                                class="validate[required,custom[alphaNumeric],maxSize[12]]"
                                placeholder="Enter the PO number"
                                value="<?php echo cleanOutput($po['number'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="input-label input-label--required">Shipping</label>
                        <div class="input-group">
                            <select class="form-control validate[required] js-shipping-types" name="shipper">
                                <option value="" disabled selected>Select shipping method</option>
                                <?php foreach($shippers as $shipper) { ?>
                                    <option
                                        value="<?php echo cleanOutput($shipper['id_shipper']); ?>"
                                        data-name="<?php echo cleanOutput($shipper['shipper_name']); ?>"
                                        <?php echo selected($shipper['id_shipper'], $order['id_shipper'] ?? null); ?>>
                                        <?php echo cleanOutput($shipper['shipper_name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="input-label">
                            Order Notes
                            <a class="ep-icon ep-icon_info fs-16 info-dialog"
                                data-message="Additional order information displayed on the invoice."
                                data-title="What is: Order Notes?"
                                title="What is: Order Notes?">
                            </a>
                        </label>
                        <textarea name="notes"
                            class="textcounter js-order-notes"
                            data-max="1000"
                            placeholder="Enter your note here"><?php echo cleanOutput($po['invoice']['notes'] ?? null); ?></textarea>
                    </div>

                    <div class="col-12 mt-15 mb-15">
                        <table id="purchase-order--ordered-items" class="main-data-table js-items-table">
                            <thead>
                                <tr>
                                    <th colspan="2">Ordered items</th>
                                    <th class="w-150">Quantity</th>
                                    <th class="w-175">Full Price, USD</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (!empty($products)) { ?>
                                    <?php foreach ($products as $key => $product) { ?>
                                        <tr>
                                            <td data-title="Ordered item" colspan="2">
                                                <div class="grid-text">
                                                    <div class="grid-text__item">
                                                        <a class="order-detail__prod-link"
                                                            href="<?php echo makeItemUrl($product['item_id'], $product['name']); ?>"
                                                            target="_blank">
                                                            <?php echo cleanOutput($product['name']); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                                <?php echo cleanOutput($product['details'] ?? ''); ?>
                                                <input
                                                    type="hidden"
                                                    name="products[<?php echo $key; ?>][id]"
                                                    value="<?php echo cleanOutput($product['item_id']); ?>">
                                            </td>

                                            <td data-title="Quantity">
                                                <input type="text"
                                                    name="products[<?php echo $key; ?>][quantity]"
                                                    class="validate[required,custom[positive_integer],min[1],max[99999999999]] js-product-active-fields js-product-field-quantity"
                                                    value="<?php echo cleanOutput($product['quantity'] ?? 0); ?>"
                                                    placeholder="Ex. 5">
                                            </td>

                                            <td data-title="Unit price">
                                                <input
                                                    type="text"
                                                    name="products[<?php echo $key; ?>][price]"
                                                    class="validate[required,custom[positive_number],min[0.01],max[99999999999.99]] js-product-active-fields js-product-field-price"
                                                    value="<?php echo cleanOutput($product['total_price'] ?? 0); ?>"
                                                    placeholder="Ex. 5">
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>

                                <tr>
                                    <td class="tar vam" colspan="3"><strong>Amount Due</strong></td>
                                    <td class="w-150 vam">
                                        <strong class="js-products-total">
                                            <?php echo cleanOutput(get_price($order['final_price'] ?? 0, false)); ?>
                                        </strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
		</div>
    </form>
</div>

<script><?php echo getPublicScriptContent('plug/js/sample_orders/popups/purchase_order/edit.js', true); ?></script>
<script>
    $(function () {
		if (!('EditPurchaseOrderPopupModule' in window)) {
			if (__debug_mode) {
				console.error(new SyntaxError("'EditPurchaseOrderPopupModule' must be defined"));
			}

			return;
		}

		var purchaseOrderHandler = new EditPurchaseOrderPopupModule(
            <?php echo json_encode(array(
                'saveUrl'       => getUrlForGroup('/sample_orders/ajax_operations/edit_purchase_order'),
                'isDialogPopup' => $is_dialog ?? false,
                'selectors'     => array(
                    'form'          => '#purchase-order--form',
                    'dueDate'       => '#purchase-order--form input.js-due-date-datepicker',
                    'itemsList'     => '#purchase-order--form table.js-items-table',
                    'orderNotes'    => '#purchase-order--form textarea.js-order-notes',
                    'totalAmount'   => '#purchase-order--form strong.js-products-total',
                    'activeItems'   => '.js-product-active-fields',
                    'activeItems'   => '.js-product-active-fields',
                    'priceField'    => '.js-product-field-price'
                ),
            )); ?>
        );

        mix(window, { sampleOrdersPopupEditPurchaseOrderFormCallBack: function () { purchaseOrderHandler.save(); } }, false);
	});
</script>
