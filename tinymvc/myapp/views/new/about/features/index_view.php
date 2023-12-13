<div class="mobile-links">
    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Menu" href="#about-flex-card__fixed-left">
        <i class="ep-icon ep-icon_items"></i> <?php echo translate('about_us_nav_menu_btn');?>
    </a>
</div>
<div class="gl-header pt-110">
    <div class="gl-header__background"></div>
    <div class="gl-header__container">
        <div class="gl-header__info">
            <h2 class="gl-header__headline">
                <?php echo translate('about_us_features_block_1_title', array('{{START_HTML_1}}' => '<span>', '{{END_HTML_1}}' => '</span>', '{{START_HTML_2}}' => '<span>', '{{END_HTML_2}}' => '</span>'));?>
            </h2>
            <div class="gl-header__subline"><?php echo translate('about_us_features_block_1_subtitle');?></div>
        </div>
        <div class="gl-header__video">
            <iframe width="100%" height="100%" src="https://www.youtube.com/embed/<?php echo config('ep_video') . '?controls=0&modestbranding=1&origin=' . __SITE_URL;?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen <?php echo addQaUniqueIdentifier("about_featured__iframe_video")?>></iframe>
        </div>
    </div>
</div>

<div class="gl-what">
    <h2 class="gl-what__headline"><?php echo translate('about_us_features_block_2_title', array('{{START_HTML_1}}' => '<span>', '{{END_HTML_1}}' => '</span>'));?></h2>
    <div class="gl-what__container">
        <div class="gl-what__row">
            <div class="gl-what__image">
                <img src="<?php echo __IMG_URL . 'public/img/man-typing.jpg';?>" alt="man typing">
            </div>
            <div class="gl-what__info">
                <h3 class="gl-what__title"><?php echo translate('about_us_features_block_2_image_1_title');?></h3>
                <div class="gl-what__text"><?php echo translate('about_us_features_block_2_image_1_text');?></div>
            </div>
        </div>
        <div class="gl-what__row">
            <div class="gl-what__info">
                <h3 class="gl-what__title"><?php echo translate('about_us_features_block_2_image_2_title');?></h3>
                <div class="gl-what__text"><?php echo translate('about_us_features_block_2_image_2_text');?></div>
            </div>
            <div class="gl-what__image">
                <img src="<?php echo __IMG_URL . 'public/img/pointing-with-finger.jpg';?>" alt="pointing with finger">
            </div>
        </div>
    </div>
</div>

<div <?php echo addQaUniqueIdentifier('features__banner-demo'); ?>>
    <?php echo widgetShowBanner('features_after_what_is_ep', 'promo-banner-wr--features'); ?>
</div>

<div class="gl-discover">
    <h2 class="gl-discover__headline"><?php echo translate('about_us_features_block_3_title', array('{{START_HTML_1}}' => '<span>', '{{END_HTML_1}}' => '</span>'));?></h2>
    <div class="row row-eq-height">
        <div class="col-12 col-md-6 col-lg-4">
            <div>
                <div class="gl-discover__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/icons/epl.svg';?>" alt="export portal logistics icon">
                </div>
                <div class="gl-discover__abbreviation"><?php echo translate('about_us_features_block_3_epl_abbr');?></div>
                <h3 class="gl-discover__title"><?php echo translate('about_us_features_block_3_epl_title');?></h3>
                <p class="gl-discover__text"><?php echo translate('about_us_features_block_3_epl_text');?></p>
                <div class="gl-discover__link-row">
                    <a class="gl-discover__link" href="<?php echo __SITE_URL . 'landing/epl';?>"><?php echo translate('about_us_features_block_3_learn_more_btn');?></a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div>
                <div class="gl-discover__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/icons/epu.svg';?>" alt="export portal university icon">
                </div>
                <div class="gl-discover__abbreviation"><?php echo translate('about_us_features_block_3_epu_abbr');?></div>
                <h3 class="gl-discover__title"><?php echo translate('about_us_features_block_3_epu_title');?></h3>
                <p class="gl-discover__text"><?php echo translate('about_us_features_block_3_epu_text');?></p>
                <div class="gl-discover__link-row">
                <a class="gl-discover__link" href="<?php echo __SITE_URL . 'landing/university';?>"><?php echo translate('about_us_features_block_3_learn_more_btn');?></a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="gl-discover__opacity">
                <div class="gl-discover__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/icons/epm.svg';?>" alt="export portal matchmaking icon">
                </div>
                <div class="gl-discover__abbreviation"><?php echo translate('about_us_features_block_3_epm_abbr');?></div>
                <h3 class="gl-discover__title"><?php echo translate('about_us_features_block_3_epm_title');?></h3>
                <p class="gl-discover__text"><?php echo translate('about_us_features_block_3_epm_text');?></p>
                <div class="gl-discover__link-row">
                    <span class="gl-discover__link"><?php echo translate('about_us_features_block_3_coming_soon_btn');?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div>
                <div class="gl-discover__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/icons/eptp.svg';?>" alt="export portal tradepassport icon">
                </div>
                <div class="gl-discover__abbreviation"><?php echo translate('about_us_features_block_3_eptp_abbr');?></div>
                <h3 class="gl-discover__title"><?php echo translate('about_us_features_block_3_eptp_title');?></h3>
                <p class="gl-discover__text"><?php echo translate('about_us_features_block_3_eptp_text');?></p>
                <div class="gl-discover__link-row">
                <a class="gl-discover__link" href="<?php echo __SITE_URL . 'landing/eptp';?>"><?php echo translate('about_us_features_block_3_learn_more_btn');?></a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div>
                <div class="gl-discover__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/icons/ep+.svg';?>" alt="export portal expertpanel icon">
                </div>
                <div class="gl-discover__abbreviation"><?php echo translate('about_us_features_block_3_ep_plus_abbr');?></div>
                <h3 class="gl-discover__title"><?php echo translate('about_us_features_block_3_ep_plus_title');?></h3>
                <p class="gl-discover__text"><?php echo translate('about_us_features_block_3_ep_plus_text');?></p>
                <div class="gl-discover__link-row">
                    <a class="gl-discover__link" href="<?php echo __SITE_URL . 'landing/ep_plus';?>"><?php echo translate('about_us_features_block_3_learn_more_btn');?></a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div>
                <div class="gl-discover__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/icons/1epg.svg';?>" alt="export portal expertpanel icon">
                </div>
                <div class="gl-discover__abbreviation"><?php echo translate('about_us_features_block_3_1epg_abbr');?></div>
                <h3 class="gl-discover__title"><?php echo translate('about_us_features_block_3_1epg_title');?></h3>
                <p class="gl-discover__text"><?php echo translate('about_us_features_block_3_1epg_text');?></p>
                <div class="gl-discover__link-row">
                    <a class="gl-discover__link" href="<?php echo __SITE_URL . 'landing/payments';?>"><?php echo translate('about_us_features_block_3_learn_more_btn');?></a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div>
                <div class="gl-discover__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/icons/smef.svg';?>" alt="export portal insights icon">
                </div>
                <div class="gl-discover__abbreviation"><?php echo translate('about_us_features_block_3_smef_abbr');?></div>
                <h3 class="gl-discover__title"><?php echo translate('about_us_features_block_3_smef_title');?></h3>
                <p class="gl-discover__text"><?php echo translate('about_us_features_block_3_smef_text');?></p>
                <div class="gl-discover__link-row">
                    <a class="gl-discover__link" href="<?php echo __SITE_URL . 'landing/smef';?>"><?php echo translate('about_us_features_block_3_learn_more_btn');?></a>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="gl-discover__opacity">
                <div class="gl-discover__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/icons/epi.svg';?>" alt="export portal insights icon">
                </div>
                <div class="gl-discover__abbreviation"><?php echo translate('about_us_features_block_3_epi_abbr');?></div>
                <h3 class="gl-discover__title"><?php echo translate('about_us_features_block_3_epi_title');?></h3>
                <p class="gl-discover__text"><?php echo translate('about_us_features_block_3_epi_text');?></p>
                <div class="gl-discover__link-row">
                    <span class="gl-discover__link"><?php echo translate('about_us_features_block_3_coming_soon_btn');?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="gl-discover__opacity">
                <div class="gl-discover__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/icons/epv.svg';?>" alt="export portal virtual icon">
                </div>
                <div class="gl-discover__abbreviation"><?php echo translate('about_us_features_block_3_epv_abbr');?></div>
                <h3 class="gl-discover__title"><?php echo translate('about_us_features_block_3_epv_title');?></h3>
                <p class="gl-discover__text"><?php echo translate('about_us_features_block_3_epv_text');?></p>
                <div class="gl-discover__link-row">
                    <span class="gl-discover__link"><?php echo translate('about_us_features_block_3_coming_soon_btn');?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4 col-xl-6">
            <div class="gl-discover__opacity gl-discover__docs">
                <div class="gl-discover__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/icons/docs.svg';?>" alt="export portal docs icon">
                </div>
                <div class="gl-discover__abbreviation"><?php echo translate('about_us_features_block_3_ep_docs_abbr');?></div>
                <h3 class="gl-discover__title"><?php echo translate('about_us_features_block_3_ep_docs_title');?></h3>
                <p class="gl-discover__text"><?php echo translate('about_us_features_block_3_ep_docs_text');?></p>
                <div class="gl-discover__link-row">
                    <span class="gl-discover__link"><?php echo translate('about_us_features_block_3_coming_soon_btn');?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4 col-xl-6">
            <div class="gl-discover__opacity gl-discover__contacts">
                <div class="gl-discover__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/icons/contacts.svg';?>" alt="export portal contacts icon">
                </div>
                <div class="gl-discover__abbreviation"><?php echo translate('about_us_features_block_3_ep_smart_abbr');?></div>
                <h3 class="gl-discover__title"><?php echo translate('about_us_features_block_3_ep_smart_title');?></h3>
                <p class="gl-discover__text"><?php echo translate('about_us_features_block_3_ep_smart_text');?></p>
                <div class="gl-discover__link-row">
                    <span class="gl-discover__link"><?php echo translate('about_us_features_block_3_coming_soon_btn');?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(!isset($webpackData)) { ?>
    <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/webinar_requests/schedule-demo-popup.js');?>"></script>
<?php } ?>
