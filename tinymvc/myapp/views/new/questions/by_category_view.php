<?php
    $count_categories = 0;
    $max_categories = 10;
?>
<?php if(!empty($counter_category)){?>
    <h2 class="community-sidebar-title <?php echo $current_page == 'all' ? ' mt-0' : ''; ?>"><?php echo translate('community_by_category_text'); ?></h2>

    <ul class="sidebar-categories-list" id="js-categories-list">
        <?php
        foreach ($quest_cats as $id_category => $data_category) {
            if (!isset($counter_category[$id_category])){
                continue;
            }
            $count_categories++;
        ?>
        <li class="sidebar-categories-list__item<?php echo $count_categories > $max_categories ? " display-n_i" : ""?>" <?php echo $count_categories > $max_categories ? "data-minMax" : ""?>>
            <a
                class="sidebar-categories-list__link"
                title="<?php echo $data_category['title_cat'] ?>"
                href="<?php echo replace_dynamic_uri($data_category['url'], $links_tpl[$questions_uri_components['category']], __COMMUNITY_ALL_URL); ?>"
                <?php echo addQaUniqueIdentifier('global__question-type'); ?>
            >
                <?php echo $data_category['title_cat'] ?>
            </a>
            <span class="sidebar-categories-list__counter" <?php echo addQaUniqueIdentifier('global__question-counter'); ?>>
                <?php echo $counter_category[$id_category];?>
            </span>
        </li>
        <?php }?>
    </ul>
    <?php if($count_categories > $max_categories) {?>
    <div class="maxlist-more">
        <button class="btn btn-light btn--50 btn-block call-action" <?php echo addQaUniqueIdentifier('global__sidebar__view-more-btn'); ?> data-js-action="minMax:toggle" data-target="js-categories-list" data-text="View more" data-text-toggled="View less">View more</button>
    </div>
<?php
    }
} ?>
