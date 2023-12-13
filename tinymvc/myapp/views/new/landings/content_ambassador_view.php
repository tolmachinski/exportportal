<header class="top-header top-header--car">
    <h1 class="top-header__headline"><?php echo translate('landing_content_ambassador_header_title'); ?></h1>
    <div class="top-header__link-row">
        <a class="top-header__link" href="https://app.smartsheet.com/b/form/aeec3bb1ff194f38a1de6fd181143208"><?php echo translate('landing_content_ambassador_header_join_ep_btn'); ?></a>
    </div>
</header>

<main>
    <section class="amb-info">
        <div class="amb-info__row">
            <h2 class="amb-headline-title amb-info__headline"><?php echo translate('landing_content_ambassador_question_about_content_ambassador'); ?></h2>
            <p class="amb-info__text"><?php echo translate('landing_content_ambassador_answer_about_content_ambassador'); ?></p>
        </div>
        <div class="amb-info__row">
            <h2 class="amb-headline-title amb-info__headline"><?php echo translate('landing_content_ambassador_question_about_ep_plus'); ?></h2>
            <p class="amb-info__text"><?php echo translate('landing_content_ambassador_answer_about_ep_plus'); ?></p>
        </div>
    </section>

    <section class="amb-members">
        <h2 class="amb-headline-title amb-members__headline"><?php echo translate('landing_content_ambassador_ep_plus_members_title'); ?></h2>
        <div class="ambassador__section-container ambassador__members-container ">
            <div class="ambassador__members-column">
                <div class="ambassador__members-item">
                    <div class="ambassador__members-icon ambassador__logistics-icon">
                        <?php echo $icons['logistics'] ?>
                    </div>
                    <h3 class="ambassador__members-title">
                        <?php echo translate('landing_logistics_ambassador_ep_plus_member_logistic'); ?>
                    </h3>
                    <a class="ambassador__members-btn btn btn-primary"  href="<?php echo __SITE_URL . 'landing/logistics_ambassador'; ?>" <?php echo addQaUniqueIdentifier("landing__members-btn")?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                </div>
            </div>
            <div class="ambassador__members-column">
                <div class="ambassador__members-item">
                    <div class="ambassador__members-icon">
                        <?php echo $icons['factory'] ?>
                    </div>
                    <h3 class="ambassador__members-title"><?php echo translate('landing_advisors_ep_plus_roles_industry_ambassadors'); ?></h3>
                    <a class="ambassador__members-btn btn btn-primary"  href="<?php echo __SITE_URL . 'landing/industry_ambassador'; ?>" <?php echo addQaUniqueIdentifier("landing__members-btn")?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                </div>
            </div>
            <div class="ambassador__members-column">
                <div class="ambassador__members-item">
                    <div class="ambassador__members-icon">
                        <?php echo $icons['government'] ?>
                    </div>
                    <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_government_and_association'); ?></h3>
                    <a class="ambassador__members-btn btn btn-primary"  href="<?php echo __SITE_URL . 'landing/government_and_association'; ?>" <?php echo addQaUniqueIdentifier("landing__members-btn")?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                </div>
            </div>
            <div class="ambassador__members-column">
            <div class="ambassador__members-item">
                <div class="ambassador__members-icon ambassador__advisors-icon">
                    <?php echo $icons['advisors'] ?>
                </div>
                <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_advisors'); ?></h3>
                <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/advisors'; ?>" <?php echo addQaUniqueIdentifier("landing__members-btn")?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
            </div>
        </div>
        <div class="ambassador__members-column">
            <div class="ambassador__members-item">
                <div class="ambassador__members-icon">
                    <?php echo $icons['country'] ?>
                </div>
                <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_country_ambassadors'); ?></h3>
                <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/country_ambassador'; ?>" <?php echo addQaUniqueIdentifier("landing__members-btn")?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
            </div>
        </div>

        </div>
    </section>

    <section class="amb-role">
        <h2 class="amb-headline-title amb-role__headline"><?php echo translate('landing_content_ambassador_role_title'); ?></h2>
        <div class="amb-role__row">
            <div class="amb-role__image">
                <img src="<?php echo __IMG_URL . 'public/img/landings/ambassadors/car/car-phone.jpg'; ?>" alt="phone-type">
            </div>
            <ul class="amb-role__list">
                <li class="amb-role__list-item">
                    <?php echo translate('landing_content_ambassador_role_block_1_li_1'); ?><br>
                    <?php echo translate('landing_content_ambassador_role_block_1_li_2'); ?><br>
                    <?php echo translate('landing_content_ambassador_role_block_1_li_3'); ?><br>
                    <?php echo translate('landing_content_ambassador_role_block_1_li_4'); ?><br>
                    <?php echo translate('landing_content_ambassador_role_block_1_li_5'); ?>
                </li>
            </ul>
        </div>
        <div class="amb-role__row">
            <div class="amb-role__image">
                <img src="<?php echo __IMG_URL . 'public/img/landings/ambassadors/car/car-women-writing.jpg'; ?>" alt="woman-writing">
            </div>
            <ul class="amb-role__list">
                <li class="amb-role__list-item"><?php echo translate('landing_content_ambassador_role_block_2_li_1'); ?></li>
            </ul>
        </div>
    </section>

    <section class="cpa-benefits">
        <h2 class="amb-headline-title cpa-benefits__headline"><?php echo translate('landing_content_ambassador_benefits_title'); ?></h2>
        <div class="cpa-benefits__container">
            <div class="cpa-benefits__column">
                <div class="cpa-benefits__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/landings/icons/cpa-share.png'; ?>" alt="share">
                </div>
                <h3 class="cpa-benefits__title"><?php echo translate('landing_content_ambassador_benefits_1'); ?></h3>
            </div>
            <div class="cpa-benefits__column">
                <div class="cpa-benefits__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/landings/icons/cpa-grow.png'; ?>" alt="grow">
                </div>
                <h3 class="cpa-benefits__title"><?php echo translate('landing_content_ambassador_benefits_2'); ?></h3>
            </div>
            <div class="cpa-benefits__column">
                <div class="cpa-benefits__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/landings/icons/cpa-write.png'; ?>" alt="write-to">
                </div>
                <h3 class="cpa-benefits__title"><?php echo translate('landing_content_ambassador_benefits_3'); ?></h3>
            </div>
            <div class="cpa-benefits__column">
                <div class="cpa-benefits__icon">
                    <img src="<?php echo __IMG_URL . 'public/img/landings/icons/cpa-promote.png'; ?>" alt="promote">
                </div>
                <h3 class="cpa-benefits__title"><?php echo translate('landing_content_ambassador_benefits_4'); ?></h3>
            </div>
        </div>
    </section>

    <div class="top-header__link-row">
        <a class="top-header__link--other top-header__link" href="https://app.smartsheet.com/b/form/aeec3bb1ff194f38a1de6fd181143208"><?php echo translate('landing_content_ambassador_header_join_ep_btn'); ?></a>
    </div>

    <section class="amb-about">
        <a class="amb-about__icon" href="<?php echo __SITE_URL; ?>" target="_blank">
            <img src="<?php echo __IMG_URL . 'public/img/ep-logo/ep-logo.png'; ?>" alt="Export Portal Logo">
        </a>
        <h2 class="amb-headline-title amb-about__headline"><?php echo translate('landing_content_ambassador_about_ep_title'); ?></h2>
        <p class="amb-about__text"><?php echo translate('landing_content_ambassador_about_ep_text'); ?></p>
        <a class="amb-about__link" href="<?php echo __SITE_URL . 'learn_more'; ?>"><?php echo translate('landing_content_ambassador_about_ep_learn_more_btn'); ?></a>
    </section>
</main>
