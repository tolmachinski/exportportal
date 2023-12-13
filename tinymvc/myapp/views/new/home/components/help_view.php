<?php
    $helpMethods = [
        [
            'title'       => translate('home_help_first_title'),
            'description' => translate('home_help_first_desc'),
            'link'        => __SITE_URL . 'faq/all',
            'picture'     => [
                'desktop'    => asset('public/build/images/index/help/faq-d.jpg'),
                'tablet'     => asset('public/build/images/index/help/faq-t.jpg'),
                'mobile'     => asset('public/build/images/index/help/faq-m.jpg'),
                'desktop@2x' => asset('public/build/images/index/help/faq-d@2x.jpg'),
                'tablet@2x'  => asset('public/build/images/index/help/faq-t@2x.jpg'),
                'mobile@2x'  => asset('public/build/images/index/help/faq-m@2x.jpg'),
            ],
        ],
        [
            'title'       => translate('home_help_second_title'),
            'description' => translate('home_help_second_desc'),
            'link'        => __SITE_URL . 'topics/help',
            'picture'     => [
                'desktop'    => asset('public/build/images/index/help/topics-d.jpg'),
                'tablet'     => asset('public/build/images/index/help/topics-t.jpg'),
                'mobile'     => asset('public/build/images/index/help/topics-m.jpg'),
                'desktop@2x' => asset('public/build/images/index/help/topics-d@2x.jpg'),
                'tablet@2x'  => asset('public/build/images/index/help/topics-t@2x.jpg'),
                'mobile@2x'  => asset('public/build/images/index/help/topics-m@2x.jpg'),
            ],
        ],
        [
            'title'       => translate('home_help_third_title'),
            'description' => translate('home_help_third_desc'),
            'link'        => __SITE_URL . 'user_guide',
            'picture'     => [
                'desktop'      => asset('public/build/images/index/help/user-guides-d.jpg'),
                'tablet'       => asset('public/build/images/index/help/user-guides-t.jpg'),
                'mobile'       => asset('public/build/images/index/help/user-guides-m.jpg'),
                'desktop@2x'   => asset('public/build/images/index/help/user-guides-d@2x.jpg'),
                'tablet@2x'    => asset('public/build/images/index/help/user-guides-t@2x.jpg'),
                'mobile-m@2x'  => asset('public/build/images/index/help/user-guides-m@2x.jpg'),
            ],
        ],
        [
            'title'       => translate('home_help_fourth_title'),
            'description' => translate('home_help_fourth_desc'),
            'link'        => __COMMUNITY_URL,
            'picture'     => [
                'desktop'      => asset('public/build/images/index/help/community-help-d.jpg'),
                'tablet'       => asset('public/build/images/index/help/community-help-t.jpg'),
                'mobile'       => asset('public/build/images/index/help/community-help-m.jpg'),
                'desktop@2x'   => asset('public/build/images/index/help/community-help-d@2x.jpg'),
                'tablet@2x'    => asset('public/build/images/index/help/community-help-t@2x.jpg'),
                'mobile@2x'    => asset('public/build/images/index/help/community-help-m@2x.jpg'),
            ],
        ],
    ];
?>

<section class="home-section help-methods container-1420">
    <div class="section-header section-header--title-only">
        <h2 class="section-header__title"><?php echo translate('home_help_header_title'); ?></h2>
        <p class="section-header__subtitle"><?php echo translate('home_help_header_subtitle'); ?></p>
    </div>

    <div class="help-methods__content">
        <?php foreach ($helpMethods as $method) { ?>
            <div class="help-methods__item" <?php echo addQaUniqueIdentifier('home__help-methods-item'); ?>>
                <picture class="help-methods__picture">
                    <source
                        media="(max-width: 575px)"
                        srcset="<?php echo getLazyImage(545, 388); ?>"
                        data-srcset="<?php echo $method['picture']['mobile']; ?> 1x, <?php echo $method['picture']['mobile@2x']; ?> 2x"
                    >
                    <source
                        media="(max-width: 1199px)"
                        srcset="<?php echo getLazyImage(488, 365); ?>"
                        data-srcset="<?php echo $method['picture']['tablet']; ?> 1x, <?php echo $method['picture']['tablet@2x']; ?> 2x"
                    >
                    <img
                        class="help-methods__image js-lazy"
                        src="<?php echo getLazyImage(344, 278); ?>"
                        data-src="<?php echo $method['picture']['desktop']; ?>"
                        data-srcset="<?php echo $method['picture']['desktop']; ?> 1x, <?php echo $method['picture']['desktop@2x']; ?> 2x"
                        alt="<?php echo $method['title']; ?>"
                    >
                </picture>
                <div class="help-methods__info">
                    <h3 class="help-methods__title"><?php echo $method['title']; ?></h3>
                    <p class="help-methods__description"><?php echo $method['description']; ?></p>
                    <a class="help-methods__link" href="<?php echo $method['link']; ?>">
                        <?php echo translate('home_help_card_learn_more_link'); echo widgetGetSvgIcon('arrowRight', 15, 15); ?>
                    </a>
                </div>
            </div>
        <?php } ?>
    </div>
    <button
        class="help-methods__btn btn btn-block btn-primary btn-new18 js-btn-call-main-chat call-action"
        type="button"
        title="<?php echo translate('header_navigation_link_chat_title', null, true); ?>"
        data-js-action="zoho-chat:show"
        <?php echo addQaUniqueIdentifier('home__help_chat-btn'); ?>
    >
        <?php echo translate('home_help_more_assistance_btn'); ?>
    </button>
</section>
