<form
    id="js-header-search-questions"
    class="community-search-header validengine relative-b"
    data-js-action="form:header_search_question"
    action="<?php echo __COMMUNITY_ALL_URL; ?>"
>
    <div class="container-center-sm">
        <h3 class="community-search-header__title"><?php echo translate('community_search_question_title'); ?></h3>

        <div class="community-search-header__inner">
            <div class="community-search-header__input">
                <select
                    class="js-search-form-input js-type-url"
                    name="category"
                    <?php echo addQaUniqueIdentifier('community__search-form_category-select'); ?>
                >
                    <option selected="selected" value=""><?php echo translate('community_questions_sidebar_form_search_option_category'); ?></option>
                    <?php foreach ($quest_cats as $category) { ?>
                        <option value="<?php echo $category['url']; ?>" <?php echo selected($search_category, $category['idcat']) ?>><?php echo $category['title_cat'] ?></option>
                    <?php } ?>
                </select>
                <a
                    class="reset-btn js-reset-input display-n_i"
                    href="<?php echo $search_params['category']['link']; ?>">
                    <i class="ep-icon ep-icon_remove-stroke "></i>
                </a>
            </div>

            <div class="community-search-header__input">
                <select
                    class="js-search-form-input js-type-url"
                    name="country"
                    <?php echo addQaUniqueIdentifier('community__search-form_country-select'); ?>
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
                <a
                    class="reset-btn js-reset-input display-n_i"
                    href="<?php echo $search_params['country']['link']; ?>">
                    <i class="ep-icon ep-icon_remove-stroke "></i>
                </a>
            </div>

            <div class="community-search-header__input">
                <input
                    class="js-search-form-input validate[required, minSize[<?php echo config('help_search_min_keyword_length'); ?>]]"
                    type="text"
                    name="keywords"
                    maxlength="50"
                    <?php if (isset($search_keywords)) { ?> value="<?php echo $search_keywords ?>" <?php } ?>
                    placeholder="<?php echo translate('community_questions_sidebar_form_search_input_keyword_placeholder'); ?>"
                    <?php echo addQaUniqueIdentifier('community__search-form_keywords-input'); ?>
                />
                <a
                    class="reset-btn js-reset-input display-n_i"
                    href="<?php echo $search_params['keywords']['link']; ?>">
                    <i class="ep-icon ep-icon_remove-stroke "></i>
                </a>
            </div>
            <div class="community-search-header__btn">
                <button
                    class="btn btn-primary btn--50 mnw-155 mnw-240-lg mnw-290-sm"
                    type="submit"
                    <?php echo addQaUniqueIdentifier('page__community__search-form_submit-btn'); ?>
                >
                    <?php echo translate('community_search_button_text'); ?>
                </button>
            </div>
        </div>
    </div>
</form>
