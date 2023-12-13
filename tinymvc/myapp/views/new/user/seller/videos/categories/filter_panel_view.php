<div class="container-fluid-modal">
    <label class="input-label"><?php echo translate("general_dt_filters_entity_search_label_text"); ?></label>
    <input type="text"
        <?php echo addQaUniqueIdentifier("seller-videos-categories__filter-panel_search-input_popup")?>
        name="keywords"
        id="filter-keywords"
        class="dt_filter keywords"
        placeholder="<?php echo translate("general_dt_filters_entity_search_placeholder", null, true); ?>"
        data-title="<?php echo translate("general_dt_filters_entity_search_title", null, true); ?>"
        maxlength="50">

    <label class="input-label"><?php echo translate("general_dt_filters_entity_created_date_label_text_alternate"); ?></label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input type="text"
                <?php echo addQaUniqueIdentifier("seller-videos-categories__filter-panel_created-from-input_popup")?>
                name="created_from"
                id="filter-created-from"
                class="datepicker-init create_from dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_from_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_created_date_from_title", null, true); ?>"
                readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input type="text"
                <?php echo addQaUniqueIdentifier("seller-videos-categories__filter-panel_created-to-input_popup")?>
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
                <?php echo addQaUniqueIdentifier("seller-videos-categories__filter-panel_updated-from-input_popup")?>
                name="updated_from"
                id="filter-updated-from"
                class="datepicker-init updated_from dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_from_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_updated_date_from_title", null, true); ?>"
                readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input type="text"
                <?php echo addQaUniqueIdentifier("seller-videos-categories__filter-panel_updated-to-input_popup")?>
                name="updated_to"
                id="filter-updated-to"
                class="datepicker-init updated_to dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_to_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_updated_date_to_title", null, true); ?>"
                readonly>
        </div>
    </div>
</div>
