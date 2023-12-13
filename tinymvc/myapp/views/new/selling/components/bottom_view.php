<div class="build-export container-1420">
    <div class="build-export__content-wrap">
        <h3 class="build-export__ttl"><?php echo translate('selling_build_export_ttl');?></h3>
        <p class="build-export__subttl"><?php echo translate('selling_build_export_subttl');?></p>
        <a class="btn btn-primary btn-new18" href="<?php echo logged_in() ? __SITE_URL . 'about' : __SITE_URL . 'register/seller'; ?>" target="_blank" <?php echo addQaUniqueIdentifier("page__selling_build_export_btn"); ?>><?php echo translate(logged_in() ? 'selling_build_export_about_btn' : 'selling_build_export_btn');?></a>
    </div>

    <picture class="build-export__background">
        <source media="(max-width: 400px)" srcset="<?php echo getLazyImage(290, 600); ?>" data-srcset="<?php echo asset("public/build/images/selling/build-business-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/selling/build-business-mobile-small@2x.jpg"); ?> 2x">
        <source media="(max-width: 575px)" srcset="<?php echo getLazyImage(395, 508); ?>" data-srcset="<?php echo asset("public/build/images/selling/build-business-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/selling/build-business-mobile@2x.jpg"); ?> 2x">
        <source media="(max-width: 991px)" srcset="<?php echo getLazyImage(738, 455); ?>" data-srcset="<?php echo asset("public/build/images/selling/build-business-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/selling/build-business-tablet@2x.jpg"); ?> 2x">
        <img class="image js-lazy" src="<?php echo getLazyImage(1420, 434);?>" data-src="<?php echo asset('public/build/images/selling/build-business.jpg'); ?>" alt="<?php echo translate('selling_build_business_img');?>" width="1920" height="434">
    </picture>
</div>
