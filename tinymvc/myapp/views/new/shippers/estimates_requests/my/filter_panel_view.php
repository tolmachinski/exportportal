<div class="container-fluid-modal">
    <label class="input-label"><?php echo translate("general_dt_filters_entity_search_label_text"); ?></label>
    <input type="text"
        name="keywords"
        id="filter-keywords"
        class="dt_filter keywords"
        placeholder="<?php echo translate("general_dt_filters_entity_search_placeholder", null, true); ?>"
        data-title="<?php echo translate("general_dt_filters_entity_search_title", null, true); ?>"
        maxlength="50">

    <label class="input-label"><?php echo translate("general_dt_filters_entity_status_label_text"); ?></label>
    <select
        name="status"
        id="filter-status"
        class="dt_filter minfo-form__input2 mb-0"
        data-title="<?php echo translate("general_dt_filters_entity_status_title", null, true); ?>">
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_status_option_all_text"); ?></option>
        <option value="new"><?php echo translate("shipping_estimates_dashboard_dt_filters_entity_status_option_new_text"); ?></option>
        <option value="processed"><?php echo translate("shipping_estimates_dashboard_dt_filters_entity_status_option_processed_text"); ?></option>
        <option value="expired"><?php echo translate("shipping_estimates_dashboard_dt_filters_entity_status_option_expired_text"); ?></option>
    </select>

    <label class="input-label"><?php echo translate("shipping_estimates_dashboard_dt_filters_entity_departure_country_label_text"); ?></label>
    <select name="from_country"
        id="filter-from-country"
        class="dt_filter minfo-form__input2 mb-0"
        data-title="<?php echo translate("shipping_estimates_dashboard_dt_filters_entity_departure_country_title", null, true); ?>">
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_country_option_all_text"); ?></option>
        <?php echo getCountrySelectOptions($countries, 0, array('include_default_option' => false));?>
    </select>

    <label class="input-label"><?php echo translate("shipping_estimates_dashboard_dt_filters_entity_departure_state_label_text"); ?></label>
    <select name="from_state"
        id="filter-from-state"
        class="dt_filter minfo-form__input2 mb-0"
        data-title="<?php echo translate("shipping_estimates_dashboard_dt_filters_entity_departure_state_title", null, true); ?>"
        disabled>
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_state_option_all_text"); ?></option>
    </select>

    <div class="inputs-40">
        <div class="wr-select2-h50 relative-b">
            <label class="input-label"><?php echo translate("shipping_estimates_dashboard_dt_filters_entity_departure_city_label_text"); ?></label>
            <select name="from_city"
                class="dt_filter minfo-form__input2 mb-0"
                id="filter-from-city"
                data-title="<?php echo translate("shipping_estimates_dashboard_dt_filters_entity_departure_city_title", null, true); ?>"
                data-theme="default ep-select2-h30 h-40"
                data-placeholder="<?php echo translate("general_dt_filters_entity_city_option_all_text", null, true); ?>"
                disabled>
                <option></option>
            </select>
        </div>
    </div>

    <label class="input-label"><?php echo translate("shipping_estimates_dashboard_dt_filters_entity_destination_country_label_text"); ?></label>
    <select name="to_country"
        id="filter-to-country"
        class="dt_filter minfo-form__input2 mb-0"
        data-title="<?php echo translate("shipping_estimates_dashboard_dt_filters_entity_destination_country_title", null, true); ?>">
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_country_option_all_text"); ?></option>
        <?php echo getCountrySelectOptions($countries, 0, array('include_default_option' => false));?>
    </select>

    <label class="input-label"><?php echo translate("shipping_estimates_dashboard_dt_filters_entity_destination_state_label_text"); ?></label>
    <select name="to_state"
        id="filter-to-state"
        class="dt_filter minfo-form__input2 mb-0"
        data-title="<?php echo translate("shipping_estimates_dashboard_dt_filters_entity_destination_state_title", null, true); ?>"
        disabled>
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_state_option_all_text"); ?></option>
    </select>

    <div class="inputs-40">
        <div class="wr-select2-h50 relative-b">
            <label class="input-label"><?php echo translate("shipping_estimates_dashboard_dt_filters_entity_destination_city_label_text"); ?></label>
            <select name="to_city"
                class="dt_filter minfo-form__input2 mb-0"
                id="filter-to-city"
                data-title="<?php echo translate("shipping_estimates_dashboard_dt_filters_entity_destination_city_title", null, true); ?>"
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

    <label class="input-label"><?php echo translate("shipping_estimates_dashboard_dt_filters_entity_countdown_label_text"); ?></label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input type="text"
                name="countdown_from"
                id="filter-countdown-from"
                class="datepicker-init countdown_from dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_from_placeholder", null, true); ?>"
                data-title="<?php echo translate("shipping_estimates_dashboard_dt_filters_entity_countdown_from_title", null, true); ?>"
                readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input type="text"
                name="countdown_to"
                id="filter-countdown-to"
                class="datepicker-init countdown_to dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_to_placeholder", null, true); ?>"
                data-title="<?php echo translate("shipping_estimates_dashboard_dt_filters_entity_countdown_to_title", null, true); ?>"
                readonly>
        </div>
    </div>

    <input type="hidden"
        class="dt_filter"
        data-title="Title"
        data-value-text="<?php echo cut_str_with_dots(cleanOutput(arrayGet($filters, 'group.title'), 100)); ?>"
        name="group_key"
        value="<?php echo arrayGet($filters, 'group.key'); ?>">
</div>