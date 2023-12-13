<section class="home-section top-products container-1420">
    <div class="section-header">
        <h2 class="section-header__title"><?php echo translate('home_header_title_top_items'); ?></h2>
        <a class="section-header__link" href="<?php echo __SITE_URL . 'items/popular';?>" <?php echo addQaUniqueIdentifier("home__top-products-view-more"); ?>><?php echo translate('home_title_links_view_more'); ?><?php echo widgetGetSvgIcon("arrowRight", 15, 15);?></a>
    </div>
    <div class="top-products__content">
        <picture class="top-products__picture">
            <source srcset="<?php echo getLazyImage(400, 200); ?>" data-srcset="<?php echo asset("public/build/images/index/top-products/top-products-bg-m2.jpg"); ?> 1x, <?php echo asset("public/build/images/index/top-products/top-products-bg-m2@2x.jpg"); ?> 2x" media="(max-width: 425px)">
            <source srcset="<?php echo getLazyImage(545, 270); ?>" data-srcset="<?php echo asset("public/build/images/index/top-products/top-products-bg-m.jpg"); ?> 1x, <?php echo asset("public/build/images/index/top-products/top-products-bg-m@2x.jpg"); ?> 2x" media="(max-width: 575px)">
            <img class="top-products__image js-lazy" src="<?php echo getLazyImage(540, 365); ?>" data-src="<?php echo asset("public/build/images/index/top-products/top-products-bg-d.jpg"); ?>" data-srcset="<?php echo asset("public/build/images/index/top-products/top-products-bg-d.jpg"); ?> 1x, <?php echo asset("public/build/images/index/top-products/top-products-bg-d@2x.jpg"); ?> 2x" alt="Explore the Top 50 Products">
        </picture>
        <div class="top-products__slider">
            <div class="products products--slider-full js-top-products loading" data-lazy-name="top-products" <?php echo addQaUniqueIdentifier('home__top-products-slider'); ?>></div>
        </div>
    </div>
    <?php views('new/partials/ajax_loader_view'); ?>
</section>
