<div class="block-search pb-30">
<?php if (isset($categories_count)) { ?>
    <h2 class="title-public__txt">Found (<?php echo intval($categories_count); ?>)</h2>
<?php } ?>

<?php if (!empty($categories)) { ?>
    <div class="all-categories__search-results" data-count-items="<?php echo $categories_count;?>">
        <?php tmvc::instance()->controller->view->display('new/categories/search_items', array('categories' => $categories));?>
    </div>
    <button class="btn btn-light btn-block mt-15 js-btn-more-categories" type="button">MORE</button>
<?php } else { ?>
    <div class="info-alert-b mt-15">
        <i class="ep-icon ep-icon_info-stroke"></i> There are no matching categories by your search request.
    </div>
<?php } ?>
</div>
