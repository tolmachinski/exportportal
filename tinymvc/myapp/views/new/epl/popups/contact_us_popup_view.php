<?php $qaPopup = isset($contactUsNotModal) && (int) $contactUsNotModal === 1 ? '' : '_popup'; ?>
<div class="wr-modal-flex contact-us-form">
    <form class="modal-flex__form captcha__form js-contact-us-form" autocomplete="off">
        <div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div class="contact-us-form__row">
                    <?php if (!logged_in()) { ?>
                        <div class="contact-us-form__col form-group">
                            <label class="input-label" for="fname"><?php echo translate('contact_page_first_name');?></label>
                            <input
                                id="fname"
                                type="text"
                                name="fname"
                                placeholder="<?php echo translate('contact_enter_name_placeholder', null, true);?>"
                                data-error-position="bottom"
                                autocomplete="nope"
                                autocapitalize="off"
                                autocorrect="off"
                                autofill="off"
                                <?php echo addQaUniqueIdentifier("global__contact-us_fname-input{$qaPopup}"); ?>
                            >
                            <input autocomplete="off" type="text" class="hidden">
                        </div>

                        <div class="contact-us-form__col form-group">
                            <label class="input-label" for="lname"><?php echo translate('contact_page_last_name'); ?></label>
                            <input
                                id="lname"
                                type="text"
                                name="lname"
                                placeholder="<?php echo translate('contact_enter_last_name_placeholder', null, true); ?>"
                                data-error-position="bottom"
                                data-error-position-mobile="top"
                                autocomplete="nope"
                                autocapitalize="off"
                                autocorrect="off"
                                autofill="off"
                                <?php echo addQaUniqueIdentifier("global__contact-us_lname-input{$qaPopup}"); ?>
                            >
                        </div>

                        <div class="contact-us-form__col form-group">
                            <label class="input-label" for="phone"><?php echo translate('contact_page_phone'); ?></label>
                            <div class="input-group js-select2-dropdown-wrapper">
                                <div class="input-group-prepend wr-select2-h50 select-country-code-group">
                                    <div class="notranslate">
                                        <select
                                            id="js-country-code"
                                            class="select-country-code"
                                            name="country_code"
                                            data-hide-error="true"
                                            <?php echo addQaUniqueIdentifier("global__contact-us-form_phone-select_popup"); ?>
                                        >
                                            <?php /** @var array<\App\Common\Contracts\Entities\CountryCodeInterface|\App\Common\Contracts\Entities\Phone\PatternsAwareInterface> $phoneCodes */ ?>
                                            <?php foreach ($phoneCodes as $phoneCode) { ?>
                                                <option
                                                    value="<?php echo cleanOutput($phoneCode->getId()); ?>"
                                                    data-phone-mask="<?php echo cleanOutput($phoneCode->getPattern(\App\Common\Contracts\Entities\Phone\PatternsAwareInterface::PATTERN_INTERNATIONAL_MASK)); ?>"
                                                    data-country-flag="<?php echo cleanOutput(getCountryFlag($phoneCodeCountry = $phoneCode->getCountry()->getName())); ?>"
                                                    data-country-name="<?php echo cleanOutput($phoneCodeCountry); ?>"
                                                    data-country="<?php echo cleanOutput($phoneCode->getCountry()->getId()); ?>"
                                                    data-code="<?php echo cleanOutput($phoneCode->getName()); ?>"
                                                >
                                                    <?php echo cleanOutput(trim("{$phoneCode->getName()} {$phoneCodeCountry}")); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <button
                                            class="select2 select2-container select2-container--default select2-lazy-loader call-action"
                                            type="button"
                                            data-js-action="lazy-loading:select2-phone-number"
                                            dir="ltr"
                                            <?php echo addQaUniqueIdentifier("global__contact-us_select2-laoder-btn{$qaPopup}"); ?>
                                        >
                                            <span class="selection">
                                                <span class="select2-selection select2-selection--single" tabindex="0">
                                                    <span class="select2-selection__rendered" title="+93 Afghanistan">
                                                        <img width="32" height="32" class="select-country-flag" src="<?php echo getCountryFlag('Afghanistan'); ?>" alt="Afghanistan">
                                                        <span class="select-country-code-number">+93</span>
                                                    </span>
                                                    <span class="select2-selection__arrow" role="presentation">
                                                        <b role="presentation"></b>
                                                    </span>
                                                </span>
                                            </span>
                                            <span class="dropdown-wrapper" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </div>
                                <input
                                    id="js-epl-register-phone-number"
                                    class="form-control"
                                    type="tel"
                                    inputmode="decimal"
                                    maxlength="25"
                                    name="phone"
                                    placeholder="<?php echo translate('epl_contact_phone_number_placeholder', null, true); ?>"
                                    data-group="true"
                                    <?php echo addQaUniqueIdentifier("global__contact-us_phone-input{$qaPopup}"); ?>
                                >
                            </div>
                        </div>

                        <div class="contact-us-form__col form-group" for="email">
                            <label class="input-label"><?php echo translate('contact_page_email'); ?></label>
                            <input
                                id="email"
                                type="email"
                                data-label="<?php echo translate('contact_page_email', null, true); ?>"
                                name="from"
                                placeholder="<?php echo translate('help_contact_us_form_label_email_placeholder', null, true); ?>"
                                autocomplete="nope"
                                autocapitalize="off"
                                autocorrect="off"
                                autofill="off"
                                <?php echo addQaUniqueIdentifier("global__contact-us_email-input{$qaPopup}"); ?>
                            >
                        </div>
                    <?php } ?>

                    <div class="contact-us-form__col contact-us-form__col--w100pr form-group">
                        <label class="input-label" for="subject"><?php echo translate('contact_page_subject'); ?></label>
                        <input
                            id="subject"
                            type="text"
                            name="subject"
                            placeholder="<?php echo translate('contact_page_type_subject_placeholder', null, true); ?>"
                            autocomplete="nope"
                            autocapitalize="off"
                            autocorrect="off"
                            autofill="off"
                            <?php if (logged_in()) { ?>data-error-position="bottom"<?php } ?>
                            <?php echo addQaUniqueIdentifier("global__contact-us_subject-input{$qaPopup}"); ?>
                        >
                    </div>
                    <div class="contact-us-form__col contact-us-form__col--w100pr form-group">
                        <label class="input-label" for="message"><?php echo translate('contact_page_message'); ?></label>
                        <textarea
                            id="message"
                            class="js-textcounter-contact-message"
                            data-max="500"
                            name="content"
                            placeholder="<?php echo translate('contact_type_message_placeholder', null, true);?>"
                            <?php echo addQaUniqueIdentifier("global__contact-us_message-textarea{$qaPopup}"); ?>></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-flex__btns modal-flex__btns--200">
            <div class="modal-flex__btns-right">
                <button
                    class="btn btn-primary"
                    type="submit"
                    <?php echo addQaUniqueIdentifier("global__contact-us_submit-btn{$qaPopup}"); ?>
                >
                    <?php echo translate('epl_contact_us_form_submit_btn'); ?>
                </button>
            </div>
        </div>
    </form>
</div>

<?php
    echo dispatchDynamicFragment(
        "epl-contact-us:form-fragment",
        [
            'laoderMessage' => translate('contact_sending_messsage', null, true),
        ]
    );
?>
