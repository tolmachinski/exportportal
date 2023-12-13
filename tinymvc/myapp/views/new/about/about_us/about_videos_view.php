<div class="container-center-sm">
    <div class="about-us__heading about-us__heading--videos">
        <h2 class="about-us__ttl"><?php echo translate('about_us_videos_ttl'); ?></h2>
        <p class="about-us__desc"><?php echo translate('about_us_videos_desc'); ?></p>
    </div>
</div>

<div id="js-about-videos-container" class="container-center-sm">
    <div class="about-videos">
        <div
            id="js-about-videos-slider"
            class="about-videos__inner about-videos--not-init swiper-container"
            <?php echo addQaUniqueIdentifier("about__videos-slider"); ?>
        >
            <div class="swiper-wrapper">
                <?php foreach ($videosList as $video) { ?>
                <?php $urlBackground = 'public/build/images/about/about_us/' . $video['link_img']; ?>
                <div class="swiper-slide">
                    <div class="about-videos__item js-about-videos-item">
                        <div
                            class="about-videos__bg call-action js-about-videos-bg"
                            data-js-action="modal:open-video-modal"
                            data-title="<?php echo $video['title_video']; ?>"
                            data-href="<?php echo $video['link_video']; ?>"
                            data-autoplay="true"
                            <?php echo addQaUniqueIdentifier("about__video-modal"); ?>
                        >
                            <picture class="display-b">
                                <source
                                    media="(max-width: 425px)"
                                    srcset="<?php echo getLazyImage(435, 236); ?>"
                                    data-srcset="<?php echo asset($urlBackground . '-mobile.jpg'); ?>"
                                >
                                <source
                                    media="(min-width: 768px) and (max-width: 991px)"
                                    srcset="<?php echo getLazyImage(380, 213); ?>"
                                    data-srcset="<?php echo asset($urlBackground . '-tablet.jpg'); ?>"
                                >
                                <img
                                    class="image js-lazy"
                                    width="536"
                                    height="300"
                                    src="<?php echo getLazyImage(536, 300); ?>"
                                    data-src="<?php echo asset($urlBackground . '.jpg'); ?>"
                                    alt="<?php echo $video['title_video']; ?>"
                                >
                            </picture>

                            <div class="youtube-play-icon">
                                <?php echo widgetGetSvgIcon('youtube-icon-play', 75, 52); ?>
                            </div>
                        </div>
                        <div class="about-videos__detail">
                            <div class="about-videos__ttl"><?php echo $video['title_video']; ?></div>
                            <div class="about-videos__desc">
                                <?php echo $video['description_video']; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
        <div id="js-swiper-pagination" class="swiper-pagination"></div>
        <div class="swiper-button-prev js-swiper-button-prev"><i class="ep-icon ep-icon_arrow-left"></i></div>
        <div class="swiper-button-next js-swiper-button-next"><i class="ep-icon ep-icon_arrow-right "></i></div>
    </div>
</div>
