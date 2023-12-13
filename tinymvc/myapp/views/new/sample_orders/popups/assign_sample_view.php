<div class="wr-modal-flex inputs-40">
	<form
        id="assign-sample-order--form"
        class="modal-flex__form validateModal"
        data-callback="sampleOrdersPopupAssignSampleFormCallBack"
        data-js-action="sample-order:asign"
    >
        <input type="hidden" name="room" value="<?php echo cleanOutput($room ?? null); ?>">
        <input type="hidden" name="recipient" value="<?php echo cleanOutput($recipient ?? null); ?>">
		<div class="modal-flex__content">
			<label class="input-label mt-0 input-label--required">Order Number</label>
			<input type="text" class="validate[required] js-order-number" name="order" placeholder="Ex. #000000001">
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary disabled" type="submit">Assign Order</button>
            </div>
		</div>
	</form>
</div>

<?php
    echo dispatchDynamicFragmentInCompatMode(
        "sample-order:assign",
        asset('public/plug/js/sample_orders/popups/assign.js', 'legacy'),
        sprintf(
            "function () {
                var sampleAssignHandler = new AssignSampleOrderPopupModule(%s);
                mix(window, { sampleOrdersPopupAssignSampleFormCallBack: function () { sampleAssignHandler.save() } }, false);
            }",
            json_encode(
                $params = [
                    'assignUrl'     => getUrlForGroup('sample_orders/ajax_operations/assign_order'),
                    'isDialogPopup' => $is_dialog ?? false,
                    'selectors'     => [
                        'form'        => '#assign-sample-order--form',
                        'numberInput' => '#assign-sample-order--form input[type="text"].js-order-number',
                    ],
                ]
            ),
        ),
        [$params],
        true
    );
?>
