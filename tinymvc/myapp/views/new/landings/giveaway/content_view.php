<!-- section Giveaway Contest for Small Businesses -->
<section class="giveaway-intro">
    <div class="giveaway-container">
        <div class="giveaway-intro__content">
            <picture class="giveaway-intro__prize-badge">
                <source
                    srcset="<?php echo asset("public/build/images/landings/giveaway-contest/prize_badge_mobile.png")?> 1x, <?php echo asset("public/build/images/landings/giveaway-contest/prize_badge_mobile@2x.png")?> 2x"
                    media="(max-width: 767px)"
                >
                <img
                    class="image"
                    srcset="<?php echo asset("public/build/images/landings/giveaway-contest/prize_badge_tablet.png")?> 1x, <?php echo asset("public/build/images/landings/giveaway-contest/prize_badge_tablet@2x.png")?> 2x"
                    alt="<?php echo translate('giveaway_page_prize_title', null, true); ?>"
                >
            </picture>
            <h1 class="giveaway-intro__title"><?php echo translate('giveaway_page_title'); ?></h1>
            <p class="giveaway-intro__description"><?php echo translate('giveaway_page_description'); ?></p>
            <?php if ($isEnded) { ?>
                <button
                    class="giveaway-intro__btn btn btn-primary btn-new18 info-dialog"
                    data-message="<?php echo translate('giveaway_ended_info_popup_text'); ?>"
                    <?php echo addQaUniqueIdentifier('landing__giveaway-get-started-btn'); ?>
                >
                    <?php echo translate('giveaway_page_get_started_btn'); ?>
                </button>
            <?php } else { ?>
                <a
                    class="giveaway-intro__btn btn btn-primary btn-new18"
                    href="https://app.smartsheet.com/b/form/20eb38cff5c841debb67a842a9c956ab"
                    target="_blank"
                    rel="nofollow noopener"
                    <?php echo addQaUniqueIdentifier('landing__giveaway-get-started-btn'); ?>
                >
                    <?php echo translate('giveaway_page_get_started_btn'); ?>
                </a>
            <?php } ?>
        </div>
    </div>

    <picture class="giveaway-intro__background">
        <source media="(max-width: 575px)" srcset="<?php echo asset("public/build/images/landings/giveaway-contest/header_mobile.jpg"); ?>">
        <source media="(max-width: 1250px)" srcset="<?php echo asset("public/build/images/landings/giveaway-contest/header_tablet.jpg"); ?>">
        <img
            class="image"
            width="1920"
            height="770"
            src="<?php echo asset("public/build/images/landings/giveaway-contest/header.jpg")?>"
            alt="<?php echo translate('giveaway_page_title', null, true); ?>">
    </picture>
</section>
<!-- endsection Giveaway Contest for Small Businesses for Small Businesses -->

<!-- section countdown until end giveaway contest -->
<section class="giveaway-section giveaway-section--countdown">
    <div class="giveaway-section__flex giveaway-container">
        <h2 class="giveaway-section__title"><?php echo translate('giveaway_page_countdown_title'); ?></h2>

        <div
            id="js-giveaway-countdown"
            class="giveaway-countdown"
            data-giveaway-start-date="<?php echo $currentDate ? $currentDate->format(\DateTimeInterface::RFC3339) : null ?>"
            data-giveaway-end-date="<?php echo !$isEnded && $endDate ? $endDate->format(\DateTimeInterface::RFC3339) : null ?>"
        >
            <?php if ($isEnded & !isBackstopEnabled()) { ?>
                <div class="giveaway-countdown__ended">
                    <div class="giveaway-countdown__ended-bg">
                        <picture>
                            <source
                                srcset="<?php echo getLazyImage(575, 100)?>"
                                data-srcset="<?php echo asset("public/build/images/landings/giveaway-contest/giveaway_ended_mobile.jpg"); ?>"
                                media="(max-width: 575px)"
                            >
                            <source
                                srcset="<?php echo getLazyImage(1200, 416)?>"
                                data-srcset="<?php echo asset("public/build/images/landings/giveaway-contest/giveaway_ended_tablet.png"); ?>"
                                media="(max-width: 991px)"
                            >
                            <img
                                class="image js-lazy"
                                src="<?php echo getLazyImage(1920, 190); ?>"
                                data-src="<?php echo asset("public/build/images/landings/giveaway-contest/giveaway_ended.png")?>"
                                alt="<?php echo translate('giveaway_page_countdown_ended_text', null, true); ?>"
                            >
                        </picture>
                    </div>
                </div>
            <?php } ?>
            <div class="giveaway-countdown__item">
                <div
                    id="js-giveaway-countdown-days-left"
                    class="giveaway-countdown__numbers"
                    <?php echo addQaUniqueIdentifier('landing__giveaway_countdown-counter'); ?>
                >
                    <?php if (!$isStarted) { ?>
                        --
                    <?php } else if ($isEnded) { ?>
                        00
                    <?php } else { ?>
                        <?php echo str_pad($dateDiff->format('%a'), 2, '0', \STR_PAD_LEFT); ?>
                    <?php } ?>
                </div>
                <div id="js-giveaway-countdown-days-txt" class="giveaway-countdown__text"><?php echo translate('giveaway_page_countdown_days'); ?></div>
            </div>
            <div class="giveaway-countdown__item">
                <div
                    id="js-giveaway-countdown-hours-left"
                    class="giveaway-countdown__numbers"
                    <?php echo addQaUniqueIdentifier('landing__giveaway_countdown-counter'); ?>
                >
                    <?php if (!$isStarted) { ?>
                        --
                    <?php } else if ($isEnded) { ?>
                        00
                    <?php } else { ?>
                        <?php echo $dateDiff->format('%H'); ?>
                    <?php } ?>
                </div>
                <div class="giveaway-countdown__text"><?php echo translate('giveaway_page_countdown_hours'); ?></div>
            </div>
            <div class="giveaway-countdown__item">
                <div
                    id="js-giveaway-countdown-minutes-left"
                    class="giveaway-countdown__numbers"
                    <?php echo addQaUniqueIdentifier('landing__giveaway_countdown-counter'); ?>
                >
                    <?php if (!$isStarted) { ?>
                        --
                    <?php } else if ($isEnded) { ?>
                        00
                    <?php } else { ?>
                        <?php echo $dateDiff->format('%I'); ?>
                    <?php } ?>
                </div>
                <div class="giveaway-countdown__text"><?php echo translate('giveaway_page_countdown_minutes'); ?></div>
                <div class="giveaway-countdown__text-mobile"><?php echo translate('giveaway_page_countdown_minutes_mobile'); ?></div>
            </div>
            <div class="giveaway-countdown__item">
                <div
                    id="js-giveaway-countdown-seconds-left"
                    class="giveaway-countdown__numbers"
                    <?php echo addQaUniqueIdentifier('landing__giveaway_countdown-counter'); ?>
                >
                    <?php if (!$isStarted) { ?>
                        --
                    <?php } else if ($isEnded) { ?>
                        00
                    <?php } else { ?>
                        <?php echo $dateDiff->format('%S'); ?>
                    <?php } ?>
                </div>
                <div class="giveaway-countdown__text"><?php echo translate('giveaway_page_countdown_seconds'); ?></div>
                <div class="giveaway-countdown__text-mobile"><?php echo translate('giveaway_page_countdown_seconds_mobile'); ?></div>
            </div>
        </div>
    </div>
</section>
<!-- endsection countdown until end giveaway contest -->

<?php encoreEntryLinkTags($webpackData['pageConnect']); ?>

<!-- section Easy Steps to Enter -->
<section class="giveaway-section">
    <div class="giveaway-container">
        <h2 class="giveaway-section__title"><?php echo translate('giveaway_page_steps_to_enter_title'); ?></h2>

        <div class="giveaway-steps">
            <div class="giveaway-steps__divider"></div>

            <div class="giveaway-steps__content">
                <div class="giveaway-steps__item">
                    <div class="giveaway-steps__number">1</div>
                    <div class="giveaway-steps__info">
                        <div class="giveaway-steps__title"><?php echo translate('giveaway_page_first_step_title'); ?></div>
                        <div class="giveaway-steps__description"><?php echo translate('giveaway_page_first_step_description'); ?></div>
                    </div>
                    <picture class="giveaway-steps__image">
                        <source
                            srcset="<?php echo getLazyImage(369, 150)?>"
                            data-srcset="<?php echo asset("public/build/images/landings/giveaway-contest/first_step_sm.jpg"); ?>"
                            media="(max-width: 991px)"
                        >
                        <img
                            class="image js-lazy"
                            src="<?php echo getLazyImage(454, 250); ?>"
                            data-src="<?php echo asset("public/build/images/landings/giveaway-contest/first_step.jpg")?>"
                            alt="<?php echo translate('giveaway_page_first_step_title', null, true); ?>"
                        >
                    </picture>
                </div>

                <div class="giveaway-steps__item">
                    <div class="giveaway-steps__number">2</div>
                    <div class="giveaway-steps__info">
                        <div class="giveaway-steps__title"><?php echo translate('giveaway_page_second_step_title'); ?></div>
                        <div class="giveaway-steps__description"><?php echo translate('giveaway_page_second_step_description'); ?></div>
                    </div>
                    <picture class="giveaway-steps__image">
                        <source
                            srcset="<?php echo getLazyImage(369, 150)?>"
                            data-srcset="<?php echo asset("public/build/images/landings/giveaway-contest/second_step_sm.jpg"); ?>"
                            media="(max-width: 991px)"
                        >
                        <img
                            class="image js-lazy"
                            src="<?php echo getLazyImage(454, 250); ?>"
                            data-src="<?php echo asset("public/build/images/landings/giveaway-contest/second_step.jpg")?>"
                            alt="<?php echo translate('giveaway_page_second_step_title', null, true); ?>">
                    </picture>
                </div>

                <div class="giveaway-steps__item">
                    <div class="giveaway-steps__number">3</div>
                    <div class="giveaway-steps__info">
                        <div class="giveaway-steps__title"><?php echo translate('giveaway_page_third_step_title'); ?></div>
                        <div class="giveaway-steps__description">
                            <?php
                                $startDate = $startDate ? $startDate->format('F d, Y gA ') : '';
                                $endDate = $endDate ? $endDate->format('F d, Y gA ') : '';

                                echo translate('giveaway_page_third_step_description', [
                                    '{{START_DATE}}' => '<span class="giveaway-steps__date">' . $startDate . ' PST</span><br>',
                                    '{{END_DATE}}'   => '<span class="giveaway-steps__date">' . $endDate . ' PST</span>',
                                ]);
                            ?>
                        </div>
                    </div>
                    <picture class="giveaway-steps__image">
                        <source
                            srcset="<?php echo getLazyImage(369, 150)?>"
                            data-srcset="<?php echo asset("public/build/images/landings/giveaway-contest/third_step_sm.jpg"); ?>"
                            media="(max-width: 991px)"
                        >
                        <img
                            class="image js-lazy"
                            src="<?php echo getLazyImage(454, 250); ?>"
                            data-src="<?php echo asset("public/build/images/landings/giveaway-contest/third_step.jpg")?>"
                            alt="Submit your video">
                    </picture>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- endsection Easy Steps to Enter -->

<?php
    $rules = [
        1 => [
            'icon'        => 'choose_location',
            'title'       => translate('giveaway_page_first_rule_title'),
            'description' => translate('giveaway_page_first_rule_description'),
        ],
        2 => [
            'icon'        => 'call_people',
            'title'       => translate('giveaway_page_second_rule_title'),
            'description' => translate('giveaway_page_second_rule_description'),
        ],
        3 => [
            'icon'        => 'be_original',
            'title'       => translate('giveaway_page_third_rule_title'),
            'description' => translate('giveaway_page_third_rule_description'),
        ],
        4 => [
            'icon'        => 'like',
            'title'       => translate('giveaway_page_fourth_rule_title'),
            'description' => translate('giveaway_page_fourth_rule_description'),
        ],
        5 => [
            'icon'        => 'respect',
            'title'       => translate('giveaway_page_fifth_rule_title'),
            'description' => translate('giveaway_page_fifth_rule_description'),
        ],
    ];
?>

<!-- section General Rules -->
<section class="giveaway-section giveaway-section--gray">
    <div class="giveaway-container">
        <h2 class="giveaway-section__title"><?php echo translate('giveaway_page_rules_title'); ?></h2>

        <div class="giveaway-rules">
            <div class="giveaway-rules__items">
                <?php foreach ($rules as $rule) { ?>
                    <div class="giveaway-rules__item">
                        <div class="giveaway-rules__icon"><?php echo $icons[$rule['icon']]; ?></div>
                        <h3 class="giveaway-rules__title"><?php echo $rule['title']; ?></h3>
                        <p class="giveaway-rules__description"><?php echo $rule['description']; ?></p>
                    </div>
                <?php } ?>
            </div>

            <p class="giveaway-rules__terms"><?php echo translate('giveaway_page_rules_terms_txt'); ?></p>
            <a
                class="giveaway-rules__btn btn btn-outline-primary btn-new18"
                href="<?php echo __SITE_URL;?>terms_and_conditions/tc_giveaway_terms_of_conditions"
                target="_blank"
                <?php echo addQaUniqueIdentifier('landing__giveaway-tc-btn'); ?>
            >
                <?php echo translate('giveaway_page_rules_terms_btn', null, true); ?>
            </a>
        </div>
    </div>
</section>
<!-- endsection General Rules -->

<!-- section How Your Video Should Look -->
<section class="giveaway-section">
    <div class="giveaway-container">
        <h2 class="giveaway-section__title giveaway-section__title--video"><?php echo translate('giveaway_page_video_block_title'); ?></h2>
        <p class="giveaway-section__subtitle giveaway-section__subtitle--video"><?php echo translate('giveaway_page_video_block_description'); ?></p>

        <button
            class="giveaway-video call-action"
            data-js-action="modal:open-video-modal"
            data-title="<?php echo translate('giveaway_page_video_block_title', null, true); ?>"
            data-href="Zbbi74MD5tE"
            data-autoplay="<?php echo !isBackstopEnabled() ? true : false ?>"
            <?php echo addQaUniqueIdentifier('landing__giveaway-video-block'); ?>
        >
            <picture>
                <source
                    srcset="<?php echo getLazyImage(475, 279)?>"
                    data-srcset="<?php echo asset('public/build/images/landings/giveaway-contest/video_bg_mobile.jpg'); ?>"
                    media="(max-width: 475px)"
                >
                <source
                    srcset="<?php echo getLazyImage(991, 470)?>"
                    data-srcset="<?php echo asset('public/build/images/landings/giveaway-contest/video_bg_tablet.jpg'); ?>"
                    media="(max-width: 991px)"
                >
                <img
                    class="image js-lazy"
                    src="<?php echo getLazyImage(1420, 500); ?>"
                    data-src="<?php echo asset('public/build/images/landings/giveaway-contest/video_bg.jpg'); ?>"
                    alt="<?php echo translate('giveaway_page_video_block_title', null, true); ?>"
                >
            </picture>

            <span class="youtube-play-icon">
                <?php echo widgetGetSvgIcon('youtube-icon-play', 75, 52); ?>
            </span>
        </button>
    </div>
</section>
<!-- endsection How Your Video Should Look -->

<!-- section Win Export Portal's Giveaway Contest  -->
<section class="giveaway-section giveaway-section--prize">
    <picture class="giveaway-prize-bg">
        <source
            srcset="<?php echo getLazyImage(575, 1091)?>"
            data-srcset="<?php echo asset('public/build/images/landings/giveaway-contest/prize_bg_mobile.jpg'); ?>"
            media="(max-width: 575px)"
        >
        <source
            srcset="<?php echo getLazyImage(1200, 1446)?>"
            data-srcset="<?php echo asset('public/build/images/landings/giveaway-contest/prize_bg_tablet.jpg'); ?>"
            media="(max-width: 1200px)"
        >
        <img
            class="image js-lazy"
            src="<?php echo getLazyImage(1912, 968); ?>"
            data-src="<?php echo asset('public/build/images/landings/giveaway-contest/prize_bg.jpg'); ?>"
            alt="<?php echo translate('giveaway_page_prize_title', null, true); ?>"
        >
    </picture>

    <div class="giveaway-container">
        <div class="giveaway-prize">
            <h2 class="giveaway-prize__title"><?php echo translate('giveaway_page_prize_title'); ?></h2>

            <picture class="giveaway-prize__badge">
                <source
                    srcset="<?php echo getLazyImage(150, 70)?>"
                    data-srcset="<?php echo asset("public/build/images/landings/giveaway-contest/prize_badge_mobile.png")?> 1x, <?php echo asset("public/build/images/landings/giveaway-contest/prize_badge_mobile@2x.png")?> 2x"
                    media="(max-width: 475px)"
                >
                <source
                    media="(max-width: 1200px)"
                    srcset="<?php echo getLazyImage(200, 92)?>"
                    data-srcset="<?php echo asset("public/build/images/landings/giveaway-contest/prize_badge_tablet.png")?> 1x, <?php echo asset("public/build/images/landings/giveaway-contest/prize_badge_tablet@2x.png")?> 2x"
                >
                <img
                    class="image js-lazy"
                    src="<?php echo getLazyImage(300, 300); ?>"
                    data-src="<?php echo asset('public/build/images/landings/giveaway-contest/prize_badge.png'); ?>"
                    alt="<?php echo translate('giveaway_page_prize_title', null, true); ?>"
                >
            </picture>

            <div class="giveaway-prize__item">
                <div class="giveaway-prize__item-icon"><?php echo $icons['cash']; ?></div>
                <div class="giveaway-prize__item-content">
                    <h3 class="giveaway-prize__item-name"><?php echo translate('giveaway_page_first_prize_title'); ?></h3>
                    <p class="giveaway-prize__item-description"><?php echo translate('giveaway_page_first_prize_desc'); ?></p>
                </div>
            </div>

            <div class="giveaway-prize__item">
                <div class="giveaway-prize__item-icon"><?php echo $icons['video_promotion']; ?></div>
                <div class="giveaway-prize__item-content">
                    <h3 class="giveaway-prize__item-name"><?php echo translate('giveaway_page_second_prize_title'); ?></h3>
                    <p class="giveaway-prize__item-description"><?php echo translate('giveaway_page_second_prize_desc'); ?></p>
                </div>
            </div>

            <?php if ($isEnded) { ?>
                <button
                    class="giveaway-prize__btn btn btn-primary btn-new18 info-dialog"
                    data-message="<?php echo translate('giveaway_ended_info_popup_text'); ?>"
                    <?php echo addQaUniqueIdentifier('landing__giveaway-try-now-btn'); ?>
                >
                    <?php echo translate('giveaway_page_prize_try_now_btn'); ?>
                </button>
            <?php } else { ?>
                <a
                    class="giveaway-prize__btn btn btn-primary btn-new18"
                    href="https://app.smartsheet.com/b/form/20eb38cff5c841debb67a842a9c956ab"
                    target="_blank"
                    rel="nofollow noopener"
                    <?php echo addQaUniqueIdentifier('landing__giveaway-try-now-btn'); ?>
                >
                    <?php echo translate('giveaway_page_prize_try_now_btn'); ?>
                </a>
            <?php } ?>
        </div>
    </div>
</section>
<!-- endsection Win Export Portal’s Giveaway Contest  -->

<!-- section Any Questions About the Contest?  -->
<div class="giveaway-container">
    <section class="giveaway-section giveaway-section--gray">
        <div class="giveaway-section__icon"><?php echo $icons['questions'] ?></div>
        <h2 class="giveaway-section__title"><?php echo translate('giveaway_page_questions_title'); ?></h2>
        <p class="giveaway-section__subtitle"><?php echo translate('giveaway_page_questions_subtitle'); ?></p>

        <div class="giveaway-questions">
            <button
                class="giveaway-questions__btn btn btn-primary btn-new18 fancybox.ajax fancyboxValidateModal"
                data-fancybox-href="<?php echo __CURRENT_SUB_DOMAIN_URL . 'contact/popup_forms/contact_us';?>"
                data-title="<?php echo translate('giveaway_page_questions_contact_us_btn', null, true); ?>"
                data-wrap-class="fancybox-contact-us"
                <?php echo addQaUniqueIdentifier('landing__giveaway-contact-us-btn'); ?>
            >
                <?php echo translate('giveaway_page_questions_contact_us_btn'); ?>
            </button>
        </div>
    </section>
</div>
<!-- endsection Any Questions About the Contest?  -->


<div class="giveaway-sections giveaway-container">
    <!-- section Any Questions About the Contest?  -->
    <section class="giveaway-section giveaway-section--gray giveaway-section--w695">
        <div class="giveaway-section__icon"><?php echo $icons['social'] ?></div>
        <h2 class="giveaway-section__title giveaway-section__title--social"><?php echo translate('giveaway_page_socials_title'); ?></h2>
        <p class="giveaway-section__subtitle"><?php echo translate('giveaway_page_socials_subtitle'); ?></p>

        <div class="giveaway-socials">
            <a
                class="giveaway-socials__link"
                href="https://www.facebook.com/ExportPortal"
                target="_blank"
                rel="nofollow noopener"
            >
                <?php echo $icons['facebook']; ?>
            </a>
            <a
                class="giveaway-socials__link"
                href="https://twitter.com/exportportal"
                target="_blank"
                rel="nofollow noopener"
            >
                <?php echo $icons['twitter']; ?>
            </a>
            <a
                class="giveaway-socials__link giveaway-socials__link--instagram"
                href="https://www.instagram.com/export.portal"
                target="_blank"
                rel="nofollow noopener"
            >
                <?php echo $icons['instagram']; ?>
            </a>
            <a
                class="giveaway-socials__link"
                href="https://www.linkedin.com/company/export-portal-los-angeles"
                target="_blank"
                rel="nofollow noopener"
            >
                <?php echo $icons['linkedin']; ?>
            </a>
        </div>
    </section>
    <!-- endsection Any Questions About the Contest?  -->

    <!-- section Any Questions About the Contest?  -->
    <section class="giveaway-section giveaway-section--gray giveaway-section--w695">
        <div class="giveaway-section__icon"><?php echo $icons['share'] ?></div>
        <h2 class="giveaway-section__title"><?php echo translate('giveaway_page_share_title'); ?></h2>
        <p class="giveaway-section__subtitle"><?php echo translate('giveaway_page_share_subtitle'); ?></p>

        <div class="giveaway-share">
            <button
                class="giveaway-share__btn btn btn-primary btn-new18 call-action"
                data-js-action="languages:open-social-modal"
                data-classes="mw-300"
                title="<?php echo translate('giveaway_page_share_popup_title', null, true); ?>"
                <?php echo addQaUniqueIdentifier('landing__giveaway-share-btn'); ?>
            >
                <?php echo translate('giveaway_page_share_btn'); ?>
            </button>
        </div>
    </section>
    <!-- endsection Any Questions About the Contest?  -->
</div>

<!-- section Footer images  -->
<section class="giveaway-section giveaway-section--footer footer-connect">
    <picture>
        <source
            srcset="<?php echo getLazyImage(575, 375)?>"
            data-srcset="<?php echo asset('public/build/images/landings/giveaway-contest/footer_images_mobile.jpg'); ?>"
            media="(max-width: 575px)"
        >
        <source
            srcset="<?php echo getLazyImage(1200, 255)?>"
            data-srcset="<?php echo asset('public/build/images/landings/giveaway-contest/footer_images_tablet.jpg'); ?>"
            media="(max-width: 1200px)"
        >
        <img
            class="image js-lazy"
            src="<?php echo getLazyImage(1920, 245); ?>"
            data-src="<?php echo asset('public/build/images/landings/giveaway-contest/footer_images.jpg'); ?>"
            alt="Images"
        >
    </picture>
</section>
<!-- endsection Footer images  -->

<?php
    encoreEntryScriptTags('app');
    encoreEntryScriptTags('footer');
    encoreEntryScriptTags($webpackData['pageConnect']);
?>


