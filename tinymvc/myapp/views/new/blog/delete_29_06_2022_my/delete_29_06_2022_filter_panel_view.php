<div class="container-fluid-modal">
    <label class="input-label"><?php echo translate("general_dt_filters_entity_search_label_text"); ?></label>
    <input class="dt_filter keywords" id="keywords" type="text" placeholder="<?php echo translate("general_dt_filters_entity_search_placeholder"); ?>" data-title="<?php echo translate("general_dt_filters_entity_search_title"); ?>" name="keywords" maxlength="50">

    <label class="input-label"><?php echo translate("general_dt_filters_entity_status_label_text"); ?></label>
    <select class="dt_filter minfo-form__input2 mb-0" data-title="<?php echo translate("general_dt_filters_entity_status_title"); ?>" name="status">
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_status_option_all_text"); ?></option>
        <option value="new"><?php echo translate("general_dt_filters_entity_status_option_new_text"); ?></option>
        <option value="moderated"><?php echo translate("general_dt_filters_entity_status_option_moderated_text"); ?></option>
    </select>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_publication_status_label_text"); ?></label>
    <select class="dt_filter minfo-form__input2 mb-0" data-title="<?php echo translate("general_dt_filters_entity_publication_status_title"); ?>" name="visibility">
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_status_option_all_text"); ?></option>
        <option value="1"><?php echo translate("general_dt_filters_entity_status_option_yes_text"); ?></option>
        <option value="0"><?php echo translate("general_dt_filters_entity_status_option_no_text"); ?></option>
    </select>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_category_label_text"); ?></label>
    <select class="dt_filter minfo-form__input2 mb-0" data-title="<?php echo translate("general_dt_filters_entity_category_title"); ?>" name="category">
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_category_option_all_text"); ?></option>
        <?php foreach($blog_categories as $item_category) { ?>
            <option value="<?php echo $item_category['id_category']; ?>"><?php echo $item_category['name']; ?></option>
        <?php } ?>
    </select>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_country_label_text"); ?></label>
    <select class="dt_filter minfo-form__input2 mb-0" data-title="<?php echo translate("general_dt_filters_entity_country_title"); ?>" name="country">
        <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_country_option_all_text"); ?></option>
        <option value="0"><?php echo translate("general_dt_filters_entity_country_option_none_text"); ?></option>
        <?php echo getCountrySelectOptions($blog_countries, 0, array('include_default_option' => false));?>
    </select>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_created_date_label_text_alternate"); ?></label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input class="datepicker-init start_from dt_filter" id="start_from" type="text" placeholder="<?php echo translate("general_dt_filters_entity_date_from_placeholder"); ?>" data-title="<?php echo translate("general_dt_filters_entity_created_date_from_title"); ?>" name="start_from" readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input class="datepicker-init start_to dt_filter" id="start_to" type="text" placeholder="<?php echo translate("general_dt_filters_entity_date_to_placeholder"); ?>" data-title="<?php echo translate("general_dt_filters_entity_created_date_to_title"); ?>" name="start_to" readonly>
        </div>
    </div>
</div>