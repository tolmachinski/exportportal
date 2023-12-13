<div class="banner <?php echo $isDetailPage ? 'banner--detail' : '';?>">
    <div class="banner__content-wrap">
        <h3 class="banner__ttl"><?php echo translate('ep_events_suggest_banner_ttl'); ?></h3>
        <div class="banner__sub-ttl"><?php echo translate('ep_events_suggest_banner_sub_ttl'); ?></div>
        <?php
            $linkContactUs = __SITE_URL . 'contact/popup_forms/contact_us';
            if(isset($webpackData)){
                $linkContactUs .= '/webpack';
            }
        ?>
        <a href="<?php echo $linkContactUs; ?>"
           data-title="<?php echo translate('ep_events_contact_us_title'); ?>" class="banner__link fancybox.ajax fancyboxValidateModal"><?php echo translate('ep_events_suggest_banner_link_text'); ?> <?php echo widgetGetSvgIcon("arrowRight", 24, 17)?></a>
    </div>
    <div class="banner__img-wrap">
        <picture>
            <source
                media="(max-width: 767px)"
                srcset="<?php echo getLazyImage(290, 120);?>"
                data-srcset="<?php echo asset("public/build/images/ep_events/suggest-banner-img-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/ep_events/suggest-banner-img-mobile@2x.jpg"); ?> 2x"
            >
            <source
                media="(max-width: 900px)"
                srcset="<?php echo getLazyImage(330, 150);?>"
                data-srcset="<?php echo asset("public/build/images/ep_events/suggest-banner-img-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/ep_events/suggest-banner-img-tablet@2x.jpg"); ?> 2x"
            >
            <img class="image js-lazy"
                 data-src="<?php echo asset("public/build/images/ep_events/suggest-banner-img.jpg"); ?>"
                 src="<?php echo getLazyImage(550, 150); ?>"
                 alt="<?php echo translate('ep_events_suggest_banner_img-alt');?>">
        </picture>
    </div>
</div>
