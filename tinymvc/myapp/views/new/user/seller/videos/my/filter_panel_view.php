<div class="container-fluid-modal">
    <label class="input-label"><?php echo translate("general_dt_filters_entity_search_label_text"); ?></label>
    <input type="text"
        <?php echo addQaUniqueIdentifier("seller-videos-my__filter-panel_search-input_popup"); ?>
        name="keywords"
        id="filter-keywords"
        class="dt_filter keywords"
        placeholder="<?php echo translate("general_dt_filters_entity_search_placeholder", null, true); ?>"
        data-title="<?php echo translate("general_dt_filters_entity_search_title", null, true); ?>"
        maxlength="50">

    <label class="input-label"><?php echo translate("general_dt_filters_entity_category_label_text"); ?></label>
    <select
        <?php echo addQaUniqueIdentifier("seller-videos-my__filter-panel_category-select_popup"); ?>
        class="dt_filter minfo-form__input2 mb-0"
        id="filter-categories"
        data-title="<?php echo translate("general_dt_filters_entity_category_title", null, true); ?>"
        name="videos_category"
    >
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_category_option_all_text"); ?></option>
        <?php foreach($videos_categories as $category) { ?>
            <option value="<?php echo $category['id_category']; ?>"><?php echo $category['category_title']; ?></option>
        <?php } ?>
    </select>

    <label class="input-label"><?php echo translate("seller_videos_dashboard_dt_filters_source_label_text"); ?></label>
    <select
        <?php echo addQaUniqueIdentifier("seller-videos-my__filter-panel_source-select_popup"); ?>
        class="dt_filter minfo-form__input2 mb-0"
        id="filter-access-type"
        data-title="<?php echo translate("seller_videos_dashboard_dt_filters_source_title", null, true); ?>"
        name="source"
    >
        <option data-default="true" value=""><?php echo translate("seller_videos_dashboard_dt_filters_source_option_all_text"); ?></option>
        <option value="youtube"><?php echo translate("seller_videos_dashboard_dt_filters_source_option_youtube_text"); ?></option>
        <option value="vimeo"><?php echo translate("seller_videos_dashboard_dt_filters_source_option_vimeo_text"); ?></option>
    </select>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_created_date_label_text_alternate"); ?></label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input type="text"
                <?php echo addQaUniqueIdentifier("seller-videos-my__filter-panel_created-from-input_popup"); ?>
                name="created_from"
                id="filter-created-from"
                class="datepicker-init create_from dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_from_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_created_date_from_title", null, true); ?>"
                readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input type="text"
                <?php echo addQaUniqueIdentifier("seller-videos-my__filter-panel_created-to-input_popup"); ?>
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
                <?php echo addQaUniqueIdentifier("seller-videos-my__filter-panel_updated-from-input_popup"); ?>
                name="updated_from"
                id="filter-updated-from"
                class="datepicker-init updated_from dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_from_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_updated_date_from_title", null, true); ?>"
                readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input type="text"
                <?php echo addQaUniqueIdentifier("seller-videos-my__filter-panel_updated-to-input_popup"); ?>
                name="updated_to"
                id="filter-updated-to"
                class="datepicker-init updated_to dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_to_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_updated_date_to_title", null, true); ?>"
                readonly>
        </div>
    </div>
</div>
