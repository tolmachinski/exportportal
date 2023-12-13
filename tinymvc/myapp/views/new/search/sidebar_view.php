<div class="filter-options">
    <?php
        if (!empty($searchParams)) {
            views('new/partials/active_filters_view', ['searchParams' => $searchParams]);
        }
    ?>

    <?php if (!empty($counterCategories)) {?>
        <div class="filter-block">
            <h3 class="filter-block__ttl">Categories</h3>

            <ul class="filter-options-multilist js-hide-max-list">
                <?php foreach ($counterCategories as $item) {?>
                    <li class="filter-options-multilist__item js-filter-multilist-item">
                        <div class="filter-options-multilist__header">
                            <?php if (isset($item['subcats'])) {?>
                                <span
                                    class="filter-options-multilist__toggler call-action"
                                    data-js-action="filters:multilist-toggle"
                                    <?php echo addQaUniqueIdentifier('global__sidebar-toggle-category'); ?>
                                >
                                    <?php echo widgetGetSvgIcon('plus', 10, 10, 'filter-options-multilist__toggler-icon'); ?>
                                </span>
                            <?php }?>
                            <a
                                class="filter-options-multilist__link"
                                href="<?php echo 'category/' . strForURL($item['name']) . '/' . $item['category_id'] . '/' . $categoryLink;?>"
                                <?php echo addQaUniqueIdentifier('global__sidebar-category'); ?>
                            >
                                <?php echo $item['name']; ?>
                            </a>

                            <span class="filter-options-multilist__counter" <?php echo addQaUniqueIdentifier('global__sidebar-counter'); ?>>
                                <?php echo $item['counter']; ?>
                            </span>
                        </div>

                        <?php if (isset($item['subcats'])) {?>
                            <?php //isSearchPage need fix later?>
                            <?php views('new/item/filter_options/tree_cat_sidebar_view', ['item' => $item, 'isSearchPage' => true]); ?>
                        <?php }?>
                    </li>
                <?php } ?>
            </ul>
        </div>
    <?php }?>

    <?php if (!empty($searchCountries)) {?>
        <div class="filter-block">
            <h3 class="filter-block__ttl">Countries</h3>

            <ul class="filter-options-list js-hide-max-list">
                <?php foreach ($searchCountries as $item) {?>
                    <li class="filter-options-list__item">
                        <div class="filter-options-list__inner">
                            <a
                                class="filter-options-list__link"
                                href="<?php echo replace_dynamic_uri(strForURL($item['country']) . '-' . $item['loc_id'], $countryLink);?>"
                                <?php echo addQaUniqueIdentifier('global__sidebar-country'); ?>
                            >
                                <?php echo $item['country']; ?>
                            </a>
                            <span class="filter-options-list__counter" <?php echo addQaUniqueIdentifier('global__sidebar-counter'); ?>>
                                <?php echo $item['loc_count']; ?>
                            </span>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
    <?php }?>

    <div class="filter-block">
        <h3 class="filter-block__ttl">New Search</h3>

        <form
            class="validengine filter-search-form"
            data-js-action="form:submit_form_search"
            data-type="items"
            action="<?php echo __SITE_URL; ?>"
        >
            <input
                class="filter-search-form__field ep-input validate[required, minSize[2]]"
                type="text"
                name="keywords"
                maxlength="50"
                placeholder="Keyword"
                value="<?php echo $keywords; ?>"
            />

            <select class="filter-search-form__field ep-select type_url" name="country">
                <?php
                    $portCountries = array_map(function ($country) {
                        $country['country_url'] = strForURL($country['country'] . ' ' . $country['id']);

                        return $country;
                    }, $countries);
                ?>

                <?php echo getCountrySelectOptions($portCountries, id_from_link($countrySelected), ['value' => 'country_url']); ?>
            </select>

            <select class="filter-search-form__field ep-select type_url" name="category">
                <option value="">Choose category</option>
                <?php foreach ($mainCats as $cat) {?>
                    <option value="<?php echo strForURL($cat['name']) . '/' . $cat['category_id']; ?>">
                        <?php echo $cat['name']; ?>
                    </option>
                <?php } ?>
            </select>

            <button class="filter-search-form__btn btn btn-new16 btn-light btn-block" type="submit">Search</button>
        </form>
    </div>
</div>

