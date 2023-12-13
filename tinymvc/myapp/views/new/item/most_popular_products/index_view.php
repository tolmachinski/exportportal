<div class="container-1420">
    <h1 class="page-main-title"><?php echo translate('most_popular_page_title'); ?></h1>

    <div class="site-content site-content--products-list<?php echo count($items) ? '' : ' site-content--show-sidebar-md'; ?>">
        <div class="site-main-content">
            <div class="filter-control-bar">
                <!-- Search counter -->
                <?php views('new/partials/search_counter_view'); ?>
                <!-- Filter btn for sidebar -->
                <?php views('new/partials/filter_btn_view'); ?>
            </div>

            <!-- Product list -->
            <?php views('new/item/list_view', ['addEncoreLinks' => true]);?>

            <?php if (ceil($count / $perPage) > 1) { ?>
                <div class="pagination-wr">
                    <?php views('new/paginator_view'); ?>
                </div>
            <?php } ?>

            <?php
                if (!empty($items) && (!logged_in() || have_right('create_product_request'))) {
                    // Product request section
                    views('new/product_requests/send_request_block_view');
                }
            ?>
        </div>

        <?php if (empty($items)) { ?>
            <div class="site-out-content">
                <?php
                    if (!logged_in() || have_right('create_product_request')) {
                        // Product request section
                        views('new/product_requests/send_request_block_view');
                    }
                ?>
            </div>
        <?php } ?>

        <!-- Sidebar -->
        <?php views('new/sidebar/index_view', ['showMd' => count($items) === 0]); ?>
    </div>
</div>

<?php
    encoreEntryLinkTags('popular_items_page');
    encoreEntryScriptTags('popular_items_page');
?>

