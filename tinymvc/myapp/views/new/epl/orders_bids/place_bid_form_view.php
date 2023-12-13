<div class="wr-modal-flex inputs-40" id="upcoming-orders-bid--wrapper">
    <form class="modal-flex__form validateModal" action="<?php echo $action; ?>" id="upcoming-orders-bid">
        <input type="hidden" name="order" value="<?php echo $order['id']; ?>">
		<div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div id="upcoming-orders-bid--fields-container">
                    <ul id="js-upcoming-orders-navs" class="nav nav-tabs display-n" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" href="#upcoming-orders-bid-step-1" aria-controls="title" role="tab" data-toggle="tab">Step 1</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#upcoming-orders-bid-step-2" aria-controls="title" role="tab" data-toggle="tab">Step 2</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane fade show active" id="upcoming-orders-bid-step-1">
                            <div class="row">
                                <div class="col-12 col-lg-6 mb-15">
                                    <label class="input-label mt-0">Order</label>
                                    <?php echo orderNumber($order['id']); ?>
                                </div>

                                <div class="col-12 col-lg-6 mb-15">
                                    <label class="input-label mt-0">Incoterms</label>
                                    <?php echo cleanOutput($shipment_type['type_name']); ?>
                                    <a class="info-dialog"
                                        data-message="<?php echo cleanOutput($shipment_type['type_description']); ?>"
                                        data-title="<?php echo cleanOutput($shipment_type['type_name']); ?>"
                                        title="<?php echo cleanOutput($shipment_type['type_name']); ?>">
                                        <i class="ep-icon ep-icon_info fs-16"></i>
                                    </a>
                                </div>

                                <div class="col-12">
                                    <label class="input-label input-label--required">Delivery time (days)</label>
                                    <div class="input-group">
                                        <input type="text"
                                            id="upcoming-orders-bid--formfield--delivery-days-start"
                                            name="delivery_days_from"
                                            class="form-control form-control--radius-left validate[required,min[1],custom[natural,funcCall[maxDeliveryDays]],max[<?php echo config('ep_shippers_max_delivery_days', 180);?>]]"
                                            placeholder="From">
                                        <label class="input-group-append"><span class="input-group-text">&mdash;</span></label>
                                        <input type="text"
                                            id="upcoming-orders-bid--formfield--delivery-days-end"
                                            name="delivery_days_to"
                                            class="form-control validate[required,min[1],custom[natural,funcCall[minDeliveryDays]],max[<?php echo config('ep_shippers_max_delivery_days', 180);?>]]"
                                            placeholder="To">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="input-label input-label--required">Shipping price ($, USD)</label>
                                    <input type="number"
                                        name="price"
                                        step="0.01"
                                        id="upcoming-orders-bid--formfield--price"
                                        class="validate[required,custom[positive_number],min[0.01]]"
                                        placeholder="e.g.: 1.00">
                                </div>

                                <div class="col-12 col-lg-6">
                                    <label class="input-label input-label--required">Freight Forwarder</label>
                                    <input type="text"
                                        name="shipment_ff"
                                        id="upcoming-orders-bid--formfield--shipment-ff"
                                        class="validate[required,maxSize[250]]"
                                        placeholder="Who arranges transport of goods?">
                                </div>

                                <div class="col-12 col-lg-6">
                                    <label class="input-label input-label--required">Container Freight Station</label>
                                    <input type="text"
                                        name="shipment_cfs"
                                        id="upcoming-orders-bid--formfield--shipment-cfs"
                                        class="validate[required,maxSize[250]]"
                                        placeholder="Place or depot">
                                </div>

                                <div class="col-12">
                                    <label class="input-label input-label--required">Who passes goods to freight forwarder?</label>
                                    <select class="validate[required]" name="shipment_pickup" id="upcoming-orders-bid--formfield--shipment-pickup">
                                        <option value disabled selected>Select the one who passes goods</option>
                                        <option value="shipper">Freight Forwarder</option>
                                        <option value="seller">Seller</option>
                                    </select>
                                </div>

                                <?php views()->display('new/epl/orders_bids/partials/date_field_view', array(
                                    'placeholder' => "e.g. 01/20/2019",
                                    'min_date'    => $min_date,
                                    'format'      => App\Common\PUBLIC_DATE_FORMAT,
                                    'title'       => 'Getting the goods from seller until',
                                    'name'        => 'pickup_date',
                                    'bind'        => array(
                                        'id'   => 'upcoming-orders-bid--formfield--delivery-start-date',
                                        'type' => 'min',
                                    ),
                                    'id'          => 'upcoming-orders-bid--formfield--pickup-date',
                                )); ?>

                                <?php views()->display('new/epl/orders_bids/partials/date_field_view', array(
                                    'placeholder' => "e.g. 01/20/2019",
                                    'min_date'    => $min_date,
                                    'format'      => App\Common\PUBLIC_DATE_FORMAT,
                                    'title'       => 'When you can start delivery?',
                                    'name'        => 'delivery_date',
                                    'bind'        => array(
                                        'id'   => 'upcoming-orders-bid--formfield--pickup-date',
                                        'type' => 'max',
                                    ),
                                    'id'          => 'upcoming-orders-bid--formfield--delivery-start-date',
                                )); ?>

                                <div class="col-12">
                                    <label class="input-label">Notes</label>
                                    <textarea name="comment"
                                        id="upcoming-orders-bid--formfield--notes"
                                        class="validate[maxSize[500]] textcounter-shipper_comment"
                                        data-max="500"
                                        placeholder="Write your notes here..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div role="tabpanel" class="tab-pane fade" id="upcoming-orders-bid-step-2">
                            <div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span>Providing cargo insurance that will cover and compensate the items against damage or loss is mandatory. Please add insurance options with price and coverage amount.</span></div>

                            <table id="upcoming-orders-bid--formfield--additional-items" class="main-data-table mt-15">
                                <thead>
                                    <tr>
                                        <th colspan="2">Insurance description</th>
                                        <th class="w-200">Amount, in USD</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="3">
                                            No shipping insurance options
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class=" mt-15">
                                <span id="upcoming-orders-bid--formaction--add-insurance"
                                    class="btn btn-dark call-function pull-right"
                                    data-callback="showInsuranceOptionForm">
                                    Add insurance option
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

		<div id="upcoming-orders-bid-step-1-actions" class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary call-function" data-callback="nextAddBit" type="button">Next</button>
            </div>
        </div>

		<div id="upcoming-orders-bid-step-2-actions" class="modal-flex__btns" style="display: none;">
            <div class="modal-flex__btns-left">
                <button class="btn btn-dark call-function" data-callback="prevAddBit" type="button">Back</button>
            </div>

            <div class="modal-flex__btns-right">
                <button class="btn btn-success" type="submit">Bid</button>
            </div>
		</div>
	</form>
</div>

<script type="text/template" id="js-upcoming-orders-bid--formtemplate--add-insurance">
    <form class="validateModalAddInsurance">
        <label class="input-label input-label--required">Name</label>
        <input
            class="validate[required,custom[productTitle],maxSize[100]]"
            type="text"
            maxlength="100"
            name="insurance_option_name"
            placeholder="Enter the insurance name">

        <label class="input-label input-label--required">Amount, in USD</label>
        <input
            class="validate[required,custom[noWhitespaces],custom[positive_number],min[0],max[999999]]"
            type="number"
            step="0.01"
            name="insurance_option_amount"
            placeholder="Enter the insurance price">

        <label class="input-label input-label--required">Description</label>
        <textarea
            class="validate[required,maxSize[1000]] textcounter-insurance_option_description"
            name="insurance_option_description"
            data-max="1000"
            placeholder="Write insurance description here..."></textarea>
    </form>
</script>

<script type="text/template" id="js-upcoming-orders-bid--formtemplate--add-insurance-actions">
    <button class="btn btn-dark w-130 call-function" data-callback="hideInsuranceOptionForm" type="button">Cancel</button>
    <button class="btn btn-primary w-130 call-function" data-callback="addInsuranceOption" type="button">Add</button>
</script>

<script type="text/template" id="upcoming-orders-bid--formtemplate--added-insurance">
	<tr class="insurance">
		<td class="w-40">
            <span class="cur-pointer ep-icon ep-icon_remove-stroke fs-14 vat lh-20 confirm-dialog"
                data-callback="deleteInsuranceOption"
                data-message="Are you sure you want to remove this row?"
                title="Delete row">
            </span>
        </td>

		<td data-title="Insurance description">
            <strong title="{{description}}">{{title}}</strong>
			<input type="hidden" name="insurance_option[{{hash}}][title]" value="{{title}}">
			<input type="hidden" name="insurance_option[{{hash}}][amount]" value="{{amount}}">
			<input type="hidden" name="insurance_option[{{hash}}][description]" value="{{description}}">
        </td>

		<td data-title="Amount, in USD">
			{{amountLabel}}
		</td>
	</tr>
</script>

<script>
$(function() {
    var navCurrentIndex = 0;
    var form = $('#upcoming-orders-bid');
    var notesField = $('#upcoming-orders-bid--formfield--notes');
    var formWrapper = $('#upcoming-orders-bid--wrapper');
    var fieldsContainer = $('#upcoming-orders-bid--fields-container');
    var deliveryDaysEndField = $('#upcoming-orders-bid--formfield--delivery-days-end');
    var deliveryDaysStartField = $('#upcoming-orders-bid--formfield--delivery-days-start');
    var insuranceOptionsTable = $('#upcoming-orders-bid--formfield--additional-items');
    var insuranceOptionsButton = $('#upcoming-orders-bid--formaction--add-insurance');
    var insuranceOptionsFormTemplate = $('#js-upcoming-orders-bid--formtemplate--add-insurance').html() || null;
    var insuranceOptionsFormTemplateActions = $('#js-upcoming-orders-bid--formtemplate--add-insurance-actions').html() || null;
    var insuranceOptionsTemplate = $('#upcoming-orders-bid--formtemplate--added-insurance').text() || null;
    var onSaveContent = function (wrapper, formElement) {
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
                callFunction('callbackPlaceBid', document, data);
                closeFancyBox();
            }
        };

        beforeSend();
        sendRequest(url, formData).done(onRequestSuccess.bind(null, document)).fail(onRequestError).always(onRequestEnd);
    };

    var onShowInsuranceOptionForm = function () {
        disableSubmit();

        BootstrapDialog.show({
            cssClass: 'info-bootstrap-dialog inputs-40',
            title: 'Add insurance option',
            message: $('<div class="ep-tinymce-text"></div>'),
            onshow: function(dialog) {
                var $modal_dialog = dialog.getModalDialog();
                $modal_dialog.addClass('modal-dialog-centered');

                dialog.getModalBody().html(insuranceOptionsFormTemplate);
                limitTextLength(dialog.getModalBody().find('[name=insurance_option_description]'));
                dialog.getModalFooter().html(insuranceOptionsFormTemplateActions).css({display: ''});

                dialog.getModalBody().find(".validateModalAddInsurance").validationEngine('detach');
                dialog.getModalBody().find(".validateModalAddInsurance").validationEngine('attach', {
					promptPosition : "topLeft:0",
					autoPositionUpdate : true,
					focusFirstField: false,
					scroll: false,
					showArrow : false,
					addFailureCssClassToField : 'validengine-border',
					onValidationComplete: function(form, status){
						if(status){
							submitAddInsuranceOption($(form));
						}else{
							systemMessages(translate_js({ plug: 'general_i18n', text: 'validate_error_message' }), 'error');
						}
					}
				});
            },
            type: 'type-light',
            size: 'size-wide',
            closable: true,
            closeByBackdrop: false,
            closeByKeyboard: false,
            draggable: false,
            animate: true,
            nl2br: false
        });
    };

    var onHideInsuranceOptionForm = function () {
        BootstrapDialog.closeAll();
        enableSubmit();
    };

    var onDeleteInsuranceOption = function (button) {
        var self = $(button);
        var row = self.closest('tr');
        var tableBody = insuranceOptionsTable.find('tbody');
        var originalRows = tableBody.find('tr').not('.insurance');
        var insuranceRows = tableBody.find('tr.insurance').not(row);
        if (insuranceRows.length === 0 && originalRows.length !== 0){
            originalRows.show();
        }

        row.remove();
    };

    var onAddInsuranceOption = function () {
        $('.modal-body .validateModalAddInsurance').submit();
    }

    var submitAddInsuranceOption = function ($form) {
        var insuranceInputs = $form.find('[name^="insurance_option_"]');
        var isValidated = insuranceInputs.toArray().reduce(function(accumulator, formfield) {
            var fieldIsValid = $(formfield).validationEngine("validate");

            return accumulator && fieldIsValid;
        }, true);

        if(!isValidated) {
            return;
        }

        var tableBody = insuranceOptionsTable.find('tbody');
        var originalRows = tableBody.find('tr').not('.insurance');
        if (originalRows.length !== 0){
            originalRows.hide();
        }

        var hash = uniqid('insurance-option-');
        var insuranceOption = insuranceOptionsTemplate;
        var insuranceAmount = parseFloat(insuranceInputs.filter('[name="insurance_option_amount"]').val());
        var content = {
            hash: hash,
            title: htmlEscape(insuranceInputs.filter('[name="insurance_option_name"]').val() || ''),
            amount: insuranceAmount,
            amountLabel: get_price(insuranceAmount, false),
            description: htmlEscape(insuranceInputs.filter('[name="insurance_option_description"]').val() || ''),
        };

        for (var key in content) {
            if (content.hasOwnProperty(key)) {
                insuranceOption = insuranceOption.replace(new RegExp('{{' + key + '}}', 'g'), content[key])
            }
        }

        tableBody.append($(insuranceOption));
        onHideInsuranceOptionForm();
    };
    var disableSubmit = function () {
        form.find('button[type=submit]').prop('disabled', true);
    };
    var enableSubmit = function () {
        form.find('button[type=submit]').prop('disabled', false);
    };
    var checkDeliveryDaysFrom = function(field, rules, i, options) {
        var deliveryDaysToValue = deliveryDaysEndField.val();
        var deliveryDaysFromValue = field.val();
        if (deliveryDaysFromValue != '' && deliveryDaysToValue != '' && (intval(deliveryDaysFromValue) > intval(deliveryDaysToValue))) {
            return "- Delivery time From must be less than or equal Delivery time To.";
        }
    };
    var checkDeliveryDaysTo = function(field, rules, i, options){
        var deliveryDaysToValue = field.val();
        var deliveryDaysFromValue = deliveryDaysStartField.val();
        if(deliveryDaysToValue != '' && deliveryDaysFromValue != '' && (intval(deliveryDaysToValue) < intval(deliveryDaysFromValue))){
            return "- Delivery time To must be greater than or equal Delivery time From.";
        }
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
    var cleanOrientationChangeHandler = function (element) {
        $(element).off('resizestop', onOrientationChange);
    };
    var onOrientationChange = function () {
        var normalize = function () {
            normalizeTables(insuranceOptionsTable);
        };

        if (!$('body').find('#' + form.attr('id')).length) {
            cleanOrientationChangeHandler(this);

            return;
        }

        normalize();
        setTimeout(normalize, 500);
    };
    var limitTextLength = function (textarea) {
        textarea.textcounter({
            countDown: true,
            countDownTextBefore: translate_js({plug:'textcounter', text: 'count_down_text_before'}),
            countDownTextAfter: translate_js({plug:'textcounter', text: 'count_down_text_after'})
        });
    };

    $('#js-upcoming-orders-navs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		e.target // newly activated tab
		e.relatedTarget // previous active tab

		var $this = $(e.target);
		var $navCurrent = $this.closest('.nav-item');
		navCurrentIndex = $navCurrent.index();
		$this.removeClass('required');

		if(navCurrentIndex == 0){
			$('#upcoming-orders-bid-step-1-actions').css({'display': 'flex'});
			$('#upcoming-orders-bid-step-2-actions').hide();
		}else{
			$('#upcoming-orders-bid-step-1-actions').hide();
			$('#upcoming-orders-bid-step-2-actions').css({'display': 'flex'});
		}
	});

    var clickNextAddBit = function(){
        var $caller_btn = $('#upcoming-orders-bid button[type=submit]');

        $('.validateModal').validationEngine('validate', {
            updatePromptsPosition:true,
            promptPosition : "topLeft:0",
            autoPositionUpdate : true,
            focusFirstField: false,
            scroll: false,
            showArrow : false,
            addFailureCssClassToField : 'validengine-border',
            onValidationComplete: function(form, status){
                if(status){
                    if(navCurrentIndex == 0){
                        $('#js-upcoming-orders-navs .active').closest('.nav-item').next('.nav-item').find('.nav-link').trigger('click');
                    }else if(navCurrentIndex == 1){

                        var tableBody = insuranceOptionsTable.find('tbody');
                        var originalRows = tableBody.find('tr.insurance');
                        if (originalRows.length == 0){
                            systemMessages('At least one insurance option is required.', 'error');
                            return false;
                        }

                        if($(form).data("callback") != undefined){
                            window[$(form).data("callback")](form, $caller_btn);
                        }else{
                            modalFormCallBack(form, $caller_btn);
                        }
                    }
                }else{
                    systemMessages(translate_js({ plug: 'general_i18n', text: 'validate_error_message' }), 'error');
                    return false;
                }
            }
        });
    }

    var clickPrevAddBit = function(){
        $('#js-upcoming-orders-navs .active').closest('.nav-item').prev('.nav-item').find('.nav-link').trigger('click');
    }

    var validateTabInit = function(){
        $('.validateModalTabs').validationEngine("detach");
        $('.validateModalTabs').validationEngine("attach", {
            updatePromptsPosition:true,
            promptPosition : "topLeft:0",
            autoPositionUpdate : true,
            focusFirstField: false,
            scroll: false,
            showArrow : false,
            addFailureCssClassToField : 'validengine-border',
            onValidationComplete: function(form, status){
                if(status){
                    if($(form).data("callback") != undefined)
                        window[$(form).data("callback")](form, $caller_btn);
                    else
                        modalFormCallBack(form, $caller_btn);
                }else{
                    systemMessages(translate_js({ plug: 'general_i18n', text: 'validate_error_message' }), 'error');
                }
            }
        });
    }

    $(window).on('resizestop', onOrientationChange);

    mobileDataTable(insuranceOptionsTable, false);
    normalizeTables(insuranceOptionsTable);
    limitTextLength(notesField);
    mix(window, {
        modalFormCallBack: onSaveContent.bind(null, formWrapper),
        showInsuranceOptionForm: onShowInsuranceOptionForm,
        hideInsuranceOptionForm: onHideInsuranceOptionForm,
        deleteInsuranceOption: onDeleteInsuranceOption,
        addInsuranceOption: onAddInsuranceOption,
        maxDeliveryDays: checkDeliveryDaysFrom,
        minDeliveryDays: checkDeliveryDaysTo,
        nextAddBit: clickNextAddBit,
        prevAddBit: clickPrevAddBit,
    }, false);
});
</script>
