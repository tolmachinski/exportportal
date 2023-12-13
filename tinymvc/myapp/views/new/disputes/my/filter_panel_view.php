<div class="container-fluid-modal">
    <label class="input-label"><?php echo translate("general_dt_filters_entity_search_label_text"); ?></label>
    <input type="text"
        name="keywords"
        id="filter-keywords"
        class="dt_filter keywords"
        placeholder="<?php echo translate("general_dt_filters_entity_search_placeholder", null, true); ?>"
        data-title="<?php echo translate("general_dt_filters_entity_search_title", null, true); ?>"
        maxlength="50">

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

    <label class="input-label"><?php echo translate("order_disputes_dashboard_dt_filters_entity_dispute_label_text"); ?></label>
    <input type="text"
        name="dispute"
        id="filter-dispute"
        class="dt_filter dispute"
        placeholder="<?php echo translate("order_disputes_dashboard_dt_filters_entity_dispute_placeholder", null, true); ?>"
        data-title="<?php echo translate("order_disputes_dashboard_dt_filters_entity_dispute_title", null, true); ?>"
        data-value-text="<?php echo !empty($filters['dispute']) ? $filters['dispute']['placeholder'] : null; ?>"
        data-value="<?php echo !empty($filters['dispute']) ? $filters['dispute']['value'] : null; ?>"
        value="<?php echo !empty($filters['dispute']) ? $filters['dispute']['placeholder'] : null; ?>"
        maxlength="12">

    <label class="input-label">Status</label>
    <select class="dt_filter" data-title="Status" name="status">
        <option value="" data-value-text="">All</option>
        <?php foreach($statuses as $status_key => $status) { ?>
            <?php if (have_right_or($status['rights'])) { ?>
                <option value="<?php echo $status_key;?>" data-value-text="<?php echo cleanOutput($status['title']); ?>">
                    <?php echo cleanOutput($status['title']); ?>
                </option>
            <?php } ?>
        <?php } ?>
    </select>

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
</div>
