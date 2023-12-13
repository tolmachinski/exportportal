<div class="wr-modal-flex" id="international-shipping-rates--form--wrapper">
	<form class="modal-flex__form validateModal"
		id="international-shipping-rates--form"
		action="<?php echo $action; ?>"
		data-callback="saveInternationalShippingRates">
		<input type="hidden" name="order" value="<?php echo (int) $order['id']; ?>"/>
		<div class="modal-flex__content">
			<div class="container-fluid-modal">
				<div class="row js__shipping-offer__add-form" id="international-shipping-rates--form--option-container" style="display: none;"></div>
				<div class="row js__shipping-offer-insurance-options-form" id="international-shipping-rates--form--insurance-container" style="display: none;"></div>
			</div>
			<table id="international-shipping-rates--form--table" class="main-data-table dataTable js__shipping-offers">
				<thead>
					<tr>
						<th class="vam w-20">#</th>
						<th class="vam">Company</th>
						<th class="w-120 tac vam">Delivery Time</th>
						<th class="w-100 tac vam">Price, USD</th>
					</tr>
				</thead>
				<tbody>
					<?php if (!empty($ishippers_quotes)) { ?>
						<?php foreach($ishippers_quotes as $ishippers_quote) { ?>
							<tr class="js__shipping-offer-option js__shipping-offer-option--<?php echo $ishippers_quote['id_shipper']; ?>">
								<td class="w-20 vam">
									<span class="cur-pointer ep-icon ep-icon_remove-stroke fs-14 vat lh-20 m-0 confirm-dialog"
										data-message="Are you sure you want to delete this shipping option?"
										data-callback="deleteShipperRateOption"
										data-shipper="<?php echo $ishippers_quote['id_shipper']; ?>"
										title="Delete">
									</span>
								</td>
								<td class="vam" data-title="Company">
									<div class="grid-text">
										<div class="grid-text__item">
											<div class="ishipper-logo">
												<div class="ishipper-logo__img">
													<img 
														class="image"
														src="<?php echo arrayGet($ishippers_quote, 'shipper.logo', __IMG_URL . "public/img/no_image/noimage-shipper-125.jpg"); ?>"
														alt="<?php echo cleanOutput(arrayGet($ishippers_quote, 'shipper.shipper_name')); ?>">
												</div>
												<span class="ishipper-logo__txt">
													<?php echo cleanOutput(arrayGet($ishippers_quote, 'shipper.shipper_name')); ?>
												</span>
											</div>
											<div class="w-100pr lh-20 txt-gray"><?php echo cleanOutput($ishippers_quote['shipment_type']); ?></div>
										</div>
									</div>
								</td>
								<td class="vam w-120" data-title="Delivery Time">
									in <?php echo cleanOutput($ishippers_quote['delivery_from']); ?> &mdash; <?php echo cleanOutput($ishippers_quote['delivery_to']); ?> days
								</td>
								<td class="vam w-100" data-title="Price, USD">
									<strong><?php echo get_price($ishippers_quote['amount'], false);?></strong>
									<div class="shipping-offer__option-inputs">
										<input type="hidden" name="ishippers_cache[]" value="<?php echo (int) $ishippers_quote['id_shipper']; ?>">
									</div>
								</td>
							</tr>
						<?php } ?>
					<?php } ?>
					<tr class="js__no-ishipper-options" <?php echo !empty($ishippers_quotes) ? 'style="display:none;"' : ''; ?>>
						<td class="tac" colspan="4">Please setup International freight forwarders' quotes.</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="js-international-shipping-rates-formactions" class="modal-flex__btns">
			<div class="modal-flex__btns-left">
				<button 
					type="button"
					class="btn btn-dark call-function"
					data-callback="showShipperRateForm"
				>
					Add Rate
				</button>
			</div>

			<div class="modal-flex__btns-right">
				<button 
					type="submit"
					id="international-shipping-rates--formactions--save-action"
					class="btn btn-primary"
					disabled
				>
					Save
				</button>
			</div>
		</div>

		<div id="international-shipping-rates--formactions--add-rates-actions" class="modal-flex__btns" style="display:none;">
			<div class="modal-flex__btns-left">
				<button class="btn btn-danger call-function" data-callback="hideShipperRateForm" type="button">Cancel</button>
			</div>
			<div class="modal-flex__btns-right">
				<button class="btn btn-success call-function" data-callback="addShipperRateOption" type="button">Add rate</button>
			</div>
		</div>

		<div id="international-shipping-rates--formactions--add-insurance-actions" class="modal-flex__btns" style="display:none;">
			<div class="modal-flex__btns-left">
				<button class="btn btn-danger call-function" data-callback="hideInsuranceForm" type="button">Cancel</button>
			</div>
			<div class="modal-flex__btns-right">
				<button class="btn btn-success call-function" data-callback="addInsuranceOption" type="button">Add option</button>
			</div>
		</div>
	</form>
</div>

<script type="text/template" id="shipping-offer__add-form">
	<div class="col-12 col-md-6">
		<label class="input-label input-label--required">Shipping Company</label>
		<select name="add_shipping_id" class="form-control validate[required]">
			<option value="">Select Shipping Company</option>
			<?php foreach($ishippers as $ishipper) { ?>
				<option value="<?php echo $ishipper['id_shipper'];?>"
					data-logo="<?php echo arrayGet($ishipper, 'logo', __IMG_URL . "public/img/no_image/noimage-shipper-125.jpg"); ?>">
					<?php echo cleanOutput($ishipper['shipper_original_name']); ?>
				</option>
			<?php } ?>
		</select>
	</div>
	<div class="col-12 col-md-6">
		<label class="input-label input-label--required">Shipping Service</label>
		<input type="text"
			name="add_shipping_shipment_type"
			class="validate[required,maxSize[100]]"
			maxlength="100"
			placeholder="e.g. Economical Ground Delivery to Businesses"/>
	</div>
	<div class="col-12 col-md-6">
		<label class="input-label input-label--required">Delivery time, days</label>
		<div class="input-group">
			<input type="text"
				id="js-ishipper-delivery-time-from"
				name="add_shipping_delivery_days_from"
				class="form-control validate[required,min[1],custom[positive_integer,funcCall[checkDeliveryDaysFrom]],max[<?php echo config('ep_shippers_max_delivery_days', 180);?>]] tac"
				placeholder="From">
			<span class="input-group-prepend">
				<span class="input-group-text">-</span>
			</span>
			<input type="text"
				id="js-ishipper-delivery-time-to"
				name="add_shipping_delivery_days_to"
				class="form-control validate[required,min[1],custom[positive_integer,funcCall[checkDeliveryDaysTo]],max[<?php echo config('ep_shippers_max_delivery_days', 180);?>]] tac"
				placeholder="To">
		</div>
	</div>
	<div class="col-12 col-md-6">
		<label class="input-label input-label--required">Amount, in USD</label>
		<input class="validate[required,min[0.1],custom[positive_number]]" type="text" name="add_shipping_amount" placeholder="0.00"/>
	</div>

	<div class="col-12">
		<label class="input-label input-label--required">Shipping Insurance</label>
		<table class="main-data-table dataTable js__shipping-offer-insurance-options">
			<thead>
				<tr>
					<th class="w-40 tac">#</th>
					<th class="tal">Insurance description</th>
					<th class="w-150 tal">Amount, in USD</th>
				</tr>
			</thead>
			<tbody>
				<tr class="js__no-insurance-options">
					<td class="tac" colspan="3">Please add Shipping Insurance options.</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="col-12 mt-15 js__shipping-offer-insurance-options-btn">
		<span class="btn btn-dark call-function"
			data-callback="showInsuranceForm">
			Add insurance option
		</span>
	</div>
</script>
<script type="text/template" id="shipping-offer__insurance-add-form">
	<div class="col-12 col-md-6">
		<label class="input-label input-label--required">Insurance name</label>
		<input type="text"
			name="insurance_option_name"
			class="validate[required, maxSize[100]]"
			placeholder="e.g. Option #1" >
	</div>
	<div class="col-12 col-md-6">
		<label class="input-label input-label--required">Insurance amount, USD</label>
		<input type="text"
			name="insurance_option_amount"
			class="validate[required,custom[noWhitespaces],min[0],max[999999],custom[positive_number]]"
			placeholder="0.00">
	</div>
	<div class="col-12">
		<label class="input-label input-label--required">Insurance description</label>
		<textarea
			name="insurance_option_description"
			class="validate[required,maxSize[1000]] textcounter-insurance_option_description"
			data-max="1000"
			placeholder="Write Insurance description here ..."></textarea>
	</div>
</script>
<script type="text/template" id="shipping-offer__insurance-add-option">
	<tr class="js__shipping-offer-insurance-option">
		<td class="w-40 vam">
			<span class="cur-pointer ep-icon ep-icon_remove-stroke fs-14 vat lh-20 m-0 confirm-dialog"
				data-message="Are you sure you want to delete this Insurance option?"
				data-callback="deleteInsuranceOption"
				title="Delete">
			</span>
		</td>
		<td class="vam">
			<strong title="{{description}}">{{title}}</strong>
			<div class="shipping-offer__insurance-option-inputs">
				<input type="hidden" name="ishippers_quotes[{{shipper}}][insurance_options][{{hash}}][title]" value="{{title}}">
				<input type="hidden" name="ishippers_quotes[{{shipper}}][insurance_options][{{hash}}][amount]" value="{{amount}}">
				<input type="hidden" name="ishippers_quotes[{{shipper}}][insurance_options][{{hash}}][description]" value="{{description}}">
			</div>
		</td>
		<td class="w-150 vam" data-title="Amount">
			<strong>{{amount}}</strong>
		</td>
	</tr>
</script>
<script type="text/template" id="shipping-offer__add-option">
	<tr class="js__shipping-offer-option js__shipping-offer-option--{{shipper}}">
		<td class="w-20 vam">
			<span class="cur-pointer ep-icon ep-icon_remove-stroke fs-14 vat lh-20 m-0 confirm-dialog"
				data-message="Are you sure you want to delete this shipping option?"
				data-callback="deleteShipperRateOption"
				data-shipper="{{shipper}}"
				title="Delete">
			</span>
		</td>
		<td class="vam" data-title="Company">
			<div class="grid-text">
				<div class="grid-text__item">
					<div class="ishipper-logo">
						<div class="ishipper-logo__img">
							<img class="image" src="{{ishipper_logo}}" alt="{{ishipper_name}}">
						</div>
						<span class="ishipper-logo__txt">{{ishipper_name}}</span>
					</div>
					<div class="w-100pr lh-20 txt-gray">{{shipment_type}}</div>
				</div>
			</div>
		</td>
		<td class="vam w-120" data-title="Delivery Time">
			in {{delivery_from}} &mdash; {{delivery_to}} days
		</td>
		<td class="w-150 vam" data-title="Price, USD">
			<strong>{{amountLabel}}</strong>
			<div class="shipping-offer__option-inputs">
				<input type="hidden" name="ishippers_quotes[{{shipper}}][shipment_type]" value="{{shipment_type}}">
				<input type="hidden" name="ishippers_quotes[{{shipper}}][delivery_from]" value="{{delivery_from}}">
				<input type="hidden" name="ishippers_quotes[{{shipper}}][delivery_to]" value="{{delivery_to}}">
				<input type="hidden" name="ishippers_quotes[{{shipper}}][amount]" value="{{amount}}">
			</div>
		</td>
	</tr>
</script>
<script>
	$(function() {
		var form = $('#international-shipping-rates--form');
		var global = $(window);
		var shippingRatesContainer = $('#international-shipping-rates--form--wrapper');
		var ishippersCompanies = JSON.parse('<?php echo json_encode(array_map('intval', array_column($ishippers, 'id_shipper')));?>');
		var insuranceFormActions = $('#international-shipping-rates--formactions--add-insurance-actions');
		var insuranceFormTemplate = $('#shipping-offer__insurance-add-form').text() || null;
		var insuranceFormContainer = $('#international-shipping-rates--form--insurance-container');
		var insuranceOptionTemplate = $('#shipping-offer__insurance-add-option').text() || null;
		var shipperOfferFormTemplate = $('#shipping-offer__add-form').text() || null;
		var shipperOfferOptionTemplate = $('#shipping-offer__add-option').text() || null;
		var $mainFormactions = $('#js-international-shipping-rates-formactions');
		var shipperOfferFormContainer = $('#international-shipping-rates--form--option-container');
		var shipperOfferFormActions = $('#international-shipping-rates--formactions--add-rates-actions');
		var shippingRatesTable = $('#international-shipping-rates--form--table');
		var submitButton = $('#international-shipping-rates--formactions--save-action');
		var hasChanges = false;
		var onSaveContent = function(wrapper, formElement) {
            var form = $(formElement);
            var document = form.find('input[name=document]').val() || null;
            var submitButton = form.find('button[type=submit]');
            var formData = form.serializeArray();
            var url = form.attr('action');
            var sendRequest = function (url, data) {
                return $.post(url, data, null, 'json');
            };
            var beforeSend = function() {
                showLoader(wrapper);
                submitButton.addClass('disabled');
            };
            var onRequestEnd = function() {
                hideLoader(wrapper);
                submitButton.removeClass('disabled');
            };
            var onRequestSuccess = function(document, data){
                systemMessages(data.message, data.mess_type);
                if(data.mess_type === 'success'){
                    callFunction('callbackSaveShippingRates', document, data);
                    closeFancyBox();
                }
            };

            beforeSend();
			sendRequest(url, formData)
				.done(onRequestSuccess.bind(null, document))
				.fail(onRequestError)
				.always(onRequestEnd);
        };
		var normalizeTables = function(tables) {
            if(tables.length !== 0){
                if($(window).width() < 768) {
                    tables.addClass('main-data-table--mobile');
				} else {
                    tables.removeClass('main-data-table--mobile');
				}
			}
        };
        var cleanOrientationChangeHandler = function(element) {
			$(element).off('resizestop', onOrientationChange);
		};
        var onOrientationChange = function(wrapper, tables) {
            return function () {
                var normalize = function () {
                    normalizeTables(tables);
                };

                if (!$('body').find('#' + wrapper.attr('id')).length) {
                    cleanOrientationChangeHandler(this);

                    return;
                }

                normalize();
                setTimeout(normalize, 500);
            };
		};
		var limitTextLength = function(textarea) {
            textarea.textcounter({
                countDown: true,
                countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
                countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
            });
		};
		var adjustTables = function (tables, wrapper, global) {
			mobileDataTable(tables, false);
			normalizeTables(tables);

			global.on('resizestop', onOrientationChange(wrapper, tables));
		};
		var checkDeliveryDaysFrom = function(field, rules, i, options) {
			var deliveryDaysFromValue = field[0].value;
			var deliveryDaysToValue = $('#js-ishipper-delivery-time-to').val();
			if(deliveryDaysFromValue != '' && deliveryDaysToValue != '' && (intval(deliveryDaysFromValue) >= intval(deliveryDaysToValue))){
				return "- Delivery time From must be less than Delivery time To.";
			}
		};
		var checkDeliveryDaysTo = function(field, rules, i, options){
			var deliveryDaysFromValue = $('#js-ishipper-delivery-time-from').val();
			var deliveryDaysToValue = field[0].value;
			if(deliveryDaysToValue != '' && deliveryDaysFromValue != '' && (intval(deliveryDaysToValue) <= intval(deliveryDaysFromValue))){
				return "- Delivery time To must be greater than Delivery time From.";
			}
		};
		var updateShippingCompanies = function(container, companies) {
			(companies || []).forEach(function (shipper) {
				if($('.js__shipping-offer-option--' + shipper).length > 0){
					container.find('select[name="add_shipping_id"]').find('option[value="' + shipper + '"]').prop('disabled', true);
				}
			});
		};
		var addShipperRecord = function(shipper) {
			ishippersCompanies.push(shipper);
		};
		var removeShipperRecord = function (shipper) {
			ishippersCompanies = ishippersCompanies.filter(function(i) { return i != shipper })
		};
		var showShipperRateForm = function(button) {
			shipperOfferFormContainer.html(shipperOfferFormTemplate).show();
			shippingRatesTable.hide();
			shipperOfferFormActions.css({'display': 'flex'});

			$mainFormactions.hide();
			submitButton.prop('disabled', true);

			$.fancybox.update();

			adjustTables(shipperOfferFormContainer.find('table'), shipperOfferFormContainer, global);
			updateShippingCompanies(shipperOfferFormContainer, ishippersCompanies);
		};
		var hideShipperRateForm = function() {
			shipperOfferFormContainer.empty().hide();
			shippingRatesTable.show();
			shipperOfferFormActions.hide();

			$mainFormactions.css({'display': 'flex'});
			submitButton.prop('disabled', false);

			$.fancybox.update();
		};
		var showInsuranceForm = function(button) {
			shipperOfferFormContainer.hide();
			shipperOfferFormActions.hide();
			insuranceFormContainer.html(insuranceFormTemplate).show();
			insuranceFormActions.css({'display': 'flex'});
			$.fancybox.update();

			limitTextLength(insuranceFormContainer.find('textarea'));
			adjustTables(insuranceFormContainer.find('table'), insuranceFormContainer, global);
		};
		var hideInsuranceForm = function() {
			insuranceFormContainer.empty().hide();
			insuranceFormActions.hide();
			shipperOfferFormContainer.show();
			shipperOfferFormActions.css({'display': 'flex'});
			$.fancybox.update();
		};
		var addInsuranceOption = function (container, button) {
			var form = button.closest('form');
			var shipper = intval(container.find('select[name="add_shipping_id"]').val() || null);
			if (null === shipper || 0 === shipper ) {
				systemMessages('Please choose a Freight Forwarding Company first.', 'warning');

				return;
			}

			var insuranceFormInputs = form.find('[name^="insurance_option_"]');
			var isValidated = insuranceFormInputs.toArray().reduce(function(accumulator, formField) {
				return accumulator && $(formField).validationEngine("validate");
			}, true);

			if(!isValidated) {
				return;
			}

			var containerBody = container.find('tbody');
			var hash = uniqid('insurance-option-');
			var insuranceOption = insuranceOptionTemplate;
			var amount = parseFloat(insuranceFormInputs.filter('[name="insurance_option_amount"]').val() || 0);
			var content = {
				hash: hash,
				title: htmlEscape(insuranceFormInputs.filter('[name="insurance_option_name"]').val() || '').trim(),
				amount: amount,
				shipper: shipper,
				description: htmlEscape(insuranceFormInputs.filter('[name="insurance_option_description"]').val() || '').trim(),
				amountLabel: get_price(amount, false),
			};

			for (var key in content) {
				if (content.hasOwnProperty(key)) {
					insuranceOption = insuranceOption.replace(new RegExp('{{' + key + '}}', 'g'), content[key]);
				}
			}

			containerBody.find('tr.js__no-insurance-options').hide();
			containerBody.append($(insuranceOption));
			hideInsuranceForm();
		};
		var addShipperRateOption = function (container, button) {
			var form = button.closest('form');
			var shipperFormInputs = form.find('[name^="add_shipping_"]');
			var shipperInsuranceInputs = [];
			var shippingOptionContent;
			var isValidated = shipperFormInputs.toArray().reduce(function(accumulator, formField) {
				return accumulator && $(formField).validationEngine("validate");
			}, true);

			if(!isValidated) {
				return;
			}

			form.find('.shipping-offer__insurance-option-inputs').each(function(){
				shipperInsuranceInputs.push($(this).clone());
			});
			if(shipperInsuranceInputs.length == 0){
				systemMessages('Please add at least one Shipping Insurance option.', 'warning');

				return;
			}

			var containerBody = container.find('tbody');
			var shippingOption = shipperOfferOptionTemplate;
			var amount = parseFloat(shipperFormInputs.filter('[name="add_shipping_amount"]').val());
			var content = {
				shipper: intval(shipperFormInputs.filter('[name="add_shipping_id"]').val() || 0),
				amount: amount,
				amountLabel: get_price(amount, false),
				ishipper_logo: shipperFormInputs.filter('[name="add_shipping_id"]').find('option:selected').data('logo'),
				ishipper_name: htmlEscape(shipperFormInputs.filter('[name="add_shipping_id"]').find('option:selected').text() || '').trim(),
				shipment_type: htmlEscape(shipperFormInputs.filter('[name="add_shipping_shipment_type"]').val()).trim(),
				delivery_from: intval(shipperFormInputs.filter('[name="add_shipping_delivery_days_from"]').val()),
				delivery_to: intval(shipperFormInputs.filter('[name="add_shipping_delivery_days_to"]').val())
			};

			for (var key in content) {
				if (content.hasOwnProperty(key)) {
					shippingOption = shippingOption.replace(new RegExp('{{' + key + '}}', 'g'), content[key])
				}
			}

			shippingOptionContent = $(shippingOption);
			shippingOptionContent.find('.shipping-offer__option-inputs').append(shipperInsuranceInputs);
			containerBody.find('tr.js__no-ishipper-options').hide();
			containerBody.append(shippingOptionContent);
			ishippersCompanies.push(content.shipper);
			hideShipperRateForm();
			enableSaveButton();
		};
		var deleteInsuranceOption = function(container, button) {
			button.closest('tr').remove();
			$.fancybox.update();
			if (!container.find('tr.js__shipping-offer-insurance-option').length) {
				container.find('tr.js__no-insurance-options').show();
			}
		};
		var deleteShipperRateOption = function(container, button) {
			button.closest('tr').remove();
			$.fancybox.update();
			if(container.find('tr.js__shipping-offer-option').length == 0){
				container.find('tr.js__no-ishipper-options').show();
			}

			removeShipperRecord(intval(button.data('shipper') || 0));
			enableSaveButton();
		};
		var enableSaveButton = function () {
			submitButton.prop('disabled', false);
		};

		adjustTables(shippingRatesTable, shippingRatesContainer, global);
		mix(window, {
			saveInternationalShippingRates: onSaveContent.bind(null, shippingRatesContainer),
			hideShipperRateForm: hideShipperRateForm,
			showShipperRateForm: showShipperRateForm,
			showInsuranceForm: showInsuranceForm,
			hideInsuranceForm: hideInsuranceForm,
			checkDeliveryDaysTo: checkDeliveryDaysTo,
			checkDeliveryDaysFrom: checkDeliveryDaysFrom,
			addInsuranceOption: addInsuranceOption.bind(null, shipperOfferFormContainer),
			addShipperRateOption: addShipperRateOption.bind(null, shippingRatesTable),
			deleteInsuranceOption: deleteInsuranceOption.bind(null, shipperOfferFormContainer),
			deleteShipperRateOption: deleteShipperRateOption.bind(null, shippingRatesContainer),
		}, false);
	});
</script>
