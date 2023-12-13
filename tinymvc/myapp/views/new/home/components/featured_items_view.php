<section class="home-section featured-items container-1420">
    <div class="section-header">
        <h2 class="section-header__title"><?php echo translate('home_header_title_featured_items'); ?></h2>
        <a class="section-header__link" href="<?php echo __SITE_URL . 'items/featured'; ?>" <?php echo addQaUniqueIdentifier("home__featured-items-link-view-more"); ?>><?php echo translate('home_title_links_view_more'); ?><?php echo widgetGetSvgIcon("arrowRight", 15, 15)?></a>
    </div>
    <div class="featured-items__content">
        <div class="products products--slider-full js-featured-items loading" data-lazy-name="featured-items" <?php echo addQaUniqueIdentifier("home__featured-items-sider"); ?>></div>
    </div>
    <?php views('new/partials/ajax_loader_view'); ?>
</section>
