<div class="learnmore-list-wr">
    <div class="learnmore-list">
        <div class="learnmore-list__item">
            <div class="learnmore-list__item-inner">
                <div class="learnmore-info">
                    <h2 class="learnmore-ttl learnmore-ttl--lh-50"><?php echo translate('learn_more_block_about_us_header'); ?></h2>
                    <div class="learnmore-info__txt"><?php echo translate('learn_more_block_about_us_header_text'); ?></div>
                    <ul class="learnmore-info__list">
                        <li class="learnmore-info__list-item">
                            <i class="ep-icon ep-icon_ok"></i> <?php echo translate('learn_more_block_about_us_label_shop'); ?>
                        </li>
                        <li class="learnmore-info__list-item">
                            <i class="ep-icon ep-icon_ok"></i> <?php echo translate('learn_more_block_about_us_label_service'); ?>
                        </li>
                        <li class="learnmore-info__list-item">
                            <i class="ep-icon ep-icon_ok"></i> <?php echo translate('learn_more_block_about_us_label_expertise'); ?>
                        </li>
                    </ul>
                    <a class="btn btn-primary" href="<?php echo __SITE_URL; ?>about" <?php echo addQaUniqueIdentifier("page__learn_more__learn-more-about-us_btn"); ?>><?php echo translate('learn_more_about_us_btn'); ?></a>
                </div>
            </div>
        </div>
        <div class="learnmore-list__item learnmore-list__item-img">
            <picture class="learnmore-list__img">
                <source media="(max-width: 575px)" srcset="<?php echo asset('public/build/images/learn-more/list-background-1-mobile.jpg'); ?> 1x, <?php echo asset('public/build/images/learn-more/list-background-1-mobile@2x.jpg'); ?> 2x">
                <img class="image" src="<?php echo asset('public/build/images/learn-more/list-background-1.jpg'); ?>" alt="<?php echo translate('learn_more_about_us_img'); ?>" width="1920" height="546" alt="">
            </picture>
        </div>
    </div>

    <?php encoreLinks(); ?>

    <div class="learnmore-list">
        <div class="learnmore-list__item learnmore-list__item-img">
            <picture class="learnmore-list__img">
                <source media="(max-width: 575px)" data-srcset="<?php echo asset('public/build/images/learn-more/list-background-2-mobile.jpg'); ?> 1x, <?php echo asset('public/build/images/learn-more/list-background-2-mobile@2x.jpg'); ?> 2x" srcset="<?php echo getLazyImage(575, 350);?>">
                <img class="image js-lazy" src="<?php echo getLazyImage(956, 660);?>" data-src="<?php echo asset('public/build/images/learn-more/list-background-2.jpg'); ?>" alt="<?php echo translate('learn_more_about_security_img'); ?>" width="1920" height="546" alt="">
            </picture>
        </div>
        <div class="learnmore-list__item learnmore-list__item--last">
            <div class="learnmore-list__item-inner">
                <div class="learnmore-info">
                    <h2 class="learnmore-ttl learnmore-ttl--lh-50">
                        <?php echo translate('learn_more_block_security_header'); ?>
                    </h2>
                    <div class="learnmore-info__txt"><?php echo translate('learn_more_block_security_header_text'); ?></div>
                    <ul class="learnmore-info__list">
                        <li class="learnmore-info__list-item">
                            <i class="ep-icon ep-icon_ok"></i> <?php echo translate('learn_more_block_security_label_buyer'); ?>
                        </li>
                        <li class="learnmore-info__list-item">
                            <i class="ep-icon ep-icon_ok"></i> <?php echo translate('learn_more_block_security_label_seller'); ?>
                        </li>
                        <li class="learnmore-info__list-item">
                            <i class="ep-icon ep-icon_ok"></i> <?php echo translate('learn_more_block_security_label_secure'); ?>
                        </li>
                    </ul>
                    <a class="btn btn-primary" href="<?php echo __SITE_URL . 'security'; ?>" <?php echo addQaUniqueIdentifier("page__learn_more__learn-more-about-security_btn"); ?>><?php echo translate('learn_more_about_security_btn'); ?></a>
                </div>
            </div>
        </div>
    </div>

    <div class="learnmore-list">
        <div class="learnmore-list__item">
            <div class="learnmore-list__item-inner">
                <div class="learnmore-info">
                    <h2 class="learnmore-ttl learnmore-ttl--lh-50">
                        <?php echo translate('learn_more_block_help_header'); ?>
                    </h2>
                    <div class="learnmore-info__txt"><?php echo translate('learn_more_block_help_header_text'); ?></div>
                    <ul class="learnmore-info__list">
                        <li class="learnmore-info__list-item">
                            <i class="ep-icon ep-icon_ok"></i> <?php echo translate('learn_more_block_help_label_faq'); ?>
                        </li>
                        <li class="learnmore-info__list-item">
                            <i class="ep-icon ep-icon_ok"></i> <?php echo translate('learn_more_block_help_label_topics'); ?>
                        </li>
                        <li class="learnmore-info__list-item">
                            <i class="ep-icon ep-icon_ok"></i> <?php echo translate('learn_more_block_help_label_use'); ?>
                        </li>
                    </ul>
                    <a class="btn btn-primary" href="<?php echo __SITE_URL . 'help'; ?>" <?php echo addQaUniqueIdentifier("page__learn_more__learn-more-about-us_btn"); ?>><?php echo translate('learn_more_about_assistance_btn'); ?></a>
                </div>
            </div>
        </div>
        <div class="learnmore-list__item learnmore-list__item-img">
            <picture class="learnmore-list__img">
                <source media="(max-width: 575px)" data-srcset="<?php echo asset('public/build/images/learn-more/list-background-3-mobile.jpg'); ?> 1x, <?php echo asset('public/build/images/learn-more/list-background-3-mobile@2x.jpg'); ?> 2x" srcset="<?php echo getLazyImage(575, 350);?>">
                <img class="image js-lazy" src="<?php echo getLazyImage(956, 660);?>" data-src="<?php echo asset('public/build/images/learn-more/list-background-3.jpg'); ?>" alt="" width="1920" height="546" alt="<?php echo translate('learn_more_about_assistance_img'); ?>">
            </picture>
        </div>
    </div>

    <div class="learnmore-list">
        <div class="learnmore-list__item learnmore-list__item-img">
            <picture class="learnmore-list__img">
                <source media="(max-width: 575px)" data-srcset="<?php echo asset('public/build/images/learn-more/list-background-4-mobile.jpg'); ?> 1x, <?php echo asset('public/build/images/learn-more/list-background-4-mobile@2x.jpg'); ?> 2x" srcset="<?php echo getLazyImage(575, 350);?>">
                <img class="image js-lazy" src="<?php echo getLazyImage(956, 660);?>" data-src="<?php echo asset('public/build/images/learn-more/list-background-4.jpg'); ?>" alt="" width="1920" height="546" alt="<?php echo translate('learn_more_about_news_and_media_img'); ?>">
            </picture>
        </div>
        <div class="learnmore-list__item learnmore-list__item--last">
            <div class="learnmore-list__item-inner">
                <div class="learnmore-info">
                    <h2 class="learnmore-ttl learnmore-ttl--lh-50">
                        <?php echo translate('learn_more_block_press_releases_header'); ?>
                    </h2>
                    <div class="learnmore-info__txt"><?php echo translate('learn_more_block_press_releases_header_text'); ?></div>
                    <ul class="learnmore-info__list">
                        <li class="learnmore-info__list-item">
                            <i class="ep-icon ep-icon_ok"></i> <?php echo translate('learn_more_block_press_releases_label_press'); ?>
                        </li>
                        <li class="learnmore-info__list-item">
                            <i class="ep-icon ep-icon_ok"></i> <?php echo translate('learn_more_block_press_releases_label_updates'); ?>
                        </li>
                        <li class="learnmore-info__list-item">
                            <i class="ep-icon ep-icon_ok"></i> <?php echo translate('learn_more_block_press_releases_label_news'); ?>
                        </li>
                    </ul>
                    <a class="btn btn-primary" href="<?php echo __SITE_URL . 'about/in_the_news'; ?>" <?php echo addQaUniqueIdentifier("page__learn_more__learn-more-about-news-and-media_btn"); ?>><?php echo translate('learn_more_go_to_page_btn'); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>
