<form
    class="validateModal inputs-40"
    method="POST"
    data-js-action="webinar-request:form-submit"
    data-callback="webinarRequestFormCallBack"
    <?php echo addQaUniqueIdentifier('global__schedule-demo_form'); ?>
>
    <div class="schedule-demo-popup__row">
        <div class="schedule-demo-popup__col-50pr">
            <div class="form-group">
                <label class="input-label"><?php echo translate('schedule_a_demo_popup_fname_label'); ?></label>
                <input
                    class="validate[required,custom[validUserName],minSize[2],maxSize[50]]"
                    type="text"
                    name="fname"
                    value="<?php echo isset($userData) ? cleanOutput($userData['fname']) : '';?>"
                    placeholder="<?php echo translate('schedule_a_demo_popup_fname_placeholder', null, true); ?>"
                    data-prompt-position="bottomLeft:0"
                    <?php echo addQaUniqueIdentifier('global__schedule-demo_form_fname_popup'); ?>
                >
            </div>
        </div>

        <div class="schedule-demo-popup__col-50pr">
            <div class="form-group">
                <label class="input-label"><?php echo translate('schedule_a_demo_popup_lname_label'); ?></label>
                <input
                    class="validate[required,custom[validUserName],minSize[2],maxSize[50]]"
                    type="text"
                    name="lname"
                    value="<?php echo isset($userData) ? cleanOutput($userData['lname']) : '';?>"
                    placeholder="<?php echo translate('schedule_a_demo_popup_lname_placeholder', null, true); ?>"
                    data-prompt-position="bottomLeft:0"
                    <?php echo addQaUniqueIdentifier('global__schedule-demo_form_lname_popup'); ?>
                >
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="input-label"><?php echo translate('schedule_a_demo_popup_email_label'); ?></label>
        <input
            class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces],maxSize[100]]"
            type="text"
            name="email"
            value="<?php echo isset($userData) ? cleanOutput($userData['email']) : '';?>"
            placeholder="<?php echo translate('schedule_a_demo_popup_email_placeholder', null, true); ?>"
            <?php echo addQaUniqueIdentifier('global__schedule-demo_form_email_popup'); ?>
        >
    </div>

    <div class="form-group">
        <label class="input-label"><?php echo translate('schedule_a_demo_popup_phone_label'); ?></label>

        <div class="input-group js-select2-dropdown-wrapper">
            <div class="input-group-prepend wr-select2-h50 select-country-code-group" <?php echo addQaUniqueIdentifier('global__schedule-demo_form_phone-select_popup'); ?>>
                <div class="notranslate">
                    <select
                        id="js-country-code-select"
                        class="select-country-code validate[required]"
                        name="code"
                    >
                        <?php /** @var array<\App\Common\Contracts\Entities\CountryCodeInterface|\App\Common\Contracts\Entities\Phone\PatternsAwareInterface> $phoneCodes */ ?>
                        <?php foreach($phoneCodes as $phoneCode) { ?>
                            <option
                                value="<?php echo cleanOutput($phoneCode->getId()); ?>"
                                data-phone-mask="<? echo cleanOutput($phoneCode->getPattern(\App\Common\Contracts\Entities\Phone\PatternsAwareInterface::PATTERN_INTERNATIONAL_MASK)); ?>"
                                data-country-flag="<?php echo cleanOutput(getCountryFlag($phoneCodeCountry = $phoneCode->getCountry()->getName())); ?>"
                                data-country-name="<?php echo cleanOutput($phoneCodeCountry); ?>"
                                data-country="<?php echo cleanOutput($phoneCode->getCountry()->getId()); ?>"
                                data-code="<?php echo cleanOutput($phoneCode->getName()); ?>"
                                <?php if ($selectedPhoneCode && $selectedPhoneCode->getId() === $phoneCode->getId()) { ?>selected<?php } ?>
                            >
                                <?php echo cleanOutput(trim("{$phoneCode->getName()} {$phoneCodeCountry}")); ?>
                            </option>
                        <?php } ?>
                    </select>

                    <?php if (isset($webpackData)) {?>
                    <button
                        id="js-lazy-loader-phone-code-btn"
                        class="select2 select2-container select2-container--default select2-lazy-loader call-action"
                        data-js-action="lazy-loading:select2"
                        dir="ltr"
                    >
                        <span class="selection">
                            <span class="select2-selection select2-selection--single" tabindex="0">
                                <span class="select2-selection__rendered" title="+93 Afghanistan">
                                    <img
                                        class="select-country-flag"
                                        width="32"
                                        height="32"
                                        src="<?php echo getCountryFlag('Afghanistan'); ?>"
                                        alt="Afghanistan"
                                    >
                                    <span>+93</span>
                                </span>
                                <span class="select2-selection__arrow" role="presentation">
                                    <b role="presentation"></b>
                                </span>
                            </span>
                        </span>
                        <span class="dropdown-wrapper" aria-hidden="true"></span>
                    </button>
                    <?php }?>
                </div>
            </div>
            <input
                id="js-phone-number"
                class="form-control validate[required,funcCall[checkPhoneMask]]"
                type="text"
                maxlength="25"
                name="phone"
                value="<?php echo isset($userData) ? cleanOutput($userData['phone']) : '';?>"
                placeholder="<?php echo translate('schedule_a_demo_popup_phone_placeholder', null, true); ?>"
                <?php echo addQaUniqueIdentifier('global__schedule-demo_form_phone_popup'); ?>
            >
        </div>
    </div>

    <div class="form-group">
        <label class="input-label"><?php echo translate('schedule_a_demo_popup_country_label'); ?></label>
        <select
            id="js-schedule-demo-input-country"
            class="validate[required]"
            name="country"
            <?php echo addQaUniqueIdentifier('global__schedule-demo_form_country_popup'); ?>
        >
            <?php echo getCountrySelectOptions($portCountry, isset($userData) ? $userData['country'] : 0, [], translate('schedule_a_demo_popup_country_placeholder')); ?>
        </select>
    </div>

    <div class="form-group">
        <label class="input-label"><?php echo translate('schedule_a_demo_popup_user_type_label'); ?></label>
        <select
            class="validate[required]"
            name="user_type"
            <?php echo addQaUniqueIdentifier('global__schedule-demo_form_user-type_popup'); ?>
        >
            <option value="" disabled <?php if (!logged_in() || !isset($userType)) { ?>selected <?php } ?>><?php echo translate('schedule_a_demo_popup_user_type_placeholder'); ?></option>
            <?php foreach ($userGroups as $key => $group) { ?>
                <option value="<?php echo $key; ?>" <?php if (isset($userType) && $userType === $group) { ?>selected <?php } ?>>
                    <?php echo $group; ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="schedule-demo-popup__btn-wr">
        <button
            class="btn btn-primary schedule-demo-popup__submit-btn"
            type="submit"
            <?php echo addQaUniqueIdentifier('global__schedule-demo_form_submit-btn_popup'); ?>
        >
            <?php echo translate('general_modal_button_submit_text'); ?>
        </button>
    </div>
</form>

<?php if(!isset($webpackData)) { ?>
    <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/inputmask-5.x/jquery.inputmask.min.js');?>"></script>
    <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/phone-mask/phone-mask-init.js');?>"></script>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/schedule_a_demo_popup.css');?>" />
<?php } ?>

<?php
    echo dispatchDynamicFragmentInCompatMode(
        'phone-mask:init',
        asset('public/plug/js/init_phone_number/index.js', 'legacy'),
        sprintf(
            "function () {
                if (!('InitPhoneMaskLegacyFragment' in window)) {
                    if (__debug_mode) {
                        console.error(new SyntaxError(\"'InitPhoneMaskLegacyFragment' must be defined\"));
                    }

                    return;
                }

                InitPhoneMaskLegacyFragment.default(%s);
            }",
            json_encode(
                $options = [
                    'textErorCountryCode'    => translate('validation_select_country_code_errror'),
                    'textErorPhoneMask'      => translate('validation_complete_phone_mask_error'),
                    'countryCodeSelector'    => '#js-country-code-select',
                    'phoneNumberSelector'    => '#js-phone-number',
                    'dropdownParentSelector' => '.js-select2-dropdown-wrapper',
                    'selectedPhone'          => $selectedPhoneCode && (int)$selectedPhoneCode->getId() ? $selectedPhoneCode->getId() : 0,
                    'lazyLoaderBtnSelector'  => '#js-lazy-loader-phone-code-btn',
                ],
            )
        ),
        [ $options ],
        true
    );
?>


