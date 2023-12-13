<div class="wr-modal-flex inputs-40">
	<form
        id="request-sample--form"
        class="modal-flex__form validateModal"
        data-callback="requestSampleOrderFormCallBack"
    >
        <input type="hidden" name="item" value="<?php echo cleanOutput($item['id'] ?? null); ?>">

		<div class="modal-flex__content">
            <?php views()->display('new/item/modal_product_detail_view', array('item' => $item ?? null, 'photo' => $photos ?? array())); ?>

			<label class="input-label input-label--info input-label--required">
                <span class="input-label__text">What do you want to include in your Sample order?</span>
                <a class="info-dialog ep-icon ep-icon_info"
                    data-content="#js-info-dialog-describe-order-sample"
                    data-title="What do you want to include in your Sample order?"
                    href="#">
                </a>
            </label>

            <div class="display-n" id="js-info-dialog-describe-order-sample">
                <p>
                    Describe your sample order: <br>
                    • How many units do you want to sample? <br>
                    • How quickly do you need to receive this sample? <br>
                    • Do you require several modifications or variations of this product? <br>
                    • Is there any other order information the seller should know before creating this quote?
                </p>
            </div>

            <div class="form-group">
                <textarea name="description" data-max="5000" class="validate[required] js-sample-description" placeholder="Describe your sample order"></textarea>
            </div>

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
                <button class="btn btn-primary" type="submit">Request Sample</button>
            </div>
		</div>
	</form>
</div>

<script><?php echo getPublicScriptContent('plug/js/sample_orders/popups/request.js', true); ?></script>
<script>
    $(function () {
		if (!('RequestSampleOrderPopupModule' in window)) {
			if (__debug_mode) {
				console.error(new SyntaxError("'RequestSampleOrderPopupModule' must be defined"))
			}

			return;
		}

		var sampleRequestHandler = new RequestSampleOrderPopupModule(
            <?php echo json_encode(array(
                'saveUrl'       => getUrlForGroup('sample_orders/ajax_operations/send_request'),
                'isDialogPopup' => $is_dialog ?? false,
                'selectors'     => array(
                    'form'              => '#request-sample--form',
                    'sampleDescription' => '#request-sample--form textarea.js-sample-description',
                ),
            )); ?>
        );

        mix(window, { requestSampleOrderFormCallBack: function () { sampleRequestHandler.save() } }, false);
	});
</script>
