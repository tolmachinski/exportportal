<div class="banner <?php echo $isDetailPage ? 'banner--detail' : '';?>">
    <div class="banner__content-wrap">
        <h3 class="banner__ttl"><?php echo translate('ep_events_invite_banner_ttl'); ?></h3>
        <div class="banner__sub-ttl"><?php echo translate('ep_events_invite_banner_sub_ttl'); ?></div>
        <a href="https://app.smartsheet.com/b/form/d2b514814816432892c3e4bb27deb996" target="_blank" class="banner__link"><?php echo translate('ep_events_invite_banner_link_text'); ?> <?php echo widgetGetSvgIcon("arrowRight", 24, 17)?></a>
    </div>
    <div class="banner__img-wrap">
        <picture>
            <source
                media="(max-width: 767px)"
                srcset="<?php echo getLazyImage(290, 120);?>"
                data-srcset="<?php echo asset("public/build/images/ep_events/invite-banner-img-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/ep_events/invite-banner-img-mobile@2x.jpg"); ?> 2x"
            >
            <source
                media="(max-width: 900px)"
                srcset="<?php echo getLazyImage(330, 150);?>"
                data-srcset="<?php echo asset("public/build/images/ep_events/invite-banner-img-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/ep_events/invite-banner-img-tablet@2x.jpg"); ?> 2x"
            >
            <img class="image js-lazy"
                 data-src="<?php echo asset("public/build/images/ep_events/invite-banner-img.jpg"); ?>"
                 src="<?php echo getLazyImage(550, 150); ?>"
                 alt="<?php echo translate('ep_events_invite_banner_img_alt');?>">
        </picture>
    </div>
</div>
