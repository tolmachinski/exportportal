<div class="container-1420">
    <h1 class="page-main-title">Featured items</h1>

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
                if (!empty($items)) {
                    if (!logged_in() || have_right('create_product_request')) {
                        // Product request section
                        views('new/product_requests/send_request_block_view');
                    }

                    // Search products by category
                    views('new/item/items_categories_view');
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

                    // Search products by category
                    views('new/item/items_categories_view');
                ?>
            </div>
        <?php } ?>

        <!-- Sidebar -->
        <?php views('new/sidebar/index_view', ['showMd' => count($items) === 0]); ?>
    </div>
</div>

<?php
    encoreEntryLinkTags('featured_items_page');
    encoreEntryScriptTags('featured_items_page');
?>

