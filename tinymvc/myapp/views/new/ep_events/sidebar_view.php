<div class="filter-options">

    <a class="sidebar__calendar-btn btn btn-primary btn-block" href="<?php echo __SITE_URL . 'calendar/my'; ?>">
        <?php echo widgetGetSvgIcon('calendar-simple', 16, 16); ?>
        <?php echo translate('ep_events_sidebar_go_to_calendar'); ?>
    </a>

    <?php if (!empty($appliedFilters)) { ?>
        <div class="filter-block">
            <h3 class="filter-block__ttl"><?php echo translate('ep_events_sidebar_active_filters'); ?></h3>

            <ul class="active-filters-params">
                <?php foreach ($appliedFilters as $appliedFilter) { ?>
                    <li class="active-filters-params__item">
                        <p class="active-filters-params__name"><?php echo $appliedFilter['name']; ?>:</p>
                        <ul class="active-filters-params__sub">
                            <li class="active-filters-params__sub-item">
                                <p class="active-filters-params__sub-name"><?php echo $appliedFilter['displayedValue']; ?></p>
                                <a class="active-filters-params__sub-remove-btn" href="<?php echo $appliedFilter['linkToResetFilter']; ?>">
                                    <?php echo widgetGetSvgIcon('plus', 11, 11); ?>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php } ?>

                <li>
                    <a class="active-filters-params__clear-btn btn btn-light btn-block btn-new16" href="<?php echo $linkToResetAllFilters; ?>">
                        <?php echo translate('ep_events_clear_all_btn'); ?>
                    </a>
                </li>
            </ul>
        </div>
    <?php } ?>


    <div class="filter-block">
        <h3 class="filter-block__ttl"><?php echo translate('ep_events_sidebar_search'); ?></h3>

        <form class="validengine filter-search-form" data-callback="searchByItem" data-type="events" data-js-action="form:submit_form_search" data-page="<?php echo $currentPage; ?>">
            <input class="filter-search-form__field ep-input validate[required, minSize[2]]" type="text" name="keywords" maxlength="50" placeholder="Keyword" value="<?php echo cleanOutput($appliedFilters['keywords']['value'] ?? ''); ?>" />

            <select class="filter-search-form__field ep-select type_url" name="country">
                <?php echo getCountrySelectOptions($countries, $appliedFilters['country']['value'] ?? null, ['value' => 'countrySlug', 'selectedPropertyName' => 'countrySlug']); ?>
            </select>

            <select class="filter-search-form__field ep-select type_url" name="type">
                <option value=""><?php echo translate('ep_events_select_type'); ?></option>
                <?php foreach ($eventsTypes as $eventsType) { ?>
                    <option value="<?php echo cleanOutput($eventsType['slug']); ?>" <?php echo selected($eventsType['slug'], $appliedFilters['type']['value'] ?? null); ?>><?php echo cleanOutput($eventsType['title']); ?></option>
                <?php } ?>
            </select>


            <select class="filter-search-form__field ep-select type_url" name="time">
                <option value=""><?php echo translate('ep_events_select_time'); ?></option>
                <option value="upcoming" <?php echo selected('upcoming', $appliedFilters['time']['value'] ?? null); ?>><?php echo translate('ep_events_upcoming_label'); ?></option>
                <option value="active" <?php echo selected('active', $appliedFilters['time']['value'] ?? null); ?>><?php echo translate('ep_events_active_label'); ?></option>
                <option value="past" <?php echo selected('past', $appliedFilters['time']['value'] ?? null); ?>><?php echo translate('ep_events_past_label'); ?></option>
            </select>

            <?php if (!empty($appliedFilters['category']['value'])) { ?>
                <input type="hidden" name="category" value="<?php echo cleanOutput($appliedFilters['category']['value']); ?>">
            <?php } ?>

            <?php if (!empty($sorting['value'])) { ?>
                <input type="hidden" name="sort" value="<?php echo cleanOutput($sorting['value']); ?>">
            <?php } ?>


            <input type="hidden" name="return_to_page" value="1">

            <button class="filter-search-form__btn btn btn-new16 btn-light btn-block" type="submit"><?php echo translate('ep_events_sidebar_search'); ?></button>
        </form>
    </div>

    <?php if (!empty($categories)) { ?>
        <div class="filter-block">
            <h3 class="filter-block__ttl"><?php echo translate('ep_events_sidebar_category'); ?></h3>

            <ul class="filter-options-list js-hide-max-list">
                <?php foreach ($categories as $item) { ?>
                    <li class="filter-options-list__item js-filter-list-item">
                        <div class="filter-options-list__inner">

                            <a class="filter-options-list__link" href="<?php echo $item['filterUrl']; ?>" <?php echo addQaUniqueIdentifier('global__sidebar-category'); ?>>
                                <?php echo cleanOutput($item['name']); ?>
                            </a>

                            <span class="filter-options-list__counter" <?php echo addQaUniqueIdentifier('global__sidebar-counter'); ?>>
                                <?php echo (int) $categoriesCounters[$item['id']]['eventsCount']; ?>
                            </span>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>
</div>

<?php views()->display('new/subscribe/new_subscribe_view'); ?>
