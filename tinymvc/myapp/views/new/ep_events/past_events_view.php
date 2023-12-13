<div class="ep-events-past">
    <?php if ('pastEvents' === $currentPage) {?>
        <div class="ep-events__heading">
            <div class="ep-events-past__heading-ttl"><?php echo translate('ep_events_found_heading'); ?> (<?php echo $count;?>)</div>
            <div class="ep-events__heading-btns">
                <button class="ep-events__btn-filter call-action" data-js-action="sidebar:toggle-visibility" type="button">
                    <?php echo widgetGetSvgIcon('filter', 17, 16);?>
                    <?php echo translate('ep_events_filter_btn'); ?>
                </button>
                <?php if (!empty($pastEvents)) {?>
                    <div class="minfo-save-search__item">
                        <span class="minfo-save-search__ttl dn-sm"><?php echo translate('sort_by_label'); ?></span>
                        <div class="dropdown show dropdown--select">
                            <button class="dropdown-toggle"
                                data-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false"
                                type="button">
                                <?php echo $sorting['displayedValue'] ?? (empty($appliedFilters['keywords']) ? translate('sort_by_date_txt') : translate('sort_by_best_match_txt'));?>
                                <?php echo widgetGetSvgIcon('arrowDown', 9, 9);?>
                            </button>

                            <div class="dropdown-menu" aria-labelledby="#">
                                <?php if (!empty($appliedFilters['keywords'])) {?>
                                    <a href="<?php echo $defaultSortingUrl;?>" class="dropdown-item"><?php echo translate('sort_by_best_match_txt'); ?></a>
                                <?php }?>
                                <a href="<?php echo ($sorting['default'] ?? null) === 'date' ? $defaultSortingUrl : replace_dynamic_uri('date', 'ep_events/past/' . $linksTpl['sort']);?>" class="dropdown-item"><?php echo translate('sort_by_date_txt'); ?></a>
                                <a href="<?php echo ($sorting['default'] ?? null) === 'most_viewed' ? $defaultSortingUrl : replace_dynamic_uri('most_viewed', 'ep_events/past/' . $linksTpl['sort']);?>" class="dropdown-item"><?php echo translate('sort_by_most_viewed_txt'); ?></a>
                            </div>
                        </div>
                    </div>
                <?php }?>
            </div>
        </div>
    <?php } else {?>
        <div class="ep-events-past__heading">
            <div class="ep-events-past__heading-ttl"><?php echo translate('ep_events_past_heading'); ?></div>
        </div>
    <?php }?>

    <?php if (!empty($pastEvents)) {?>
        <div class="ep-events-past__list">
            <?php views()->display('new/ep_events/past_event_item_view'); ?>
        </div>
    <?php } elseif ('pastEvents' === $currentPage) {?>
        <?php views()->display('new/search/cheerup_view');?>
    <?php }?>

    <div class="flex-display flex-jc--c flex-ai--c">
        <?php if ('pastEvents' === $currentPage) {?>
            <?php views()->display('new/paginator_view');?>
        <?php } else {?>
            <a class="btn btn-new16 btn-light ep-events-past__btn" href="<?php echo __SITE_URL . 'ep_events/past'?>"><?php echo translate('ep_events_view_all_btn'); ?></a>
        <?php }?>
    </div>
</div>
