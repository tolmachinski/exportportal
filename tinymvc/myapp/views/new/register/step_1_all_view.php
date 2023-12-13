
<div class="form-group">
    <label class="input-label">
        <?php echo translate('register_label_first_name'); ?>
        <a class="info-dialog"
            data-message="<?php echo translate('register_label_first_name_info');?>"
            data-title="<?php echo translate('register_label_first_name'); ?>"
            title="<?php echo translate('register_label_first_name'); ?>"
            href="#"
        >
            <?php echo widgetGetSvgIcon("info", 16, 16, "info-dialog-icon")?>
        </a>
    </label>

    <input
        type="text"
        name="fname"
        class="validate[required,custom[validUserName],minSize[2],maxSize[50]] js-ep-self-autotrack"
        placeholder="<?php echo translate('register_label_first_name_placeholder'); ?>"
        data-tracking-events="change"
        data-tracking-alias="form-register_<?php echo $register_type; ?>"
        <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-first-name")?>>
</div>

<div class="form-group">
    <label class="input-label"><?php echo translate('register_label_last_name'); ?></label>
    <input
        type="text"
        name="lname"
        class="validate[required,custom[validUserName],minSize[2],maxSize[50]] js-ep-self-autotrack"
        placeholder="<?php echo translate('register_label_last_name_placeholder'); ?>"
        data-tracking-events="change"
        data-tracking-alias="form-register_<?php echo $register_type; ?>"
        <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-last-name")?>>
</div>

<div class="form-group">
    <label class="input-label"><?php echo translate('register_label_email'); ?></label>
    <input
        type="text"
        class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces],maxSize[100],funcCall[checkEmail]] js-ep-self-autotrack"
        name="email"
        value="<?php echo $email; ?>"
        placeholder="<?php echo translate('register_label_email_placeholder'); ?>"
        data-tracking-events="change"
        data-tracking-alias="form-register_<?php echo $register_type; ?>"
        <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-email")?>>
</div>

<div class="form-group">
    <label class="input-label"><?php echo translate('register_label_password');?></label>
    <span class="view-password">
        <span class="ep-icon ep-icon_invisible call-action" data-js-action="register-forms:view-password"></span>
        <input
            id="js-register-password"
            class="validate[required]"
            type="password"
            name="password"
            placeholder="<?php echo translate('register_label_password_placeholder');?>"
            <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-password")?>>
        <?php app()->view->display('new/register/password_strength_view'); ?>
    </span>
</div>

<div class="form-group">
    <label class="input-label"><?php echo translate('register_label_confirm_password');?></label>
    <span class="view-password">
        <span class="ep-icon ep-icon_invisible call-action" data-js-action="register-forms:view-password"></span>
        <input
            class="validate[required,equals[js-register-password]]"
            type="password"
            name="confirm_password"
            placeholder="<?php echo translate('register_label_confirm_password_placeholder');?>"
            <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-password-confirm")?>>
    </span>
</div>

<div class="form-group" id="js-country-code-wr">
    <label class="input-label"><?php echo translate('register_label_phone');?></label>
    <div class="input-group">
        <div class="input-group-prepend wr-select2-h50 select-country-code-group" <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-phone-select")?>>
            <div class="notranslate">
                <select
                    id="js-country-code"
                    class="select-country-code validate[required]"
                    name="country_code">
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
                <span class="select2 select2-container select2-container--default select2-lazy-loader call-action" data-js-action="lazy-loading:select2" dir="ltr">
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
                </span>
            </div>
        </div>
        <input
            id="js-register-phone-number"
            class="form-control validate[required,funcCall[checkPhoneMask]]"
            type="text"
            maxlength="25"
            name="phone"
            placeholder="<?php echo translate('register_label_phone_placeholder');?>"
            <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-phone-input")?>>
    </div>
</div>
