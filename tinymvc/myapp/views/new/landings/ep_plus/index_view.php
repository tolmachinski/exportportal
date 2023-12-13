<script>
    $(document).ready(function() {
        var autoplay = Boolean(<?php echo !isBackstopEnabled(); ?>);

        $('[data-slider]').bxSlider({
            mode: 'horizontal',
            infiniteLoop: true,
            controls: false,
            auto: true,
            autoStart: autoplay,
            speed: 500
        });
    });
</script>

<main class="ep-plus">
    <div class="ep-plus__header">
        <img class="image image-cover" src="<?php echo __IMG_URL . 'public/img/ep_plus/ep_plus_header.jpg'; ?>" alt="ep plus">
        <div class="ep-plus__header-title-wr">
            <h1 class="ep-plus__header-title"><?php echo translate('ep_plus_header_main_title'); ?></h1>
        </div>
    </div>
    <section class="ep-plus__about">
        <div class="ep-plus__container">
            <div class="ep-plus__info">
                <h2 class="ep-plus__info-title"><?php echo translate('ep_plus_what_is_ep_title'); ?></h2>
                <p class="ep-plus__info-text"><?php echo translate('ep_plus_what_is_ep_desc'); ?></p>
            </div>

            <div class="ep-plus-about-list">
                <div class="ambassador__members-column">
                    <div class="ambassador__members-item">
                        <div class="ambassador__members-icon ambassador__logistics-icon">
                            <?php echo $icons['logistics'] ?>
                        </div>
                        <h3 class="ambassador__members-title">
                            <?php echo translate('landing_logistics_ambassador_ep_plus_member_logistic'); ?>
                        </h3>
                        <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/logistics_ambassador' ?>"><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                    </div>
                </div>


                <div class="ambassador__members-column">
                    <div class="ambassador__members-item">
                        <div class="ambassador__members-icon">
                            <?php echo $icons['factory'] ?>
                        </div>
                        <h3 class="ambassador__members-title"><?php echo translate('landing_advisors_ep_plus_roles_industry_ambassadors'); ?></h3>
                        <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/industry_ambassador'; ?>" <?php echo addQaUniqueIdentifier("logistics-ambassador__members-btn") ?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                    </div>
                </div>
                <div class="ambassador__members-column">
                    <div class="ambassador__members-item">
                        <div class="ambassador__members-icon">
                            <?php echo $icons['teach'] ?>
                        </div>
                        <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_content_ambassadors'); ?></h3>
                        <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/content_ambassador'; ?>" <?php echo addQaUniqueIdentifier("logistics-ambassador__members-btn") ?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                    </div>
                </div>
                <div class="ambassador__members-column">
                    <div class="ambassador__members-item">
                        <div class="ambassador__members-icon">
                            <?php echo $icons['government'] ?>
                        </div>
                        <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_government_and_association'); ?></h3>
                        <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/government_and_association'; ?>" <?php echo addQaUniqueIdentifier("logistics-ambassador__members-btn") ?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                    </div>
                </div>
                <div class="ambassador__members-column">
                    <div class="ambassador__members-item">
                        <div class="ambassador__members-icon ambassador__advisors-icon">
                            <?php echo $icons['advisors'] ?>
                        </div>
                        <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_advisors'); ?></h3>
                        <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/advisors'; ?>" <?php echo addQaUniqueIdentifier("logistics-ambassador__members-btn") ?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                    </div>
                </div>
                <div class="ambassador__members-column">
                    <div class="ambassador__members-item">
                        <div class="ambassador__members-icon">
                            <?php echo $icons['country'] ?>
                        </div>
                        <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_country_ambassadors'); ?></h3>
                        <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/country_ambassador'; ?>" <?php echo addQaUniqueIdentifier("logistics-ambassador__members-btn") ?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <section class="ep-plus__fields">
        <div class="ep-plus__container">
            <div class="ep-plus__info">
                <h2 class="ep-plus__info-title"><?php echo translate('ep_plus_who_can_join'); ?></h2>
                <p class="ep-plus__info-text"><?php echo translate('ep_plus_who_can_join_subtext'); ?></p>
            </div>
            <div class="ep-plus-fields-list">
                <div class="ep-plus-fields-list__item">
                    <div class="ep-plus-fields-list__image">
                        <img class="image" src="<?php echo __IMG_URL . 'public/img/ep_plus/invited_icons/icon-1.png'; ?>" alt="<?php echo translate('ep_plus_about_list_item_1', null, true); ?>">
                    </div>
                    <p class="ep-plus-fields-list__title"><?php echo translate('ep_plus_about_list_item_1'); ?></p>
                </div>
                <div class="ep-plus-fields-list__item">
                    <div class="ep-plus-fields-list__image">
                        <img class="image" src="<?php echo __IMG_URL . 'public/img/ep_plus/invited_icons/icon-2.png'; ?>" alt="<?php echo translate('ep_plus_about_list_item_2', null, true); ?>">
                    </div>
                    <p class="ep-plus-fields-list__title"><?php echo translate('ep_plus_about_list_item_2'); ?></p>
                </div>
                <div class="ep-plus-fields-list__item">
                    <div class="ep-plus-fields-list__image">
                        <img class="image" src="<?php echo __IMG_URL . 'public/img/ep_plus/invited_icons/icon-3.png'; ?>" alt="<?php echo translate('ep_plus_about_list_item_3', null, true); ?>">
                    </div>
                    <p class="ep-plus-fields-list__title"><?php echo translate('ep_plus_about_list_item_3'); ?></p>
                </div>
                <div class="ep-plus-fields-list__item">
                    <div class="ep-plus-fields-list__image">
                        <img class="image" src="<?php echo __IMG_URL . 'public/img/ep_plus/invited_icons/icon-4.png'; ?>" alt="<?php echo translate('ep_plus_about_list_item_4', null, true); ?>">
                    </div>
                    <p class="ep-plus-fields-list__title"><?php echo translate('ep_plus_about_list_item_4'); ?></p>
                </div>
                <div class="ep-plus-fields-list__item">
                    <div class="ep-plus-fields-list__image">
                        <img class="image" src="<?php echo __IMG_URL . 'public/img/ep_plus/invited_icons/icon-5.png'; ?>" alt="<?php echo translate('ep_plus_about_list_item_5', null, true); ?>">
                    </div>
                    <p class="ep-plus-fields-list__title"><?php echo translate('ep_plus_about_list_item_5'); ?></p>
                </div>
                <div class="ep-plus-fields-list__item">
                    <div class="ep-plus-fields-list__image">
                        <img class="image" src="<?php echo __IMG_URL . 'public/img/ep_plus/invited_icons/icon-6.png'; ?>" alt="<?php echo translate('ep_plus_about_list_item_6', null, true); ?>">
                    </div>
                    <p class="ep-plus-fields-list__title"><?php echo translate('ep_plus_about_list_item_6'); ?></p>
                </div>
                <div class="ep-plus-fields-list__item">
                    <div class="ep-plus-fields-list__image">
                        <img class="image" src="<?php echo __IMG_URL . 'public/img/ep_plus/invited_icons/icon-7.png'; ?>" alt="<?php echo translate('ep_plus_about_list_item_7', null, true); ?>">
                    </div>
                    <p class="ep-plus-fields-list__title"><?php echo translate('ep_plus_about_list_item_7'); ?></p>
                </div>
                <div class="ep-plus-fields-list__item">
                    <div class="ep-plus-fields-list__image">
                        <img class="image" src="<?php echo __IMG_URL . 'public/img/ep_plus/invited_icons/icon-8.png'; ?>" alt="<?php echo translate('ep_plus_about_list_item_8', null, true); ?>">
                    </div>
                    <p class="ep-plus-fields-list__title"><?php echo translate('ep_plus_about_list_item_8'); ?></p>
                </div>
                <div class="ep-plus-fields-list__item">
                    <div class="ep-plus-fields-list__image">
                        <img class="image" src="<?php echo __IMG_URL . 'public/img/ep_plus/invited_icons/icon-9.png'; ?>" alt="<?php echo translate('ep_plus_about_list_item_9', null, true); ?>">
                    </div>
                    <p class="ep-plus-fields-list__title"><?php echo translate('ep_plus_about_list_item_9'); ?></p>
                </div>
            </div>

            <p class="ep-plus__info-text"><?php echo translate('ep_plus_about_info'); ?></p>
        </div>
    </section>

    <section class="ep-plus-slider">
        <div data-slider>
            <div class="ep-plus-slider__item">
                <img class="image" src="<?php echo __IMG_URL; ?>public/img/ep_plus/ep_plus_slider.jpg" alt="why join">
                <div class="ep-plus-slider__info-wr">
                    <div class="ep-plus-slider__info">
                        <h2 class="ep-plus-slider__headline"><?php echo translate('ep_plus_slider_headline'); ?></h2>
                        <p class="ep-plus-slider__text"><?php echo translate('ep_plus_slider_paragraph_text'); ?></p>
                        <p class="ep-plus-slider__bottomline"><?php echo translate('ep_plus_slider_bottomline_text'); ?></p>
                    </div>
                </div>
            </div>
            <div class="ep-plus-slider__item">
                <img class="image" src="<?php echo __IMG_URL; ?>public/img/ep_plus/ep_plus_slider2.jpg" alt="why join">
                <div class="ep-plus-slider__info-wr">
                    <div class="ep-plus-slider__info">
                        <h2 class="ep-plus-slider__headline"><?php echo translate('ep_plus_why_ep_title'); ?></h2>
                        <p class="ep-plus-slider__text"><?php echo translate('ep_plus_slider_paragraph_text'); ?></p>
                        <p class="ep-plus-slider__bottomline"><?php echo translate('ep_plus_slider_bottomline_text_2'); ?></p>
                    </div>
                </div>
            </div>
            <div class="ep-plus-slider__item">
                <img class="image" src="<?php echo __IMG_URL; ?>public/img/ep_plus/ep_plus_slider3.jpg" alt="why join">
                <div class="ep-plus-slider__info-wr">
                    <div class="ep-plus-slider__info">
                        <h2 class="ep-plus-slider__headline"><?php echo translate('ep_plus_why_ep_title'); ?></h2>
                        <p class="ep-plus-slider__text"><?php echo translate('ep_plus_slider_paragraph_text'); ?></p>
                        <p class="ep-plus-slider__bottomline"><?php echo translate('ep_plus_slider_bottomline_text_3'); ?></p>
                    </div>
                </div>
            </div>
            <div class="ep-plus-slider__item">
                <img class="image" src="<?php echo __IMG_URL; ?>public/img/ep_plus/ep_plus_slider4.jpg" alt="why join">
                <div class="ep-plus-slider__info-wr">
                    <div class="ep-plus-slider__info">
                        <h2 class="ep-plus-slider__headline"><?php echo translate('ep_plus_why_ep_title'); ?></h2>
                        <p class="ep-plus-slider__text"><?php echo translate('ep_plus_slider_paragraph_text'); ?></p>
                        <p class="ep-plus-slider__bottomline"><?php echo translate('ep_plus_slider_bottomline_text_4'); ?></p>
                    </div>
                </div>
            </div>
            <div class="ep-plus-slider__item">
                <img class="image" src="<?php echo __IMG_URL; ?>public/img/ep_plus/ep_plus_slider5.jpg" alt="why join">
                <div class="ep-plus-slider__info-wr">
                    <div class="ep-plus-slider__info">
                        <h2 class="ep-plus-slider__headline"><?php echo translate('ep_plus_why_ep_title'); ?></h2>
                        <p class="ep-plus-slider__text"><?php echo translate('ep_plus_slider_paragraph_text'); ?></p>
                        <p class="ep-plus-slider__bottomline"><?php echo translate('ep_plus_slider_bottomline_text_5'); ?></p>
                    </div>
                </div>
            </div>
            <div class="ep-plus-slider__item">
                <img class="image" src="<?php echo __IMG_URL; ?>public/img/ep_plus/ep_plus_slider6.jpg" alt="why join">
                <div class="ep-plus-slider__info-wr">
                    <div class="ep-plus-slider__info">
                        <h2 class="ep-plus-slider__headline"><?php echo translate('ep_plus_why_ep_title'); ?></h2>
                        <p class="ep-plus-slider__text"><?php echo translate('ep_plus_slider_paragraph_text'); ?></p>
                        <p class="ep-plus-slider__bottomline"><?php echo translate('ep_plus_slider_bottomline_text_6'); ?></p>
                    </div>
                </div>
            </div>
            <div class="ep-plus-slider__item">
                <img class="image" src="<?php echo __IMG_URL; ?>public/img/ep_plus/ep_plus_slider7.jpg" alt="why join">
                <div class="ep-plus-slider__info-wr">
                    <div class="ep-plus-slider__info">
                        <h2 class="ep-plus-slider__headline"><?php echo translate('ep_plus_why_ep_title'); ?></h2>
                        <p class="ep-plus-slider__text"><?php echo translate('ep_plus_slider_paragraph_text'); ?></p>
                        <p class="ep-plus-slider__bottomline"><?php echo translate('ep_plus_slider_bottomline_text_7'); ?></p>
                    </div>
                </div>
            </div>
            <div class="ep-plus-slider__item">
                <img class="image" src="<?php echo __IMG_URL; ?>public/img/ep_plus/ep_plus_slider8.jpg" alt="why join">
                <div class="ep-plus-slider__info-wr">
                    <div class="ep-plus-slider__info">
                        <h2 class="ep-plus-slider__headline"><?php echo translate('ep_plus_why_ep_title'); ?></h2>
                        <p class="ep-plus-slider__text"><?php echo translate('ep_plus_slider_paragraph_text'); ?></p>
                        <p class="ep-plus-slider__bottomline"><?php echo translate('ep_plus_slider_bottomline_text_8'); ?></p>
                    </div>
                </div>
            </div>
            <div class="ep-plus-slider__item">
                <img class="image" src="<?php echo __IMG_URL; ?>public/img/ep_plus/ep_plus_slider9.jpg" alt="why join">
                <div class="ep-plus-slider__info-wr">
                    <div class="ep-plus-slider__info">
                        <h2 class="ep-plus-slider__headline"><?php echo translate('ep_plus_why_ep_title'); ?></h2>
                        <p class="ep-plus-slider__text"><?php echo translate('ep_plus_slider_paragraph_text'); ?></p>
                        <p class="ep-plus-slider__bottomline"><?php echo translate('ep_plus_slider_bottomline_text_9'); ?></p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <section class="ep-plus__register">
        <div class="ep-plus__container">
            <h3 class="ep-plus__register-title"><?php echo translate('ep_plus_register_block_title'); ?></h3>

            <div class="ep-plus-about-list">
                <div class="ambassador__members-column">
                    <div class="ambassador__members-item">
                        <div class="ambassador__members-icon ambassador__logistics-icon">
                            <?php echo $icons['logistics'] ?>
                        </div>
                        <h3 class="ambassador__members-title">
                            <?php echo translate('landing_logistics_ambassador_ep_plus_member_logistic'); ?>
                        </h3>
                        <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/logistics_ambassador' ?>"><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                    </div>
                </div>


                <div class="ambassador__members-column">
                    <div class="ambassador__members-item">
                        <div class="ambassador__members-icon">
                            <?php echo $icons['factory'] ?>
                        </div>
                        <h3 class="ambassador__members-title"><?php echo translate('landing_advisors_ep_plus_roles_industry_ambassadors'); ?></h3>
                        <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/industry_ambassador'; ?>" <?php echo addQaUniqueIdentifier("logistics-ambassador__members-btn") ?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                    </div>
                </div>
                <div class="ambassador__members-column">
                    <div class="ambassador__members-item">
                        <div class="ambassador__members-icon">
                            <?php echo $icons['teach'] ?>
                        </div>
                        <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_content_ambassadors'); ?></h3>
                        <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/content_ambassador'; ?>" <?php echo addQaUniqueIdentifier("logistics-ambassador__members-btn") ?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                    </div>
                </div>
                <div class="ambassador__members-column">
                    <div class="ambassador__members-item">
                        <div class="ambassador__members-icon">
                            <?php echo $icons['government'] ?>
                        </div>
                        <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_government_and_association'); ?></h3>
                        <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/government_and_association'; ?>" <?php echo addQaUniqueIdentifier("logistics-ambassador__members-btn") ?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                    </div>
                </div>
                <div class="ambassador__members-column">
                    <div class="ambassador__members-item">
                        <div class="ambassador__members-icon ambassador__advisors-icon">
                            <?php echo $icons['advisors'] ?>
                        </div>
                        <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_advisors'); ?></h3>
                        <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/advisors'; ?>" <?php echo addQaUniqueIdentifier("logistics-ambassador__members-btn") ?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                    </div>
                </div>
                <div class="ambassador__members-column">
                    <div class="ambassador__members-item">
                        <div class="ambassador__members-icon">
                            <?php echo $icons['country'] ?>
                        </div>
                        <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_country_ambassadors'); ?></h3>
                        <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/country_ambassador'; ?>" <?php echo addQaUniqueIdentifier("logistics-ambassador__members-btn") ?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                    </div>
                </div>

            </div>

        </div>
    </section>
</main>
