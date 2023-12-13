<section class="home-section start-selling-steps-block container-1420">
    <div class="section-header section-header--title-only">
        <h2 class="section-header__title"><?php echo $sectionTitle; ?></h2>
    </div>

    <ul class="start-steps<?php echo is_buyer() ? ' start-steps--buying' : ''; ?>">
        <?php foreach ($steps as $k => $step) { ?>
            <li class="start-steps__item">
                <?php if ($step['video']) { ?>
                    <button
                        class="start-steps__video call-action"
                        data-js-action="modal:open-video-modal"
                        data-title="<?php echo $step['title']; ?>"
                        data-href="<?php echo $step['video']['short_url']; ?>"
                        data-autoplay="<?php echo !isBackstopEnabled() ? true : false; ?>"
                        <?php echo addQaUniqueIdentifier("home__steps-to-start_video-{$k}"); ?>
                    >
                        <picture class="start-steps__picture">
                            <source
                                media="(max-width: 767px)"
                                srcset="<?php echo getLazyImage(576, 238); ?>"
                                data-srcset="<?php echo $step['video']['picture']['mobile']; ?> 1x, <?php echo $step['video']['picture']['mobile@2x']; ?> 2x"
                            >
                            <source
                                media="(max-width: 991px)"
                                srcset="<?php echo getLazyImage(369, 207); ?>"
                                data-srcset="<?php echo $step['video']['picture']['tablet']; ?> 1x, <?php echo $step['video']['picture']['tablet@2x']; ?> 2x"
                            >
                            <img
                                class="start-steps__img js-lazy"
                                width="453"
                                height="250"
                                src="<?php echo getLazyImage(453, 250); ?>"
                                data-src="<?php echo $step['video']['picture']['desktop']; ?>"
                                data-srcset="<?php echo $step['video']['picture']['desktop']; ?> 1x, <?php echo $step['video']['picture']['desktop@2x']; ?> 2x"
                                alt="<?php echo $step['title']; ?>"
                            >
                        </picture>

                        <span class="youtube-play-icon">
                            <?php echo widgetGetSvgIcon('youtube-icon-play', 75, 52); ?>
                        </span>
                    </button>
                <?php } ?>

                <?php if ($step['picture']) { ?>
                    <picture class="start-steps__picture">
                        <source
                            media="(max-width: 575px)"
                            srcset="<?php echo getLazyImage(576, 238); ?>"
                            data-srcset="<?php echo $step['picture']['mobile']; ?> 1x, <?php echo $step['picture']['mobile@2x']; ?> 2x"
                        >
                        <source
                            media="(max-width: 1199px)"
                            srcset="<?php echo getLazyImage(369, 207); ?>"
                            data-srcset="<?php echo $step['picture']['tablet']; ?> 1x, <?php echo $step['picture']['tablet@2x']; ?> 2x"
                        >
                        <img
                            class="start-steps__img js-lazy"
                            src="<?php echo getLazyImage(453, 250); ?>"
                            data-src="<?php echo $step['picture']['desktop']; ?>"
                            data-srcset="<?php echo $step['picture']['desktop']; ?> 1x, <?php echo $step['picture']['desktop@2x']; ?> 2x"
                            alt="<?php echo $step['title']; ?>"
                        >
                    </picture>
                <?php } ?>

                <div class="start-steps__delimiter">
                    <div class="start-steps__number"><?php echo $k; ?></div>
                </div>

                <div class="start-steps__info">
                    <div class="start-steps__detail">
                        <h3 class="start-steps__ttl"><?php echo $step['title']; ?></h3>
                        <p class="start-steps__desc"><?php echo $step['description']; ?></p>
                    </div>

                    <?php if ($step['link']) { ?>
                        <a
                            class="start-steps__link"
                            href="<?php echo $step['link']['href']; ?>"
                            title="<?php echo $step['title']; ?>"
                            <?php echo addQaUniqueIdentifier("home__steps-to-start_link-{$k}"); ?>
                        >
                            <?php echo $step['link']['text']; ?><?php echo widgetGetSvgIcon('arrowRight', 15, 15); ?>
                        </a>
                    <?php } ?>
                </div>
            </li>
        <?php } ?>
    </ul>
</section>
