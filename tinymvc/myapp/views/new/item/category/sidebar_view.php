<?php
    if (!empty($searchParams)) {
        views('new/partials/active_filters_view', ['searchParams' => $searchParams]);
    }
?>

<?php if (!empty($subcats) && empty($categoryHeader)) {?>
    <div class="filter-block">
        <h3 class="filter-block__ttl">Subcategories</h3>

        <ul class="filter-options-list js-hide-max-list">
            <?php foreach ($subcats as $category) {?>
                <?php $catlink = ((2 == $category['cat_type']) ? strForURL($parentCategory['name']) . '-' : '') . strForURL($category['name']) . '/' . $category['category_id']; ?>
                <li class="filter-options-list__item">
                    <div class="filter-options-list__inner">
                        <a
                            class="filter-options-list__link"
                            data-id="<?php echo $category['category_id']; ?>"
                            href="<?php echo replace_dynamic_uri($catlink, $linksTpl[$categoryUriComponents['category']]); ?>"
                            <?php echo addQaUniqueIdentifier('global__sidebar-subcategory'); ?>
                        >
                            <?php echo $category['name']; ?>
                        </a>
                        <span class="filter-options-list__counter" <?php echo addQaUniqueIdentifier('global__sidebar-counter'); ?>>
                            <?php echo $category['counter']; ?>
                        </span>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>

<?php if (isset($locations) && count($locations)) {?>
    <div class="filter-block">
        <h3 class="filter-block__ttl">Location</h3>

        <ul class="filter-options-list js-hide-max-list">
            <?php foreach ($locations as $location) {?>
                <?php $locationLink = strForURL($location['loc_name'] . ' ' . $location['loc_id']); ?>
                <li class="filter-options-list__item">
                    <div class="filter-options-list__inner">
                        <a
                            class="filter-options-list__link"
                            data-id="<?php echo $location['loc_id']; ?>"
                            href="<?php echo replace_dynamic_uri($locationLink, $linksTpl[$categoryUriComponents[$location['loc_type']]]); ?>"
                            <?php echo addQaUniqueIdentifier('global__sidebar-country'); ?>
                        >
                            <?php echo $location['loc_name']; ?>
                        </a>
                        <span class="filter-options-list__counter" <?php echo addQaUniqueIdentifier('global__sidebar-counter'); ?>>
                            <?php echo $location['loc_count']; ?>
                        </span>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>

<div class="filter-block">
    <form class="filter-search-form validengine" action="<?php echo $makeFormLink; ?>" method="GET">
        <h3 class="filter-block__ttl">
            <?php echo translate('category_sidebar_show_only_title'); ?>
        </h3>
        <ul class="filter-options-list filter-options-list--mb-33">
            <li class="filter-options-list__item filter-options-list__item--mb-10">
                <label class="custom-checkbox">
                    <input
                        id="sidebar-show-only-featured-checkbox"
                        name="featured"
                        type="checkbox"
                        <?php echo addQaUniqueIdentifier('global__sidebar__show-only_featured-checkbox'); ?>
                        <?php echo isset($filters['featured']) ? 'checked' : ''; ?>
                    >
                    <span class="custom-checkbox__text">
                        <?php echo translate('category_sidebar_featured_input'); ?>
                    </span>
                </label>
            </li>
            <li class="filter-options-list__item filter-options-list__item--mb-10">
                <label class="custom-checkbox">
                    <input
                        id="sidebar-show-only-highlighted-checkbox"
                        name="highlighted"
                        type="checkbox"
                        <?php echo addQaUniqueIdentifier('global__sidebar__show-only_highlighted-checkbox'); ?>
                        <?php echo isset($filters['highlighted']) ? 'checked' : ''; ?>
                    >
                    <span class="custom-checkbox__text">
                        <?php echo translate('category_sidebar_highlighted_input'); ?>
                    </span>
                </label>
            </li>
            <li class="filter-options-list__item filter-options-list__item--mb-10">
                <label class="custom-checkbox">
                    <input
                        id="sidebar-show-only-handmade-checkbox"
                        name="handmade"
                        type="checkbox"
                        <?php echo addQaUniqueIdentifier('global__sidebar__show-only_handmade-checkbox'); ?>
                        <?php echo isset($filters['handmade']) ? 'checked' : ''; ?>
                    >
                    <span class="custom-checkbox__text">
                        <?php echo translate('category_sidebar_handmade_input'); ?>
                    </span>
                </label>
            </li>
        </ul>
        <h3 class="filter-block__ttl filter-block__ttl--mb-14">
            <?php echo translate('category_sidebar_refine_search_title'); ?>
        </h3>
        <input
            class="filter-search-form__field ep-input validate[minSize[2], maxSize[50]]"
            type="text"
            name="keywords"
            maxlength="50"
            value="<?php echo isset($filters['keywords']) ? $filters['keywords'] : ''; ?>"
            placeholder="Keywords"
        />

        <input
            class="filter-search-form__field ep-input validate[min[0], custom[integer]]"
            type="text"
            name="price_from"
            value="<?php echo isset($filters['price_from']) ? $filters['price_from'] : ''; ?>"
            placeholder="Price from"
        >

        <input
            class="filter-search-form__field ep-input validate[min[0], custom[integer]]"
            type="text"
            name="price_to"
            value="<?php echo isset($filters['price_to']) ? $filters['price_to'] : ''; ?>"
            placeholder="Price to"
        >

        <input
            class="filter-search-form__field ep-input validate[max[<?php echo date('Y'); ?>], custom[integer]]"
            type="text"
            name="year_from"
            value="<?php echo isset($filters['year_from']) ? $filters['year_from'] : ''; ?>"
            placeholder="Year from"
        >

        <input
            class="filter-search-form__field ep-input validate[max[<?php echo date('Y'); ?>], custom[integer]]"
            type="text"
            name="year_to"
            value="<?php echo isset($filters['year_to']) ? $filters['year_to'] : ''; ?>"
            placeholder="Year to"
        >

        <?php if (isset($sortBy)) {?>
            <input
                type="hidden"
                id="attributes"
                name="sort_by"
                value="<?php echo $sortBy; ?>"
            >
        <?php } ?>

        <button class="filter-search-form__btn btn btn-new16 btn-light btn-block" type="submit">Search</button>
    </form>
</div>

<?php if (!empty($otherCategories) && empty($categoryHeader)) {?>
    <div class="filter-block">
        <h3 class="filter-block__ttl">Other Categories</h3>

        <ul class="filter-options-list js-hide-max-list">
            <?php foreach ($otherCategories as $category) {?>
                <?php $categoryUrl = (2 == $category['cat_type'] ? strForURL($parentCategory['name']) . '-' : '') . strForURL($category['name']) . '/' . $category['category_id']; ?>
                <li class="filter-options-list__item">
                    <div class="filter-options-list__inner">
                        <a
                            class="filter-options-list__link"
                            data-id="<?php echo $category['category_id']; ?>"
                            href="<?php echo replace_dynamic_uri($categoryUrl, $linksTpl[$categoryUriComponents['category']]); ?>"
                            <?php echo addQaUniqueIdentifier('global__sidebar-other-category'); ?>
                        >
                            <?php echo $category['name']; ?>
                        </a>
                        <span class="filter-options-list__counter" <?php echo addQaUniqueIdentifier('global__sidebar-counter'); ?>>
                            <?php echo $category['counter']; ?>
                        </span>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>

<?php if (isset($otherLocations) && count($otherLocations)) {?>
    <div class="filter-block">
        <h3 class="filter-block__ttl">Other locations</h3>

        <ul class="filter-options-list js-hide-max-list">
            <?php foreach ($otherLocations as $location) {?>
                <?php $locationLink = strForURL($location['loc_name'] . ' ' . $location['loc_id']); ?>
                <li class="filter-options-list__item">
                    <div class="filter-options-list__inner">
                        <a
                            class="filter-options-list__link"
                            data-id="<?php echo $location['loc_id']; ?>"
                            href="<?php echo replace_dynamic_uri($locationLink, $linksTpl[$categoryUriComponents[$location['loc_type']]]); ?>"
                            <?php echo addQaUniqueIdentifier('global__sidebar-country'); ?>
                        >
                            <?php echo $location['loc_name']; ?>
                        </a>
                        <span class="filter-options-list__counter" <?php echo addQaUniqueIdentifier('global__sidebar-counter'); ?>>
                            <?php echo $location['loc_count']; ?>
                        </span>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>
