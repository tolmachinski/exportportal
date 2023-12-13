<div class="container-1420">
    <div class="site-content site-content--products-list site-content--sidebar-right">
        <div class="site-main-content">
            <?php if (!empty($eventPromotion)) { ?>
                <?php views()->display('new/ep_events/events_banner_view'); ?>
            <?php } ?>
            <div class="ep-events">
                <div class="ep-events__heading">
                    <div class="ep-events__heading-ttl"><?php echo empty($appliedFilters) ? translate('ep_events_upcoming_heading') : translate('ep_events_found_heading') . ' (' . $count . ')'; ?></div>
                    <div class="ep-events__heading-btns">
                        <button class="ep-events__btn-filter call-action" data-js-action="sidebar:toggle-visibility" <?php echo addQaUniqueIdentifier('ep-events__filter__btn'); ?>>
                            <?php echo widgetGetSvgIcon('filter', 18, 17); ?>
                            <?php echo translate('ep_events_filter_btn'); ?>
                        </button>
                        <?php if (!empty($upcomingEvents)) { ?>
                            <div class="minfo-save-search__item">
                                <span class="minfo-save-search__ttl dn-sm"><?php echo translate('sort_by_label'); ?></span>
                                <div class="dropdown show dropdown--select">
                                    <button class="dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <?php echo $sorting['displayedValue'] ?? (empty($appliedFilters['keywords']) ? translate('sort_by_date_txt') : translate('sort_by_best_match_txt')); ?>
                                        <?php echo widgetGetSvgIcon('arrowDown', 9, 9); ?>
                                    </button>

                                    <div class="dropdown-menu" aria-labelledby="#">
                                        <?php if (!empty($appliedFilters['keywords'])) { ?>
                                            <a href="<?php echo $defaultSortingUrl; ?>" class="dropdown-item"><?php echo translate('sort_by_best_match_txt'); ?></a>
                                        <?php } ?>
                                        <a href="<?php echo ($sorting['default'] ?? null) === 'date' ? $defaultSortingUrl : replace_dynamic_uri('date', 'ep_events/' . $linksTpl['sort']); ?>" class="dropdown-item"><?php echo translate('sort_by_date_txt'); ?></a>
                                        <a href="<?php echo ($sorting['default'] ?? null) === 'most_viewed' ? $defaultSortingUrl : replace_dynamic_uri('most_viewed', 'ep_events/' . $linksTpl['sort']); ?>" class="dropdown-item"><?php echo translate('sort_by_most_viewed_txt'); ?></a>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <?php if (!empty($upcomingEvents)) { ?>
                    <div class="ep-events__list">
                        <?php views('new/ep_events/event_item_view', ['events' => $upcomingEvents, 'needToShowBanners' => true]); ?>
                    </div>

                    <?php if ($count >= (int) config('ep_events_per_page', 10)) { ?>
                        <div class="flex-display flex-jc--c flex-ai--c mb-25">
                            <?php views()->display('new/paginator_view'); ?>
                        </div>
                    <?php } ?>
                <?php } else {?>
                    <?php if (empty($appliedFilters)) {?>
                        <div class="ep-events__list">
                            <div class="info-alert-b">
                                <i class="ep-icon ep-icon_info-stroke"></i>
                                <div>
                                    <?php echo translate('ep_events_no_upcoming_events');?>
                                </div>
                            </div>
                        </div>
                    <?php } else {?>
                        <?php views()->display('new/search/cheerup_view'); ?>
                    <?php }?>

                    <?php views('new/ep_events/invite_banner_view');?>
                    <?php views('new/ep_events/suggest_banner_view');?>
                <?php } ?>

                <?php
                encoreEntryLinkTags('ep_events_page');
                encoreEntryScriptTags('ep_events_page');
                ?>

                <?php if (!empty($highlightedEvent)) {?>
                    <?php views('new/ep_events/highlighted_event_view'); ?>
                <?php }?>

                <?php if (!empty($recommendedEvents)) { ?>
                    <div class="ep-events__heading">
                        <div class="ep-events__heading-ttl ep-events__heading-ttl--20"><?php echo translate('ep_events_detail_recommended_header'); ?></div>
                    </div>

                    <div class="ep-events__list mt-13 <?php echo $appliedFilters ? 'ep-events__list--recommended' : ''; ?>">
                        <?php views()->display('new/ep_events/event_item_view', ['events' => $recommendedEvents, 'needToShowBanners' => false]); ?>
                    </div>
                <?php } ?>

                <?php if (!empty($pastEvents)) { ?>
                    <?php views()->display('new/ep_events/past_events_view'); ?>
                <?php } ?>
            </div>
        </div>
        <!-- Sidebar -->
        <?php views('new/sidebar/index_view', ['rightSide' => true]); ?>
    </div>
</div>

<?php
    encoreEntryLinkTags('featured_items_page');
    encoreEntryScriptTags('featured_items_page');
?>
