<?php $isset_search_params = !empty($search_params);?>
<div class="questions__header <?php echo $questions_counter > 0 ? '' : 'questions__header--not-found';?>">
    <h2
        class="questions__heading <?php echo $current_page === "all" && !$search_params ? "questions__heading--all" : ""; ?> <?php echo $search_params ? "questions__heading--search" : ""; ?>"
    >
        <?php echo $isset_search_params ? translate('community_found_questions_title') . '<span class="txt-gray" ' . addQaUniqueIdentifier('global__question-counter') . '> ' . $questions_counter . '</span>' : $page_title;?>
    </h2>

    <?php if($current_page != 'questions' && $questions_counter > 0){?>
        <div class="questions__header-btns <?php echo ($current_page == 'all' && $search_params) ? 'mb-0' : ''; ?>">
            <?php if (!$isset_search_params) { ?>
                <div class="questions__filters-btn">
                    <a
                        class="btn btn-light btn--50 mnw-140 fancybox.ajax fancyboxValidateModal"
                        data-title="<?php echo translate('community_filters_word', null, true); ?>"
                        data-mw="535"
                        href="<?php echo __COMMUNITY_URL . 'community_questions/popup_forms_all/show_filters'; ?>"
                    >
                        <?php echo translate('community_filters_word'); ?>
                    </a>
                </div>
            <?php } ?>

            <div class="dropdown questions__filter">
                <a
                class="dropdown-toggle questions__toggle"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false">
                    <?php echo isset($order_by) ? $order_by : $list_sort_by[array_key_first($list_sort_by)]; ?>

                    <span class="questions__filter-arrow" >
                        <img
                            src="<?php echo asset('public/build/images/arrow-select-new2.png'); ?>"
                            alt="Select arrow"
                        >
                    </span>
                </a>

                <div class="dropdown-menu">
                    <?php foreach($list_sort_by as $sort_by => $sort_title){?>
                        <a  class="dropdown-item"
                            href="<?php echo replace_dynamic_uri($sort_by, $links_tpl['order_by'], __COMMUNITY_ALL_URL); ?>"
                            title="<?php echo $sort_title; ?>">
                            <span class="txt"><?php echo $sort_title;?></span>
                        </a>
                    <?php }?>
                </div>
            </div>
        </div>
    <?php }?>
</div>

<?php views()->display('new/questions/list_view') ?>

<?php if (!empty($questions)) { ?>
    <?php if($current_page === 'questions'){?>
        <div class="questions__view-all">
            <a class="btn btn-outline-dark btn--50 mnw-200 mnw-236-lg mnw-290-sm" href="<?php echo __COMMUNITY_ALL_URL; ?>"><?php echo translate('community_view_all_questions_button_text'); ?></a>
        </div>
    <?php } else {?>
        <div class="questions__view-pagination" <?php echo addQaUniqueIdentifier('community__list_paginator'); ?>>
            <?php views()->display('new/paginator_view'); ?>
        </div>
    <?php } ?>
<?php } ?>

<?php views()->display('new/questions/ask_community_view'); ?>
