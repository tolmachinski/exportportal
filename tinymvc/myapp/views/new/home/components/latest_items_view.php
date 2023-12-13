<section class="home-section latest-items container-1420">
    <div class="section-header">
        <h2 class="section-header__title"><?php echo translate('home_latest_items_title'); ?></h2>
        <a class="section-header__link" href="<?php echo __SITE_URL . 'items/latest'; ?>" <?php echo addQaUniqueIdentifier("home__latest-items-view-more"); ?>><?php echo translate('home_title_links_view_more'); ?><?php echo widgetGetSvgIcon("arrowRight", 15, 15)?></a>
    </div>
    <div class="latest-items__content">
        <div class="products products--slider-full js-latest-items loading" data-lazy-name="latest-items" <?php echo addQaUniqueIdentifier("home__latest-items-slider"); ?>></div>
        <a href="<?php echo __SITE_URL . 'items/latest'; ?>" class="latest-items__btn btn btn-primary btn-block btn-new18" <?php echo addQaUniqueIdentifier("home__latest-view-all-items-btn"); ?>><?php echo translate('home_latest_items_view_all_items_btn'); ?></a>
    </div>
    <?php views('new/partials/ajax_loader_view'); ?>
</section>
