<div class="filter-options-multilist__sub js-filter-multilist-subitems">
    <?php foreach ($item['subcats'] as $subitem) {?>
        <?php
            $linkCategoryPrefix = isset($subitem['subcats']) ? '' : strForURL($item['name']) . '-';
            if ($isSearchPage ?? null) {
                $categoryUrl = 'category/' . strForURL($subitem['name']) . '/' . $subitem['category_id'] . '/' . $categoryLink;
            } else {
                $categoryUrl = replace_dynamic_uri($linkCategoryPrefix . strForURL($subitem['name']) . '/' . $subitem['category_id'], $categoryLink);
            }
        ?>

        <div class="filter-options-multilist__sub-item">
            <span><?php echo widgetGetSvgIcon('minus', 10, 11, 'filter-options-multilist__sub-item-icon'); ?></span>
            <a
                class="filter-options-multilist__sub-link"
                href="<?php echo $categoryUrl; ?>"
                <?php echo addQaUniqueIdentifier('global__sidebar-toggled-category'); ?>
            >
                <?php echo $subitem['name']; ?>
            </a>

            <span class="filter-options-multilist__counter" <?php echo addQaUniqueIdentifier('global__sidebar-counter'); ?>>
                <?php echo $subitem['counter']; ?>
            </span>
        </div>

        <?php if (isset($subitem['subcats'])) {
            views('new/item/filter_options/tree_cat_sidebar_view', ['item' => $subitem]);
        } ?>
    <?php }?>
</div>
