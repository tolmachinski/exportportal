<?php ($current_page != 'all' || $search_params) ? views()->display('new/questions/sidebar_questions_list_view') : ''; ?>

<div class="sidebar-questions__filters <?php echo ($current_page == 'all' && !$search_params) ? 'sidebar-questions__filters--all' : ''; ?>">

    <?php if(in_array($current_page, array('question_detail', 'all'))){?>
        <div class="sidebar-questions__filters-category" <?php echo addQaUniqueIdentifier('community__sidebar_categories-list'); ?>>
            <?php views()->display('new/questions/by_category_view');?>
        </div>
    <?php } ?>

    <?php if (!$search_params){?>
        <div
            class="<?php echo ($questions_counter > 0 && $current_page != 'questions') ? 'sidebar-questions__countries' : '' ?>"
            <?php echo addQaUniqueIdentifier('community__sidebar_countries-list'); ?>
        >
            <?php if ($questions_counter > 0) { views()->display('new/questions/by_country_view'); }?>
        </div>
    <?php } ?>
</div>

