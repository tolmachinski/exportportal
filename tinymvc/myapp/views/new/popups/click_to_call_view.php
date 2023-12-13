<form
    id="click-to-call-popup"
    method="POST"
    data-js-action="click-to-call-popup:form-submit"
    data-phone-block="#click-to-call-popup--form-field--phohe-container"
    data-time-zone="#click-to-call-popup--form-field--time-zone-select"
    data-counter-textarea="#click-to-call-popup--form-field--counter-textarea"
    <?php echo addQaUniqueIdentifier('global__click-to-call-popup_form'); ?>>
    <?php if(!logged_in()){?>
    <div class="click-to-call-popup__row">
        <div class="click-to-call-popup__col-50pr">
            <div class="form-group">
                <label class="ep-label ep-label--popup ep-label--required">
                    <?php echo translate('click_to_call_popup_fname_label'); ?>
                </label>
                <input
                    class="ep-input ep-input--popup validate[required,custom[validUserName],minSize[2],maxSize[50]]"
                    type="text"
                    name="fname"
                    placeholder="<?php echo translate('click_to_call_popup_fname_placeholder'); ?>"
                    data-prompt-position="bottomLeft:0"
                    <?php echo addQaUniqueIdentifier('global__click-to-call-popup_form_fname_popup'); ?>>
            </div>
        </div>

        <div class="click-to-call-popup__col-50pr">
            <div class="form-group">
                <label class="ep-label ep-label--popup ep-label--required">
                    <?php echo translate('click_to_call_popup_lname_label'); ?>
                </label>
                <input
                    class="ep-input ep-input--popup validate[required,custom[validUserName],minSize[2],maxSize[50]]"
                    type="text"
                    name="lname"
                    placeholder="<?php echo translate('click_to_call_popup_lname_placeholder'); ?>"
                    data-prompt-position="bottomLeft:0"
                    <?php echo addQaUniqueIdentifier('global__click-to-call-popup_form_lname_popup'); ?>>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="ep-label ep-label--popup ep-label--required">
            <?php echo translate('click_to_call_popup_email_label'); ?>
        </label>
        <input
            class="ep-input ep-input--popup validate[required,custom[noWhitespaces],custom[emailWithWhitespaces],maxSize[100]]"
            type="text"
            name="email"
            placeholder="<?php echo translate('click_to_call_popup_email_placeholder'); ?>"
            <?php echo addQaUniqueIdentifier('global__click-to-call-popup_form_email_popup'); ?>>
    </div>
    <?php } ?>

    <div class="form-group">
        <label class="ep-label ep-label--popup ep-label--required">
            <?php echo translate('click_to_call_popup_time_zone_label'); ?>
        </label>
        <select
            id="click-to-call-popup--form-field--time-zone-select"
            class="js-click-to-call-popup-input-time-zone ep-select ep-select--popup validate[required]"
            name="timezone"
            <?php echo addQaUniqueIdentifier('global__click-to-call-popup_form_country_popup'); ?>>
            <?php if (isset($timezones)) {
                foreach ($timezones as $timezone) { ?>
                <option value="<?php echo $timezone['id']; ?>" ><?php echo $timezone['name_country']; ?> (UTC<?php echo (float) $timezone['hours'] <= 0 ? (float) $timezone['hours'] : sprintf('%+d', $timezone['hours']); ?>)</option>
                <?php }
            } ?>
        </select>
    </div>

    <div
        id="click-to-call-popup--form-field--phohe-container"
        class="click-to-call-popup__phone js-select-phone-codes-lazy-block inputs-40"
        data-list-field="#click-to-call--popup-country-code-select"
        data-number-field="#click-to-call--popup-phone-number"
        data-parent=".click-to-call-popup__phone"
    >
        <label class="ep-label ep-label--popup ep-label--required">
            <?php echo translate('click_to_call_popup_phone_label'); ?>
        </label>

        <div class="input-group">
            <div
                class="input-group-prepend wr-select2-h50 select-country-code-group"
                <?php echo addQaUniqueIdentifier('global__click-to-call-popup_form_phone-select_popup'); ?>>
                <div class="notranslate">
                    <select
                        id="click-to-call--popup-country-code-select"
                        class="ep-select ep-select--popup select-country-code validate[required]"
                        name="phone_code"
                        data-validation-type="phoneCodeListType"
                        data-validation-rules="validate[required]"
                        data-validation-container="click-to-call-popup--form-field--phohe-container"
                        data-override-location="true">
                        <?php /** @var array<\App\Common\Contracts\Entities\CountryCodeInterface|\App\Common\Contracts\Entities\Phone\PatternsAwareInterface> $phoneCodes */ ?>
                        <?php foreach ($phoneCodes as $phoneCode) { ?>
                        <option
                            value="<?php echo cleanOutput($phoneCode->getId()); ?>"
                            data-phone-mask="<?php echo cleanOutput($phoneCode->getPattern(\App\Common\Contracts\Entities\Phone\PatternsAwareInterface::PATTERN_INTERNATIONAL_MASK)); ?>"
                            data-country-flag="<?php echo cleanOutput(getCountryFlag($phoneCodeCountry = $phoneCode->getCountry()->getName())); ?>" data-country-name="<?php echo cleanOutput($phoneCodeCountry); ?>"
                            data-country="<?php echo cleanOutput($phoneCode->getCountry()->getId()); ?>"
                            data-code="<?php echo cleanOutput($phoneCode->getName()); ?>"
                            <?php if ($selectedPhoneCode && $selectedPhoneCode->getId() === $phoneCode->getId()) { ?>
                                selected
                            <?php } ?>>
                            <?php echo cleanOutput(trim("{$phoneCode->getName()} {$phoneCodeCountry}")); ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <input
                id="click-to-call--popup-phone-number"
                class="ep-input ep-input--popup validate[required]"
                type="text"
                maxlength="25"
                name="phone"
                placeholder="<?php echo translate('click_to_call_popup_phone_placeholder'); ?>"
                <?php if (null !== $selectedCode) { ?>
                data-current-mask="<?php echo cleanOutput($selectedCode->getPattern(\App\Common\Contracts\Entities\Phone\PatternsAwareInterface::PATTERN_INTERNATIONAL_MASK)); ?>"
                <?php } ?>
                <?php echo addQaUniqueIdentifier('global__click-to-call-popup_form_phone_popup'); ?>
            >
        </div>
    </div>

    <label
        class="custom-checkbox"
        <?php echo addQaUniqueIdentifier('global__click-to-call-popup_form_whats-app-checkbox'); ?>>
        <input
            name="whatsapp"
            type="checkbox"
            value="1"
            <?php echo addQaUniqueIdentifier('global__click-to-call-popup_form_whats-app-input-checkbox'); ?>>
        <span class="custom-checkbox__text"><?php echo translate('click_to_call_popup_checkbox_text'); ?></span>
    </label>

    <div class="form-group click-to-call-popup__textarea">
        <label class="ep-label ep-label--popup ep-label--required" for="click-to-call-popup--form-field--counter-textarea">
            <?php echo translate('click_to_call_popup_detail_request_label'); ?>
        </label>
        <textarea
            id="click-to-call-popup--form-field--counter-textarea"
            class="ep-textarea validate[required, maxSize[250]] textcounter_contact-message"
            data-max="250"
            name="comment"
            data-label="<?php echo translate('help_contact_us_form_label_message', null, true); ?>"
            placeholder="<?php echo translate('click_to_call_popup_detail_request_placeholder', null, true); ?>"
            <?php echo addQaUniqueIdentifier('global__contact-us_message-textarea'); ?>></textarea>
    </div>

    <div class="click-to-call-popup__btn-wr inputs-40">
        <button
            class="btn btn-primary click-to-call-popup__submit-btn"
            type="submit"
            <?php echo addQaUniqueIdentifier('global__click-to-call-popup_form_submit-btn_popup'); ?>>
            <?php echo translate('general_modal_button_submit_text'); ?>
        </button>
    </div>
</form>
