<?php if (!empty($categories)) { ?>
    <div class="category-fast-search__result-inner" data-count-items="<?php echo $categories_count;?>">
        <?php tmvc::instance()->controller->view->display('new/item/add_item/partials/dropdown_search_items_view', array('categories' => $categories));?>
    </div>
    <button class="btn btn-light btn-block mt-15 js-btn-fast-more-categories" type="button">MORE</button>
<?php } else { ?>
    <div class="info-alert-b">
        <i class="ep-icon ep-icon_info-stroke"></i> There are no matching categories by your search request.
    </div>
<?php } ?>
