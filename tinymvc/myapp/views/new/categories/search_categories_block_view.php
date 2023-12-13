
<div class="minfo-title">
    <h3 class="minfo-title__name">Search category</h3>
</div>

<form class="validengine" data-js-action="categories:search-form-submit">
    <div class="input-group all-categories__input-group">
        <span class="input-group-btn">
            <i class="js-clean-categ-search ep-icon ep-icon_remove-stroke"></i>
            <input name="keywords" type="text" class="form-control validate[required, minSize[3], maxSize[50]]" maxlength="50" value="<?php echo cleanOutput($keywords ?: '');?>" placeholder="Enter keywords">
        </span>
        <span class="input-group-btn">
            <button class="btn btn-primary btn-block" type="submit">Search category</button>
        </span>
        <span class="fileinput-loader-btn ml-10 fs-15" style="display: none;">
            <img class="image" src="<?php echo __IMG_URL;?>public/img/loader.svg" alt="loader"> Searching...
        </span>
    </div>
</form>

<?php
    if (!empty($keywords)) {
        tmvc::instance()->controller->view->display('new/categories/search_categories_view', array('categories' => $categories, 'categories_count' => $categories_count));
    }
?>
