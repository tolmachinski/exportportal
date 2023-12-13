<?php encoreLinks(); ?>

<section class="ambassador__info ambassador__section">
    <div class="ambassador__section--container">
        <h2 class="ambassador__info-title ambassador__section-title"><?php echo translate('landing_logistics_ambassador_what_is_title'); ?></h2>
        <p class="ambassador__info-text ambassador__section-text"><?php echo translate('landing_logistics_ambassador_what_is_text'); ?></p>
    </div>
</section>

<section class="ambassador__info ambassador__section">
    <div class="ambassador__section--container">
        <h2 class="ambassador__info-title ambassador__section-title"><?php echo translate('landing_logistics_ambassador_what_is_ep_plus_title'); ?></h2>
        <p class="ambassador__info-text ambassador__section-text"><?php echo translate('landing_logistics_ambassador_what_is_ep_plus_text'); ?></p>
    </div>
</section>

<section class="ambassador__members ambassador__section">
    <h2 class="ambassador__section-title"><?php echo translate('landing_government_and_association_ep_plus_members_title'); ?></h2>
    <div class="ambassador__section-container ambassador__members-container">
        <div class="ambassador__members-column">
            <div class="ambassador__members-item">
                <div class="ambassador__members-icon ambassador__advisors-icon">
                    <?php echo $icons['advisors'] ?>
                </div>
                <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_advisors'); ?></h3>
                <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/advisors'; ?>" <?php echo addQaUniqueIdentifier("landing__members-btn") ?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
            </div>
        </div>
        <div class="ambassador__members-column">
            <div class="ambassador__members-item">
                <div class="ambassador__members-icon">
                    <?php echo $icons['factory'] ?>
                </div>
                <h3 class="ambassador__members-title"><?php echo translate('landing_advisors_ep_plus_roles_industry_ambassadors'); ?></h3>
                <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/industry_ambassador'; ?>" <?php echo addQaUniqueIdentifier("landing__members-btn") ?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
            </div>
        </div>
        <div class="ambassador__members-column">
            <div class="ambassador__members-item">
                <div class="ambassador__members-icon">
                    <?php echo $icons['teach'] ?>
                </div>
                <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_content_ambassadors'); ?></h3>
                <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/content_ambassador'; ?>" <?php echo addQaUniqueIdentifier("landing__members-btn") ?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
            </div>
        </div>
        <div class="ambassador__members-column">
            <div class="ambassador__members-item">
                <div class="ambassador__members-icon">
                    <?php echo $icons['government'] ?>
                </div>
                <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_government_and_association'); ?></h3>
                <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/government_and_association'; ?>" <?php echo addQaUniqueIdentifier("landing__members-btn") ?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
            </div>
        </div>
        <div class="ambassador__members-column">
            <div class="ambassador__members-item">
                <div class="ambassador__members-icon">
                    <?php echo $icons['country'] ?>
                </div>
                <h3 class="ambassador__members-title"><?php echo translate('landing_industry_ambassador_ep_plus_members_country_ambassadors'); ?></h3>
                <a class="ambassador__members-btn btn btn-primary" href="<?php echo __SITE_URL . 'landing/country_ambassador'; ?>" <?php echo addQaUniqueIdentifier("landing__members-btn") ?>><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
            </div>
        </div>

    </div>
</section>

<section class="ambassador__responsibilities ambassador__section">
    <div class="ambassador__responsibilities-container ambassador__section-container">
        <h2 class="ambassador__responsibilities-title ambassador__section-title"><?php echo translate('landing_logistics_ambassador_responsibilities_title'); ?></h2>
        <p class="ambassador__responsibilities-text ambassador__section-text"><?php echo translate('landing_logistics_ambassador_responsibilities_text'); ?></p>

        <div class="ambassador__responsibilities-item">
            <div class="ambassador__responsibilities-image">
                <picture class="ambassador__responsibilities-picture">
                    <source media="(max-width: 665px)" srcset="<?php echo getLazyImage(320, 150); ?>" data-srcset="<?php echo asset("public/build/images/landings/logistics_ambassador/responsibilities-1-mobile.jpg"); ?>">
                    <source media="(max-width: 991px)" srcset="<?php echo getLazyImage(250, 150); ?>" data-srcset="<?php echo asset("public/build/images/landings/logistics_ambassador/responsibilities-1-tablet.jpg"); ?>">
                    <img class="js-lazy" src="<?php echo getLazyImage(250, 180); ?>" data-src="<?php echo asset("public/build/images/landings/logistics_ambassador/responsibilities-1.jpg"); ?>" alt="What is EPTP">
                </picture>
            </div>
            <ul class="ambassador__responsibilities-list">
                <li class="ambassador__responsibilities-list-item ambassador__section-text">
                    <?php echo translate('landing_logistics_ambassador_responsibilities_block_1_li_1'); ?>
                </li>
                <li class="ambassador__responsibilities-list-item ambassador__section-text">
                    <?php echo translate('landing_logistics_ambassador_responsibilities_block_1_li_2'); ?>
                </li>
                <li class="ambassador__responsibilities-list-item ambassador__section-text">
                    <?php echo translate('landing_logistics_ambassador_responsibilities_block_1_li_3'); ?>
                </li>
                <li class="ambassador__responsibilities-list-item ambassador__section-text">
                    <?php echo translate('landing_logistics_ambassador_responsibilities_block_1_li_4'); ?>
                </li>
            </ul>
        </div>

        <div class="ambassador__responsibilities-item">
            <div class="ambassador__responsibilities-image">
                <picture class="ambassador__responsibilities-picture">
                    <source media="(max-width: 665px)" srcset="<?php echo getLazyImage(320, 150); ?>" data-srcset="<?php echo asset("public/build/images/landings/logistics_ambassador/responsibilities-2-mobile.jpg"); ?>">
                    <source media="(max-width: 991px)" srcset="<?php echo getLazyImage(250, 150); ?>" data-srcset="<?php echo asset("public/build/images/landings/logistics_ambassador/responsibilities-2-tablet.jpg"); ?>">
                    <img class="js-lazy" src="<?php echo getLazyImage(250, 180); ?>" data-src="<?php echo asset("public/build/images/landings/logistics_ambassador/responsibilities-2.jpg"); ?>" alt="What is EPTP">
                </picture>
            </div>
            <ul class="ambassador__responsibilities-list">
                <li class="ambassador__responsibilities-list-item ambassador__section-text">
                    <?php echo translate('landing_logistics_ambassador_responsibilities_block_2_li_1'); ?>
                </li>
                <li class="ambassador__responsibilities-list-item ambassador__section-text">
                    <?php echo translate('landing_logistics_ambassador_responsibilities_block_2_li_2'); ?>

                    <a class="ambassador__responsibilities-link" href="<?php echo __SITE_URL . 'landing/epl'; ?>"><?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?></a>
                </li>
                <li class="ambassador__responsibilities-list-item ambassador__section-text">
                    <?php echo translate('landing_logistics_ambassador_responsibilities_block_2_li_3'); ?>
                </li>
            </ul>
        </div>

        <div class="ambassador__responsibilities-item">
            <div class="ambassador__responsibilities-image">
                <picture class="ambassador__responsibilities-picture">
                    <source media="(max-width: 665px)" srcset="<?php echo getLazyImage(320, 150); ?>" data-srcset="<?php echo asset("public/build/images/landings/logistics_ambassador/responsibilities-3-mobile.jpg"); ?>">
                    <source media="(max-width: 991px)" srcset="<?php echo getLazyImage(250, 150); ?>" data-srcset="<?php echo asset("public/build/images/landings/logistics_ambassador/responsibilities-3-tablet.jpg"); ?>">
                    <img class="js-lazy" src="<?php echo getLazyImage(250, 180); ?>" data-src="<?php echo asset("public/build/images/landings/logistics_ambassador/responsibilities-3.jpg"); ?>" alt="What is EPTP">
                </picture>
            </div>
            <ul class="ambassador__responsibilities-list">
                <li class="ambassador__responsibilities-list-item ambassador__section-text">
                    <?php echo translate('landing_logistics_ambassador_responsibilities_block_3_li_1'); ?>
                </li>
                <li class="ambassador__responsibilities-list-item ambassador__section-text">
                    <?php echo translate('landing_logistics_ambassador_responsibilities_block_3_li_2'); ?>
                </li>
                <li class="ambassador__responsibilities-list-item ambassador__section-text">
                    <?php echo translate('landing_logistics_ambassador_responsibilities_block_3_li_3'); ?>
                </li>
            </ul>
        </div>

    </div>
</section>

<section class="ambassador__benefits ambassador__section">
    <h2 class="ambassador__benefits-title ambassador__section-title">
        <?php echo translate('landing_logistics_ambassador_benefits_title'); ?>
    </h2>
    <div class="ambassador__benefits-container">
        <div class="ambassador__benefits-column">
            <div class="ambassador__benefits-item">
                <div class="ambassador__benefits-icon ambassador__world-icon">
                    <?php echo $icons['world'] ?>
                </div>
                <h3 class="ambassador__benefits-text ambassador__section-text">
                    <?php echo translate('landing_logistics_ambassador_benefits_1'); ?>
                </h3>
            </div>
            <div class="ambassador__benefits-item">
                <div class="ambassador__benefits-icon">
                    <?php echo $icons['connecting'] ?>
                </div>
                <h3 class="ambassador__benefits-text ambassador__section-text">
                    <?php echo translate('landing_logistics_ambassador_benefits_2'); ?>
                </h3>
            </div>
            <div class="ambassador__benefits-item">
                <div class="ambassador__benefits-icon ambassador__knowledge-icon">
                    <?php echo $icons['knowledge'] ?>
                </div>
                <h3 class="ambassador__benefits-text ambassador__section-text">
                    <?php echo translate('landing_logistics_ambassador_benefits_3'); ?>
                </h3>
            </div>
            <div class="ambassador__benefits-item">
                <div class="ambassador__benefits-icon ambassador__portfolio-icon">
                    <?php echo $icons['portfolio'] ?>
                </div>
                <h3 class="ambassador__benefits-text ambassador__section-text">
                    <?php echo translate('landing_logistics_ambassador_benefits_4'); ?>
                </h3>
            </div>
            <div class="ambassador__benefits-item">
                <div class="ambassador__benefits-icon ambassador__financial-icon">
                    <?php echo $icons['financial'] ?>
                </div>
                <h3 class="ambassador__benefits-text ambassador__section-text">
                    <?php echo translate('landing_logistics_ambassador_benefits_5'); ?>
                </h3>
            </div>
            <div class="ambassador__benefits-item">
                <div class="ambassador__benefits-icon ambassador__opportunities-icon">
                    <?php echo $icons['opportunities'] ?>
                </div>
                <h3 class="ambassador__benefits-text ambassador__section-text">
                    <?php echo translate('landing_logistics_ambassador_benefits_6'); ?>
                </h3>
            </div>
        </div>
        <a class="ambassador__join-btn ambassador__benefits-btn btn btn-outline-dark" href="https://app.smartsheet.com/b/form/20d4887af6be4c65a18b6cb8ddc8b814" <?php echo addQaUniqueIdentifier("logistics-ambassador__benefits-btn") ?>>
            <?php echo translate('landing_government_and_association_header_join_ep_btn'); ?>
        </a>
    </div>
</section>


<section class="ambassador__about ambassador__section">
    <div class="ambassador__about-container ambassador__section-container">
        <div class="ambassador__about-gif">
            <img class="js-lazy" src="<?php echo getLazyImage(160, 120); ?>" data-src="<?php echo asset("public/build/images/landings/logistics_ambassador/gif.gif"); ?>" alt="What is EPTP" <?php echo addQaUniqueIdentifier("logistics-ambassador__gif-logo")?>>
        </div>
        <h2 class="ambassador__about-title ambassador__section-title">
            <?php echo translate('landing_logistics_ambassador_about_ep_title'); ?>
        </h2>
        <p class="ambassador__about-text ambassador__section-text">
            <?php echo translate('landing_logistics_ambassador_about_ep_text_1'); ?>
        </p>
        <p class="ambassador__about-text ambassador__section-text">
            <?php echo translate('landing_logistics_ambassador_about_ep_text_2'); ?>
        </p>
        <a class="ambassador__about-btn btn btn-primary" href="<?php echo __SITE_URL . 'learn_more'; ?>" <?php echo addQaUniqueIdentifier("logistics-ambassador__about-btn") ?>>
            <?php echo translate('landing_industry_ambassador_ep_plus_members_learn_more_btn'); ?>
        </a>
    </div>
</section>
