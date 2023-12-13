<div class="container-fluid-modal">
    <label class="input-label"><?php echo translate("general_dt_filters_entity_order_label_text"); ?></label>
    <input type="text"
        name="order"
        id="filter-order"
        class="dt_filter order"
        placeholder="<?php echo translate("general_dt_filters_entity_order_placeholder", null, true); ?>"
        data-title="<?php echo translate("general_dt_filters_entity_order_title", null, true); ?>"
        data-value-text="<?php echo !empty($filters['order']) ? $filters['order']['placeholder'] : null; ?>"
        data-value="<?php echo !empty($filters['order']) ? $filters['order']['value'] : null; ?>"
        value="<?php echo !empty($filters['order']) ? $filters['order']['placeholder'] : null; ?>"
        maxlength="12">

    <label class="input-label"><?php echo translate("general_dt_filters_entity_weight_label_text"); ?></label>
    <div class="row">
        <div class="col-6">
            <input type="text"
                name="min_weight"
                id="filter-min-weight"
                class="dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_min_weight_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_min_weight_title", null, true); ?>">
        </div>
        <div class="col-6">
            <input type="text"
                name="max_weight"
                id="filter-max-weight"
                class="dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_max_weight_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_max_weight_title", null, true); ?>">
        </div>
    </div>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_length_label_text"); ?></label>
    <div class="row">
        <div class="col-6">
            <input type="text"
                name="min_length"
                id="filter-min-length"
                class="dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_min_length_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_min_length_title", null, true); ?>">
        </div>
        <div class="col-6">
            <input type="text"
                name="max_length"
                id="filter-max-length"
                class="dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_max_length_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_max_length_title", null, true); ?>">
        </div>
    </div>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_width_label_text"); ?></label>
    <div class="row">
        <div class="col-6">
            <input type="text"
                name="min_width"
                id="filter-min-width"
                class="dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_min_width_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_min_width_title", null, true); ?>">
        </div>
        <div class="col-6">
            <input type="text"
                name="max_width"
                id="filter-max-width"
                class="dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_max_width_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_max_width_title", null, true); ?>">
        </div>
    </div>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_height_label_text"); ?></label>
    <div class="row">
        <div class="col-6">
            <input type="text"
                name="min_height"
                id="filter-min-height"
                class="dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_min_height_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_min_height_title", null, true); ?>">
        </div>
        <div class="col-6">
            <input type="text"
                name="max_height"
                id="filter-max-height"
                class="dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_max_height_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_max_height_title", null, true); ?>">
        </div>
    </div>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_shipping_type_label_text"); ?></label>
    <select name="shipment_type"
        id="filter-shipment-types"
        class="dt_filter minfo-form__input2 mb-0"
        data-title="<?php echo translate("general_dt_filters_entity_shipping_type_title", null, true); ?>">
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_shipping_type_option_all_text"); ?></option>
        <?php foreach($shipment_types as $type) { ?>
            <option value="<?php echo $type['id_type']; ?>">
                <?php echo cleanOutput($type['type_name']); ?>
            </option>
        <?php } ?>
    </select>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_departure_country_label_text"); ?></label>
    <select name="from_country"
        id="filter-from-country"
        class="dt_filter minfo-form__input2 mb-0"
        data-title="<?php echo translate("general_dt_filters_entity_departure_country_title", null, true); ?>">
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_country_option_all_text"); ?></option>
        <?php echo getCountrySelectOptions($countries, 0, array('include_default_option' => false));?>
    </select>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_departure_state_label_text"); ?></label>
    <select name="from_state"
        id="filter-from-state"
        class="dt_filter minfo-form__input2 mb-0"
        data-title="<?php echo translate("general_dt_filters_entity_departure_state_title", null, true); ?>"
        disabled>
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_state_option_all_text"); ?></option>
    </select>

    <div class="inputs-40">
        <div class="wr-select2-h50 relative-b">
            <label class="input-label"><?php echo translate("general_dt_filters_entity_departure_city_label_text"); ?></label>
            <select name="from_city"
                class="dt_filter minfo-form__input2 mb-0"
                id="filter-from-city"
                data-title="<?php echo translate("general_dt_filters_entity_departure_city_title", null, true); ?>"
                data-theme="default ep-select2-h30 h-40"
                data-placeholder="<?php echo translate("general_dt_filters_entity_city_option_all_text", null, true); ?>"
                disabled>
                <option></option>
            </select>
        </div>
    </div>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_destination_country_label_text"); ?></label>
    <select name="to_country"
        id="filter-to-country"
        class="dt_filter minfo-form__input2 mb-0"
        data-title="<?php echo translate("general_dt_filters_entity_destination_country_title", null, true); ?>">
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_country_option_all_text"); ?></option>
        <?php echo getCountrySelectOptions($countries, 0, array('include_default_option' => false));?>
    </select>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_destination_state_label_text"); ?></label>
    <select name="to_state"
        id="filter-to-state"
        class="dt_filter minfo-form__input2 mb-0"
        data-title="<?php echo translate("general_dt_filters_entity_destination_state_title", null, true); ?>"
        disabled>
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_state_option_all_text"); ?></option>
    </select>

    <div class="inputs-40">
        <div class="wr-select2-h50 relative-b">
            <label class="input-label"><?php echo translate("general_dt_filters_entity_destination_city_label_text"); ?></label>
            <select name="to_city"
                class="dt_filter minfo-form__input2 mb-0"
                id="filter-to-city"
                data-title="<?php echo translate("general_dt_filters_entity_destination_city_title", null, true); ?>"
                data-theme="default ep-select2-h30 h-40"
                data-placeholder="<?php echo translate("general_dt_filters_entity_city_option_all_text", null, true); ?>"
                disabled>
                <option></option>
            </select>
        </div>
    </div>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_created_date_label_text_alternate"); ?></label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input type="text"
                name="created_from"
                id="filter-created-from"
                class="datepicker-init create_from dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_from_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_created_date_from_title", null, true); ?>"
                readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input type="text"
                name="created_to"
                id="filter-created-to"
                class="datepicker-init create_to dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_to_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_created_date_to_title", null, true); ?>"
                readonly>
        </div>
    </div>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_updated_date_label_text_alternate"); ?></label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input type="text"
                name="updated_from"
                id="filter-updated-from"
                class="datepicker-init updated_from dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_from_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_updated_date_from_title", null, true); ?>"
                readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input type="text"
                name="updated_to"
                id="filter-updated-to"
                class="datepicker-init updated_to dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_to_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_updated_date_to_title", null, true); ?>"
                readonly>
        </div>
    </div>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_expiration_date_label_text"); ?></label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input type="text"
                name="expires_from"
                id="filter-expires-from"
                class="datepicker-init expires_from dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_from_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_expiration_date_from_title", null, true); ?>"
                readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input type="text"
                name="expires_to"
                id="filter-expires-to"
                class="datepicker-init expires_to dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_to_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_expiration_date_to_title", null, true); ?>"
                readonly>
        </div>
    </div>
</div>
