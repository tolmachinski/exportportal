<div class="community-main-header">
    <picture class="picture-background">
        <source media="(max-width: 425px)"
                srcset="<?php echo asset("public/build/images/community_help/community_header-sm.jpg"); ?>">
        <source media="(max-width: 991px)"
                srcset="<?php echo asset("public/build/images/community_help/community_header-md.jpg"); ?>">
        <img class="image"
            src="<?php echo asset("public/build/images/community_help/community_help_header.jpg"); ?>" alt="Why buy with Export Portal">
    </picture>
    <div class="container-center-sm">
        <?php if(!logged_in()){?>
            <div class="community-main-header__inner">
                <div class="nav-logo">
                    <a href="<?php echo __SITE_URL;?>">
                        <img
                            class="nav-logo__image nav-logo__image--main"
                            src="<?php echo asset("public/build/images/logo/ep_logo.png"); ?>"
                            alt="Export Portal Logo">
                    </a>
                    <div class="nav-logo__divider"></div>
                    <a class="nav-logo__title" href="<?php echo __COMMUNITY_URL;?>"><?php echo translate('community_help_header_title'); ?></a>
                </div>

                <div class="nav-ask">
                    <a
                        class="nav-ask__button fancybox.ajax fancyboxValidateModal call-action"
                        <?php echo addQaUniqueIdentifier('page__community__header_ask-question-btn'); ?>
                    <?php if(logged_in()){?>
                        data-title="<?php echo translate('community_ask_a_question_text', null, true); ?>"
                        data-mw="535"
                        href="<?php echo __COMMUNITY_URL . 'community_questions/popup_forms/add_question'; ?>"
                    <?php } else {?>
                        data-js-action="lazy-loading:login"
                        data-title="<?php echo translate('header_navigation_link_login', null, true); ?>"
                        data-mw="400"
                        href="<?php echo __SITE_URL . 'login'; ?>"
                    <?php } ?>
                    >
                        <span class="nav-ask__button--desktop"><?php echo translate('community_ask_a_question_text'); ?></span>
                        <span class="nav-ask__button--mobile"><?php echo translate('community_ask_a_question_mobile_text'); ?></span>
                    </a>
                </div>
            </div>
        <?php } ?>
        <div>
            <div class="info-title <?php if(logged_in()){echo 'info-title__index';} ?>">
                <h1 class="info-title__heading"><?php echo translate('community_help_center_text'); ?></h1>
                <p class="info-title__subheading"><?php echo translate('community_subheader_info'); ?></p>
            </div>

            <form
                id="js-search-questions"
                class="validengine relative-b"
                data-js-action="form:search_question"
                action="<?php echo __COMMUNITY_ALL_URL; ?>"
            >
                <div class="js-community-search-form community-search-form">
                    <select
                        class="js-type-url"
                        name="category"
                        <?php echo addQaUniqueIdentifier('page__community__main-header_any-category-select'); ?>
                    >
                        <option selected="selected" value=""><?php echo translate('community_questions_sidebar_form_search_option_category'); ?></option>
                        <?php foreach ($quest_cats as $category) { ?>
                            <option value="<?php echo $category['url']; ?>" <?php echo selected($search_category, $category['idcat']) ?>><?php echo $category['title_cat'] ?></option>
                        <?php } ?>
                    </select>

                    <select
                        class="js-type-url"
                        name="country"
                        <?php echo addQaUniqueIdentifier('page__community__main-header_any-country-select'); ?>
                    >
                        <option selected="selected" value=""><?php echo translate('community_questions_sidebar_form_search_option_country'); ?></option>
                            <?php
                                $port_countries = array_map(function($country){
                                    $country['country_url'] = strForURL($country['country'] . ' ' . $country['id']);

                                    return $country;
                                }, $countries);

                                echo getCountrySelectOptions($port_countries, $search_country, array('value' => 'country_url', 'include_default_option' => false));
                            ?>
                    </select>

                    <input
                        class="validate[required, minSize[<?php echo config('help_search_min_keyword_length'); ?>]]"
                        type="text"
                        name="keywords"
                        maxlength="50"
                        <?php if (isset($search_keywords)) { ?>value="<?php echo $search_keywords ?>" <?php } ?>
                        <?php echo addQaUniqueIdentifier('page__community__main-header_keyword-input'); ?>
                        placeholder="<?php echo translate('community_questions_sidebar_form_search_input_keyword_placeholder'); ?>" />

                    <div class="community-search-form__btn">
                        <button class="btn btn-primary btn--50 mnw-155 mnw-240-lg" <?php echo addQaUniqueIdentifier('page__community__main-header_search-btn'); ?> type="submit"><?php echo translate('community_search_button_text'); ?></button>
                    </div>
                </div>
            </form>
        </div>
        <button class="btn btn-primary btn--50 mnw-290 community-main-header__search-btn call-action" data-js-action="form:show_search_form" <?php echo addQaUniqueIdentifier('page__community__main-header_search-question-btn'); ?> type="button">
            <?php echo translate('community_search_a_question_button'); ?>
        </button>
    </div>
</div>
