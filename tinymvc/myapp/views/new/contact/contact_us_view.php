<?php $qaPopup = isset($contact_us_not_modal) && (int) $contact_us_not_modal === 1 ? '' : '_popup'; ?>
<div class="wr-modal-flex inputs-40">
	<form class="modal-flex__form captcha__form <?php echo isset($contact_us_not_modal) ? "validengine" : "validateModal h-auto flex--0";?>" data-callback="contactUsFormSubmit" data-js-action="contact-us:form-submit">
		<div class="modal-flex__content">
            <div class="container-fluid-modal">
                <div class="row">
                    <?php if(!logged_in()){?>
                        <div class="col-12 col-xl-6">
                            <label class="input-label"><?php echo translate('contact_page_first_name'); ?></label>
                            <input
                                type="text"
                                class="validate[required, custom[validUserName], minSize[2], maxSize[50]]"
                                data-prompt-position="bottomLeft:0"
                                data-label="<?php echo translate('contact_page_first_name', null, true);?>"
                                name="fname"
                                placeholder="<?php echo translate('contact_enter_name_placeholder', null, true); ?>"
                                <?php echo addQaUniqueIdentifier("global__contact-us_fname-input{$qaPopup}"); ?>
                            >
                        </div>

                        <div class="col-12 col-xl-6">
                            <label class="input-label"><?php echo translate('contact_page_last_name'); ?></label>
                            <input
                                type="text"
                                class="validate[required, custom[validUserName], minSize[2], maxSize[50]]"
                                data-prompt-position="bottomLeft:0"
                                data-label="<?php echo translate('contact_page_last_name', null, true); ?>"
                                name="lname"
                                placeholder="<?php echo translate('contact_enter_last_name_placeholder', null, true); ?>"
                                <?php echo addQaUniqueIdentifier("global__contact-us_lname-input{$qaPopup}"); ?>
                            >
                        </div>

                        <div class="col-12 col-xl-6">
                            <label class="input-label"><?php echo translate('contact_page_phone'); ?></label>

                            <div class="input-group js-contact-us-dropdown-wrapper">
                                <div class="input-group-prepend wr-select2-h50 select-country-code-group">
                                    <div class="notranslate">
                                        <select
                                            id="js-contact-country-code"
                                            class="select-country-code validate[required]"
                                            name="country_code"
                                        >
                                            <?php /** @var array<\App\Common\Contracts\Entities\CountryCodeInterface|\App\Common\Contracts\Entities\Phone\PatternsAwareInterface> $phone_codes */ ?>
                                            <?php foreach($phone_codes as $phone_code) { ?>
                                                <option
                                                    value="<?php echo cleanOutput($phone_code->getId()); ?>"
                                                    data-phone-mask="<?php echo cleanOutput($phone_code->getPattern(\App\Common\Contracts\Entities\Phone\PatternsAwareInterface::PATTERN_INTERNATIONAL_MASK)); ?>"
                                                    data-country-flag="<?php echo cleanOutput(getCountryFlag($phone_code_country = $phone_code->getCountry()->getName())); ?>"
                                                    data-country-name="<?php echo cleanOutput($phone_code_country); ?>"
                                                    data-country="<?php echo cleanOutput($phone_code->getCountry()->getId()); ?>"
                                                    data-code="<?php echo cleanOutput($phone_code->getName()); ?>">
                                                    <?php echo cleanOutput(trim("{$phone_code->getName()} {$phone_code_country}")); ?>
                                                </option>
                                            <?php } ?>
                                        </select>

                                        <?php if (isset($webpackData)) {?>
                                        <span class="select2 select2-container select2-container--default select2-lazy-loader call-action" data-js-action="lazy-loading:select2" dir="ltr">
                                            <span class="selection">
                                                <span class="select2-selection select2-selection--single" tabindex="0">
                                                    <span class="select2-selection__rendered" title="+93 Afghanistan">
                                                        <img
                                                            class="select-country-flag"
                                                            src="<?php echo getCountryFlag('Afghanistan'); ?>"
                                                            alt="Afghanistan"
                                                            width="32"
                                                            height="32"
                                                        >
                                                        <span>+93</span>
                                                    </span>
                                                    <span class="select2-selection__arrow" role="presentation">
                                                        <b role="presentation"></b>
                                                    </span>
                                                </span>
                                            </span>
                                            <span class="dropdown-wrapper" aria-hidden="true"></span>
                                        </span>
                                        <?php }?>
                                    </div>
                                </div>
                                <input
                                    id="js-contact-phone-number"
                                    class="form-control validate[required,funcCall[checkPhoneMask]]"
                                    type="text"
                                    maxlength="25"
                                    name="phone"
                                    placeholder="<?php echo translate('contact_enter_phone_number_placeholder', null, true); ?>"
                                    <?php echo addQaUniqueIdentifier("global__contact-us_phone-input{$qaPopup}"); ?>
                                >
                            </div>
                        </div>

                        <div class="col-12 col-xl-6">
                            <label class="input-label"><?php echo translate('contact_page_email'); ?></label>
                            <input
                                type="email"
                                class="validate[required, custom[noWhitespaces],custom[emailWithWhitespaces], maxSize[256]]"
                                data-label="<?php echo translate('contact_page_email', null, true); ?>"
                                name="from"
                                placeholder="example@mail.com"
                                <?php echo addQaUniqueIdentifier("global__contact-us_email-input{$qaPopup}"); ?>
                            >
                        </div>
                    <?php } ?>

                    <div class="col-lg-12 pt-5">
                        <label class="input-label"><?php echo translate('contact_page_subject'); ?></label>
                        <input
                            class="validate[required, maxSize[100]]"
                            <?php echo addQaUniqueIdentifier("global__contact-us_subject-input{$qaPopup}"); ?>
                            type="text"
                            name="subject"
                            placeholder="<?php echo translate('contact_page_type_subject_placeholder', null, true); ?>"
                            <?php echo addQaUniqueIdentifier("global__contact-us_subject-input{$qaPopup}"); ?>
                        >
                    </div>
                    <div class="col-lg-12">
                        <label class="input-label"><?php echo translate('contact_page_message'); ?></label>
                        <textarea
                            class="validate[required, maxSize[500]] textcounter_contact-message"
                            data-max="500"
                            name="content"
                            data-label="<?php echo translate('contact_page_message', null, true); ?>"
                            placeholder="<?php echo translate('contact_type_message_placeholder', null, true); ?>"
                            <?php echo addQaUniqueIdentifier("global__contact-us_message-textarea{$qaPopup}"); ?>
                        ></textarea>
                    </div>

                    <div class="col-lg-12">
                        <?php if(isset($contact_us_not_modal) && $contact_us_not_modal > 0) { ?>
                            <br>
                            <div class="modal-flex__btns">
                                <div class="modal-flex__btns-right">
                                    <button
                                        class="btn btn-primary"
                                        type="submit"
                                        <?php echo addQaUniqueIdentifier("global__contact-us_submit-btn"); ?>
                                    >
                                        <?php echo translate('contact_us_form_submit_btn'); ?>
                                    </button>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <?php if (isset($module)) {?>
                        <input type="hidden" name="module" value="<?php echo $module;?>">
                    <?php }?>
                </div>
            </div>
        </div>

        <?php if(!isset($contact_us_not_modal) || $contact_us_not_modal == 0){?>
            <div class="modal-flex__btns">
                <div class="modal-flex__btns-right">
                    <button
                        class="btn btn-primary"
                        type="submit"
                        <?php echo addQaUniqueIdentifier("global__contact-us_submit-btn{$qaPopup}"); ?>
                    >
                        <?php echo translate('help_contact_us_form_button_submit');?>
                    </button>
                </div>
            </div>
        <?php }?>
	</form>
</div>

<?php if (!isset($webpackData) && !logged_in()) {?>
    <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/inputmask-5.x/jquery.inputmask.min.js');?>"></script>
    <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/phone-mask/phone-mask-init.js');?>"></script>
    <?php tmvc::instance()->controller->view->display('new/google_recaptcha/script_inclusions'); ?>
<?php } ?>

<?php
    echo dispatchDynamicFragmentInCompatMode(
        "contact-us:form-fragment",
        asset('public/plug/js/contact_us/index.js', 'legacy'),
        sprintf(
            "function () {
                if (!('ContactUsLegacyFragment' in window)) {
                    if (__debug_mode) {
                        console.error(new SyntaxError(\"'ContactUsLegacyFragment' must be defined\"));
                    }

                    return;
                }

                ContactUsLegacyFragment.default(%s, \"%s\", \"%s\", \"%s\", \"%s\");
            }",
            logged_in() ? 'true' : 'false',
            $message = translate('contact_sending_messsage', null, true),
            $url = logged_in()
                ? getUrlForGroup('/contact/ajax_contact_operations/send_admin_message')
                : getUrlForGroup('/contact/ajax_contact_operations/email_contact_admin'),
            $text_phone_mask = translate('validation_complete_phone_mask_error'),
            $text_country_code = translate('validation_select_country_code_errror'),
        ),
        [
            $message,
            logged_in(),
            $url,
            $text_phone_mask,
            $text_country_code,
        ]
    );
?>
