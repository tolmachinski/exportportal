<?php tmvc::instance()->controller->view->display('new/google_recaptcha/script_inclusions');?>

<div class="js-modal-flex wr-modal-flex inputs-40">
    <form class="modal-flex__form validateModal" action="<?php echo $action; ?>" id="attend-event--form">
        <input type="hidden" name="id_event" value="<?php echo $id_event; ?>">
        <div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <label class="input-label input-label--required">
                            <?php echo translate('cr_events_dashboard_modal_event_field_first_name_label_text'); ?>
                        </label>
                        <input type="text"
                            maxlength="250"
                            name="first_name"
                            id="attend-event--formfield--first-name"
                            class="validate[required,maxSize[250]]"
                            placeholder="<?php echo translate('cr_events_dashboard_modal_event_field_first_name_placeholder_text', null, true); ?>">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="input-label input-label--required">
                            <?php echo translate('cr_events_dashboard_modal_event_field_last_name_label_text'); ?>
                        </label>
                        <input type="text"
                            maxlength="250"
                            name="last_name"
                            id="attend-event--formfield--last-name"
                            class="validate[required,maxSize[250]]"
                            placeholder="<?php echo translate('cr_events_dashboard_modal_event_field_last_name_placeholder_text', null, true); ?>">
                    </div>

                    <div class="col-12 col-md-6 wr-select2-h50">
                        <label class="input-label input-label--required">
                            <?php echo translate('cr_events_dashboard_modal_event_field_phone_code_label_text'); ?>
                        </label>
                        <select name="phone_code" class="form-control validate[required,maxSize[15]]" id="attend-event--formfield--phone-codes">
                            <option></option>
                            <?php /** @var array<\App\Common\Contracts\Entities\CountryCodeInterface> $phone_codes */ ?>
                            <?php foreach($phone_codes as $phone_code) { ?>
                                <option
                                    value="<?php echo cleanOutput($phone_code->getId()); ?>"
                                    data-country-flag="<?php echo cleanOutput(getCountryFlag($phone_code_country = $phone_code->getCountry()->getName())); ?>"
                                    data-country-name="<?php echo cleanOutput($phone_code_country); ?>"
                                    data-country="<?php echo cleanOutput($phone_code->getCountry()->getId()); ?>">
                                    <?php echo cleanOutput(trim("{$phone_code->getName()} {$phone_code_country}")); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="input-label input-label--required">
                            <?php echo translate('cr_events_dashboard_modal_event_field_phone_label_text'); ?>
                        </label>
                        <input type="text"
                            maxlength="100"
                            name="phone"
                            id="attend-event--formfield--phone"
                            class="validate[required,custom[phoneNumber],maxSize[100]]"
                            placeholder="<?php echo translate('cr_events_dashboard_modal_event_field_phone_placeholder_text', null, true); ?>">
                    </div>

                    <div class="col-12">
                        <label class="input-label input-label--required">
                            <?php echo translate('cr_events_dashboard_modal_event_field_email_label_text'); ?>
                        </label>
                        <input type="text"
                            maxlength="250"
                            name="email"
                            id="attend-event--formfield--email"
                            class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces],maxSize[250]]"
                            placeholder="<?php echo translate('cr_events_dashboard_modal_event_field_email_placeholder_text', null, true); ?>">
                    </div>
                </div>
            </div>
        </div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit" id="attend-event--formaction--submit">
                    <?php echo translate('cr_events_dashboard_modal_event_buttons_submit_text'); ?>
                </button>
            </div>
		</div>
    </form>
</div>

<script type="application/javascript">
    $(function() {
        $.fn.setValHookType = function (type) {
            this.each(function () {
                this.type = type;
            });

            return this;
        };

        var onSaveContent = function(formElement) {
            googleRecaptchaValidation(recaptcha_parameters, formElement).then(function(form) {
                var wrapper = form.closest('.js-modal-flex');
                var submitButton = form.find('button[type=submit]');
                var formData = form.serializeArray();
                var url = form.attr('action');
                var sendRequest = function (url, data) {
                    return $.ajax({
                        method: 'POST',
                        crossDomain: true,
                        headers: { "X-Requested-With": "XMLHttpRequest" },
                        dataType: 'json',
                        url: url,
                        data: data,
                    });
                };
                var beforeSend = function() {
                    showLoader(wrapper);
                    submitButton.addClass('disabled');
                };
                var onRequestEnd = function() {
                    hideLoader(wrapper);
                    submitButton.removeClass('disabled');
                };
                var onRequestSuccess = function(data) {
                    hideLoader(wrapper);
                    systemMessages(data.message, data.mess_type);
                    if(data.mess_type === 'success') {
                        closeFancyBox();
                        callFunction('callbackAttendEvent', data);
                    }
                };

                beforeSend();
                sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
            });
        };

        var phoneCodes = $('#attend-event--formfield--phone-codes');
        var phoneCodesOptions = {
            language: __site_lang,
            placeholder: phoneCodes.data('placeholder') || null,
            templateResult: formatCcode,
			placeholder: "Select country code",
            dropdownAutoWidth : true,
            theme: "default ep-select2-h30",
            width: '100%',
        };
        if(phoneCodes.length) {
            $.valHooks.select2 = {
                get: function (el) {
                    return phoneCodes.val() || '';
                }
            };

            phoneCodes.select2(phoneCodesOptions)
                .data('select2')
                .$container
                    .attr('id', 'attend-event--formfield--tags-container')
                    .addClass('validate[required]')
                    .setValHookType('select2');
        }

        window.modalFormCallBack = onSaveContent;
    });
</script>
