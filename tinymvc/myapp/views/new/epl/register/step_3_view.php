
<div
    id="js-epl-step-register-3"
    class="account-registration-step js-account-registration-step"
    <?php echo addQaUniqueIdentifier("{$companyType}-registration__step-3"); ?>
>
    <div class="form-group">
        <label class="input-label"><?php echo translate('epl_register_country_label'); ?></label>
        <select
            id="js-register-input-country"
            name="country"
            <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-select-country")?>
        >
            <?php echo getCountrySelectOptions($portCountry, 0, [], "Select your country"); ?>
        </select>
    </div>

    <div id="js-select2-state-wr" class="form-group" <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-select-state"); ?>>
        <label class="input-label"><?php echo translate('epl_register_state_label'); ?></label>
        <select id="js-register-input-country-states" name="states">
            <option value=""><?php echo translate('epl_register_select_state_or_region'); ?></option>
        </select>
    </div>

    <div id="js-select2-city-wr" class="form-group wr-select2-h50" <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-select-city")?>>
        <label class="input-label"><?php echo translate('epl_register_city_label'); ?></label>
        <select id="js-register-input-port-city" name="port_city">
            <option value=""><?php echo translate('epl_register_select_your_city'); ?></option>
        </select>
    </div>

    <div class="form-group form-group--hidden">
        <label class="input-label">
            <?php echo translate('form_label_location'); ?>
            <button
                class="info-dialog js-info-dialog"
                type="button"
                data-message="<?php echo translate('register_label_location_info', null, true);?>"
                data-title="<?php echo translate('register_label_location_info_dialog_title', null, true); ?>"
                title="<?php echo translate('register_label_location_info_title', null, true); ?>"
            >
                <?php echo widgetGetSvgIconEpl("info", 16, 16, "info-dialog-icon") ?>
            </button>
        </label>
        <input type="hidden" name="custom_location" value="1">
        <input
            id="js-register-input-location"
            type="text"
            name="location"
            placeholder="<?php echo translate('register_label_location_placeholder', null, true); ?>"
            disabled
        >
    </div>

    <div class="form-group">
        <label class="input-label"><?php echo translate('epl_register_company_address_label'); ?></label>
        <input
            type="text"
            name="address"
            placeholder="<?php echo translate('epl_register_company_address_placeholder', null, true); ?>"
            <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-address") ?>
        >
    </div>

    <div class="form-group">
        <label class="input-label"><?php echo translate('epl_register_zip_label'); ?></label>
        <input
            type="text"
            name="zip"
            placeholder="<?php echo translate('epl_register_zip_placeholder', null, true); ?>"
            <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-zip-code") ?>
        >
    </div>

    <input type="hidden" name="company_type" value="<?php echo $companyType; ?>">

    <?php views()->display('new/epl/register/register_submit_btns_view', ['companyType' => $companyType]); ?>
</div>

<?php
    echo dispatchDynamicFragment("location-inline:boot", [
        false,
        true,
        __CURRENT_SUB_DOMAIN_URL . '/location/ajax_get_regions',
        [
            "input"   => "#js-register-input-location",
            "country" => "#js-register-input-country",
            "region"  => "#js-register-input-country-states",
            "city"    => "#js-register-input-port-city",
        ],
        [
            "optionPlaceholder" => translate('register_location_option_text'),
            "regionPlaceholder" => translate('register_state_region_text'),
            "cityPlaceholder"   => translate('register_select_city_option'),
        ]
    ], true);
?>
