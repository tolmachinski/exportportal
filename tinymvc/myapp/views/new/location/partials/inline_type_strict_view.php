<div class="form-group">
    <label class="input-label"><?php echo translate('form_label_country'); ?></label>
    <select id="js-register-input-country" class="validate[required]" name="country" <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-select-country")?>>
        <?php echo getCountrySelectOptions($port_country, 0, array(), translate('register_country_text')); ?>
    </select>
</div>

<div id="js-select2-state-wr" class="form-group" <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-select-state")?>>
    <label class="input-label"><?php echo translate('form_label_state'); ?></label>
    <select id="js-register-input-country-states" name="states" data-validation-template="validate[required]">
        <option value=""><?php echo translate('register_state_region_text'); ?></option>
    </select>
</div>

<div id="js-select2-city-wr" class="form-group wr-select2-h50" <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-select-city")?>>
    <label class="input-label"><?php echo translate('form_label_city'); ?></label>
    <select id="js-register-input-port-city" name="port_city" data-validation-template="validate[required]">
        <option value=""><?php echo translate('form_placeholder_select2_country_first'); ?></option>
    </select>
</div>

<div class="form-group" style="display: none;">
    <label class="input-label">
        <?php echo translate('form_label_location'); ?>
        <a href="#"
            class="info-dialog ep-icon ep-icon_info"
            data-message="<?php echo translate('register_label_location_info');?>"
            data-title="<?php echo translate('register_label_location_info_dialog_title'); ?>"
            title="<?php echo translate('register_label_location_info_title'); ?>">
        </a>
    </label>
    <input type="hidden" name="custom_location" value="1" disabled>
    <input
        id="js-register-input-location"
        type="text"
        name="location"
        class="validate[required,minSize[2],maxSize[300]]"
        placeholder="<?php echo translate('register_label_location_placeholder'); ?>"
        disabled>
</div>

<?php
    echo dispatchDynamicFragment("location-inline:boot", [
        false,
        true,
        getUrlForGroup('/location/ajax_get_regions'),
        [
            "input"   => "#js-register-input-location",
            "country" => "#js-register-input-country",
            "region"  =>  "#js-register-input-country-states",
            "city"    => "#js-register-input-port-city",
        ],
        [
            "optionPlaceholder" => translate('register_location_option_text'),
            "regionPlaceholder" => translate('register_state_region_text'),
            "cityPlaceholder"   => translate('register_select_city_option'),
        ]
    ], true);
?>
