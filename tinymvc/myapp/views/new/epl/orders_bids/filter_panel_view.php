<div class="container-fluid-modal">
    <label class="input-label"><?php echo translate("order_bids_dashboard_dt_filters_entity_bid_label_text"); ?></label>
    <input type="text"
        name="bid"
        id="filter-bid"
        class="dt_filter bid"
        placeholder="<?php echo translate("order_bids_dashboard_dt_filters_entity_bid_placeholder", null, true); ?>"
        data-title="<?php echo translate("order_bids_dashboard_dt_filters_entity_bid_title", null, true); ?>"
        data-value-text="<?php echo !empty($filters['bid']) ? $filters['bid']['placeholder'] : null; ?>"
        data-value="<?php echo !empty($filters['bid']) ? $filters['bid']['value'] : null; ?>"
        value="<?php echo !empty($filters['bid']) ? $filters['bid']['placeholder'] : null; ?>"
        maxlength="12">

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

    <label class="input-label"><?php echo translate("general_dt_filters_entity_status_label_text", null, true); ?></label>
    <select name="status"
        id="filter-status"
        class="dt_filter minfo-form__input2 mb-0"
        data-title="<?php echo translate("general_dt_filters_entity_status_title", null, true); ?>">
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_status_option_all_text_alternate", null, true); ?></option>
        <option value="awaiting" <?php echo !empty($filters['status']['value']) ? selected($filters['status']['value'], 'awaiting'): null; ?>>
            <?php echo translate("orders_bids_dashboard_dt_column_bid_status_awaiting_text", null, true); ?>
        </option>
        <option value="confirmed" <?php echo !empty($filters['status']['value']) ? selected($filters['status']['value'], 'confirmed'): null; ?>>
            <?php echo translate("orders_bids_dashboard_dt_column_bid_status_confirmed_text", null, true); ?>
        </option>
        <option value="declined" <?php echo !empty($filters['status']['value']) ? selected($filters['status']['value'], 'declined'): null; ?>>
            <?php echo translate("orders_bids_dashboard_dt_column_bid_status_declined_text", null, true); ?>
        </option>
    </select>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_shipping_type_label_text", null, true); ?></label>
    <select name="shipment_type"
        id="filter-shipment-types"
        class="dt_filter minfo-form__input2 mb-0"
        data-title="<?php echo translate("general_dt_filters_entity_shipping_type_title", null, true); ?>">
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_shipping_type_option_all_text", null, true); ?></option>
        <?php foreach($shipment_types as $type) { ?>
            <option value="<?php echo $type['id_type']; ?>">
                <?php echo cleanOutput($type['type_name']); ?>
            </option>
        <?php } ?>
    </select>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_departure_country_label_text", null, true); ?></label>
    <select name="from_country"
        id="filter-from-country"
        class="dt_filter minfo-form__input2 mb-0"
        data-title="<?php echo translate("general_dt_filters_entity_departure_country_title", null, true); ?>">
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_country_option_all_text", null, true); ?></option>
        <?php echo getCountrySelectOptions($countries, 0, array('include_default_option' => false));?>
    </select>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_departure_state_label_text", null, true); ?></label>
    <select name="from_state"
        id="filter-from-state"
        class="dt_filter minfo-form__input2 mb-0"
        data-title="<?php echo translate("general_dt_filters_entity_departure_state_title", null, true); ?>"
        disabled>
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_state_option_all_text", null, true); ?></option>
    </select>

    <div class="inputs-40">
        <div class="wr-select2-h50 relative-b">
            <label class="input-label"><?php echo translate("general_dt_filters_entity_departure_city_label_text", null, true); ?></label>
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

    <label class="input-label"><?php echo translate("general_dt_filters_entity_destination_country_label_text", null, true); ?></label>
    <select name="to_country"
        id="filter-to-country"
        class="dt_filter minfo-form__input2 mb-0"
        data-title="<?php echo translate("general_dt_filters_entity_destination_country_title", null, true); ?>">
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_country_option_all_text", null, true); ?></option>
        <?php echo getCountrySelectOptions($countries, 0, array('include_default_option' => false));?>
    </select>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_destination_state_label_text", null, true); ?></label>
    <select name="to_state"
        id="filter-to-state"
        class="dt_filter minfo-form__input2 mb-0"
        data-title="<?php echo translate("general_dt_filters_entity_destination_state_title", null, true); ?>"
        disabled>
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_state_option_all_text", null, true); ?></option>
    </select>

    <div class="inputs-40">
        <div class="wr-select2-h50 relative-b">
            <label class="input-label"><?php echo translate("general_dt_filters_entity_destination_city_label_text", null, true); ?></label>
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

    <label class="input-label"><?php echo translate("general_dt_filters_entity_created_date_label_text_alternate", null, true); ?></label>
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

    <label class="input-label"><?php echo translate("general_dt_filters_entity_updated_date_label_text_alternate", null, true); ?></label>
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

    <label class="input-label"><?php echo translate("general_dt_filters_entity_pickup_date_label_text", null, true); ?></label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input type="text"
                name="pickup_from"
                id="filter-pickup-from"
                class="datepicker-init pickup_from dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_from_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_pickup_date_from_title", null, true); ?>"
                readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input type="text"
                name="pickup_to"
                id="filter-pickup-to"
                class="datepicker-init pickup_to dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_to_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_pickup_date_to_title", null, true); ?>"
                readonly>
        </div>
    </div>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_delivery_start_date_label_text", null, true); ?></label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input type="text"
                name="delivery_from"
                id="filter-delivery-from"
                class="datepicker-init delivery_from dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_from_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_delivery_start_date_from_title", null, true); ?>"
                readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input type="text"
                name="delivery_to"
                id="filter-delivery-to"
                class="datepicker-init delivery_to dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_to_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_delivery_start_date_to_title", null, true); ?>"
                readonly>
        </div>
    </div>
</div>
