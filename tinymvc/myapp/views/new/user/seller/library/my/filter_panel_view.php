<div class="container-fluid-modal">
    <label class="input-label"><?php echo translate("general_dt_filters_entity_search_label_text"); ?></label>
    <input type="text"
        name="keywords"
        id="filter-keywords"
        class="dt_filter keywords"
        placeholder="<?php echo translate("general_dt_filters_entity_search_placeholder", null, true); ?>"
        data-title="<?php echo translate("general_dt_filters_entity_search_title", null, true); ?>"
        maxlength="50"
        <?php echo addQaUniqueIdentifier('seller-library-my__filter-panel_keywords-input'); ?>
    >

    <label class="input-label"><?php echo translate("general_dt_filters_entity_category_label_text"); ?></label>
    <select
        id="filter-categories"
        class="dt_filter minfo-form__input2 mb-0"
        data-title="<?php echo translate("general_dt_filters_entity_category_title", null, true); ?>"
        name="library_category"
        <?php echo addQaUniqueIdentifier('seller-library-my__filter-panel_category-select'); ?>
    >
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_category_option_all_text"); ?></option>
        <?php foreach($library_categories as $category) { ?>
            <option value="<?php echo $category['id_category']; ?>"><?php echo $category['category_title']; ?></option>
        <?php } ?>
    </select>

    <label class="input-label"><?php echo translate("seller_library_dashboard_dt_filters_access_type_label_text"); ?></label>
    <select
        id="filter-access-type"
        class="dt_filter minfo-form__input2 mb-0"
        data-title="<?php echo translate("seller_library_dashboard_dt_filters_access_type_title", null, true); ?>"
        name="access"
        <?php echo addQaUniqueIdentifier('seller-library-my__filter-panel_access-type-select'); ?>
    >
        <option data-default="true" value=""><?php echo translate("seller_library_dashboard_dt_filters_access_type_option_all_text"); ?></option>
        <option value="public"><?php echo translate("seller_library_dashboard_dt_filters_access_type_option_public_text"); ?></option>
        <option value="private"><?php echo translate("seller_library_dashboard_dt_filters_access_type_option_private_text"); ?></option>
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
                readonly
                <?php echo addQaUniqueIdentifier('seller-library-my__filter-panel_created-from-input'); ?>
            >
        </div>
        <div class="col-12 col-lg-6">
            <input type="text"
                name="created_to"
                id="filter-created-to"
                class="datepicker-init create_to dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_to_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_created_date_to_title", null, true); ?>"
                readonly
                <?php echo addQaUniqueIdentifier('seller-library-my__filter-panel_created-to-input'); ?>
            >
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
                readonly
                <?php echo addQaUniqueIdentifier('seller-library-my__filter-panel_updated-from-input'); ?>
            >
        </div>
        <div class="col-12 col-lg-6">
            <input type="text"
                name="updated_to"
                id="filter-updated-to"
                class="datepicker-init updated_to dt_filter"
                placeholder="<?php echo translate("general_dt_filters_entity_date_to_placeholder", null, true); ?>"
                data-title="<?php echo translate("general_dt_filters_entity_updated_date_to_title", null, true); ?>"
                readonly
                <?php echo addQaUniqueIdentifier('seller-library-my__filter-panel_updated-to-input'); ?>
            >
        </div>
    </div>
</div>
