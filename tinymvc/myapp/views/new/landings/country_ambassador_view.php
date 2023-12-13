<header class="top-header top-header--amb">
    <h1 class="top-header__headline"><?php echo translate('landing_country_ambassador_header_title'); ?></h1>
    <div class="top-header__link-row">
        <a class="top-header__link" href="https://app.smartsheet.com/b/form/6eff9226197d4a00a6fc1e87facf81e7"><?php echo translate('landing_country_ambassador_header_join_ep_btn'); ?></a>
    </div>
</header>

<main>
    <section class="amb-info">
        <div class="amb-info__row">
            <h2 class="amb-headline-title amb-info__headline"><?php echo translate('landing_country_ambassador_question_about_country_ambassador'); ?></h2>
            <p class="amb-info__text"><?php echo translate('landing_country_ambassador_answer_about_country_ambassador'); ?></p>
        </div>
        <div class="amb-info__row">
            <h2 class="amb-headline-title amb-info__headline"><?php echo translate('landing_country_ambassador_question_about_ep_plus'); ?></h2>
            <p class="amb-info__text"><?php echo translate('landing_country_ambassador_answer_about_ep_plus'); ?></p>
        </div>
    </section>

    <section class="amb-members">
        <h2 class="amb-headline-title amb-members__headline"><?php echo translate('landing_country_ambassador_ep_plus_members_title'); ?></h2>
        <div class="ambassador__section-container ambassador__members-container ">
            <div class="ambassador__members-column">
                <div class="ambassador__members-item">
                    <div class="ambassador__members-icon ambassador__logistics-icon">
                        <?php echo $icons['logistics'] ?>
                    </div>
                    <h3 class="ambassador__members-title">
                        <?php echo translate('landing_logistics_ambassador_ep_plus_member_logistic'); ?>
                    </h3>
                    <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/logistics_ambassador' ?>" <?php echo addQaUniqueIdentifier("landing__members-btn")?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                </div>
            </div>
            <div class="ambassador__members-column">
                <div class="ambassador__members-item">
                    <div class="ambassador__members-icon">
                        <?php echo $icons['factory'] ?>
                    </div>
                    <h3 class="ambassador__members-title"><?php echo translate('landing_advisors_ep_plus_roles_industry_ambassadors'); ?></h3>
                    <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/industry_ambassador'; ?>" <?php echo addQaUniqueIdentifier("landing__members-btn")?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
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
                        <?php echo $icons['government'] ?>
                    </div>
                    <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_government_and_association'); ?></h3>
                    <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/government_and_association'; ?>" <?php echo addQaUniqueIdentifier("logistics-ambassador__members-btn") ?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                </div>
            </div>

        </div>
    </section>

    <section class="amb-role">
        <h2 class="amb-headline-title amb-role__headline"><?php echo translate('landing_country_ambassador_country_ambassador_role_title'); ?></h2>
        <div class="amb-role__row">
            <div class="amb-role__image">
                <img src="<?php echo __IMG_URL . 'public/img/landings/ambassadors/amb/amb-smiling-people.jpg'; ?>" alt="Content Creation">
            </div>
            <ul class="amb-role__list">
                <li class="amb-role__list-item"><?php echo translate('landing_country_ambassador_country_ambassador_role_block_1_li_1'); ?></li>
                <li class="amb-role__list-item"><?php echo translate('landing_country_ambassador_country_ambassador_role_block_1_li_2'); ?></li>
            </ul>
        </div>
        <div class="amb-role__row">
            <div class="amb-role__image">
                <img src="<?php echo __IMG_URL . 'public/img/landings/ambassadors/amb/amb-woman-in-glasses.jpg'; ?>" alt="Generating brand awareness">
            </div>
            <ul class="amb-role__list">
                <li class="amb-role__list-item"><?php echo translate('landing_country_ambassador_country_ambassador_role_block_2_li_1'); ?></li>
                <li class="amb-role__list-item"><?php echo translate('landing_country_ambassador_country_ambassador_role_block_2_li_2'); ?></li>
            </ul>
        </div>
        <div class="amb-role__row">
            <div class="amb-role__image">
                <img src="<?php echo __IMG_URL . 'public/img/landings/ambassadors/amb/amb-meeting.jpg'; ?>" alt="Providing insight and feedback">
            </div>
            <ul class="amb-role__list">
                <li class="amb-role__list-item"><?php echo translate('landing_country_ambassador_country_ambassador_role_block_3_li_1'); ?></li>
                <li class="amb-role__list-item"><?php echo translate('landing_country_ambassador_country_ambassador_role_block_3_li_2'); ?></li>
            </ul>
        </div>
        <div class="amb-role__row">
            <div class="amb-role__image">
                <img src="<?php echo __IMG_URL . 'public/img/landings/ambassadors/amb/amb-smiling-man-with-phone.jpg'; ?>" alt="Promoting">
            </div>
            <ul class="amb-role__list">
                <li class="amb-role__list-item"><?php echo translate('landing_country_ambassador_country_ambassador_role_block_4_li_1'); ?></li>
                <li class="amb-role__list-item"><?php echo translate('landing_country_ambassador_country_ambassador_role_block_4_li_2'); ?></li>
            </ul>
        </div>
    </section>

    <section class="amb-benefits">
        <h2 class="amb-headline-title amb-benefits__headline"><?php echo translate('landing_country_ambassador_benefits_title'); ?></h2>
        <div class="amb-benefits__container">
            <div class="amb-benefits__column">
                <div class="amb-benefits__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/landings/icons/amb-commision.svg'; ?>" alt="Commision">
                </div>
                <h3 class="amb-benefits__title"><?php echo translate('landing_country_ambassador_benefits_1'); ?></h3>
            </div>
            <div class="amb-benefits__column">
                <div class="amb-benefits__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/landings/icons/amb-represent.svg'; ?>" alt="Representation">
                </div>
                <h3 class="amb-benefits__title"><?php echo translate('landing_country_ambassador_benefits_2'); ?></h3>
            </div>
            <div class="amb-benefits__column">
                <div class="amb-benefits__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/landings/icons/amb-orange_users.png'; ?>" alt="Users">
                </div>
                <h3 class="amb-benefits__title"><?php echo translate('landing_country_ambassador_benefits_3'); ?></h3>
            </div>
            <div class="amb-benefits__column">
                <div class="amb-benefits__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/landings/icons/amb-smes.png'; ?>" alt="SMEs">
                </div>
                <h3 class="amb-benefits__title"><?php echo translate('landing_country_ambassador_benefits_4'); ?></h3>
            </div>
            <div class="amb-benefits__column">
                <div class="amb-benefits__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/landings/icons/amb-promote.png'; ?>" alt="Promote SMEs">
                </div>
                <h3 class="amb-benefits__title"><?php echo translate('landing_country_ambassador_benefits_5'); ?></h3>
            </div>
            <div class="amb-benefits__column">
                <div class="amb-benefits__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/landings/icons/amb-explain.png'; ?>" alt="Panel Discussion">
                </div>
                <h3 class="amb-benefits__title"><?php echo translate('landing_country_ambassador_benefits_6'); ?></h3>
            </div>
            <div class="amb-benefits__column">
                <div class="amb-benefits__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/landings/icons/amb-join.png'; ?>" alt="Networking Opportunities">
                </div>
                <h3 class="amb-benefits__title"><?php echo translate('landing_country_ambassador_benefits_7'); ?></h3>
            </div>
            <div class="amb-benefits__column">
                <div class="amb-benefits__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/landings/icons/amb-gain.png'; ?>" alt="Export Portal Intelligence">
                </div>
                <h3 class="amb-benefits__title"><?php echo translate('landing_country_ambassador_benefits_8'); ?></h3>
                <div class="amb-benefits__title"><?php echo translate('landing_country_ambassador_benefits_8_1'); ?></div>
                <div class="amb-benefits__title"><?php echo translate('landing_country_ambassador_benefits_8_2'); ?></div>
            </div>
            <div class="amb-benefits__column">
                <div class="amb-benefits__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/landings/icons/amb-technology.png'; ?>" alt="technology">
                </div>
                <h3 class="amb-benefits__title"><?php echo translate('landing_country_ambassador_benefits_9'); ?></h3>
            </div>
            <div class="amb-benefits__column">
                <div class="amb-benefits__block">
                    <div class="amb-benefits__icon">
                        <img src="<?php echo __IMG_URL . 'public/img/landings/icons/amb-university.png'; ?>" alt="Technology and Developments">
                    </div>
                    <h3 class="amb-benefits__title"><?php echo translate('landing_country_ambassador_benefits_10'); ?></h3>
                    <div class="amb-benefits__title"><?php echo translate('landing_country_ambassador_benefits_10_1'); ?><br><br><?php echo translate('landing_country_ambassador_benefits_10_2'); ?><br><br><?php echo translate('landing_country_ambassador_benefits_10_3'); ?><br><br><?php echo translate('landing_country_ambassador_benefits_10_4'); ?></div>
                </div>
            </div>
        </div>
    </section>

    <div class="top-header__link-row">
        <a class="top-header__link--other top-header__link" href="https://app.smartsheet.com/b/form/6eff9226197d4a00a6fc1e87facf81e7"><?php echo translate('landing_country_ambassador_header_join_ep_btn'); ?></a>
    </div>

    <h2 class="amb-headline-title amb-about__headline tac"><?php echo translate('landing_country_ambassador_video_title'); ?></h2>

    <div class="amb-video-wr">
        <iframe class="amb-video" src="<?php echo translate('trade_professionals_invited_block_video_youtube_link') . '?controls=0&showinfo=0'; ?>" allowfullscreen></iframe>
    </div>

    <section class="amb-about">
        <a class="amb-about__icon" href="<?php echo __SITE_URL; ?>" target="_blank">
            <img src="<?php echo __IMG_URL . 'public/img/ep-logo/ep-logo.png'; ?>" alt="Export Portal Logo">
        </a>
        <h2 class="amb-headline-title amb-about__headline"><?php echo translate('landing_country_ambassador_about_ep_title'); ?></h2>
        <p class="amb-about__text"><?php echo translate('landing_country_ambassador_about_ep_text'); ?></p>
        <a class="amb-about__link" href="<?php echo __SITE_URL . 'learn_more'; ?>"><?php echo translate('landing_country_ambassador_about_ep_learn_more_btn'); ?></a>
    </section>
</main>
