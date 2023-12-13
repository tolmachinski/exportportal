<label class="input-label input-label--required mt-0"><?php echo translate('seller_pictures_dashboard_modal_field_categrory_label_text'); ?></label>

<div id="js-category-container"></div>

<script type="text/template" id="js-categories-select">

    <div id="js-category" class="input-group">

        <select
            name="category"
            class="form-control validate[required]"
            <?php echo addQaUniqueIdentifier('global__categories-field__category-select'); ?>
        >

            <?php if(!empty($categories)) {

                $condition = selected($main['id_category'], $category['id_category']); ?>

                <?php if($condition === "selected") { ?>
                    <option value=""><?php echo translate('seller_library_dashboard_modal_field_category_placeholder'); ?></option>
                <?php } ?>

                <?php foreach($categories as $category) { ?>
                    <option value="<?php echo $category['id_category']; ?>" <?php echo selected($main['id_category'], $category['id_category']); ?>>
                        <?php echo cleanOutput($category['category_title']); ?>
                    </option>
                <?php } ?>

            <?php } ?>

        </select>

        <div class="input-group-btn">
            <a class="btn btn-dark call-function"
                <?php echo addQaUniqueIdentifier("popup__categories-field__add-category-btn"); ?>
               data-callback="addNewCategory"
               data-title="<?php echo translate("seller_pictures_categories_dashboard_add_category_modal_title", null, true); ?>"
               title="<?php echo translate("seller_pictures_categories_dashboard_add_category_modal_title", null, true); ?>"
               href="#"
            >

               <i class="ep-icon ep-icon_plus-circle "></i>
            </a>
        </div>

    </div>

</script>

<script type="text/template" id="js-categories-input">

    <div id="js-new-category" class="input-group">
        <input class="form-control validate[required,maxSize[50]]"
               type="text"
               name="new_category"
               placeholder="<?php echo translate("seller_pictures_write_new_category_text", null, true); ?>"
               <?php echo addQaUniqueIdentifier('global__categories-field__new-category-input'); ?>
        >

        <div class="input-group-btn">
            <a
                class="btn btn-dark call-function"
                data-callback="showSelectCategories"
                href="#"
                <?php echo addQaUniqueIdentifier('global__categories-field__show-selected-category-btn'); ?>
            >
                <i class="ep-icon ep-icon_remove-circle "></i>
            </a>
        </div>
    </div>

</script>

<script type="application/javascript">

    var addNewCategory = function() {
        $("#js-category-container").html($('#js-categories-input').html());
    }

    var showSelectCategories = function(){
        $("#js-category-container").html($('#js-categories-select').html());
    };

    $(function(){
        showSelectCategories();
    });

</script>
