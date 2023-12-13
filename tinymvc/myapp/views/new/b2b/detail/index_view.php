<?php use App\Common\Contracts\B2B\B2bRequestLocationType;?>
<div class="container-1420">
    <div class="b2b-detail">
        <h1 class="b2b-detail__title" <?php echo addQaUniqueIdentifier('page__b2b__request-title'); ?>>
            <?php echo $request['b2b_title']; ?>
        </h1>

        <div class="b2b-detail__col-left">
            <div class="b2b-detail__logo">
                <img
                    class="image"
                    src="<?php echo $request['main_image']['url']; ?>"
                    alt="<?php echo $request['name_company']; ?>"
                    <?php echo addQaUniqueIdentifier('page__b2b__request-image'); ?>
                >
            </div>

            <?php if (i_have_company()) { ?>
                <?php if (is_my_company($request['id_company'])) {?>
                    <a class="btn btn-primary btn-new16 btn-block" href="<?php echo __SITE_URL . 'b2b/edit/' . $request['id_request']; ?>">
                        <?php echo translate('b2b_detail_edit_request_btn'); ?>
                    </a>
                <?php } else {?>
                    <button
                        class="btn btn-primary btn-new16 btn-block fancybox.ajax fancyboxValidateModal"
                        data-title="<?php echo translate('b2b_detail_become_a_partner_btn', null, true); ?>"
                        data-fancybox-href="<?php echo __SITE_URL . 'b2b/popup_forms/become_partener/' . $request['id_request']; ?>"
                        type="button"
                    >
                        <?php echo translate('b2b_detail_become_a_partner_btn'); ?>
                    </button>
                <?php }?>
            <?php } ?>

            <div class="dropdown">
                <button
                    id="dropdownMenuButton"
                    class="dropdown-toggle btn btn-new16 btn-light btn-block"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                    type="button"
                >
                    <?php echo translate('b2b_detail_more_actions_dropdown_btn'); ?>
                    <i class="ep-icon ep-icon_menu-circles pl-10"></i>
                </button>

                <?php $isLoggedIn = logged_in(); ?>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <button
                        class="dropdown-item<?php echo $isLoggedIn ? ' fancybox.ajax fancyboxValidateModal' : ' js-require-logged-systmess'; ?>"
                        data-fancybox-href="<?php echo __SITE_URL . 'b2b/popup_forms/email/' . $request['id_request']; ?>"
                        data-title="<?php echo translate('b2b_detail_email_popup_title', null, true); ?>"
                        type="button"
                    >
                        <i class="ep-icon ep-icon_envelope-send"></i> <?php echo translate('b2b_detail_more_actions_dropdown_email_btn'); ?>
                    </button>

                    <button
                        class="dropdown-item<?php echo $isLoggedIn ? ' fancybox.ajax fancyboxValidateModal' : ' js-require-logged-systmess'; ?>"
                        data-fancybox-href="<?php echo __SITE_URL . 'b2b/popup_forms/share/' . $request['id_request']; ?>"
                        data-title="<?php echo translate('b2b_detail_share_popup_title', null, true); ?>"
                        type="button"
                    >
                        <i class="ep-icon ep-icon_share-stroke"></i> <?php echo translate('b2b_detail_more_actions_dropdown_share_btn'); ?>
                    </button>

                    <?php if ($isLoggedIn) { ?>
                        <?php echo !empty($btnChat) ? $btnChat : ''; ?>
                    <?php } else { ?>
                        <buton
                            class="dropdown-item js-require-logged-systmess"
                            data-title="<?php echo translate('b2b_detail_more_actions_dropdown_contact_author_btn', null, true); ?>"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_envelope"></i> <?php echo translate('b2b_detail_more_actions_dropdown_contact_author_btn'); ?>
                        </buton>
                    <?php } ?>

                    <?php if ($isLoggedIn && $iFollowed) { ?>
                        <button
                            id="<?php echo 'js-follow-b2b-' . $request['id_request']; ?>"
                            class="dropdown-item call-action"
                            data-request="<?php echo $request['id_request']; ?>"
                            data-js-action="b2b-requests:unfollow"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_unfollow"></i> <?php echo translate('b2b_detail_more_actions_dropdown_unfollow_btn'); ?>
                        </button>
                    <?php } else { ?>
                        <button
                            id="<?php echo 'js-follow-b2b-' . $request['id_request']; ?>"
                            class="dropdown-item<?php echo $isLoggedIn ? ' fancybox.ajax fancyboxValidateModal' : ' js-require-logged-systmess'; ?>"
                            data-title="Follow this request"
                            data-fancybox-href="<?php echo __SITE_URL . 'follow/popup_forms/follow_b2b_request/' . $request['id_request']; ?>"
                            type="button"
                        >
                            <i class="ep-icon ep-icon_follow"></i> <?php echo translate('b2b_detail_more_actions_dropdown_follow_btn'); ?>
                        </button>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="b2b-detail__col-right">
            <div class="b2b-detail__info-row">
                <p class="b2b-detail__info-label"><?php echo translate('b2b_detail_company_label'); ?></p>
                <div class="b2b-detail__info-desc b2b-detail__info-desc--ttu" <?php echo addQaUniqueIdentifier('page__b2b__company-name'); ?>>
                    <a class="link" href="<?php echo getCompanyURL($request['company']); ?>">
                        <?php echo $request['company']['name_company']; ?>
                    </a>
                </div>
            </div>

            <div class="b2b-detail__info-row">
                <p class="b2b-detail__info-label"><?php echo translate('b2b_detail_country_from_label'); ?></p>
                <div class="b2b-detail__info-desc">
                    <div class="b2b-detail__address">
                        <img
                            class="b2b-detail__country-flag js-lazy"
                            width="24"
                            height="16"
                            src="<?php echo getLazyImage(24, 16); ?>"
                            data-src="<?php echo getCountryFlag($request['company']['country']);?>"
                            alt="<?php echo $request['company']['country']; ?>"
                            <?php echo addQaUniqueIdentifier('page__b2b__country-image'); ?>
                        />
                        <span class="b2b-detail__country-name" <?php echo addQaUniqueIdentifier('page__b2b__country-name'); ?>>
                            <?php echo $request['company']['country']; ?>
                        </span>

                        <div class="b2b-detail__map-actions">
                            <a
                                class="b2b-detail__map-link fancyboxIframe fancybox.iframe"
                                data-h="500"
                                data-title="Google Map"
                                href="<?php echo __SITE_URL; ?>google_maps/get_direction/b2b/<?php echo $request['id_request']; ?>"
                                <?php echo addQaUniqueIdentifier('page__b2b__map-btn'); ?>
                            >
                                <?php echo translate('b2b_detail_get_direction_btn'); ?>
                            </a>
                            <a
                                class="b2b-detail__map-link"
                                href="http://maps.google.com/?q=<?php echo rawurlencode($request['company']['country'] . ', ' . $request['company']['city'] . ', ' . $request['company']['address_company']); ?>"
                                target="_blank"
                            >
                                <?php echo translate('b2b_detail_google_map_btn'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="b2b-detail__info-row">
                <p class="b2b-detail__info-label"><?php echo translate('b2b_detail_partners_type_label'); ?></p>
                <p class="b2b-detail__info-desc" <?php echo addQaUniqueIdentifier('page__b2b__partners-type'); ?>>
                    <?php echo $request['p_type']; ?>
                </p>
            </div>

            <div class="b2b-detail__info-row">
                <p class="b2b-detail__info-label"><?php echo translate('b2b_detail_business_industry_label'); ?></p>
                <p class="b2b-detail__info-desc" <?php echo addQaUniqueIdentifier('page__b2b__bussiness-industry'); ?>>
                    <?php echo $industriesContent; ?>
                </p>
            </div>

            <div class="b2b-detail__info-row">
                <p class="b2b-detail__info-label"><?php echo translate('b2b_detail_business_category_label'); ?></p>
                <p class="b2b-detail__info-desc" <?php echo addQaUniqueIdentifier('page__b2b__bussiness-category'); ?>>
                    <?php echo $categoriesContent; ?>
                </p>
            </div>

            <div class="b2b-detail__info-row">
                <p class="b2b-detail__info-label"><?php echo translate('b2b_detail_search_in_label'); ?></p>
                <div class="b2b-detail__info-desc">
                    <div class="b2b-detail__location<?php echo B2bRequestLocationType::COUNTRY() === $request['type_location'] ? ' b2b-detail__location--countries' : ''; ?>">
                        <?php if (B2bRequestLocationType::COUNTRY() === ($request['type_location'] ?? null)) {?>
                            <?php foreach ($request['countries'] as $countryId => $country) {?>
                                <div class="b2b-detail__country">
                                    <img
                                        class="b2b-detail__country-flag js-lazy"
                                        width="24"
                                        height="16"
                                        src="<?php echo getLazyImage(24, 16); ?>"
                                        data-src="<?php echo getCountryFlag($country['country']);?>"
                                        alt="<?php echo cleanOutput($country['country']); ?>"
                                        <?php echo addQaUniqueIdentifier('page__b2b__country-image'); ?>
                                    />
                                    <span class="b2b-detail__country-name" <?php echo addQaUniqueIdentifier('page__b2b__country-name'); ?>>
                                        <?php echo $country['country']; ?>
                                    </span>
                                </div>
                            <?php }?>
                        <?php } elseif (B2bRequestLocationType::RADIUS() === ($request['type_location'] ?? null)) {?>
                            <span><?php echo $request['b2b_radius']; ?> km</span>
                        <?php } else {?>
                            <span><?php echo translate('b2b_detail_search_type'); ?></span>
                        <?php }?>
                    </div>
                </div>
            </div>

            <div class="b2b-detail__info-row">
                <p class="b2b-detail__info-label">Description</p>
                <div class="b2b-detail__info-desc read-more-text js-read-more-text">
                    <div class="read-more-text__content ep-tinymce-text js-read-more-content" <?php echo addQaUniqueIdentifier('page__b2b__request-description'); ?>>
                        <?php echo $request['b2b_message']; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($request['b2b_tags'])) { ?>
                <div class="ep-tags_new" <?php echo addQaUniqueIdentifier('page__b2b__request-tags'); ?>>
                    <?php
                        $tags = explode(';', $request['b2b_tags']);
                        $tags = array_filter($tags);

                        foreach ($tags as $tag) {
                            $tag = str_replace('#', '', $tag);
                    ?>
                        <a
                            class="ep-tags__item_new"
                            href="<?php echo $tagLink . strForUrlKeywords((string) $tag); ?>"
                            title="<?php echo capitalWord($tag); ?>"
                            <?php echo addQaUniqueIdentifier('page__b2b__request-tag'); ?>
                        >
                            # <?php echo capitalWord($tag); ?>
                        </a>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

        <?php if ($request['photos']) { ?>
            <section class="b2b-section">
                <div class="b2b-section__heading">
                    <h2 class="b2b-section__title"><?php echo translate('b2b_detail_section_additional_pictures_title'); ?></h2>
                </div>

                <div class="b2b-pictures">
                    <?php foreach ($request['photos'] as $key => $photo) { ?>
                        <div class="b2b-pictures__img-wr">
                            <a
                                class="link fancyboxGallery"
                                data-image-index="<?php echo $key; ?>"
                                data-title="<?php echo cleanOutput($request['b2b_title']); ?>"
                                rel="galleryItem"
                                href="<?php echo $photo['original_url'];?>"
                            >
                                <img
                                    class="b2b-pictures__image js-lazy"
                                    data-src="<?php echo $photo['url']; ?>"
                                    src="<?php echo getLazyImage(133, 100); ?>"
                                    alt="<?php echo cleanOutput($request['b2b_title']); ?>"
                                    width="133"
                                    height="100"
                                    <?php echo addQaUniqueIdentifier('page__b2b__additional-pictures_img'); ?>
                                >
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>

        <?php if (!empty($userItems)) { ?>
            <section class="b2b-section">
                <div class="b2b-section__heading">
                    <h2 class="b2b-section__title"><?php echo translate('b2b_detail_section_added_items_title'); ?></h2>
                    <?php if ($countUserItems > 12) { ?>
                        <a class="b2b-section__link" href="<?php echo getCompanyURL($request['company']) . '/products'; ?>">
                            <?php echo translate('ep_matchmaking_view_more_btn'); ?><?php echo widgetGetSvgIcon('arrowRight', 15, 15); ?>
                        </a>
                    <?php } ?>
                </div>

                <div class="b2b-user-items">
                    <div
                        id="js-b2b-request-products-slider"
                        class="products products--slider-full"
                        data-count-items="<?php echo count($userItems); ?>"
                    >
                        <?php views('new/item/list_item_view', ['items' => $userItems, 'has_hover' => false]);?>
                    </div>
                </div>
            </section>
        <?php } ?>

        <?php if (!empty($request['userRequests'])) { ?>
            <!-- Section Other B2B Requests -->
            <section class="b2b-section b2b-other-request">
                <div class="b2b-section__heading">
                    <h2 class="b2b-section__title"><?php echo translate('b2b_detail_section_other_b2b_title'); ?></h2>
                </div>

                <div class="b2b-other-request__row">
                    <div id="js-other-b2b-requests-wrapper" class="b2b-other-request__items">
                        <?php views('new/b2b/detail/other_requests_view', ['userRequests' => $request['userRequests']]); ?>
                    </div>
                    <?php if ($request['countOtherRequests'] > config('b2b_detail_page_other_b2b_limit', 4)) { ?>
                        <button
                            class="b2b-other-request__btn btn btn-new16 btn-light call-action"
                            data-js-action="b2b-requests:load-more"
                            data-request="<?php echo $request['id_request']; ?>"
                            <?php echo addQaUniqueIdentifier('page__b2b__other-requests_view-more-btn'); ?>
                        >
                            <?php echo translate('b2b_detail_load_more_btn'); ?>
                        </button>
                    <?php } ?>
                </div>
            </section>
            <!-- End Section Other B2B Requests -->
        <?php } ?>

        <?php if (!empty($request['partners'])) { ?>
            <!-- Section Partners -->
            <section class="b2b-section">
                <div class="b2b-section__heading">
                    <h2 class="b2b-section__title">
                        <?php echo translate('b2b_detail_section_partners_title'); ?>
                        <span class="txt-gray" <?php echo addQaUniqueIdentifier('page__b2b__counter'); ?>>
                            <?php echo $request['countPartners']; ?>
                        </span>
                    </h2>
                </div>

                <div class="b2b-partners">
                    <div id="js-b2b-request-partners-wrapper" class="b2b-partners__inner" <?php echo addQaUniqueIdentifier('page__b2b__partners-list'); ?>>
                        <?php views('new/b2b/detail/partners_view', ['partners' => $request['partners']]); ?>
                    </div>

                    <?php if ($request['countPartners'] > config('b2b_detail_page_partners_per_page', 6)) { ?>
                        <div class="b2b-partners__load-more">
                            <button
                                class="b2b-partners__btn btn btn-new16 btn-light call-action"
                                data-js-action="b2b-requests:partners.load-more"
                                data-request="<?php echo $request['id_request']; ?>"
                                <?php echo addQaUniqueIdentifier('page__b2b__partners_load-more-btn'); ?>
                            >
                                <?php echo translate('b2b_detail_load_more_btn'); ?>
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </section>
            <!-- End Section Partners -->
        <?php } ?>

        <?php if (!empty($request['followers'])) { ?>
            <!-- Section Followers -->
            <section class="b2b-section">
                <div class="b2b-section__heading">
                    <h2 class="b2b-section__title">
                        <?php echo translate('b2b_detail_section_followers_title'); ?>
                        <span class="txt-gray" <?php echo addQaUniqueIdentifier('page__b2b__counter'); ?>>
                            <?php echo $request['countFollowers']; ?>
                        </span>
                    </h2>
                </div>

                <div class="b2b-followers">
                    <div id="js-b2b-request-followers-wrapper" class="b2b-followers__inner" <?php echo addQaUniqueIdentifier('page__b2b__followers-list'); ?>>
                        <?php views('new/b2b/detail/followers_view', ['followers' => $request['followers']]); ?>
                    </div>

                    <?php if ($request['countFollowers'] > 8) { ?>
                        <div class="b2b-followers__load-more">
                            <button
                                class="b2b-followers__btn btn btn-new16 btn-light call-action"
                                data-js-action="b2b-requests:followers.load-more"
                                data-request="<?php echo $request['id_request']; ?>"
                                <?php echo addQaUniqueIdentifier('page__b2b__followers_load-more-btn'); ?>
                            >
                                <?php echo translate('b2b_detail_load_more_btn'); ?>
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </section>
            <!-- End Section Followers -->
        <?php } ?>

        <!-- Section Tips and Advice -->
        <section id="advices_section" class="b2b-section">
            <div class="b2b-section__heading">
                <h2 class="b2b-section__title">
                    <?php echo translate('b2b_detail_section_tips_and_advices_title'); ?>
                    <span id="js-b2b-advices-counter" class="txt-gray" <?php echo addQaUniqueIdentifier('page__b2b__counter'); ?>>
                        <?php echo $request['countAdvice']; ?>
                    </span>
                </h2>
                <?php if ($writeAdvice) { ?>
                    <button
                        id="js-btn-add-advice"
                        class="b2b-advices__add-btn btn js-btn-add-advice fancybox.ajax fancyboxValidateModal"
                        data-fancybox-href="<?php echo __SITE_URL;?>b2b/popup_forms/add_advice/<?php echo $request['id_request'];?>"
                        data-title="<?php echo translate('b2b_detail_add_advices_btn'); ?>"
                        title="<?php echo translate('b2b_detail_add_advices_btn'); ?>"
                        type="button"
                        <?php echo addQaUniqueIdentifier('page__b2b__advices_add-btn'); ?>
                    >
                        <i class="ep-icon ep-icon_plus-circle"></i> <?php echo translate('b2b_detail_add_advices_btn'); ?>
                    </button>
                <?php } ?>
            </div>

            <div class="b2b-advices">
                <div id="js-b2b-request-advices-wrapper" class="b2b-advices__inner" <?php echo addQaUniqueIdentifier('page__b2b__advices-list'); ?>>
                    <?php
                        if (!empty($request['advice'])) {
                            views('new/b2b/detail/advices_view', ['advices' => $request['advice'], 'helpful' => $request['helpful']]);
                        } else { ?>
                            <div id="js-b2b-advices-empty-block" class="b2b-advices__empty">
                                <i class="ep-icon ep-icon_info-stroke"></i> <?php echo $writeAdvice ? translate('b2b_detail_company_does_not_have_advice_for_partners') : translate('b2b_detail_company_does_not_have_advice_for_not_partners'); ?>
                            </div>
                        <?php } ?>
                </div>

                <?php if ($request['countAdvice'] > config('b2b_detail_page_advice_per_page', 3)) { ?>
                    <div class="b2b-advices__load-more">
                        <button
                            class="b2b-advices__btn btn btn-new16 btn-light call-action"
                            data-js-action="b2b-requests:advices.load-more"
                            data-request="<?php echo $request['id_request']; ?>"
                            <?php echo addQaUniqueIdentifier('page__b2b__advices_load-more-btn'); ?>
                        >
                            <?php echo translate('b2b_detail_load_more_btn'); ?>
                        </button>
                    </div>
                <?php } ?>
            </div>
        </section>
        <!-- End section Tips and Advice -->
    </div>
</div>

<?php
    encoreEntryLinkTags('b2b_request_detail_page');
    encoreEntryScriptTags('b2b_request_detail_page');
?>
