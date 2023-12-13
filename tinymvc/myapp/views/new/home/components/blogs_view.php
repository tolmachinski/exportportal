<section class="home-section blogs container-1420">
    <div class="section-header">
        <h2 class="section-header__title"><?php echo translate('home_header_title_blogs'); ?></h2>
        <a class="section-header__link" href="<?php echo __BLOG_URL; ?>" <?php echo addQaUniqueIdentifier("home__our-blog-go-to-blogs"); ?>><?php echo translate('home_header_link_to_blogs'); ?><?php echo widgetGetSvgIcon("arrowRight", 15, 15)?></a>
    </div>
    <div id="js-blogs-slider" class="blogs__content loading" data-lazy-name="blogs" <?php echo addQaUniqueIdentifier("home__our-blog-slider"); ?>></div>
    <?php views('new/partials/ajax_loader_view'); ?>
</section>
