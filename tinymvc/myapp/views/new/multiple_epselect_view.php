<?php
    $selector = "#multiple-epselect-{$widget_id}";
    $options = [
        'selected_cat_json'                             => json_decode($selected_cat_json),
        'industries_required'                           => empty($industries_required) || $industries_required == false ? 0 : 1,
        'industries_only'                               => empty($industries_only) || $industries_only == false ? 0 : 1,
        'max_industries'                                => !empty($max_industries) && $max_industries > 0 ? $max_industries : 0,
        'industries_select_all'                         => !empty($industries_select_all) && $industries_select_all > 0 && !empty($industries_only) && $industries_only != false ? 1 : 0,
        'categories_selected_by_id'                     => !empty($categories_selected_by_id) ? 0 : 1,
        'industries_selected'                           => !empty($industries_selected) ? $industries_selected_count : 1,
        'translate_multiple_select_selected_categories' => translate('multiple_select_selected_categories'),
        'translate_multiple_select_no_industries'       => translate('multiple_select_no_industries'),
        'widget_id'                                     => $widget_id,
        'input_suffix'                                  => $input_suffix,
    ];

    if ($dispatchDynamicFragment) {
        echo dispatchDynamicFragment(
            'multiple-select:boot',
            [$selector, $options],
            true
        );
    } else {
        echo dispatchDynamicFragmentInCompatMode(
            'multiple-select:boot',
            asset('public/plug/multiple-ep-select/multiple-ep-select.js', 'legacy'),
            sprintf(
                "function () { $('%s').multipleEpSelect(%s); }",
                $selector,
                json_encode($options)
            ),
            [$selector, $options],
            true
        );
    }
?>

<?php
    $multiple_toggle = '';

    if(empty($industries_only) || $industries_only == false){
        $multiple_toggle = '<i class="multiple-epselect__toggle ep-icon ep-icon_plus-stroke"></i>';
        $multiple_ul_empty = '<ul class="multiple-epselect__inner"></ul>';
    }
?>

<div
    id="multiple-epselect-<?php echo $widget_id;?>"
    class="multiple-epselect"
    <?php echo addQaUniqueIdentifier("company-edit__select-industries-div")?>
    tabindex="0"
>
    <div class="js-multiple-epselect-inputs"></div>

    <div class="multiple-epselect__input">
        <?php if(!empty($industries_select_all)
            && $industries_select_all > 0
            && !empty($industries_only)
            && $industries_only != false
            && empty($industries_selected)){?>
            <?php echo translate('multiple_select_select_industries_placeholder');?>
        <?php }else if(!empty($industries_select_all)
            && $industries_select_all > 0
            && !empty($industries_only)
            && $industries_only != false
            && !empty($industries_selected)){?>
            <?php echo translate('multiple_select_count_selected_industries_placeholder', array('{{COUNT}}' => $industries_selected_count));?>
        <?php }else if(empty($industries_selected)){?>
            <?php echo (empty($input_placeholder))?translate('multiple_select_select_industries_or_categories_placeholder'):$input_placeholder;?>
        <?php }else{ ?>
            <?php echo translate('multiple_select_select_industries_and_categories_count_placeholder', array('{{COUNT_C}}' => count($categories_selected_by_id), '{{COUNT_I}}' => $industries_selected_count));?>
        <?php } ?>
    </div>
    <div class="multiple-epselect__list-wr">
        <ul class="multiple-epselect__list">
        <?php if(
            !empty($industries_select_all)
            && $industries_select_all > 0
            && !empty($industries_only)
            && $industries_only != false
            ){?>
            <li class="multiple-epselect__parent multiple-epselect__parent--all">
                <div class="multiple-epselect__top">
                    <label
                        class="js-multiple-epselect-all js-call-multiple-select-all-industries"
                        data-callback="multipleSelectAllIndustries"
                    >
                        <span class="pseudo-checkbox mr-15"></span>

                        <span class="name"><?php echo translate('multiple_select_industries_label_select_all');?></span>
                    </label>
                </div>
            </li>
        <?php }?>

        <?php if(empty($industries_selected)){?>
            <?php
                foreach($industries as $industry){
                    $industry_has_childrens = true;
                    $industry_has_childrens_class = '';
                    $industry_top_class = '';

                    if(
                        (isset($industry['cat_childrens']) && empty($industry['cat_childrens']))
                        ||
                        (isset($industry['has_children']) && empty($industry['has_children']))
                    ){
                        $industry_has_childrens = false;
                        $industry_has_childrens_class = ' disabled';
                    }

                    if(
                        !empty($industries_top)
                        && in_array($industry['category_id'], $industries_top)
                    ){
                        $industry_top_class = ' order--1';
                    }
            ?>
                <li
                    class="multiple-epselect__parent<?php echo $industry_has_childrens_class;?><?php echo $industry_top_class;?>"
                    data-industry="<?php echo $industry['category_id'];?>"
                >
                    <div class="multiple-epselect__top">
                        <?php if(!$industry_has_childrens){?>
                            <label>
                                <?php if(empty($industries_only) || $industries_only == false){?>
                                    <i class="multiple-epselect__toggle ep-icon ep-icon_minus-stroke txt-gray-light"></i>
                                <?php }else{?>
                                    <span class="pseudo-checkbox disabled mr-15"></span>
                                <?php }?>
                        <?php }else{?>
                            <?php if(empty($industries_only) || $industries_only == false){?>
                                <label
                                    class="js-call-multiple-toggle-categories"
                                    data-callback="multipleToggleCategories"
                                >
                                    <?php echo $multiple_toggle;?>
                            <?php }else{?>
                                <label
                                    class="js-call-check-industry"
                                    data-callback="callCheckIndustry"
                                >
                                    <span class="pseudo-checkbox mr-15"></span>
                            <?php }?>
                        <?php }?>

                            <span class="name"><?php echo $industry['name']?></span>
                        </label>
                    </div>

                    <?php echo $multiple_ul_empty;?>
                </li>
            <?php } ?>
        <?php }else{ ?>
            <?php
                foreach($industries as $industry){
                    $industry_item_checked = isset($industries_selected[$industry['category_id']])?true:false;
                    $industry_count = count($categories[$industry['category_id']]);

                    $industry_has_childrens = true;
                    $industry_has_childrens_class = '';
                    if(
                        (isset($industry['cat_childrens']) && empty($industry['cat_childrens']))
                        ||
                        (isset($industry['has_children']) && empty($industry['has_children']))
                    ){
                        $industry_has_childrens = false;
                        $industry_has_childrens_class = ' disabled';
                    }
            ?>
                <li
                    class="multiple-epselect__parent<?php echo $industry_has_childrens_class;?><?php echo (isset($selected_categories_array[$industry['category_id']]) || $industry_item_checked)?' checked order--1':'';?>"
                    data-industry="<?php echo $industry['category_id'];?>"
                >
                    <div class="multiple-epselect__top">
                        <?php if(!$industry_has_childrens){?>
                            <label>
                                <?php if(empty($industries_only) || $industries_only == false){?>
                                    <i class="multiple-epselect__toggle ep-icon ep-icon_minus-stroke txt-gray-light"></i>
                                <?php }else{?>
                                    <span class="pseudo-checkbox disabled mr-15"></span>
                                <?php }?>
                        <?php }else{?>
                            <?php if(empty($industries_only) || $industries_only == false){?>
                                <label
                                    class="js-call-multiple-toggle-categories"
                                    data-callback="multipleToggleCategories"
                                >
                                    <?php echo $multiple_toggle;?>
                            <?php }else{?>
                                <label
                                    class="js-call-check-industry"
                                    data-callback="callCheckIndustry"
                                >
                                    <span class="pseudo-checkbox<?php echo ($industry_item_checked)?' checked':'';?> mr-15"></span>
                            <?php }?>
                        <?php }?>

                            <span class="name"><?php echo $industry['name']?></span>
                            <?php if($industry_count > 0){?>
                                <div class="multiple-epselect__counted"><?php echo translate('multiple_select_selected_categories');?> <span class="js-count-cat-selected"><?php echo (isset($selected_categories_array[$industry['category_id']]))?(int)$selected_categories_array[$industry['category_id']]:0;?></span>/<?php echo $industry_count;?></div>
                            <?php }?>
                        </label>
                    </div>

                    <?php if(!$industry_item_checked){?>
                        <?php echo $multiple_ul_empty; ?>
                    <?php }else{?>
                        <?php if(empty($industries_only) || $industries_only == false){?>
                            <ul class="multiple-epselect__inner">
                                <?php foreach($categories[$industry['category_id']] as $category) {?>
                                    <li>
                                        <label
                                            class="js-call-check-category"
                                            data-callback="callCheckCategory"
                                            data-category="<?php echo $category['category_id'];?>"
                                        >
                                            <span class="pseudo-checkbox<?php echo ((isset($categories_selected_by_id[$category['category_id']]))?' checked':'');?> mr-15"></span>

                                            <span class="name"><?php echo $category['name']?></span>
                                        </label>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php }?>
                    <?php } ?>
                </li>
            <?php } ?>
        <?php } ?>
        </ul>
    </div>

    <?php if(true === $show_disclaimer && !empty($disclaimer_text)){ ?>
        <div class="pt-5 fs-12 txt-normal txt-blue2"><?php echo $disclaimer_text;?></div>
    <?php } ?>
</div>

