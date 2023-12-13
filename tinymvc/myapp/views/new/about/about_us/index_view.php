<div class="mobile-links ml-15">
    <a
        class="btn btn-primary btn-panel-left fancyboxSidebar fancybox"
        data-title="Menu"
        data-mw="290"
        href="#about-flex-card__fixed-left"
        <?php echo addQaUniqueIdentifier("global__about-mobile-buttons-menu"); ?>
    >
        <?php echo getEpIconSvg('items', [16, 16]); ?> <?php echo translate('about_us_nav_menu_btn'); ?>
    </a>
</div>

<div class="about-us">
    <div class="about-us__row">
        <div class="about-us__col about-us__col--1105">
            <div class="about-us__info">
                <h2 class="about-us__ttl about-us__ttl--320"><?php echo translate('about_us_what_ep_ttl'); ?></h2>
                <div class="about-us__desc">
                    <p><?php echo translate('about_us_what_ep_desc1', ['{{START_HTML_TAG}}' => '<strong>', '{{END_HTML_TAG}}' => '</strong>']); ?></p>
                    <p><?php echo translate('about_us_what_ep_desc2', ['{{START_HTML_TAG}}' => '<strong>', '{{END_HTML_TAG}}' => '</strong>']); ?></p>
                </div>
                <a href="<?php echo __SITE_URL . 'learn_more'; ?>" class="about-us__btn btn btn-new16 btn-primary" <?php echo addQaUniqueIdentifier("about__learn-more-btn"); ?>>
                    <?php echo translate('about_us_what_ep_learn_more_link'); ?>
                </a>
            </div>
        </div>

        <?php encoreLinks();?>

        <div class="about-us__col about-us__col--805">
            <div class="about-us__bg about-us__bg--center">
                <picture class="display-b h-100pr">
                    <source
                        media="(max-width: 767px)"
                        srcset="<?php echo getLazyImage(638, 525); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/about_us/what_is_ep-mobile.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 991px)"
                        srcset="<?php echo getLazyImage(991, 225); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/about_us/what_is_ep-tablet.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 1200px)"
                        srcset="<?php echo getLazyImage(1200, 272); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/about_us/what_is_ep-1200.jpg'); ?>"
                    >
                    <img
                        class="image js-lazy"
                        width="805"
                        height="508"
                        src="<?php echo getLazyImage(805, 508); ?>"
                        data-src="<?php echo asset('public/build/images/about/about_us/what_is_ep.jpg'); ?>"
                        alt="<?php echo translate('about_us_what_ep_img_alt', null, true); ?>"
                    >
                </picture>
            </div>
        </div>
    </div>

    <div id="js-request-demo-banner" class="container-center-sm" <?php echo addQaUniqueIdentifier('about__banner-demo'); ?>>
        <?php echo widgetShowBanner('about_after_what_is_ep', 'promo-banner-wr--about'); ?>
    </div>

    <?php if (!empty($videos)) { ?>
        <?php foreach ($videos as $video) { ?>
            <div class="container-center-sm">
                <div
                    class="about-us-video call-action"
                    data-js-action="modal:open-video-modal"
                    data-title="<?php echo translate('about_us_video_txt', null, true); ?>"
                    data-href="<?php echo $video['link_video']; ?>"
                    data-autoplay="true"
                    <?php echo addQaUniqueIdentifier("about__video-modal"); ?>
                >
                    <picture class="display-b h-100pr">
                        <source
                            media="(max-width: 425px)"
                            srcset="<?php echo getLazyImage(360, 195); ?>"
                            data-srcset="<?php echo asset('public/build/images/about/about_us/about_us_video_bg-mobile.jpg'); ?>"
                        >
                        <source
                            media="(max-width: 991px)"
                            srcset="<?php echo getLazyImage(740, 400); ?>"
                            data-srcset="<?php echo asset('public/build/images/about/about_us/about_us_video_bg-tablet.jpg'); ?>"
                        >
                        <img
                            class="image js-lazy"
                            src="<?php echo getLazyImage(1170, 635); ?>"
                            data-src="<?php echo asset('public/build/images/about/about_us/about_us_video_bg.jpg'); ?>"
                            width="1170"
                            height="635"
                            alt="<?php echo translate('about_us_video_txt', null, true); ?>"
                        >
                    </picture>

                    <div class="youtube-play-icon">
                        <?php echo widgetGetSvgIcon('youtube-icon-play', 75, 52); ?>
                    </div>

                    <p class="about-us-video__txt"><?php echo translate('about_us_video_txt'); ?></p>
                </div>
            </div>
        <?php } ?>
    <?php } ?>

    <div class="container-center-sm">
        <div class="about-us__heading">
            <h2 class="about-us__ttl about-us__ttl--225"><?php echo translate('about_us_why_choose_ep_ttl'); ?></h2>
            <p class="about-us__desc about-us__desc--benefits"><?php echo translate('about_us_why_choose_ep_desc'); ?></p>
        </div>
    </div>

    <div class="about-us__row">
        <div class="about-us__col about-us__col--885">
            <ul class="about-us-benefits">
                <li class="about-us-benefits__item">
                    <div class="about-us-benefits__icon"><?php echo $icons['security']; ?></div>
                    <div class="about-us-benefits__ttl"><?php echo translate('about_us_benefits_total_security_ttl'); ?></div>
                    <p class="about-us-benefits__desc"><?php echo translate('about_us_benefits_total_security_desc'); ?></p>
                </li>

                <li class="about-us-benefits__item">
                    <div class="about-us-benefits__icon"><?php echo $icons['protection']; ?></div>
                    <div class="about-us-benefits__ttl"><?php echo translate('about_us_benefits_strong_protection_ttl'); ?></div>
                    <p class="about-us-benefits__desc"><?php echo translate('about_us_benefits_strong_protection_desc'); ?></p>
                </li>

                <li class="about-us-benefits__item">
                    <div class="about-us-benefits__icon"><?php echo $icons['professional_expertise']; ?></div>
                    <div class="about-us-benefits__ttl"><?php echo translate('about_us_benefits_professional_expertise_ttl'); ?></div>
                    <p class="about-us-benefits__desc"><?php echo translate('about_us_benefits_professional_expertise_desc'); ?></p>
                </li>

                <li class="about-us-benefits__item">
                    <div class="about-us-benefits__icon"><?php echo $icons['support']; ?></div>
                    <div class="about-us-benefits__ttl"><?php echo translate('about_us_benefits_quality_service_ttl'); ?></div>
                    <p class="about-us-benefits__desc"><?php echo translate('about_us_benefits_quality_service_desc', null, true); ?></p>
                </li>
            </ul>
        </div>

        <div class="about-us__col about-us__col--1035">
            <div class="about-us__bg about-us__bg--mnw-1035 about-us__bg--center-sm">
                <picture class="display-b h-100pr">
                    <source
                        media="(max-width: 767px)"
                        srcset="<?php echo getLazyImage(862, 595); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/about_us/benefits_bg-mobile.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 991px)"
                        srcset="<?php echo getLazyImage(348, 758); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/about_us/benefits_bg-tablet.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 1200px)"
                        srcset="<?php echo getLazyImage(430, 790); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/about_us/benefits_bg-1200.jpg'); ?>"
                    >
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(1035, 802); ?>"
                        data-src="<?php echo asset('public/build/images/about/about_us/benefits_bg.jpg'); ?>"
                        width="1035"
                        height="802"
                        alt="<?php echo translate('about_us_why_choose_ep_ttl', null, true); ?>"
                    >
                </picture>
            </div>
        </div>
    </div>

    <div class="container-center-sm">
        <div class="about-us__heading about-us__heading--members">
            <h2 class="about-us__ttl"><?php echo translate('about_us_members_heading_ttl'); ?></h2>
            <p class="about-us__desc about-us__desc--members"><?php echo translate('about_us_members_heading_desc'); ?></p>
        </div>
    </div>

    <div class="about-us__row about-us__row--reverse-sm">
        <div class="about-us__col">
            <div class="about-us__info about-us__info--left">
                <h2 class="about-us__ttl about-us__ttl--members"><?php echo translate('about_us_members_buyers_ttl'); ?></h2>
                <p class="about-us__desc"><?php echo translate('about_us_members_buyers_desc'); ?></p>
                <a href="<?php echo __SITE_URL . 'buying'; ?>" class="about-us__link" <?php echo addQaUniqueIdentifier("about__buyers-learn-more-btn"); ?>>
                    <?php echo translate('about_us_members_learn_more_link'); ?>
                    <i class="ep-icon ep-icon_arrow-line-right pl-7"></i>
                </a>
            </div>
        </div>

        <div class="about-us__col">
            <div class="about-us__bg about-us__bg--mnw-960 about-us__bg--center-sm about-us__bg--right">
                <picture>
                    <source
                        media="(max-width: 767px)"
                        srcset="<?php echo getLazyImage(862, 446); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/about_us/buyers-mobile.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 991px)"
                        srcset="<?php echo getLazyImage(481, 457); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/about_us/buyers-tablet.jpg'); ?>"
                    >
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(960, 400); ?>"
                        data-src="<?php echo asset('public/build/images/about/about_us/buyers.jpg'); ?>"
                        width="960"
                        height="400"
                        alt="<?php echo translate('about_us_members_buyers_ttl', null, true); ?>"
                    >
                </picture>
            </div>
        </div>
    </div>

    <div class="about-us__row">
        <div class="about-us__col">
            <div class="about-us__bg about-us__bg--mnw-960 about-us__bg--center-sm about-us__bg--left">
                <picture>
                    <source
                        media="(max-width: 767px)"
                        srcset="<?php echo getLazyImage(862, 446); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/about_us/sellers-mobile.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 991px)"
                        srcset="<?php echo getLazyImage(481, 457); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/about_us/sellers-tablet.jpg'); ?>"
                    >
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(960, 400); ?>"
                        data-src="<?php echo asset('public/build/images/about/about_us/sellers.jpg'); ?>"
                        width="960"
                        height="400"
                        alt="<?php echo translate('about_us_members_sellers_ttl', null, true); ?>"
                    >
                </picture>
            </div>
        </div>

        <div class="about-us__col">
            <div class="about-us__info about-us__info--right">
                <h2 class="about-us__ttl about-us__ttl--members"><?php echo translate('about_us_members_sellers_ttl'); ?></h2>
                <p class="about-us__desc"><?php echo translate('about_us_members_sellers_desc'); ?></p>
                <a href="<?php echo __SITE_URL . 'selling'; ?>" class="about-us__link" <?php echo addQaUniqueIdentifier("about__sellers-learn-more-btn"); ?>>
                    <?php echo translate('about_us_members_learn_more_link'); ?>
                    <i class="ep-icon ep-icon_arrow-line-right pl-7"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="about-us__row about-us__row--reverse-sm">
        <div class="about-us__col">
            <div class="about-us__info about-us__info--left">
                <h2 class="about-us__ttl about-us__ttl--members"><?php echo translate('about_us_members_mnf_ttl'); ?></h2>
                <p class="about-us__desc"><?php echo translate('about_us_members_mnf_desc'); ?></p>
                <a href="<?php echo __SITE_URL . 'manufacturer_description'; ?>" class="about-us__link" <?php echo addQaUniqueIdentifier("about__mnf-learn-more-btn"); ?>>
                    <?php echo translate('about_us_members_learn_more_link'); ?>
                    <i class="ep-icon ep-icon_arrow-line-right pl-7"></i>
                </a>
            </div>
        </div>

        <div class="about-us__col">
            <div class="about-us__bg about-us__bg--mnw-960 about-us__bg--center-sm about-us__bg--right">
                <picture>
                    <source
                        media="(max-width: 767px)"
                        srcset="<?php echo getLazyImage(862, 446); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/about_us/manufacturers-mobile.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 991px)"
                        srcset="<?php echo getLazyImage(481, 457); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/about_us/manufacturers-tablet.jpg'); ?>"
                    >
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(960, 400); ?>"
                        data-src="<?php echo asset('public/build/images/about/about_us/manufacturers.jpg'); ?>"
                        width="960"
                        height="400"
                        alt="<?php echo translate('about_us_members_mnf_ttl', null, true); ?>"
                    >
                </picture>
            </div>
        </div>
    </div>

    <div class="about-us__row">
        <div class="about-us__col">
            <div class="about-us__bg about-us__bg--mnw-960 about-us__bg--center-sm about-us__bg--left">
                <picture>
                    <source
                        media="(max-width: 767px)"
                        srcset="<?php echo getLazyImage(862, 446); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/about_us/freight_forwarders-mobile.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 991px)"
                        srcset="<?php echo getLazyImage(481, 457); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/about_us/freight_forwarders-tablet.jpg'); ?>"
                    >
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(960, 400); ?>"
                        data-src="<?php echo asset('public/build/images/about/about_us/freight_forwarders.jpg'); ?>"
                        width="960"
                        height="400"
                        alt="<?php echo translate('about_us_members_ff_ttl', null, true); ?>"
                    >
                </picture>
            </div>
        </div>

        <div class="about-us__col">
            <div class="about-us__info about-us__info--right">
                <h2 class="about-us__ttl about-us__ttl--members"><?php echo translate('about_us_members_ff_ttl'); ?></h2>
                <p class="about-us__desc"><?php echo translate('about_us_members_ff_desc'); ?></p>
                <a href="<?php echo __SITE_URL . 'shipper_description'; ?>" class="about-us__link" <?php echo addQaUniqueIdentifier("about__ff-learn-more-btn"); ?>>
                    <?php echo translate('about_us_members_learn_more_link'); ?>
                    <i class="ep-icon ep-icon_arrow-line-right pl-7"></i>
                </a>
            </div>
        </div>
    </div>

    <?php
        if (!empty($videosList)) {
            views()->display('new/about/about_us/about_videos_view');
        }
    ?>

    <div class="container-center-sm">
        <div class="about-us__heading about-us__heading--smes">
            <h2 class="about-us__ttl about-us__ttl--smes"><?php echo translate('about_us_smes_ttl'); ?></h2>
            <p class="about-us__desc about-us__desc--smes"><?php echo translate('about_us_smes_desc'); ?></p>
        </div>
    </div>

    <div class="about-us__row">
        <div class="about-us__col about-us__col--1105 about-us__col--w50pr">
            <div class="about-us__bg about-us__bg--smes about-us__bg--center">
                <picture class="display-b h-100pr">
                    <source
                        media="(max-width: 767px)"
                        srcset="<?php echo getLazyImage(862, 654); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/about_us/smes-mobile.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 991px)"
                        srcset="<?php echo getLazyImage(495, 1128); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/about_us/smes-tablet.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 1200px)"
                        srcset="<?php echo getLazyImage(430, 790); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/about_us/smes-1200.jpg'); ?>"
                    >
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(1075, 878); ?>"
                        data-src="<?php echo asset('public/build/images/about/about_us/smes.jpg'); ?>"
                        width="1075"
                        height="878"
                        alt="<?php echo translate('about_us_smes_ttl', null, true); ?>"
                    >
                </picture>
            </div>
        </div>

        <div class="about-us__col about-us__col--805 about-us__col--w50pr">
            <ul class="about-us-smes">
                <li class="about-us-smes__item">
                    <div class="about-us-smes__icon"><?php echo $icons['calendar']; ?></div>
                    <div class="about-us-smes__ttl">
                        <?php echo translate('about_us_smes_item1_ttl', [
                            '{{COUNT_HTML_TAG}}' => '<span class="about-us-smes__count">',
                            '{{START_HTML_TAG}}' => '<span>',
                            '{{END_HTML_TAG}}'   => '</span>',
                        ]); ?>
                    </div>
                    <p class="about-us-smes__desc"><?php echo translate('about_us_smes_item1_desc'); ?></p>
                </li>

                <li class="about-us-smes__item">
                    <div class="about-us-smes__icon"><?php echo $icons['users']; ?></div>
                    <div class="about-us-smes__ttl">
                        <?php echo translate('about_us_smes_item2_ttl', [
                            '{{COUNT_HTML_TAG}}' => '<span class="about-us-smes__count">',
                            '{{START_HTML_TAG}}' => '<span>',
                            '{{END_HTML_TAG}}'   => '</span>',
                        ]); ?>
                    </div>
                    <p class="about-us-smes__desc"><?php echo translate('about_us_smes_item2_desc'); ?></p>
                </li>

                <li class="about-us-smes__item">
                    <div class="about-us-smes__icon"><?php echo $icons['trolley']; ?></div>
                    <div class="about-us-smes__ttl">
                        <?php echo translate('about_us_smes_item3_ttl', [
                            '{{COUNT_HTML_TAG}}' => '<span class="about-us-smes__count">',
                            '{{START_HTML_TAG}}' => '<span>',
                            '{{END_HTML_TAG}}'   => '</span>',
                        ]); ?>
                    </div>
                    <p class="about-us-smes__desc"><?php echo translate('about_us_smes_item3_desc'); ?></p>
                </li>

                <li class="about-us-smes__item">
                    <div class="about-us-smes__icon"><?php echo $icons['planet_earth']; ?></div>
                    <div class="about-us-smes__ttl">
                        <?php echo translate('about_us_smes_item4_ttl', [
                            '{{COUNT_HTML_TAG}}' => '<span class="about-us-smes__count">',
                            '{{START_HTML_TAG}}' => '<span>',
                            '{{END_HTML_TAG}}'   => '</span>',
                        ]); ?>
                    </div>
                    <p class="about-us-smes__desc"><?php echo translate('about_us_smes_item4_desc', null, true); ?></p>
                </li>
            </ul>
        </div>
    </div>

    <div class="about-us-footer footer-connect">
        <div class="about-us-footer__bg">
            <picture>
                <source
                    media="(max-width: 425px)"
                    srcset="<?php echo getLazyImage(510, 641); ?>"
                    data-srcset="<?php echo asset('public/build/images/about/about_us/footer_bg-mobile.jpg'); ?>"
                >
                <source
                    media="(max-width: 991px)"
                    srcset="<?php echo getLazyImage(991, 562); ?>"
                    data-srcset="<?php echo asset('public/build/images/about/about_us/footer_bg-tablet.jpg'); ?>"
                >
                <source
                    media="(max-width: 1200px)"
                    srcset="<?php echo getLazyImage(1200, 650); ?>"
                    data-srcset="<?php echo asset('public/build/images/about/about_us/footer_bg-1200.jpg'); ?>"
                >
                <img
                    class="image js-lazy"
                    src="<?php echo getLazyImage(1920, 760); ?>"
                    data-src="<?php echo asset('public/build/images/about/about_us/footer_bg.jpg'); ?>"
                    width="1920"
                    height="760"
                    alt="<?php echo translate('about_us_footer_ttl', null, true); ?>"
                >
            </picture>
        </div>

        <div class="container-center-sm">
            <div class="about-us-footer__content">
                <div class="about-us-footer__img">
                    <img
                        class="image js-lazy"
                        <?php echo addQaUniqueIdentifier("about__footer_content_img")?>
                        src="<?php echo getLazyImage(185, 205); ?>"
                        data-src="<?php echo asset('public/build/images/about/about_us/ep_logo.gif'); ?>"
                        width="185"
                        height="205"
                        alt="<?php echo translate('about_us_ep_logo_alt', null, true); ?>"
                    >
                </div>

                <div class="about-us-footer__ttl"><?php echo translate('about_us_footer_ttl'); ?></div>
                <p class="about-us-footer__subttl"><?php echo translate('about_us_footer_desc'); ?></p>
                <a href="<?php echo __SITE_URL . 'register'; ?>" class="about-us-footer__btn btn btn-new16 btn-primary" <?php echo addQaUniqueIdentifier("about__join-us-btn"); ?>>
                    <?php echo translate('about_us_footer_register_btn'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

