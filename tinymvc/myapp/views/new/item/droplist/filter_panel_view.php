<div class="container-fluid-modal">
    <label class="input-label"><?php echo translate("general_dt_filters_entity_search_label_text"); ?></label>
    <div class="row">
        <div class="col-12 col-lg-12 mb-15-sm-max">
            <input type="text"
                   name="keywords"
                   id="filter-keywords"
                   class="dt_filter keywords"
                   placeholder="<?php echo translate("general_dt_filters_entity_search_placeholder", null, true); ?>"
                   data-title="<?php echo translate("general_dt_filters_entity_search_title", null, true); ?>"
                   maxlength="50">
        </div>
    </div>

    <label class="input-label"><?php echo translate("general_dt_filters_entity_category_label_text"); ?></label>
    <div class="row">
        <div class="col-12 col-lg-12 mb-15-sm-max">
            <select class="dt_filter minfo-form__input2 mb-0" level="1" name="category" data-title="<?php echo translate("general_dt_filters_entity_category_title"); ?>">
                <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_category_option_all_text"); ?></option>
                <?php foreach ($categories as $category) { ?>
                    <option data-categories="<?php echo $category['category_id']; ?>" value="<?php echo $category['category_id']; ?>"><?php echo capitalWord($category['name']); ?> (<?php echo $category['counter']; ?>)</option>
                    <?php if (!empty($category['subcats'])) { recursive_ctegories_product($category['subcats'], ' '); } ?>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <label class="input-label"><?php echo translate("general_dt_filters_price_fluctuation", null, true); ?></label>
            <select class="dt_filter minfo-form__input2 mb-0" level="1" name="price_fluctuation" data-title="<?php echo translate("general_dt_filters_entity_category_title"); ?>">
                <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_status_option_all_text", null, true); ?></option>
                <option value="lt"><?php echo translate("general_dt_filters_price_dropped", null, true); ?></option>
                <option value="gt"><?php echo translate("general_dt_filters_price_went_up", null, true); ?></option>
                <option value="eq"><?php echo translate("general_dt_filters_price_is_the_same", null, true); ?></option>
            </select>
        </div>

        <div class="col-12 col-lg-6 mb-15-sm-max">
            <label class="input-label"><?php echo translate("general_dt_filters_availability", null, true); ?></label>
            <select class="dt_filter minfo-form__input2 mb-0" level="1" name="availability" data-title="<?php echo translate("general_dt_filters_entity_category_title"); ?>">
                <option data-default="true" value=""><?php echo translate("general_dt_filters_entity_status_option_all_text", null, true); ?></option>
                <option value="<?php echo \App\Common\Contracts\Droplist\ItemStatus::ACTIVE() ?>"><?php echo translate("general_dt_filters_available", null, true); ?></option>
                <option value="<?php echo \App\Common\Contracts\Droplist\ItemStatus::BLOCKED() ?>"><?php echo translate("general_dt_filters_not_available", null, true); ?></option>
            </select>
        </div>
    </div>

    <label class="input-label"><?php echo translate("general_dt_filters_price_change_date", null, true); ?></label>
    <div class="row">
        <div class="col-12 col-lg-6 mb-15-sm-max">
            <input class="datepicker-init start_from dt_filter" id="price_changed_at" type="text" placeholder="From" data-title="Price change date from" name="price_changed_at" placeholder="From" readonly>
        </div>
        <div class="col-12 col-lg-6">
            <input class="datepicker-init start_to dt_filter" id="price_changed_to" type="text" placeholder="To" data-title="Price change date to" name="price_changed_to" readonly>
        </div>
    </div>
</div>
