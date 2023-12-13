<div class="b2b-sidebar">
    <?php
        if (!empty($searchParams)) {
            views('new/partials/active_filters_view', ['searchParams' => $searchParams]);
        }
    ?>

    <div class="filter-block call-action" data-js-action="lazy-loading:b2b-search-form-validation">
        <h2 class="filter-block__ttl"><?php echo translate('sidebar_search_form_title'); ?></h2>

        <div class="filter-options-list hide-max-list-form-elements js-hide-max-list-form-elements">
            <div id="js-search-b2b-uri-info">
                <select id="js-search-b2b-golden-categories-select" class="filter-search-form__field ep-select" name="golden_categories">
                    <option value="" selected><?php echo translate('sidebar_choose_golden_category_placeholder'); ?></option>
                    <?php foreach ($goldenCategories as $category) {?>
                        <option
                            value="<?php echo $category['id_group']; ?>"
                            <?php echo selected($category['id_group'], $appliedFilters['byGoldenCategory'] ?? 0); ?>
                        >
                            <?php echo $category['title']; ?>
                        </option>
                    <?php } ?>
                </select>

                <select id="js-search-b2b-industry-select" class="filter-search-form__field ep-select" name="industry">
                    <option value="" selected><?php echo translate('sidebar_choose_industry_select_placeholder'); ?></option>
                    <?php foreach ($allIndustries as $industry) {?>
                        <option
                            value="<?php echo $industry['category_id']; ?>"
                            data-name="<?php echo strForURL($industry['name']); ?>"
                            data-id-group="<?php echo $industry['golden_category']['id_group']; ?>"
                            <?php echo selected($industry['category_id'], $appliedFilters['byIndustry'] ?? 0); ?>
                        >
                            <?php echo $industry['name']; ?>
                        </option>
                    <?php } ?>
                </select>

                <select
                    id="js-search-b2b-category-select"
                    class="filter-search-form__field ep-select"
                    name="category"
                    data-applied-filters="<?php echo $appliedFilters['byCategory'] ?? 0; ?>"
                    disabled
                >
                    <option value="" selected><?php echo translate('sidebar_category_select_placeholder'); ?></option>
                    <?php foreach ($allIndustries as $industry) {?>
                        <?php if (!empty($allCategoriesByIndustry[$industry['category_id']])) {?>
                            <optgroup data-id="<?php echo $industry['category_id']; ?>" label="<?php echo cleanOutput($industry['name']); ?>">
                                <?php foreach ($allCategoriesByIndustry[$industry['category_id']] as $category) {?>
                                    <option
                                        data-id="<?php echo $category['category_id']; ?>"
                                        value="<?php echo $category['category_id']; ?>"
                                        data-name="<?php echo strForURL($category['name']); ?>"
                                    >
                                        <?php echo $category['name']; ?>
                                    </option>
                                <?php }?>
                            </optgroup>
                        <?php }?>
                    <?php }?>
                </select>

                <select class="filter-search-form__field ep-select" name="country" id="country">
                    <option value="" selected>
                        <?php echo translate('sidebar_select_country_placeholder'); ?>
                    </option>
                    <?php foreach ($allCountries as $country) {?>
                        <option
                            value="<?php echo $country['id']; ?>"
                            data-name="<?php echo $country['country_alias']; ?>"
                            <?php echo selected($country['id'], $appliedFilters['byCountry'] ?? 0); ?>
                        >
                            <?php echo $country['country']; ?>
                        </option>
                    <?php }?>
                </select>
            </div>

            <form id="js-b2b-search-form" class="filter-search-form" method="GET">
                <select class="filter-search-form__field ep-select" name="partener_type">
                    <option value="" selected><?php echo translate('sidebar_select_choose_type_placeholder'); ?></option>
                    <?php foreach ($allPartnersTypes as $partnerType) {?>
                        <option
                            value="<?php echo $partnerType['id_type']; ?>"
                            <?php echo selected($partnerType['id_type'], $appliedFilters['byPartnerType'] ?? 0); ?>
                        >
                            <?php echo $partnerType['name']; ?>
                        </option>
                    <?php }?>
                </select>

                <div class="relative-b">
                    <input
                        class="filter-search-form__field ep-input validate[maxSize[50]]"
                        type="text"
                        name="keywords"
                        placeholder="<?php echo translate('search_params_label_keywords', null, true); ?>"
                        maxlength="50"
                        <?php echo isset($appliedFilters['byKeywords']) ? 'value="' . cleanOutput($appliedFilters['byKeywords']) . '"' : ''; ?>
                    >
                </div>

                <input id="js-golden-cateories-hidden-input" type="hidden" name="golden_category" value="<?php echo $appliedFilters['byGoldenCategory'] ?? ''; ?>">
            </form>
        </div>

        <button
            class="btn btn-new16 btn-light btn-block call-action"
            data-js-action="b2b-search-form:submit"
            type="button"
        >
            <?php echo translate('sidebar_search_form_title'); ?>
        </button>
    </div>

    <?php if (!empty($b2bcountries)) {?>
        <div class="filter-block">
            <h2 class="filter-block__ttl"><?php echo translate('sidebar_filters_countries_title'); ?></h2>

            <ul class="filter-options-list js-hide-max-list">
                <?php foreach ($b2bcountries as $country) {?>
                    <li class="filter-options-list__item">
                        <div class="filter-options-list__inner">
                            <a
                                class="filter-options-list__link"
                                href="<?php echo __SITE_URL . 'b2b/all/country/' . strForURL($country['name'] . ' ' . $country['id']); ?>"
                                title="<?php echo cleanOutput(capitalWord($country['name'])); ?>"
                                <?php echo addQaUniqueIdentifier('global__sidebar-country'); ?>
                            >
                                <?php echo capitalWord($country['name']); ?>
                            </a>
                            <span class="filter-options-list__counter" <?php echo addQaUniqueIdentifier('global__sidebar-counter'); ?>>
                                <?php echo $country['counter']; ?>
                            </span>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
    <?php }?>

    <?php if (!empty($b2bindustries)) {?>
        <div class="filter-block">
            <h2 class="filter-block__ttl"><?php echo translate('sidebar_filters_industries_title'); ?></h2>

            <ul class="filter-options-list js-hide-max-list">
                <?php foreach ($b2bindustries as $industry) {?>
                    <li class="filter-options-list__item">
                        <div class="filter-options-list__inner">
                            <a
                                class="filter-options-list__link"
                                href="<?php echo __SITE_URL . 'b2b/all/industry/' . strForURL($industry['name'] . ' ' . $industry['id']); ?>"
                                title="<?php echo cleanOutput($industry['name']); ?>"
                                <?php echo addQaUniqueIdentifier('global__sidebar-industry'); ?>
                            >
                                <?php echo $industry['name']; ?>
                            </a>
                            <span class="filter-options-list__counter" <?php echo addQaUniqueIdentifier('global__sidebar-counter'); ?>>
                                <?php echo $industry['counter']; ?>
                            </span>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
    <?php }?>

    <?php if (!empty($appliedFilters['byIndustry']) && !empty($categoriesCountersByIndustry[$appliedFilters['byIndustry']])) {?>
        <div class="filter-block">
            <h2 class="filter-block__ttl"><?php echo translate('sidebar_filters_categories_title'); ?></h2>

            <ul class="filter-options-list js-hide-max-list">
                <?php foreach ($categoriesCountersByIndustry[$appliedFilters['byIndustry']] as $category) {?>
                    <li class="filter-options-list__item">
                        <div class="filter-options-list__inner">
                            <a
                                class="filter-options-list__link"
                                href="<?php echo replace_dynamic_uri(strForURL($category['name'] . ' ' . $category['id']), $linksTpl['category']); ?>"
                                title="<?php echo cleanOutput($category['name']); ?>"
                                <?php echo addQaUniqueIdentifier('global__sidebar-category'); ?>
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
    <?php }?>
</div>
