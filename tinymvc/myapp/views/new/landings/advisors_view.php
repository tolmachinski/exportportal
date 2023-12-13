<?php if (!isset($webpackData)) { ?>
    <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/landings/advisors.js'); ?>"></script>
<?php } ?>

<div class="epl-page">


    <?php encoreLinks(); ?>

    <section class="epl-slider">
        <div class="epl-slider__container js-advisors-benefits-slider" id="js-advisors-benefits-slider">
            <div class="epl-slider__slide" <?php echo addQaUniqueIdentifier('landing__advisors-item'); ?>>
                <img class="epl-slider__image js-lazy" data-src="<?php echo asset("public/build/images/landings/advisors/670887766.jpg"); ?>" src="<?php echo getLazyImage(1912, 670); ?>" alt="Advisor Benefits">
                <div class="epl-slider__info">
                    <h2 class="epl-slider__headline"><?php echo translate('landing_advisors_benefits_slides_title'); ?></h2>
                    <div class="epl-slider__text"><?php echo translate('landing_advisors_benefits_slide_1_text'); ?></div>
                </div>
            </div>
            <div class="epl-slider__slide" <?php echo addQaUniqueIdentifier('landing__advisors-item'); ?>>

                <img class="epl-slider__image js-lazy" data-src="<?php echo asset("public/build/images/landings/advisors/593112233.jpg"); ?>" src="<?php echo getLazyImage(1912, 670); ?>" alt="Advisor Benefits">
                <div class="epl-slider__info">
                    <h2 class="epl-slider__headline"><?php echo translate('landing_advisors_benefits_slides_title'); ?></h2>
                    <div class="epl-slider__text"><?php echo translate('landing_advisors_benefits_slide_2_text'); ?></div>
                </div>
            </div>
            <div class="epl-slider__slide" <?php echo addQaUniqueIdentifier('landing__advisors-item'); ?>>
                <img class="epl-slider__image js-lazy" data-src="<?php echo asset("public/build/images/landings/advisors/1310708237.jpg"); ?>" src="<?php echo getLazyImage(1912, 670); ?>" alt="Advisor Benefits">
                <div class="epl-slider__info">
                    <h2 class="epl-slider__headline"><?php echo translate('landing_advisors_benefits_slides_title'); ?></h2>
                    <div class="epl-slider__text"><?php echo translate('landing_advisors_benefits_slide_3_text'); ?></div>
                    <ul class="epl-slider__list">
                        <li class="epl-slider__list-item"><?php echo translate('landing_advisors_benefits_slide_3_li_1'); ?></li>
                        <li class="epl-slider__list-item"><?php echo translate('landing_advisors_benefits_slide_3_li_2'); ?></li>
                        <li class="epl-slider__list-item"><?php echo translate('landing_advisors_benefits_slide_3_li_3'); ?></li>
                    </ul>
                </div>
            </div>
            <div class="epl-slider__slide" <?php echo addQaUniqueIdentifier('landing__advisors-item'); ?>>
                <img class="epl-slider__image js-lazy" data-src="<?php echo asset("public/build/images/landings/advisors/1300697224.jpg"); ?>" src="<?php echo getLazyImage(1912, 670); ?>" alt="Advisor Benefits">
                <div class="epl-slider__info">
                    <h2 class="epl-slider__headline"><?php echo translate('landing_advisors_benefits_slides_title'); ?></h2>
                    <div class="epl-slider__text"><?php echo translate('landing_advisors_benefits_slide_4_text'); ?></div>
                </div>
            </div>
            <div class="epl-slider__slide" <?php echo addQaUniqueIdentifier('landing__advisors-item'); ?>>

                <img class="epl-slider__image js-lazy" data-src="<?php echo asset("public/build/images/landings/advisors/1149705779.jpg"); ?>" src="<?php echo getLazyImage(1912, 670); ?>" alt="Advisor Benefits">
                <div class="epl-slider__info">
                    <h2 class="epl-slider__headline"><?php echo translate('landing_advisors_benefits_slides_title'); ?></h2>
                    <div class="epl-slider__text"><?php echo translate('landing_advisors_benefits_slide_5_text'); ?></div>
                    <ul class="epl-slider__list">
                        <li class="epl-slider__list-item"><?php echo translate('landing_advisors_benefits_slide_5_li_1'); ?></li>
                        <li class="epl-slider__list-item"><?php echo translate('landing_advisors_benefits_slide_5_li_2'); ?></li>
                        <li class="epl-slider__list-item"><?php echo translate('landing_advisors_benefits_slide_5_li_3'); ?></li>
                        <li class="epl-slider__list-item"><?php echo translate('landing_advisors_benefits_slide_5_li_4'); ?></li>
                    </ul>
                </div>
            </div>
            <div class="epl-slider__slide" <?php echo addQaUniqueIdentifier('landing__advisors-item'); ?>>

                <img class="epl-slider__image js-lazy" data-src="<?php echo asset("public/build/images/landings/advisors/1084051082.jpg"); ?>" src="<?php echo getLazyImage(1912, 670); ?>" alt="Advisor Benefits">
                <div class="epl-slider__info">
                    <h2 class="epl-slider__headline"><?php echo translate('landing_advisors_benefits_slides_title'); ?></h2>
                    <div class="epl-slider__text"><?php echo translate('landing_advisors_benefits_slide_6_text'); ?></div>
                    <ul class="epl-slider__list">
                        <li class="epl-slider__list-item"><?php echo translate('landing_advisors_benefits_slide_6_li_1'); ?></li>
                        <li class="epl-slider__list-item"><?php echo translate('landing_advisors_benefits_slide_6_li_2'); ?></li>
                        <li class="epl-slider__list-item"><?php echo translate('landing_advisors_benefits_slide_6_li_3'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="epl-structure-adv">
        <h2 class="epl-structure-adv__headline"><?php echo translate('landing_advisors_ep_plus_roles_title'); ?></h2>
        <div class="ambassador__section-container ambassador__members-container ">
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
                    <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/industry_ambassador'; ?>"><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                </div>
            </div>
            <div class="ambassador__members-column">
                <div class="ambassador__members-item">
                    <div class="ambassador__members-icon">
                        <?php echo $icons['teach'] ?>
                    </div>
                    <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_content_ambassadors'); ?></h3>
                    <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/content_ambassador'; ?>"><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                </div>
            </div>

            <div class="ambassador__members-column">
                <div class="ambassador__members-item">
                    <div class="ambassador__members-icon">
                        <?php echo $icons['government'] ?>
                    </div>
                    <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_government_and_association'); ?></h3>
                    <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/government_and_association'; ?>"><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                </div>
            </div>
            <div class="ambassador__members-column">
                <div class="ambassador__members-item">
                    <div class="ambassador__members-icon">
                        <?php echo $icons['country'] ?>
                    </div>
                    <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_country_ambassadors'); ?></h3>
                    <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/country_ambassador'; ?>"><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                </div>
            </div>

        </div>
    </section>

    <section class="epl-slider">
        <div class="epl-slider__container js-advisors-benefits-slider" id="js-advisors-difference-slider">
            <div class="epl-slider__slide" <?php echo addQaUniqueIdentifier('landing__advisors-item'); ?>>
                <img class="epl-slider__image js-lazy" data-src="<?php echo asset("public/build/images/landings/advisors/462875188.jpg"); ?>" src="<?php echo getLazyImage(1912, 670); ?>" alt="Advisor Benefits">
                <div class="epl-slider__info">
                    <h2 class="epl-slider__headline"><?php echo translate('landing_advisors_make_a_difference_slides_title'); ?></h2>
                    <div class="epl-slider__text"><?php echo translate('landing_advisors_make_a_difference_slide_1_text'); ?></div>
                </div>
            </div>
            <div class="epl-slider__slide" <?php echo addQaUniqueIdentifier('landing__advisors-item'); ?>>
                <img class="epl-slider__image js-lazy" data-src="<?php echo asset("public/build/images/landings/advisors/1109499098.jpg"); ?>" src="<?php echo getLazyImage(1912, 670); ?>" alt="Advisor Benefits">
                <div class="epl-slider__info">
                    <h2 class="epl-slider__headline"><?php echo translate('landing_advisors_make_a_difference_slides_title'); ?></h2>
                    <div class="epl-slider__text"><?php echo translate('landing_advisors_make_a_difference_slide_2_text'); ?></div>
                </div>
            </div>
            <div class="epl-slider__slide" <?php echo addQaUniqueIdentifier('landing__advisors-item'); ?>>
                <img class="epl-slider__image js-lazy" data-src="<?php echo asset("public/build/images/landings/advisors/1201483894.jpg"); ?>" src="<?php echo getLazyImage(1912, 670); ?>" alt="Advisor Benefits">
                <div class="epl-slider__info">
                    <h2 class="epl-slider__headline"><?php echo translate('landing_advisors_make_a_difference_slides_title'); ?></h2>
                    <div class="epl-slider__text"><?php echo translate('landing_advisors_make_a_difference_slide_3_text'); ?></div>
                </div>
            </div>
            <div class="epl-slider__slide" <?php echo addQaUniqueIdentifier('landing__advisors-item'); ?>>
                <img class="epl-slider__image js-lazy" data-src="<?php echo asset("public/build/images/landings/advisors/1122992726.jpg"); ?>" src="<?php echo getLazyImage(1912, 670); ?>" alt="Advisor Benefits">
                <div class="epl-slider__info">
                    <h2 class="epl-slider__headline"><?php echo translate('landing_advisors_make_a_difference_slides_title'); ?></h2>
                    <div class="epl-slider__text"><?php echo translate('landing_advisors_make_a_difference_slide_4_text'); ?></div>
                </div>
            </div>
            <div class="epl-slider__slide" <?php echo addQaUniqueIdentifier('landing__advisors-item'); ?>>

                <img class="epl-slider__image js-lazy" data-src="<?php echo asset("public/build/images/landings/advisors/1201483894.jpg"); ?>" src="<?php echo getLazyImage(1912, 670); ?>" alt="Advisor Benefits">
                <div class="epl-slider__info">
                    <h2 class="epl-slider__headline"><?php echo translate('landing_advisors_make_a_difference_slides_title'); ?></h2>
                    <div class="epl-slider__text"><?php echo translate('landing_advisors_make_a_difference_slide_5_text'); ?></div>
                </div>
            </div>
        </div>
    </section>

    <section class="epl-who">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <p class="ep-large-text tac">
                        <?php echo translate('landing_advisors_who_we_are_text'); ?>
                    </p>
                    <div class="epl-about__link">
                        <a href="https://app.smartsheet.com/b/form/59c42f8a630d4503998d17fd0442706c" target="_blank"><?php echo translate('landing_advisors_become_an_advisor_btn'); ?></a>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="epl-who__headline"><?php echo translate('landing_advisors_who_we_are_title'); ?></h2>
        <div class="epl-who__logo">
            <img src="<?php echo asset("public/build/images/logo/ep-logo.png"); ?>" alt="Export Portal Logo">
        </div>
        <div class="epl-who__ep"><?php echo translate('landing_advisors_who_we_are_subtitle'); ?></div>
        <ul class="epl-who__list">
            <li class="epl-who__list-item">
                <img class="epl-who__icon epl-who__guard js-lazy" data-src="<?php echo asset("public/build/images/landings/icons/about-icons-11.png"); ?>" src="<?php echo getLazyImage(65, 110); ?>" alt="Guard">
                <div class="epl-who__text"><?php echo translate('landing_advisors_who_we_are_list_1'); ?></div>
            </li>
            <li class="epl-who__list-item">
                <img class="epl-who__icon epl-who__money js-lazy" data-src="<?php echo asset("public/build/images/landings/icons/about-icons-12.png"); ?>" src="<?php echo getLazyImage(101, 110); ?>" alt="Money">
                <div class="epl-who__text"><?php echo translate('landing_advisors_who_we_are_list_2'); ?></div>
            </li>
            <li class="epl-who__list-item">
                <img class="epl-who__icon epl-who__friendship js-lazy" data-src="<?php echo asset("public/build/images/landings/icons/about-icons-13.png"); ?>" src="<?php echo getLazyImage(109, 110); ?>" alt="Friendship">
                <div class="epl-who__text"><?php echo translate('landing_advisors_who_we_are_list_3'); ?></div>
            </li>
        </ul>
    </section>

</div>
