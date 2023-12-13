<div class="banner container-1420">
    <div class="banner__wrap">
        <picture class="banner__bg">
            <source media="(max-width: 575px)" srcset="<?php echo getLazyImage(290, 100); ?>" data-srcset="<?php echo asset("public/build/images/selling/banner-mobile.png"); ?> 1x, <?php echo asset("public/build/images/selling/banner-mobile@2x.png"); ?> 2x">
            <source media="(max-width: 991px)" srcset="<?php echo getLazyImage(738, 188); ?>" data-srcset="<?php echo asset("public/build/images/selling/banner-tablet.png"); ?> 1x, <?php echo asset("public/build/images/selling/banner-tablet@2x.png"); ?> 2x">
            <img class="image js-lazy" src="<?php echo getLazyImage(1170, 188); ?>" data-src="<?php echo asset("public/build/images/selling/banner.png"); ?>" data-srcset="<?php echo asset("public/build/images/selling/banner.png"); ?> 1x, <?php echo asset("public/build/images/selling/banner@2x.png"); ?> 2x" alt="<?php echo translate('selling_certified_banner_title'); ?>">
        </picture>
        <div class="banner__body">
            <h3 class="banner__title"><?php echo translate('selling_certified_banner_title'); ?></h3>
            <p class="banner__text">
                <?php echo translate('selling_certified_banner_text'); ?>
            </p>
            <a class="btn btn-primary btn-new18" href="<?php echo __SITE_URL . 'about/certification_and_upgrade_benefits' ?>" target="_blank"><?php echo translate('selling_banner_btn'); ?></a>
        </div>
    </div>
</div>
