<div class="form-group">
    <label class="input-label"><?php echo translate('form_label_country'); ?></label>
    <select id="js-register-input-country" class="validate[required]" name="country" <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-select-country")?>>
        <?php echo getCountrySelectOptions($port_country, 0, array(), translate('register_country_text')); ?>
    </select>
</div>

<div id="js-select2-state-wr" class="form-group" <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-select-state")?>>
    <label class="input-label"><?php echo translate('form_label_state'); ?></label>
    <select id="js-register-input-country-states" name="states">
        <option value=""><?php echo translate('register_state_region_text'); ?></option>
    </select>
</div>

<div id="js-select2-city-wr" class="form-group wr-select2-h50" <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-select-city")?>>
    <label class="input-label"><?php echo translate('form_label_city'); ?></label>
    <select id="js-register-input-port-city" class="validate[required]" name="port_city">
        <option value=""><?php echo translate('form_placeholder_select2_country_first'); ?></option>
    </select>
</div>

<?php
    echo dispatchDynamicFragment("location-inline:boot", [
        false,
        false,
        getUrlForGroup('/location/ajax_get_regions'),
        [
            "country" => "#js-register-input-country",
            "region"  =>  "#js-register-input-country-states",
            "city"    => "#js-register-input-port-city",
        ],
        [
            "regionPlaceholder" => translate('register_state_region_text'),
            "cityPlaceholder"   => translate('register_select_city_option'),
        ]
    ], true);
?>
