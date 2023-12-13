
<?php
    encoreEntryLinkTags('b2b_landing_page');
    encoreEntryScriptTags('b2b_landing_page');
    $isLoggedIn = logged_in();
?>

<!-- Section Strong Matches -->
<section class="strong-matches container-1420">
    <div class="strong-matches__container">
        <h2 class="strong-matches__title">
            <?php echo translate('ep_matchmaking_strong_matches_title'); ?>
        </h2>

        <picture class="strong-matches__picture">
            <source
                media="(max-width: 575px)"
                srcset="<?php echo asset('public/build/images/b2b/landing/strong-matches/strong-matches-mobile.jpg'); ?>"
                data-srcset="<?php echo asset('public/build/images/b2b/landing/strong-matches/strong-matches-mobile.jpg'); ?> 1x,
                <?php echo asset('public/build/images/b2b/landing/strong-matches/strong-matches-mobile@2x.jpg'); ?> 2x"
            >
            <source
                media="(max-width: 991px)"
                srcset="<?php echo asset('public/build/images/b2b/landing/strong-matches/strong-matches-tablet.jpg'); ?>"
                data-srcset="<?php echo asset('public/build/images/b2b/landing/strong-matches/strong-matches-tablet.jpg'); ?> 1x,
                <?php echo asset('public/build/images/b2b/landing/strong-matches/strong-matches-tablet@2x.jpg'); ?> 2x"
            >
            <img
                class="strong-matches__image"
                src="<?php echo asset('public/build/images/b2b/landing/strong-matches/strong-matches.jpg'); ?>"
                srcset="<?php echo asset('public/build/images/b2b/landing/strong-matches/strong-matches.jpg'); ?> 1x,
                <?php echo asset('public/build/images/b2b/landing/strong-matches/strong-matches@2x.jpg'); ?> 2x"
                alt="<?php echo translate('ep_matchmaking_strong_matches_title'); ?>"
            >
        </picture>
    </div>

</section>
<!-- End Section Strong Matches -->

<!-- Section How it Works -->
<section class="ep-matchmaking-section how-it-works container-1420">
    <div class="section-header section-header--title-only">
        <h2 class="section-header__title">
            <?php echo translate('ep_matchmaking_how_it_works_title'); ?>
        </h2>
    </div>
    <div class="how-it-works__row">
        <ul class="how-it-works__list">
            <li class="how-it-works__item">
                <picture class="how-it-works__picture">
                    <source
                        media="(max-width: 575px)"
                        srcset="<?php echo getLazyImage(520, 215); ?>"
                        data-srcset="<?php echo asset('public/build/images/b2b/landing/how-it-works/active-user-mobile.jpg'); ?> 1x, <?php echo asset('public/build/images/b2b/landing/how-it-works/active-user-mobile@2x.jpg'); ?> 2x"
                    >
                    <source
                        media="(max-width: 991px)"
                        srcset="<?php echo getLazyImage(369, 170); ?>"
                        data-srcset="<?php echo asset('public/build/images/b2b/landing/how-it-works/active-user-tablet.jpg'); ?> 1x, <?php echo asset('public/build/images/b2b/landing/how-it-works/active-user-tablet@2x.jpg'); ?> 2x"
                    >
                    <img
                        class="how-it-works__img js-lazy"
                        src="<?php echo getLazyImage(332, 250); ?>"
                        data-src="<?php echo asset('public/build/images/b2b/landing/how-it-works/active-user.jpg'); ?>"
                        data-srcset="<?php echo asset('public/build/images/b2b/landing/how-it-works/active-user.jpg'); ?> 1x, <?php echo asset('public/build/images/b2b/landing/how-it-works/active-user@2x.jpg'); ?> 2x"
                        alt="<?php echo translate('ep_matchmaking_active_user_title'); ?>"
                    >
                </picture>

            <div class="how-it-works__delimiter">
                <div class="how-it-works__number">
                    1
                </div>
            </div>

            <div class="how-it-works__info">
                <div class="how-it-works__detail">
                    <h3 class="how-it-works__ttl">
                        <?php echo translate('ep_matchmaking_active_user_title'); ?>
                    </h3>
                    <p class="how-it-works__desc">
                        <?php echo translate('ep_matchmaking_active_user_text'); ?>
                    </p>
                </div>
            </div>
            </li>

            <li class="how-it-works__item">
                <picture class="how-it-works__picture">
                    <source
                        media="(max-width: 575px)"
                        srcset="<?php echo getLazyImage(520, 215); ?>"
                        data-srcset="<?php echo asset('public/build/images/b2b/landing/how-it-works/product-request-mobile.jpg'); ?> 1x, <?php echo asset('public/build/images/b2b/landing/how-it-works/product-request-mobile@2x.jpg'); ?> 2x"
                    >
                    <source
                        media="(max-width: 991px)"
                        srcset="<?php echo getLazyImage(369, 170); ?>"
                        data-srcset="<?php echo asset('public/build/images/b2b/landing/how-it-works/product-request-tablet.jpg'); ?> 1x, <?php echo asset('public/build/images/b2b/landing/how-it-works/product-request-tablet@2x.jpg'); ?> 2x"
                    >
                    <img
                        class="how-it-works__img js-lazy"
                        src="<?php echo getLazyImage(332, 252); ?>"
                        data-src="<?php echo asset('public/build/images/b2b/landing/how-it-works/product-request.jpg'); ?>"
                        data-srcset="<?php echo asset('public/build/images/b2b/landing/how-it-works/product-request.jpg'); ?> 1x, <?php echo asset('public/build/images/b2b/landing/how-it-works/product-request@2x.jpg'); ?> 2x"
                        alt="<?php echo translate('ep_matchmaking_send_product_requests_title'); ?>"
                    >
                </picture>

            <div class="how-it-works__delimiter">
                <div class="how-it-works__number">
                    2
                </div>
            </div>

            <div class="how-it-works__info">
                <div class="how-it-works__detail">
                    <h3 class="how-it-works__ttl">
                        <?php echo translate('ep_matchmaking_send_product_requests_title'); ?>
                    </h3>
                    <p class="how-it-works__desc">
                        <?php echo translate('ep_matchmaking_send_product_requests_text'); ?>
                    </p>
                </div>
            </div>
            </li>

            <li class="how-it-works__item">
                <picture class="how-it-works__picture">
                    <source
                        media="(max-width: 575px)"
                        srcset="<?php echo getLazyImage(520, 215); ?>"
                        data-srcset="<?php echo asset('public/build/images/b2b/landing/how-it-works/perfect-match-mobile.jpg'); ?> 1x, <?php echo asset('public/build/images/b2b/landing/how-it-works/perfect-match-mobile@2x.jpg'); ?> 2x"
                    >
                    <source
                        media="(max-width: 991px)"
                        srcset="<?php echo getLazyImage(369, 170); ?>"
                        data-srcset="<?php echo asset('public/build/images/b2b/landing/how-it-works/perfect-match-tablet.jpg'); ?> 1x, <?php echo asset('public/build/images/b2b/landing/how-it-works/perfect-match-tablet@2x.jpg'); ?> 2x"
                    >
                    <img
                        class="how-it-works__img js-lazy"
                        src="<?php echo getLazyImage(332, 252); ?>"
                        data-src="<?php echo asset('public/build/images/b2b/landing/how-it-works/perfect-match.jpg'); ?>"
                        data-srcset="<?php echo asset('public/build/images/b2b/landing/how-it-works/perfect-match.jpg'); ?> 1x, <?php echo asset('public/build/images/b2b/landing/how-it-works/perfect-match@2x.jpg'); ?> 2x"
                        alt="<?php echo translate('ep_matchmaking_find_perfect_match_title'); ?>"
                    >
                </picture>

            <div class="how-it-works__delimiter">
                <div class="how-it-works__number">
                    3
                </div>
            </div>

            <div class="how-it-works__info">
                <div class="how-it-works__detail">
                    <h3 class="how-it-works__ttl">
                        <?php echo translate('ep_matchmaking_find_perfect_match_title'); ?>
                    </h3>
                    <p class="how-it-works__desc">
                        <?php echo translate('ep_matchmaking_find_perfect_match_text'); ?>
                    </p>
                </div>
            </div>
            </li>

            <li class="how-it-works__item">
                <picture class="how-it-works__picture">
                    <source
                        media="(max-width: 575px)"
                        srcset="<?php echo getLazyImage(520, 215); ?>"
                        data-srcset="<?php echo asset('public/build/images/b2b/landing/how-it-works/contact-your-matches-mobile.jpg'); ?> 1x, <?php echo asset('public/build/images/b2b/landing/how-it-works/contact-your-matches-mobile@2x.jpg'); ?> 2x"
                    >
                    <source
                        media="(max-width: 991px)"
                        srcset="<?php echo getLazyImage(369, 170); ?>"
                        data-srcset="<?php echo asset('public/build/images/b2b/landing/how-it-works/contact-your-matches-tablet.jpg'); ?> 1x, <?php echo asset('public/build/images/b2b/landing/how-it-works/contact-your-matches-tablet@2x.jpg'); ?> 2x"
                    >
                    <img
                        class="how-it-works__img js-lazy"
                        src="<?php echo getLazyImage(332, 252); ?>"
                        data-src="<?php echo asset('public/build/images/b2b/landing/how-it-works/contact-your-matches.jpg'); ?>"
                        data-srcset="<?php echo asset('public/build/images/b2b/landing/how-it-works/contact-your-matches.jpg'); ?> 1x, <?php echo asset('public/build/images/b2b/landing/how-it-works/contact-your-matches@2x.jpg'); ?> 2x"
                        alt="<?php echo translate('ep_matchmaking_contact_your_matches_title'); ?>"
                    >
                </picture>

            <div class="how-it-works__delimiter">
                <div class="how-it-works__number">4</div>
            </div>

            <div class="how-it-works__info">
                <div class="how-it-works__detail">
                    <h3 class="how-it-works__ttl">
                        <?php echo translate('ep_matchmaking_contact_your_matches_title'); ?>
                    </h3>
                    <p class="how-it-works__desc">
                        <?php echo translate('ep_matchmaking_contact_your_matches_text'); ?>
                    </p>
                </div>
            </div>
            </li>
        </ul>

        <?php if (!$isLoggedIn) {?>
            <button
                class="how-it-works__btn btn btn-new16 btn-primary call-action"
                <?php echo addQaUniqueIdentifier('page__b2b__header_add-request-btn'); ?>
                data-js-action="b2b:add-request"
                data-title="<?php echo translate('ep_matchmaking_popup_title'); ?>"
                data-sub-title="<?php echo translate('ep_matchmaking_popup_subtitle'); ?>"
                data-image="<?php echo asset('public/build/images/b2b/landing/b2b-popup.jpg'); ?>"
                data-mw="400"
            >
                <?php echo translate('ep_matchmaking_header_add_request_btn'); ?>
            </button>

        <?php } else { ?>
            <a
                class="how-it-works__btn btn btn-new16 btn-primary"
                <?php echo addQaUniqueIdentifier('page__b2b__how-it-works_add-request-btn'); ?>
                <?php if(is_buyer() || is_shipper()) { ?>
                    href="<?php echo __SITE_URL . 'b2b/all'; ?>"
                <?php } else { ?>
                    href="<?php echo __SITE_URL . 'b2b/reg'; ?>"
                <?php } ?>
            >
                <?php if(is_buyer() || is_shipper()) { ?>
                    <?php echo translate('ep_matchmaking_header_view_request_btn'); ?>
                <?php } else { ?>
                    <?php echo translate('ep_matchmaking_header_add_request_btn'); ?>
                <?php } ?>
            </a>
        <?php } ?>
    </div>
</section>
<!-- End Section How it Works -->

<!-- Section Benefits of B2B Matchmaking  -->
<?php
    $benefitsData = [
        'reversed'    => true,
        'atasType'    => 'b2b-matchmaking',
        'title'       => translate('ep_matchmaking_b2b_benefits_header_title'),
        'subTitle'    => translate('ep_matchmaking_b2b_benefits_header_text'),
        'benefits'    => [
            [
                'icon'      => asset('public/build/images/b2b/landing/benefits-of-b2b-matchmaking/matchmaking-benefits-one.svg'),
                'title'     => translate('ep_matchmaking_b2b_benefits_first_title'),
                'paragraph' => translate('ep_matchmaking_b2b_benefits_first_desc'),
            ],
            [
                'icon'      => asset('public/build/images/b2b/landing/benefits-of-b2b-matchmaking/matchmaking-benefits-two.svg'),
                'title'     => translate('ep_matchmaking_b2b_benefits_second_title'),
                'paragraph' => translate('ep_matchmaking_b2b_benefits_second_desc'),
            ],
            [
                'icon'      => asset('public/build/images/b2b/landing/benefits-of-b2b-matchmaking/matchmaking-benefits-three.svg'),
                'title'     => translate('ep_matchmaking_b2b_benefits_third_title'),
                'paragraph' => translate('ep_matchmaking_b2b_benefits_third_desc'),
            ],
            [
                'icon'      => asset('public/build/images/b2b/landing/benefits-of-b2b-matchmaking/matchmaking-benefits-four.svg'),
                'title'     => translate('ep_matchmaking_b2b_benefits_fourth_title'),
                'paragraph' => translate('ep_matchmaking_b2b_benefits_fourth_desc'),
            ],
            [
                'icon'      => asset('public/build/images/b2b/landing/benefits-of-b2b-matchmaking/matchmaking-benefits-five.svg'),
                'title'     => translate('ep_matchmaking_b2b_benefits_five_title'),
                'paragraph' => translate('ep_matchmaking_b2b_benefits_five_desc'),
            ],
        ],
        'picture'     => [
            'desktop'    => asset('public/build/images/b2b/landing/benefits-of-b2b-matchmaking/benefits-of-b2b-matchmaking.jpg'),
            'tablet'     => asset('public/build/images/b2b/landing/benefits-of-b2b-matchmaking/benefits-of-b2b-matchmaking-tablet.jpg'),
            'mobile'     => asset('public/build/images/b2b/landing/benefits-of-b2b-matchmaking/benefits-of-b2b-matchmaking-mobile.jpg'),
            'desktop@2x' => asset('public/build/images/b2b/landing/benefits-of-b2b-matchmaking/benefits-of-b2b-matchmaking@2x.jpg'),
            'tablet@2x'  => asset('public/build/images/b2b/landing/benefits-of-b2b-matchmaking/benefits-of-b2b-matchmaking-tablet@2x.jpg'),
            'mobile@2x'  => asset('public/build/images/b2b/landing/benefits-of-b2b-matchmaking/benefits-of-b2b-matchmaking-mobile@2x.jpg'),
        ],
    ];

    echo views()->display('new/home/components/benefits_view', ['benefitsData' => $benefitsData]);
?>
<!-- End Section Benefits of B2B Matchmaking  -->

<!-- Section Search by Country  -->

<section class="ep-matchmaking-section search-by-country container-1420">
    <div class="section-header">
        <h2 class="section-header__title">
            <?php echo translate('ep_matchmaking_search_by_country_header_title'); ?>
        </h2>
        <p class="section-header__subtitle">
            <?php echo translate('ep_matchmaking_search_by_country_header_text'); ?>
        </p>
    </div>
    <div class="search-by-country__map js-search-by-country loading" data-lazy-name="search-by-country">
        <div class="search-by-country__map-info">
            <h3 class="search-by-country__map-title">
                <?php echo translate('ep_matchmaking_search_by_country_top_countries_title'); ?>
            </h3>
            <ul class="search-by-country__map-list">
            <?php
                foreach ($topCountries as $country) { ?>
                <li class="search-by-country__map-item">
                    <a
                        class="search-by-country__map-link<?php echo !$isLoggedIn ? ' fancybox.ajax fancyboxValidateModal call-action' : ''; ?>"
                        <?php if (!$isLoggedIn) { ?>
                            data-mw="400"
                            data-title="Login"
                            data-js-action="lazy-loading:login"
                            href="<?php echo __SITE_URL . 'login'; ?>"
                        <?php } else { ?>
                            href="<?php echo __SITE_URL . 'b2b/all/country/' . strForURL($country['country'] . ' ' . $country['id']); ?>"
                        <?php } ?>
                        <?php echo addQaUniqueIdentifier('page__b2b__search-by-country_link'); ?>
                    >
                        <img class="search-by-country__map-icon" src="<?php echo asset('public/build/images/b2b/landing/benefits-of-b2b-matchmaking/location-icon.svg'); ?>" alt="Top Countries" />
                        <span
                            class="search-by-country__map-country js-country-name"
                            data-country-name="<?php echo $country['country']; ?>"
                            <?php echo addQaUniqueIdentifier('page__b2b__search-by-country_country-name'); ?>
                        >
                            <?php echo $country['country']; ?>
                        </span>
                        <span class="search-by-country__map-counter" <?php echo addQaUniqueIdentifier('page__b2b__search-by-country_counter'); ?>>
                            (<?php echo $country['counter']; ?>)
                        </span>
                    </a>
                </li>
            <?php }?>
            </ul>
            <a
                class="search-by-country__link<?php echo !$isLoggedIn ? ' fancybox.ajax fancyboxValidateModal call-action' : ''; ?>"
                <?php if (!$isLoggedIn) { ?>
                    data-mw="400"
                    data-title="Login"
                    data-js-action="lazy-loading:login"
                    href="<?php echo __SITE_URL . 'login'; ?>"
                <?php } else { ?>
                    href="<?php echo __SITE_URL . 'b2b/all'; ?>"
                <?php } ?>
                <?php echo addQaUniqueIdentifier('page__b2b__search-by-country_view-more-link'); ?>
            >
                <?php echo translate('ep_matchmaking_view_more_btn'); ?><?php echo widgetGetSvgIcon('arrowRight', 15, 15); ?>
            </a>
        </div>
        <div class="search-by-country__map-image js-country-map"></div>
    </div>
    <?php views('new/partials/ajax_loader_view'); ?>
</section>

<!-- End Section Search by Country  -->

<!-- Section Search by Category  -->
<section class="categories-group-section search-by-category ep-matchmaking-section container-1420">
    <div class="section-header">
        <h2 class="section-header__title">
            <?php echo translate('ep_matchmaking_search_by_category_header_title'); ?>
        </h2>
    </div>

    <div class="categories-group-main categories-group-main--banner">
        <?php foreach ($categoryGroups as $category) {?>
            <div class="categories-group-main__item">
                <img
                    class="image js-lazy"
                    src="<?php echo getLazyImage(384, 222); ?>"
                    data-src="<?php echo asset('public/build/images/categories-group/' . $category['img']); ?>"
                    alt="<?php echo $category['title']; ?>"
                >
                <a
                    class="categories-group-main__inner<?php echo !$isLoggedIn ? ' fancybox.ajax fancyboxValidateModal call-action' : ''; ?>"
                    <?php if (!$isLoggedIn) { ?>
                        data-mw="400"
                        data-title="Login"
                        data-js-action="lazy-loading:login"
                        href="<?php echo __SITE_URL . 'login'; ?>"
                    <?php } else { ?>
                        href="<?php echo __SITE_URL . 'b2b/all?golden_category=' . $category['id_group']; ?>"
                    <?php } ?>
                    <?php echo addQaUniqueIdentifier('categories__select-category'); ?>
                >
                    <h3 class="categories-group-main__name">
                        <?php echo implode('&<br>', explode('&', $category['title'])); ?>
                    </h3>
                </a>
            </div>
        <?php } ?>
    </div>
</section>
<!-- End Section Search by Category  -->

<!-- Section Latest B2B Requests -->
<section class="ep-matchmaking-section latest-b2b-request container-1420">
    <div class="section-header">
        <h2 class="section-header__title">
            <?php echo translate('ep_matchmaking_latest_b2b_request_header_title'); ?>
        </h2>
    </div>

    <div class="latest-b2b-request__row">
        <div class="latest-b2b-request__items">
            <?php
                foreach ($latestRequests as $request) {
                    views('new/b2b/b2b_card_view', [
                        'request'       => $request,
                        'classList'     => ' b2b-card--label',
                    ]);
                }
            ?>
        </div>
        <a
            class="btn btn-new16 btn-primary latest-b2b-request__btn<?php echo !$isLoggedIn ? ' fancybox.ajax fancyboxValidateModal call-action' : ''; ?>"
            <?php if (!$isLoggedIn) { ?>
                data-mw="400"
                data-title="Login"
                data-js-action="lazy-loading:login"
                href="<?php echo __SITE_URL . 'login'; ?>"
            <?php } else { ?>
                href="<?php echo __SITE_URL . 'b2b/all'; ?>"
            <?php } ?>
            <?php echo addQaUniqueIdentifier('page__b2b__b2b-requests_view-more-btn'); ?>
        >
            <?php echo translate('ep_matchmaking_view_more_btn'); ?>
        </a>
    </div>
</section>
<!-- End Section Latest B2B Requests -->

<?php if (!$isLoggedIn) {?>
   <!-- Section Think Local Sell Global -->
    <section class="ep-matchmaking-section sell-global container-1420">
        <div class="sell-global__row">
            <div class="sell-global__col">
                <div class="sell-global__bg">
                    <picture>
                        <source
                            media="(max-width: 575px)"
                            srcset="<?php echo getLazyImage(290, 200); ?>"
                            data-srcset="<?php echo asset('public/build/images/b2b/landing/think-local/think-local-mobile.jpg'); ?> 1x, <?php echo asset('public/build/images/b2b/landing/think-local/think-local-mobile@2x.jpg'); ?> 2x"
                        >
                        <source
                            media="(max-width: 991px)"
                            srcset="<?php echo getLazyImage(369, 407); ?>"
                            data-srcset="<?php echo asset('public/build/images/b2b/landing/think-local/think-local-tablet.jpg'); ?> 1x, <?php echo asset('public/build/images/b2b/landing/think-local/think-local-tablet@2x.jpg'); ?> 2x"
                        >
                        <img
                            class="image js-lazy"
                            src="<?php echo getLazyImage(710, 400); ?>"
                            data-src="<?php echo asset('public/build/images/b2b/landing/think-local/think-local.jpg'); ?>"
                            data-srcset="<?php echo asset('public/build/images/b2b/landing/think-local/think-local.jpg'); ?> 1x, <?php echo asset('public/build/images/b2b/landing/think-local/think-local@2x.jpg'); ?> 2x"
                            width="956"
                            height="550"
                            alt="<?php echo translate('why_ep_think_local_sell_global_ttl'); ?>"
                        >
                    </picture>
                </div>
            </div>

            <div class="sell-global__col">
                <div class="sell-global__side">
                    <h2 class="sell-global__ttl sell-global__ttl--sellglobal">
                        <?php echo translate('why_ep_think_local_sell_global_ttl'); ?>
                    </h2>
                    <p class="sell-global__subttl">
                        <?php echo translate('why_ep_think_local_sell_global_subttl'); ?>
                    </p>
                    <a
                        href="<?php echo __SITE_URL . 'register'; ?>"
                        class="btn btn-new16 btn-primary"
                        <?php echo addQaUniqueIdentifier('b2b__register-btn'); ?>
                    >
                        <?php echo translate('why_ep_think_local_sell_global_btn'); ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="sell-global__row sell-global__row--reverse-sm">
            <div class="sell-global__col">
                <div class="sell-global__side sell-global__side--left">
                    <h2 class="sell-global__ttl">
                        <?php echo translate('why_ep_get_started_ttl'); ?>
                    </h2>
                    <p class="sell-global__subttl">
                        <?php echo translate('why_ep_get_started_subttl'); ?>
                    </p>
                    <a
                        href="<?php echo __SITE_URL . 'contact'; ?>"
                        class="btn btn-new16 btn-primary"
                        <?php echo addQaUniqueIdentifier('b2b__contact-us-btn'); ?>
                    >
                        <?php echo translate('why_ep_get_started_btn'); ?>
                    </a>
                </div>
            </div>

            <div class="sell-global__col">
                <div class="sell-global__bg">
                    <picture>
                        <source
                            media="(max-width: 575px)"
                            srcset="<?php echo getLazyImage(425, 266); ?>"
                            data-srcset="<?php echo asset('public/build/images/b2b/landing/think-local/get-started-mobile.jpg'); ?> 1x, <?php echo asset('public/build/images/b2b/landing/think-local/get-started-mobile@2x.jpg'); ?> 2x"
                        >
                        <source
                            media="(max-width: 991px)"
                            srcset="<?php echo getLazyImage(767, 480); ?>"
                            data-srcset="<?php echo asset('public/build/images/b2b/landing/think-local/get-started-tablet.jpg'); ?> 1x, <?php echo asset('public/build/images/b2b/landing/think-local/get-started-tablet@2x.jpg'); ?> 2x"
                        >
                        <img
                            class="image js-lazy"
                            src="<?php echo getLazyImage(710, 400); ?>"
                            data-src="<?php echo asset('public/build/images/b2b/landing/think-local/get-started.jpg'); ?>"
                            data-srcset="<?php echo asset('public/build/images/b2b/landing/think-local/get-started.jpg'); ?> 1x, <?php echo asset('public/build/images/b2b/landing/think-local/get-started@2x.jpg'); ?> 2x"
                            width="956"
                            height="550"
                            alt="<?php echo translate('why_ep_get_started_ttl'); ?>"
                        >
                    </picture>
                </div>
            </div>
        </div>
    </section>
    <!-- End Section Think Local Sell Global -->
<?php }?>

<?php if (is_manufacturer() || is_seller()) {?>
    <!-- Section Certification Benefits -->
    <?php views('new/home/components/certification_benefits_view'); ?>
    <!-- End Section Certification benefits -->
<?php }?>

<?php if (is_buyer()) {?>
    <!-- Section Buyer Benefits -->
    <?php views('new/home/components/buyer_benefits_view');?>
    <!-- End Section Section Buyer Benefits -->
<?php }?>

<?php if (is_shipper()) {?>
    <!-- Section Shipping Benefits -->
    <?php views('new/home/components/shipping_benefits_view');?>
    <!-- End Section Shipping Benefits -->
<?php }?>
