<div class="wr-modal-flex inputs-40">
	<form
        id="edit-delivery-address--form"
        class="modal-flex__form validateModal"
        data-callback="sampleOrdersPopupsEditDeliveryAddressFormCallBack"
    >
        <input type="hidden" name="id_order" value="<?php echo $id_order; ?>">

		<div class="modal-flex__content">
			<label class="input-label input-label--info input-label--required">
                <span class="input-label__text">Delivery Address?</span><a class="info-dialog ep-icon ep-icon_info" data-content="#js-info-dialog-delivery-address-order-sample" data-title="Delivery Address?" href="#"></a>
            </label>

            <div class="display-n" id="js-info-dialog-delivery-address-order-sample">
                <p>
                    Street, Country, Region/State, City, Zip/Postal Code
                </p>
            </div>

            <?php widgetLocationBlock(
                new \Symfony\Component\HttpFoundation\ParameterBag(array(
                    'saved'              => $address ?? null,
                    'overrided'          => $other_address_input_value ?? null,
                    'overrided_location' => $other_location ?? null,
                )),
                new \Symfony\Component\HttpFoundation\ParameterBag(array('address' => true, 'postal_code' => true))
            ); ?>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
		</div>
	</form>
</div>

<script><?php echo getPublicScriptContent('plug/js/sample_orders/popups/delivery_address/edit.js', true); ?></script>
<script>
    $(function () {
		if (!('EditDeliveryAddressPopupModule' in window)) {
			if (__debug_mode) {
				console.error(new SyntaxError("'EditDeliveryAddressPopupModule' must be defined"))
			}

			return;
		}

		var deliveryAddressHandler = new EditDeliveryAddressPopupModule(
            <?php echo json_encode(array(
                'saveUrl'       => getUrlForGroup('/sample_orders/ajax_operations/set_delivery_address'),
                'location'      => $other_location ?? null,
                'isDialogPopup' => $is_dialog ?? false,
                'selectors'     => array(
                    'form' => '#edit-delivery-address--form',
                ),
            )); ?>
        );

        mix(window, { sampleOrdersPopupsEditDeliveryAddressFormCallBack: function () { deliveryAddressHandler.save() } }, false);
	});
</script>
