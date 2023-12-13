<div id="sidebar-search" class="ep-events__search-block">
    <?php if (!empty($appliedFilters)) { ?>
        <h3 class="minfo-sidebar-ttl">
            <span class="minfo-sidebar-ttl__txt"><?php echo translate('active_filters_block_title'); ?></span>
        </h3>

        <div class="minfo-sidebar-box">
            <div class="minfo-sidebar-box__desc">
                <ul class="minfo-sidebar-params">
                    <?php foreach ($appliedFilters as $appliedFilter) { ?>
                        <li class="minfo-sidebar-params__item">
                            <div class="minfo-sidebar-params__ttl">
                                <div class="minfo-sidebar-params__name"><?php echo $appliedFilter['name']; ?>:</div>
                            </div>

                            <ul class="minfo-sidebar-params__sub">
                                <li class="minfo-sidebar-params__sub-item">
                                    <div class="minfo-sidebar-params__sub-ttl"><?php echo $appliedFilter['displayedValue']; ?></div>
                                    <a class="minfo-sidebar-params__sub-close ep-icon ep-icon_remove-stroke" href="<?php echo $appliedFilter['linkToResetFilter']; ?>"></a>
                                </li>
                            </ul>
                        </li>
                    <?php } ?>
                    <li>
                        <a class="btn btn-light btn-block txt-blue2" href="<?php echo $linkToResetAllFilters; ?>"><?php echo translate('ep_events_clear_all_btn'); ?></a>
                    </li>
                </ul>
            </div>
        </div>
    <?php } ?>

    <h3 class="minfo-sidebar-ttl">
        <span class="minfo-sidebar-ttl__txt"><?php echo translate('header_search_form_title'); ?></span>
    </h3>

    <div class="minfo-sidebar-box">
        <div class="minfo-sidebar-box__desc">
            <form class="relative-b validengine"
                  data-callback="searchByItem"
                  data-type="events"
                  data-js-action="form:submit_form_search"
                  data-page="<?php echo $currentPage;?>"
            >
                <input class="minfo-form__input2"
                       type="text"
                       name="keywords"
                       maxlength="50"
                       placeholder="<?php echo translate('ep_events_keyword_title'); ?>"
                       value="<?php echo cleanOutput($appliedFilters['keywords']['value'] ?? ''); ?>"
                >

                <select class="minfo-form__input2 type_url" name="country">
                    <?php echo getCountrySelectOptions($countries, $appliedFilters['country']['value'] ?? null, ['value' => 'countrySlug', 'selectedPropertyName' => 'countrySlug']); ?>
                </select>

                <select class="minfo-form__input2 type_url" name="type">
                    <option value=""><?php echo translate('ep_events_select_type'); ?></option>
                    <?php foreach ($eventsTypes as $eventsType) { ?>
                        <option value="<?php echo cleanOutput($eventsType['slug']); ?>" <?php echo selected($eventsType['slug'], $appliedFilters['type']['value'] ?? null); ?>><?php echo cleanOutput($eventsType['title']); ?></option>
                    <?php } ?>
                </select>

                <select class="minfo-form__input2 type_url" name="time">
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

                <button class="btn btn-dark btn-block minfo-form__btn2" type="submit"><?php echo translate('header_search_form_events_submit_btn'); ?></button>
            </form>
        </div>
    </div>

    <?php if (!empty($categories)) { ?>
        <h3 class="minfo-sidebar-ttl">
            <span class="minfo-sidebar-ttl__txt"><?php echo translate('ep_events_detail_category_label'); ?></span>
        </h3>

        <div class="minfo-sidebar-box">
            <div class="minfo-sidebar-box__desc">
                <ul class="minfo-sidebar-box__list">
                    <?php foreach ($categories as $category) { ?>
                        <li class="minfo-sidebar-box__list-item">
                            <a class="minfo-sidebar-box__list-link" href="<?php echo $category['filterUrl']; ?>">
                                <?php echo cleanOutput($category['name']); ?>
                            </a>
                            <span class="minfo-sidebar-box__list-counter">(<?php echo (int) $categoriesCounters[$category['id']]['eventsCount']; ?>)</span>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    <?php } ?>
</div>
