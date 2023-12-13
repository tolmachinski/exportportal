<div class="container-fluid-modal">
    <label class="input-label"><?php echo translate("general_dt_filters_entity_search_label_text"); ?></label>
    <input type="text"
        name="keywords"
        id="filter-keywords"
        class="dt_filter keywords"
        placeholder="<?php echo translate("general_dt_filters_entity_search_placeholder", null, true); ?>"
        data-title="<?php echo translate("general_dt_filters_entity_search_title", null, true); ?>"
        maxlength="50">

    <label class="input-label"><?php echo translate("cr_events_dt_filters_entity_event_type_label_text"); ?></label>
    <select class="dt_filter minfo-form__input2 mb-0" id="filter-event-type" data-title="<?php echo translate("cr_events_dt_filters_entity_event_type_title", null, true); ?>" name="event_type">
        <option data-default="true" value=""><?php echo translate("cr_events_dt_filters_entity_event_type_option_all_text"); ?></option>
        <option value="active"><?php echo translate("cr_events_dt_filters_entity_event_type_option_active_text"); ?></option>
        <option value="expired"><?php echo translate("cr_events_dt_filters_entity_event_type_option_expired_text"); ?></option>
    </select>

    <?php if (have_right('manage_cr_personal_events')) { ?>
        <label class="input-label"><?php echo translate("cr_events_dt_filters_entity_event_status_label_text"); ?></label>
        <select class="dt_filter minfo-form__input2 mb-0" id="filter-status" data-title="<?php echo translate("cr_events_dt_filters_entity_event_status_title", null, true); ?>" name="status">
            <option data-default="true" value=""><?php echo translate("cr_events_dt_filters_entity_event_status_option_all_text"); ?></option>
            <option value="init"><?php echo translate("cr_events_dt_filters_entity_event_type_option_new_text"); ?></option>
            <option value="approved"><?php echo translate("cr_events_dt_filters_entity_event_type_option_approved_text"); ?></option>
        </select>
    <?php } ?>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_category_label_text"); ?></label>
    <select class="dt_filter minfo-form__input2 mb-0" id="filter-category" data-title="<?php echo translate("general_dt_filters_entity_category_title", null, true); ?>" name="category">
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_category_option_all_text"); ?></option>
        <?php foreach($categories as $category) { ?>
            <option value="<?php echo $category['id']; ?>"><?php echo cleanOutput($category['event_type_name']); ?></option>
        <?php } ?>
    </select>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_country_label_text"); ?></label>
    <select class="dt_filter minfo-form__input2 mb-0" id="filter-country" data-title="<?php echo translate("general_dt_filters_entity_country_title", null, true); ?>" name="country">
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_country_option_all_text"); ?></option>
        <?php echo getCountrySelectOptions($countries, 0, array('include_default_option' => false, 'value' => 'id_country'));?>
    </select>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_state_label_text"); ?></label>
    <select disabled class="dt_filter minfo-form__input2 mb-0" id="filter-state" data-title="<?php echo translate("general_dt_filters_entity_state_title", null, true); ?>" name="state">
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_state_option_all_text"); ?></option>
    </select>

    <div class="inputs-40">
        <div class="wr-select2-h50 relative-b">
            <label class="input-label"><?php echo translate("general_dt_filters_entity_city_label_text"); ?></label>
            <select name="city"
                class="dt_filter minfo-form__input2 mb-0"
                id="filter-city"
                data-placeholder="<?php echo translate("general_dt_filters_entity_city_option_all_text", null, true); ?>"
                data-theme="default ep-select2-h30 h-40"
                data-title="<?php echo translate("general_dt_filters_entity_city_title", null, true); ?>"
                disabled>
                <option></option>
            </select>
        </div>
    </div>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_date_starts_label_text"); ?></label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input type="text"
                name="start_from"
                id="filter-starts-from"
                class="datepicker-init starts_from dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_from_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_date_starts_from_title", null, true); ?>"
                readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input type="text"
                name="start_to"
                id="filter-starts-to"
                class="datepicker-init starts_to dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_to_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_date_starts_to_title", null, true); ?>"
                readonly>
        </div>
    </div>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_date_ends_label_text"); ?></label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input type="text"
                name="end_from"
                id="filter-ends-from"
                class="datepicker-init ends_from dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_from_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_date_ends_from_title", null, true); ?>"
                readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input type="text"
                name="end_to"
                id="filter-ends-to"
                class="datepicker-init ends_to dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_to_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_date_ends_to_title", null, true); ?>"
                readonly>
        </div>
    </div>
</div>