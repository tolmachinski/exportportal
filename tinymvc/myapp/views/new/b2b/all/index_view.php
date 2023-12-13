<div class="container-1420">
    <div class="site-content b2b-page">
        <div class="site-main-content">
            <div class="filter-control-bar">
                <!-- Search counter -->
                <?php views('new/partials/search_counter_view'); ?>

                <button
                    class="filter-btn call-action"
                    data-js-action="sidebar:toggle-visibility"
                >
                    <?php echo widgetGetSvgIcon('filter', 17, 17, 'filter-btn__icon'); ?> <?php echo translate('general_filter'); ?>
                </button>
            </div>

            <div class="b2b-requests-list">
                <?php if (!empty($b2bRequests)) { ?>
                    <?php
                        foreach ($b2bRequests as $key => $request) {
                            views('new/b2b/b2b_card_view', [
                                'request'       => $request,
                                'removeLazyImg' => $key < 4,
                            ]);
                        }
                    ?>
                    <?php if (ceil($count / $perPage) > 1) { ?>
                        <div class="pagination-wr">
                            <?php views('new/paginator_view'); ?>
                        </div>
                    <?php } ?>
                <?php } else {?>
                    <div class="info-alert-b">
                        <i class="ep-icon ep-icon_info-stroke"></i> <span><?php echo translate('b2b_all_not_found_requests'); ?></span>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- Sidebar -->
        <?php views('new/sidebar/index_view', ['showMd' => 0 === count($b2bRequests)]); ?>
    </div>
</div>

<?php
    encoreEntryLinkTags('b2b_requests_all_page');
    encoreEntryScriptTags('b2b_requests_all_page');
?>
