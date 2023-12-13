<div class="js-modal-flex wr-modal-flex inputs-40">
    <form
        id="add-service--form"
        class="modal-flex__form validateModal"
        data-callback="sellerServicesMyAddServiceFormCallBack"
        action="<?php echo $action; ?>"
    >
        <div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div class="row">
                    <div class="col-12">
                        <label class="input-label input-label--required">
                            <?php echo translate('company_services_dashboard_modal_field_name_label_text'); ?>
                        </label>
                        <input type="text"
                            maxlength="50"
                            name="title"
                            id="add-service--formfield--name"
                            class="validate[required,maxSize[50]]"
                            placeholder="<?php echo translate('company_services_dashboard_modal_field_name_placeholder_text', null, true); ?>">
                    </div>

                    <div class="col-12 col-md-6 wr-select2-h50">
                        <label class="input-label input-label--required">
                            <?php echo translate('company_services_dashboard_modal_field_phone_code_label_text'); ?>
                        </label>
                        <select name="phone_code" class="form-control validate[required]" id="add-service--formfield--phone-codes">
                            <option></option>
                            <?php /** @var array<\App\Common\Contracts\Entities\CountryCodeInterface> $phone_codes */ ?>
                            <?php foreach($phone_codes as $phone_code) { ?>
                                <option
                                    value="<?php echo cleanOutput($phone_code->getId()); ?>"
                                    data-country-flag="<?php echo cleanOutput(getCountryFlag($phone_code_country = $phone_code->getCountry()->getName())); ?>"
                                    data-country-name="<?php echo cleanOutput($phone_code_country); ?>"
                                    data-country="<?php echo cleanOutput($phone_code->getCountry()->getId()); ?>"
                                    <?php if ($selected_phone_code && $selected_phone_code->getId() === $phone_code->getId()) { ?>selected<?php } ?>>
                                    <?php echo cleanOutput(trim("{$phone_code->getName()} {$phone_code_country}")); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="input-label input-label--required">
                            <?php echo translate('company_services_dashboard_modal_field_phone_label_text'); ?>
                        </label>
                        <input type="text"
                            maxlength="60"
                            name="phone"
                            id="add-service--formfield--phone"
                            class="validate[required,custom[phoneNumber],maxSize[60]]"
                            placeholder="<?php echo translate('company_services_dashboard_modal_field_phone_placeholder_text', null, true); ?>">
                    </div>

                    <div class="col-12">
                        <label class="input-label input-label--required">
                            <?php echo translate('company_services_dashboard_modal_field_email_label_text'); ?>
                        </label>
                        <input type="text"
                            maxlength="100"
                            name="email"
                            id="add-service--formfield--email"
                            class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces],maxSize[100]]"
                            placeholder="<?php echo translate('company_services_dashboard_modal_field_email_placeholder_text', null, true); ?>">
                    </div>

                    <div class="col-12">
                        <label class="input-label input-label--required">
                            <?php echo translate('company_services_dashboard_modal_field_description_label_text'); ?>
                        </label>
                        <textarea name="text"
                            id="add-service--formfield--description"
                            class="validate[required,maxSize[500]] h-80 textcounter"
                            data-max="500"
                            placeholder="<?php echo translate('company_services_dashboard_modal_field_description_placeholder_text', null, true); ?>"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit" id="add-service--formaction--submit">
                    <?php echo translate('general_modal_button_save_text'); ?>
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
            var form = $(formElement);
            var wrapper = form.closest('.js-modal-flex');
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
            var onRequestSuccess = function(data){
                hideLoader(wrapper);
                systemMessages(data.message, data.mess_type);
                if(data.mess_type === 'success'){
                    closeFancyBox();
                    callFunction('callbackAddServiceBlock', data);
                }
            };

            beforeSend();
            sendRequest(url, formData).done(onRequestSuccess).fail(onRequestError).always(onRequestEnd);
        };

        var description = $('#add-service--formfield--description');
        var phoneCodes = $('#add-service--formfield--phone-codes');
        var counterOptions = {
            countDown: true,
            countDownTextBefore: translate_js({plug: 'textcounter', text: 'count_down_text_before'}),
            countDownTextAfter: translate_js({plug: 'textcounter', text: 'count_down_text_after'})
        };
        var phoneCodesOptions = {
            language: __site_lang,
            templateResult: formatCcode,
            placeholder: "Select country code",
            dropdownAutoWidth : true,
            theme: "default ep-select2-h30",
            width: '100%',
        };
        if(description.length) {
            description.textcounter(counterOptions);
        }
        if(phoneCodes.length) {
            $.valHooks.select2 = {
                get: function (el) {
                    return phoneCodes.val() || '';
                }
            };

            phoneCodes.select2(phoneCodesOptions)
                .data('select2')
                .$container
                    .attr('id', 'add-service--formfield--tags-container')
                    .addClass('validate[required]')
                    .setValHookType('select2');
        }

        window.sellerServicesMyAddServiceFormCallBack = onSaveContent;
    });
</script>
