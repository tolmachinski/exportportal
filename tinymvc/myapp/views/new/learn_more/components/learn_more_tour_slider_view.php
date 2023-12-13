<div class="learnmore-slider-tour">
    <div class="container-center">

        <div class="wr-learnmore-country__titles">
            <h2 class="learnmore-ttl tac">
                <?php echo translate('learn_more_block_country_tour_header'); ?>
            </h2>
            <div class="learnmore-subttl tac"><?php echo translate('learn_more_block_country_tour_subtitle'); ?></div>
        </div>

        <div class="learnmore-slider-tour__wr">
            <div class="learnmore-slider-tour__container learnmore-slider-tour__slider-block js-learnmore-slider-tour" data-lazy-name="learnmore-slider-tour">
                <div class="learnmore-slider-tour__item call-action"
                     data-js-action="modal:open-video-modal"
                     data-title="<?php echo translate('learn_more_block_country_tour_video_mumbai', null, true); ?>"
                     data-href="22cwRO7BKGE"
                     data-autoplay="true"
                    <?php echo addQaUniqueIdentifier("page__learnmore-tour__slider-item"); ?>
                >
                    <div class="youtube-play-icon">
                        <?php echo widgetGetSvgIcon('youtube-icon-play', 85, 60); ?>
                    </div>
                    <img class="image js-lazy"
                         src="<?php echo getLazyImage(720, 453); ?>" height="453" width="720"
                         data-src="<?php echo asset('public/build/images/learn-more/slider-1.jpg'); ?>"
                         alt="<?php echo translate('learn_more_block_country_tour_video_mumbai') ?>"
                        <?php echo addQaUniqueIdentifier("page__learnmore-tour__slider-image"); ?>
                    >
                    <div class="learnmore-slider-tour__detail">
                        <div class="learnmore-slider-tour__ttl" <?php echo addQaUniqueIdentifier("page__learnmore-tour__slider-ttl"); ?>><?php echo translate('learn_more_block_country_tour_video_mumbai'); ?></div>
                        <div class="learnmore-slider-tour__date" <?php echo addQaUniqueIdentifier("page__learnmore-tour__slider-date"); ?>><?php echo translate('calendar_m_02'); ?> 2018</div>
                    </div>
                </div>
                <div class="learnmore-slider-tour__item call-action"
                     data-js-action="modal:open-video-modal"
                     data-title="<?php echo translate('learn_more_block_country_tour_video_russia', null, true); ?>"
                     data-href="8g8fvH7MgrI"
                     data-autoplay="true"
                    <?php echo addQaUniqueIdentifier("page__learnmore-tour__slider-item"); ?>
                >
                    <div class="youtube-play-icon">
                        <?php echo widgetGetSvgIcon('youtube-icon-play', 85, 60); ?>
                    </div>
                    <img class="image js-lazy"
                         src="<?php echo getLazyImage(720, 453); ?>" height="453" width="720"
                         data-src="<?php echo asset('public/build/images/learn-more/slider-2.jpg'); ?>"
                         alt="<?php echo translate('learn_more_block_country_tour_video_russia'); ?>"
                        <?php echo addQaUniqueIdentifier("page__learnmore-tour__slider-image"); ?>
                    >
                    <div class="learnmore-slider-tour__detail">
                        <div class="learnmore-slider-tour__ttl" <?php echo addQaUniqueIdentifier("page__learnmore-tour__slider-ttl"); ?>><?php echo translate('learn_more_block_country_tour_video_russia'); ?></div>
                        <div class="learnmore-slider-tour__date" <?php echo addQaUniqueIdentifier("page__learnmore-tour__slider-date"); ?>><?php echo translate('calendar_m_05'); ?> 2018</div>
                    </div>
                </div>
                <div class="learnmore-slider-tour__item call-action" data-js-action="modal:open-video-modal" data-title="<?php echo translate('learn_more_block_country_tour_video_vietnam', null, true); ?>" data-href="XgapsnD6lAQ" data-autoplay="true" <?php echo addQaUniqueIdentifier("page__learnmore-tour__video-modal"); ?>>
                    <div class="youtube-play-icon">
                        <?php echo widgetGetSvgIcon('youtube-icon-play', 85, 60); ?>
                    </div>
                    <img class="image js-lazy" src="<?php echo getLazyImage(720, 453); ?>" data-src="<?php echo asset('public/build/images/learn-more/slider-3.jpg'); ?>" alt="<?php echo translate('learn_more_block_country_tour_video_vietnam'); ?>">
                    <div class="learnmore-slider-tour__detail">
                        <div class="learnmore-slider-tour__ttl"><?php echo translate('learn_more_block_country_tour_video_vietnam'); ?></div>
                        <div class="learnmore-slider-tour__date"><?php echo translate('calendar_m_06'); ?> 2018</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="learnmore-slider-tour__slider-btn">
            <div class="learnmore-slider-tour__subttl"><?php echo translate('learn_more_block_country_tour_let_us_know'); ?></div>
            <a class="btn btn-primary fancybox.ajax fancyboxValidateModal" data-title="<?php echo translate('learn_more_block_country_tour_btn_contact', null, true); ?>" href="<?php echo __SITE_URL . 'contact/popup_forms/contact_us'; ?>"><?php echo translate('learn_more_block_country_tour_btn_contact'); ?></a>
        </div>
    </div>
</div>
