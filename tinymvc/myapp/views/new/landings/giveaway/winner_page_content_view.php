<!-- section Giveaway Contest for Small Businesses has Ended -->
<section class="giveaway-intro giveaway-intro--winner">
    <div class="giveaway-container">
        <div id="js-giveaway-intro-section" class="giveaway-intro__content giveaway-intro__content--winner">
            <h1 class="giveaway-intro__title"><?php echo translate('giveaway_winner_page_intro_title'); ?></h1>
            <p class="giveaway-intro__description"><?php echo translate('giveaway_winner_page_intro_desc'); ?></p>
            <button
                class="giveaway-intro__btn btn btn-primary btn-new18 call-action"
                data-js-action="giveaway:scroll-to"
                data-anchor="here-is-our-winner"
                <?php echo addQaUniqueIdentifier('landing__giveaway-view-our-winner-btn'); ?>
            >
                <?php echo translate('giveaway_winner_page_intro_btn'); ?>
            </button>
        </div>
    </div>

    <picture class="giveaway-intro__background">
        <source media="(max-width: 575px)" srcset="<?php echo asset("public/build/images/landings/giveaway-contest/header_winner_mobile.jpg"); ?>">
        <source media="(max-width: 1250px)" srcset="<?php echo asset("public/build/images/landings/giveaway-contest/header_winner_tablet.jpg"); ?>">
        <img
            class="image"
            width="1920"
            height="770"
            src="<?php echo asset("public/build/images/landings/giveaway-contest/header_winner.jpg")?>"
            alt="<?php echo translate('giveaway_winner_page_intro_title', null, true); ?>">
    </picture>
</section>
<!-- endsection Giveaway Contest for Small Businesses has Ended -->

<!-- section Here’s our Winner! -->
<section class="giveaway-section">
    <div class="giveaway-winner">
        <picture class="giveaway-winner__bg">
            <source
                media="(max-width: 400px)"
                srcset="<?php echo asset("public/build/images/landings/giveaway-contest/winner_bg_mobile.png"); ?>"
            >
            <source
                media="(max-width: 1200px)"
                srcset="<?php echo asset("public/build/images/landings/giveaway-contest/winner_bg_tablet.png"); ?>"
            >
            <img
                class="image"
                src="<?php echo asset('public/build/images/landings/giveaway-contest/winner_bg.png'); ?>"
                alt="<?php echo translate('giveaway_winner_page_winner_section_title', null, true); ?>"
            >
        </picture>
        <div class="giveaway-container">
            <h2 class="giveaway-winner__title"><?php echo translate('giveaway_winner_page_winner_section_title'); ?></h2>

            <button
                id="here-is-our-winner"
                class="giveaway-winner__video call-action"
                data-js-action="modal:open-video-modal"
                data-title="<?php echo translate('giveaway_page_video_block_title', null, true); ?>"
                data-href="<?php echo config('giveaway_winner_video_link'); ?>"
                data-autoplay="true"
                <?php echo addQaUniqueIdentifier('landing__giveaway-video-block'); ?>
            >
                <picture>
                    <source
                        media="(max-width: 575px)"
                        srcset="<?php echo getLazyImage(575, 337)?>"
                        data-srcset="<?php echo asset('public/build/images/landings/giveaway-contest/winner_video_bg_mobile.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 1024px)"
                        srcset="<?php echo getLazyImage(1024, 486)?>"
                        data-srcset="<?php echo asset('public/build/images/landings/giveaway-contest/winner_video_bg_tablet.jpg'); ?>"
                    >
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(1420, 500); ?>"
                        data-src="<?php echo asset('public/build/images/landings/giveaway-contest/winner_video_bg.jpg'); ?>"
                        alt="<?php echo translate('giveaway_winner_page_winner_section_video_alt', null, true); ?>"
                    >
                </picture>

                <span class="youtube-play-icon">
                    <?php echo widgetGetSvgIcon('youtube-icon-play', 75, 52); ?>
                </span>
            </button>

            <div class="giveaway-winner__info">
                <picture class="giveaway-winner__info-bg">
                    <source
                        media="(max-width: 525px)"
                        srcset="<?php echo getLazyImage(311, 98)?>"
                        data-srcset="<?php echo asset('public/build/images/landings/giveaway-contest/laurel_wreaths_mobile.png'); ?>"
                    >
                    <source
                        media="(max-width: 1200px)"
                        srcset="<?php echo getLazyImage(738, 200)?>"
                        data-srcset="<?php echo asset('public/build/images/landings/giveaway-contest/laurel_wreaths_tablet.png'); ?>"
                    >
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(1420, 300); ?>"
                        data-src="<?php echo asset('public/build/images/landings/giveaway-contest/laurel_wreaths.png'); ?>"
                        alt="<?php echo translate('giveaway_winner_page_congratulations_bg_alt', null, true); ?>"
                    >
                </picture>
                <h3 class="giveaway-winner__congratulations-title"><?php echo translate('giveaway_winner_page_congratulations_title'); ?></h3>
                <h4 class="giveaway-winner__winner-name">[ENTER NAME]</h4>
                <p class="giveaway-winner__congratulations-desc"><?php echo translate('giveaway_winner_page_congratulations_desc1'); ?></p>
                <p class="giveaway-winner__congratulations-desc"><?php echo translate('giveaway_winner_page_congratulations_desc2'); ?></p>
            </div>
        </div>
    </div>
</section>
<!-- endsection Here’s our Winner! -->

<?php encoreEntryLinkTags($webpackData['pageConnect']); ?>

<!-- We Support SMEs All around the World -->
<section class="giveaway-section giveaway-section--promo">
    <div class="giveaway-promo">
        <picture class="giveaway-promo__bg">
            <source
                media="(max-width: 575px)"
                srcset="<?php echo getLazyImage(575, 719)?>"
                data-srcset="<?php echo asset('public/build/images/landings/giveaway-contest/ep_promo_mobile.jpg'); ?>"
            >
            <source
                media="(max-width: 1024px)"
                srcset="<?php echo getLazyImage(1024, 837)?>"
                data-srcset="<?php echo asset('public/build/images/landings/giveaway-contest/ep_promo_tablet.jpg'); ?>"
            >
            <source
                media="(max-width: 1200px)"
                srcset="<?php echo getLazyImage(1200, 837)?>"
                data-srcset="<?php echo asset('public/build/images/landings/giveaway-contest/ep_promo_1200.jpg'); ?>"
            >
            <img
                class="image js-lazy"
                src="<?php echo getLazyImage(1920, 730); ?>"
                data-src="<?php echo asset('public/build/images/landings/giveaway-contest/ep_promo.jpg'); ?>"
                alt="<?php echo translate('giveaway_winner_page_promo_bg_alt'); ?>"
            >
        </picture>
        <div class="giveaway-container">
            <div class="giveaway-promo__content">
                <div class="giveaway-promo__icon">
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(186, 204); ?>"
                        data-src="<?php echo asset('public/build/images/landings/giveaway-contest/epl_logo_gif.gif'); ?>"
                        alt="Export Portal"
                    >
                </div>

                <h2 class="giveaway-promo__ttl"><?php echo translate('giveaway_winner_page_promo_title'); ?></h2>
                <p class="giveaway-promo__subttl"><?php echo translate('giveaway_winner_page_promo_subtitle'); ?></p>
                <a
                    class="giveaway-promo__btn btn btn-primary btn-new18"
                    href="<?php echo logged_in() ? __SITE_URL . 'usr/' . strForUrl(user_name_session()) . '-' . id_session() : __SITE_URL . 'register'; ?>"
                    <?php echo addQaUniqueIdentifier('landing__giveaway-join-now-btn'); ?>
                >
                    <?php echo translate('giveaway_winner_page_promo_btn'); ?>
                </a>
            </div>
        </div>
    </div>
</section>
<!-- endWe Support SMEs All around the World -->

<!-- section Stay Connected  -->
<div class="giveaway-container">
    <section class="giveaway-section giveaway-section--gray">
        <div class="giveaway-section__icon"><?php echo $icons['envelope'] ?></div>
        <h2 class="giveaway-section__title"><?php echo translate('giveaway_winner_page_subscribe_title'); ?></h2>
        <p class="giveaway-section__subtitle giveaway-section__subtitle--subscribe"><?php echo translate('giveaway_winner_page_subscribe_subtitle'); ?></p>
        <a
            class="giveaway-section__btn btn btn-primary btn-new18"
            href="<?php echo __SITE_URL . 'subscribe';?>"
            <?php echo addQaUniqueIdentifier('landing__giveaway-subscribe-btn'); ?>
        >
            <?php echo translate('giveaway_winner_page_subscribe_btn'); ?>
        </a>
    </section>
</div>
<!-- endsection Stay Connected  -->

<!-- section Footer images  -->
<section class="giveaway-section giveaway-section--footer footer-connect">
    <picture>
        <source
            srcset="<?php echo getLazyImage(575, 375)?>"
            data-srcset="<?php echo asset('public/build/images/landings/giveaway-contest/winner_footer_images_mobile.jpg'); ?>"
            media="(max-width: 575px)"
        >
        <source
            srcset="<?php echo getLazyImage(1200, 255)?>"
            data-srcset="<?php echo asset('public/build/images/landings/giveaway-contest/winner_footer_images_tablet.jpg'); ?>"
            media="(max-width: 1200px)"
        >
        <img
            class="image js-lazy"
            src="<?php echo getLazyImage(1912, 245); ?>"
            data-src="<?php echo asset('public/build/images/landings/giveaway-contest/winner_footer_images.jpg'); ?>"
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
