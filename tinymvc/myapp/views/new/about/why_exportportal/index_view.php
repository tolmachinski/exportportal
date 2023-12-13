<div class="container-center-sm">
    <div class="mobile-links display-n">
        <a class="btn btn-new16 btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Menu" href="#about-flex-card__fixed-left" <?php echo addQaUniqueIdentifier("global__about-mobile-buttons-menu")?>>
            <i class="ep-icon ep-icon_items"></i>
            <?php echo translate('label_menu');?>
        </a>
    </div>
</div>

<div class="wexport">
    <section class="wexport-section">
        <div class="container-center-sm">
            <h2 class="wexport-section__ttl wexport-section__ttl--functions"><?php echo translate('why_ep_how_is_simplufyng_global_trade'); ?></h2>
            <p class="wexport-section__subttl wexport-section__subttl--functions"><?php echo translate('why_ep_functions_subttl'); ?></p>

            <?php views()->display('new/about/why_exportportal/ep_functions_view'); ?>
        </div>
    </section>

    <div class="container-center-sm" <?php echo addQaUniqueIdentifier('why_ep__banner-demo'); ?>>
        <?php echo widgetShowBanner('why_ep_before_benefits', 'promo-banner-wr--why-ep'); ?>
    </div>

    <section class="wexport-section wexport-section--benefits">
        <div class="container-center-sm">
            <h2 class="wexport-section__ttl wexport-section__ttl--benefits"><?php echo translate('why_ep_benefits_ttl'); ?></h2>
            <p class="wexport-section__subttl wexport-section__subttl--benefits"><?php echo translate('why_ep_benefits_subttl'); ?></p>
        </div>

        <?php views()->display('new/about/why_exportportal/ep_benefits_view'); ?>
    </section>


        <section id="customers-reviews" class="wexport-section wexport-section--gray">
            <h2 class="wexport-section__ttl"><?php echo translate('why_ep_reviews_ttl'); ?></h2>
            <?php if (!empty($epReviews)) {?>
                <p class="wexport-section__subttl wexport-section__subttl--reviews"><?php echo translate('why_ep_reviews_subttl'); ?></p>
            <?php }?>

            <div class="container-center-sm">
                <?php views()->display('new/ep_reviews/reviews_view'); ?>
            </div>
        </section>

    <section class="wexport-section">
        <div class="wexport-section__row">
            <div class="wexport-section__col wexport-section__col--sm100pr">
                <div class="wexport-section__bg">
                    <picture>
                        <source
                            media="(max-width: 425px)"
                            srcset="<?php echo getLazyImage(425, 266); ?>"
                            data-srcset="<?php echo asset('public/build/images/about/why_ep/sell_global-mobile.jpg'); ?>"
                        >
                        <source
                            media="(max-width: 767px)"
                            srcset="<?php echo getLazyImage(767, 480); ?>"
                            data-srcset="<?php echo asset('public/build/images/about/why_ep/sell_global-tablet.jpg'); ?>"
                        >
                        <source
                            media="(max-width: 1200px)"
                            srcset="<?php echo getLazyImage(600, 905); ?>"
                            data-srcset="<?php echo asset('public/build/images/about/why_ep/sell_global-1200.jpg'); ?>"
                        >
                        <img
                            class="image js-lazy"
                            src="<?php echo getLazyImage(960, 550); ?>"
                            data-src="<?php echo asset('public/build/images/about/why_ep/sell_global.jpg'); ?>"
                            width="956"
                            height="550"
                            alt="<?php echo translate('why_ep_think_local_sell_global_ttl', null, true); ?>"
                        >
                    </picture>
                </div>
            </div>

            <div class="wexport-section__col wexport-section__col--sm100pr">
                <div class="wexport-section__side">
                    <h2 class="wexport-section__ttl wexport-section__ttl--sellglobal"><?php echo translate('why_ep_think_local_sell_global_ttl'); ?></h2>
                    <p class="wexport-section__subttl"><?php echo translate('why_ep_think_local_sell_global_subttl'); ?></p>
                    <a href="<?php echo __SITE_URL . 'register'; ?>" class="btn btn-new16 btn-primary" <?php echo addQaUniqueIdentifier("why-ep__register-btn"); ?>>
                        <?php echo translate('why_ep_think_local_sell_global_btn'); ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="wexport-section__row wexport-section__row--reverse-sm">
            <div class="wexport-section__col wexport-section__col--sm100pr">
                <div class="wexport-section__side wexport-section__side--left">
                    <h2 class="wexport-section__ttl"><?php echo translate('why_ep_get_started_ttl'); ?></h2>
                    <p class="wexport-section__subttl"><?php echo translate('why_ep_get_started_subttl'); ?></p>
                    <a href="<?php echo __SITE_URL . 'contact'; ?>" class="btn btn-new16 btn-primary" <?php echo addQaUniqueIdentifier("why-ep__contact-us-btn")?>>
                        <?php echo translate('why_ep_get_started_btn'); ?>
                    </a>
                </div>
            </div>

            <div class="wexport-section__col wexport-section__col--sm100pr">
                <div class="wexport-section__bg">
                    <picture>
                        <source
                            media="(max-width: 425px)"
                            srcset="<?php echo getLazyImage(425, 266); ?>"
                            data-srcset="<?php echo asset('public/build/images/about/why_ep/get_started-mobile.jpg'); ?>"
                        >
                        <source
                            media="(max-width: 767px)"
                            srcset="<?php echo getLazyImage(767, 480); ?>"
                            data-srcset="<?php echo asset('public/build/images/about/why_ep/get_started-tablet.jpg'); ?>"
                        >
                        <source
                            media="(max-width: 1200px)"
                            srcset="<?php echo getLazyImage(600, 905); ?>"
                            data-srcset="<?php echo asset('public/build/images/about/why_ep/get_started-1200.jpg'); ?>"
                        >
                        <img
                            class="image js-lazy"
                            src="<?php echo getLazyImage(960, 550); ?>"
                            data-src="<?php echo asset('public/build/images/about/why_ep/get_started.jpg'); ?>"
                            width="956"
                            height="550"
                            alt="<?php echo translate('why_ep_get_started_ttl', null, true); ?>"
                        >
                    </picture>
                </div>
            </div>
        </div>
    </section>

    <section class="wexport-section wexport-section--footer footer-connect">
        <div class="wexport-questions">
            <div class="wexport-questions__bg">
                <picture>
                <source
                        media="(max-width: 425px)"
                        srcset="<?php echo getLazyImage(425, 430); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/why_ep/have_questions_bg-mobile.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 768px)"
                        srcset="<?php echo getLazyImage(768, 400); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/why_ep/have_questions_bg-tablet.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 1200px)"
                        srcset="<?php echo getLazyImage(1200, 625); ?>"
                        data-srcset="<?php echo asset('public/build/images/about/why_ep/have_questions_bg-1200.jpg'); ?>"
                    >
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(1920, 750); ?>"
                        data-src="<?php echo asset('public/build/images/about/why_ep/have_questions_bg.jpg'); ?>"
                        width="1912"
                        height="750"
                        alt="<?php echo translate('why_ep_still_have_questins_ttl', null, true); ?>"
                    >
                </picture>
            </div>
            <div class="container-center-sm">
                <div class="wexport-questions__content">
                    <div class="wexport-questions__icon">
                        <img
                            class="image js-lazy"
                            src="<?php echo getLazyImage(86, 84); ?>"
                            data-src="<?php echo asset('public/build/images/about/why_ep/questions.png'); ?>"
                            width="86"
                            height="84"
                            alt="<?php echo translate('why_ep_still_have_questins_ttl', null, true); ?>"
                        >
                    </div>

                    <div class="wexport-questions__ttl"><?php echo translate('why_ep_still_have_questins_ttl'); ?></div>
                    <p class="wexport-questions__subttl"><?php echo translate('why_ep_still_have_questins_subttl'); ?></p>
                    <a href="<?php echo __SITE_URL . 'help'; ?>" class="wexport-questions__btn btn btn-new16 btn-primary" <?php echo addQaUniqueIdentifier("why-ep__visit-help-page-btn")?>>
                        <?php echo translate('why_ep_still_have_questins_btn'); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<?php if(!isset($webpackData)) { ?>
    <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/webinar_requests/schedule-demo-popup.js');?>"></script>
<?php } ?>
