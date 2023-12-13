<div
    id="js-epl-step-register-1"
    class="account-registration-step js-account-registration-step active"
    <?php echo addQaUniqueIdentifier("{$companyType}-registration__step-1"); ?>
>
    <div class="form-group">
        <div class="form-row">
        <label class="input-label">
            <?php echo translate('epl_register_first_name_label'); ?>
        </label>
        <button
                class="info-dialog js-info-dialog"
                type="button"
                data-message="<?php echo translate('register_label_first_name_info', null, true); ?>"
                data-title="<?php echo translate('epl_register_first_name_label', null, true); ?>"
                title="<?php echo translate('epl_register_first_name_label', null, true); ?>"
            >
                <?php echo widgetGetSvgIconEpl("info", 16, 16, "info-dialog-icon") ?>
            </button>
        </div>
        <input
            class="js-ep-self-autotrack"
            type="text"
            name="fname"
            placeholder="e.g. John"
            data-tracking-events="change"
            data-tracking-alias="form-register_<?php echo $registerType; ?>"
            <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-first-name") ?>
        >
    </div>

    <div class="form-group">
        <label class="input-label"><?php echo translate('epl_register_last_name_label'); ?></label>
        <input
            class="js-ep-self-autotrack"
            type="text"
            name="lname"
            placeholder="e.g. Doe"
            data-tracking-events="change"
            data-tracking-alias="form-register_<?php echo $registerType; ?>"
            <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-last-name") ?>
        >
    </div>

    <div class="form-group">
        <label class="input-label"><?php echo translate('epl_register_email_label'); ?></label>
        <input
            class="js-ep-self-autotrack"
            type="email"
            name="email"
            value="<?php echo $email; ?>"
            placeholder="<?php echo translate('epl_register_email_placeholder', null, true); ?>"
            data-tracking-events="change"
            data-tracking-alias="form-register_<?php echo $registerType; ?>"
            <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-email") ?>
        >
    </div>

    <div class="form-group">
        <label class="input-label"><?php echo translate('epl_register_password_label'); ?></label>

        <div class="view-password">
            <input
                id="js-password"
                type="password"
                name="password"
                placeholder="<?php echo translate('epl_register_password_placeholder', null, true); ?>"
                <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-password"); ?>
            >
            <button class="js-view-password-btn" type="button" tabindex="-1">
                <i class="ep-icon ep-icon_invisible"></i>
            </button>
            <?php views()->display('new/epl/authenticate/password_strength_view'); ?>
        </div>
    </div>

    <div class="form-group">
        <label class="input-label"><?php echo translate('epl_register_confirm_password_label'); ?></label>
        <div class="view-password">
            <input
                id="js-confirm-password"
                type="password"
                name="confirm_password"
                placeholder="<?php echo translate('epl_register_confirm_password_placeholder', null, true); ?>"
                <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-password-confirm"); ?>
            >
            <button class="js-view-password-btn" type="button" tabindex="-1">
                <i class="ep-icon ep-icon_invisible"></i>
            </button>
        </div>
    </div>

    <div id="js-country-code-wr" class="form-group">
        <label class="input-label"><?php echo translate('epl_register_phone_label'); ?></label>
        <div class="input-group">
            <div class="input-group-prepend wr-select2-h50 select-country-code-group" <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-phone-select") ?>>
                <div class="notranslate">
                    <select
                        id="js-country-code"
                        class="select-country-code"
                        name="country_code"
                        data-hide-error="true"
                    >
                        <option value="" disabled selected><?php echo translate('epl_registe_country_code'); ?></option>
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
                        data-js-action="lazy-loading:select2"
                        dir="ltr"
                    >
                        <span class="selection">
                            <span class="select2-selection select2-selection--single" tabindex="0">
                                <span class="select2-selection__rendered" title="<?php echo translate('epl_registe_country_code', null, true); ?>">
                                    <span><?php echo translate('epl_registe_country_code'); ?></span>
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
                placeholder="<?php echo translate('epl_register_phone_placeholder', null, true); ?>"
                data-group="true"
                readonly
                <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-phone-input") ?>
            >
        </div>
    </div>

    <div class="account-registration-actions">
            <div class="account-registration-actions__right">
                <button
                    class="btn btn-primary btn-block btn--w150-sm call-action"
                    type="button"
                    data-js-action="register-forms:next-register-steps"
                    data-step="validate_step_1_all"
                    <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-next-btn") ?>
                >
                    <?php echo translate('epl_register_next_btn'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
