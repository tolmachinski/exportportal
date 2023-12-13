<div class="container-1420">
    <div class="site-content site-content--products-list<?php echo count($items) ? '' : ' site-content--show-sidebar-md'; ?>">
        <div class="site-main-content">
            <div class="filter-control-bar<?php echo count($items) ? '' : ' filter-control-bar--empty-results'; ?>">
                <!-- Search counter -->
                <?php views('new/partials/search_counter_view'); ?>
                <!-- Sort bar -->
                <?php views('new/partials/sort_bar_view'); ?>
            </div>

            <!-- Product list -->
            <?php views('new/item/list_view', ['addEncoreLinks' => true]); ?>

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

                    // Import/Export & requirements info
                    views('new/partials/article_and_requirement_info_view');
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

                    // Import/Export & requirements info
                    views('new/partials/article_and_requirement_info_view');
                ?>
            </div>
        <?php } ?>

        <!-- Sidebar -->
        <?php views('new/sidebar/index_view', ['showMd' => count($items) === 0]); ?>
    </div>

    <?php
        if (empty($items)) {
            // Promo products slider
            views('new/search/footer_content_view');
        }
    ?>
</div>

<?php
    encoreEntryLinkTags('search_page');
    encoreEntryScriptTags('search_page');
?>

