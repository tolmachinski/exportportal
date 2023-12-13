<div class="container-fluid-modal">
    <label class="input-label"><?php echo translate("general_dt_filters_entity_search_label_text"); ?></label>
    <input type="text"
        name="keywords"
        id="documents--order--filter-keywords"
        class="dt-filter keywords"
        placeholder="<?php echo translate("general_dt_filters_entity_search_placeholder", null, true); ?>"
        data-title="<?php echo translate("general_dt_filters_entity_search_title", null, true); ?>"
        maxlength="50">

    <label class="input-label"><?php echo translate("order_documents_dashboard_datagrid_filters_entity_document_label_text"); ?></label>
    <input type="text"
        name="document"
        id="documents--order--filter-document"
        class="dt-filter document"
        placeholder="<?php echo translate("order_documents_dashboard_datagrid_filters_entity_document_placeholder", null, true); ?>"
        data-title="<?php echo translate("order_documents_dashboard_datagrid_filters_entity_document_title", null, true); ?>"
        data-value-text="<?php echo !empty($filters['document']) ? $filters['document']['placeholder'] : null; ?>"
        data-value="<?php echo !empty($filters['document']) ? $filters['document']['value'] : null; ?>"
        value="<?php echo !empty($filters['document']) ? $filters['document']['placeholder'] : null; ?>"
        maxlength="12">

    <label class="input-label"><?php echo translate("order_documents_dashboard_datagrid_filters_entity_order_label_text"); ?></label>
    <input type="text"
        name="order"
        id="documents--order--filter-order"
        class="dt-filter order"
        placeholder="<?php echo translate("order_documents_dashboard_datagrid_filters_entity_order_placeholder", null, true); ?>"
        data-title="<?php echo translate("order_documents_dashboard_datagrid_filters_entity_order_title", null, true); ?>"
        data-value-text="<?php echo !empty($filters['order']) ? $filters['order']['placeholder'] : null; ?>"
        data-value="<?php echo !empty($filters['order']) ? $filters['order']['value'] : null; ?>"
        value="<?php echo !empty($filters['order']) ? $filters['order']['placeholder'] : null; ?>"
        maxlength="12">

    <label class="input-label"><?php echo translate("general_dt_filters_entity_created_date_label_text_alternate"); ?></label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input type="text"
                name="created_from"
                id="documents--order--filter-created-from"
                class="datepicker-init create_from dt-filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_from_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_created_date_from_title", null, true); ?>"
                readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input type="text"
                name="created_to"
                id="documents--order--filter-created-to"
                class="datepicker-init create_to dt-filter"
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
                id="documents--order--filter-updated-from"
                class="datepicker-init updated_from dt-filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_from_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_updated_date_from_title", null, true); ?>"
                readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input type="text"
                name="updated_to"
                id="documents--order--filter-updated-to"
                class="datepicker-init updated_to dt-filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_to_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_updated_date_to_title", null, true); ?>"
                readonly>
        </div>
    </div>
</div>

