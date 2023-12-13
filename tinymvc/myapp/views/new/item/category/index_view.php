<div class="container-1420">
    <?php if (empty($categoryHeader) || have_right('sell_item')) { ?>
            <div class="minfo-header-wr">
                <?php if(empty($categoryCeader)){?>
                    <h1 class="minfo-header-ttl"><?php echo $category['h1'];?></h1>
                <?php }?>

                <?php if(have_right('sell_item')){?>
                    <a class="btn-add-category btn btn-primary" href="<?php echo __SITE_URL . 'items/my?select_category=' . $linkAddItem;?>">
                        <span class="btn-add-category__text"><?php echo getEpIconSvg('plus-circle', [18, 18]);?>Add item to this category</span>
                    </a>
                <?php }?>
            </div>
    <?php }?>

    <div class="site-content site-content--products-list<?php echo count($items) ? '' : ' site-content--show-sidebar-md'; ?>">
        <div class="site-main-content">
            <div class="filter-control-bar<?php echo count($items) ? '' : ' filter-control-bar--empty-results'; ?>">
                <!-- Search counter -->
                <?php views('new/partials/search_counter_view'); ?>
                <!-- Sort bar -->
                <?php views('new/partials/sort_bar_view'); ?>
            </div>

            <!-- Product list -->
            <?php views('new/item/list_view', ['addEncoreLinks' => (bool) $customEncoreLinks ?? false]);?>

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
                    views('new/partials/category_article_and_requirement_info_view');
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

                <?php if ($itemsRecommended) {?>
                    <div class="pt-50">
                        <div class="minfo-title mt-25">
                            <h3 class="minfo-title__name fs-20"><?php echo translate('items_list_products_you_may_like'); ?></h3>
                        </div>

                        <div class="products">
                            <?php views('new/item/list_item_view', ['items' => $itemsRecommended]); ?>
                        </div>
                    </div>
                <?php }?>

                <!-- Import/Export & requirements info -->
                <?php views('new/partials/category_article_and_requirement_info_view'); ?>
            </div>
        <?php } ?>

        <!-- Sidebar -->
        <?php views('new/sidebar/index_view', ['showMd' => count($items) === 0]); ?>
    </div>
</div>

<?php
    encoreEntryLinkTags('category');
    encoreEntryScriptTags('category');
?>

