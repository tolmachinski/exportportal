<div class="wr-modal-flex inputs-40">
	<form
        id="edit-tracking-info--form"
        class="modal-flex__form validateModal"
        data-callback="sampleOrdersEditTrackingInfoFormCallBack"
    >
		<div class="modal-flex__content updateValidationErrorsPosition">
            <div class="container-fluid-modal">
				<div class="row">
					<?php if (!empty($shipper)) {?>
						<div class="col-12 col-md-8">
							<label class="input-label">Shipping company</label>
							<img class="h-25 vam" src="<?php echo __SITE_URL . 'public/img/ishippers_logo/' . $shipper['shipper_logo']; ?>" alt="<?php echo cleanOutput($shipper['shipper_name']); ?>">
							<span class="lh-25"><?php echo cleanOutput($shipper['shipper_name']); ?></span>
						</div>
						<div class="col-12 col-md-4">
					<?php } else {?>
						<div class="col-12">
					<?php }?>
						<label class="input-label">Order number</label>
						<span class="lh-25"><?php echo orderNumber($id); ?></span>
					</div>
				</div>
			</div>

			<?php if (empty($delivery_date)) { ?>
				<label class="input-label input-label--required">Delivery date</label>
				<input  type="text"
					name="delivery_date"
					class="js-delivery-datepicker js-datepicker-validate validate[required]"
					autocomplete="delivery_date"
					readonly>
			<?php } else { ?>
				<label class="input-label">Delivery date</label>
				<span class="lh-25"><?php echo cleanOutput(getDateFormat($delivery_date, 'Y-m-d', 'm/d/Y')); ?></span>
			<?php } ?>

			<label class="input-label input-label--required">Tracking info</label>
			<textarea name="track_info"
				class="validate[required,maxSize[1000]] textcounter js-tracking"
				data-max="1000"
				placeholder="Tracking info"><?php echo cleanOutput($tracking_info); ?></textarea>
            <input type="hidden" name="id_order" value="<?php echo $id; ?>"/>
		</div>

		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
		</div>
	</form>
</div>

<script><?php echo getPublicScriptContent('plug/js/sample_orders/popups/tracking_info/edit.js', true); ?></script>
<script>
    $(function () {
		if (!('EditTrackingInfoPopupModule' in window)) {
			if (__debug_mode) {
				console.error(new SyntaxError("'EditTrackingInfoPopupModule' must be defined"))
			}

			return;
		}

		var trackingInfoHandler = new EditTrackingInfoPopupModule(
			<?php echo json_encode(array(
				'saveUrl'       => getUrlForGroup('/sample_orders/ajax_operations/edit_tracking_info'),
				'isDialogPopup' => $is_dialog ?? false,
				'selectors'     => array(
					'form'         => '#edit-tracking-info--form',
					'trackingInfo' => '#edit-tracking-info--form .js-tracking',
					'deliveryDate' => '#edit-tracking-info--form .js-delivery-datepicker',
				),
			)); ?>
        );

        mix(window, { sampleOrdersEditTrackingInfoFormCallBack: function () { trackingInfoHandler.save() } }, false);
	});
</script>
