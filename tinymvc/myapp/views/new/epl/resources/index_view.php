<section class="epl-resources-questions-block">
    <div class="container-1420">
        <div class="epl-desc-text-b epl-desc-text-b--mw-650 epl-desc-text-b--tac">
            <h2 class="epl-desc-text-b__ttl">
                <?php echo translate('epl_ff_resources_community_help_title'); ?>
            </h2>
            <p class="epl-desc-text-b__text">
                <?php echo translate('epl_ff_resources_community_help_subtitle'); ?>
            </p>
        </div>

        <ul class="epl-resources-questions">
            <?php foreach ($communityQuestions as $question) { ?>
                <li class="epl-resources-questions__item" <?php echo addQaUniqueIdentifier('global__question'); ?>>
                    <img
                        class="epl-resources-questions__user-img epl-resources-questions__user-img--desktop js-lazy"
                        data-src="<?php echo getDisplayImageLink(['{ID}' => $question['id_user'], '{FILE_NAME}' => $question['user_photo']], 'users.main', ['thumb_size' => 0, 'no_image_group' => $question['user_group']]); ?>"
                        src="<?php echo getLazyImage(75, 75); ?>"
                        alt="<?php echo cleanOutput("{$question['fname']} {$question['lname']}"); ?>"
                        <?php echo addQaUniqueIdentifier('global__question-image'); ?>
                    />
                    <div class="epl-resources-questions__inner">
                        <a
                            class="epl-resources-questions__title"
                            href="<?php echo __COMMUNITY_URL . 'question/' . strForURL($question['title_question']) . '-' . $question['id_question']; ?>"
                            target="_blank"
                            <?php echo addQaUniqueIdentifier('global__question-title'); ?>
                        >
                            <?php echo $question['title_question']; ?>
                        </a>
                        <div class="epl-resources-questions__row">
                            <img
                                class="epl-resources-questions__user-img epl-resources-questions__user-img--mobile js-lazy"
                                data-src="<?php echo getDisplayImageLink(['{ID}' => $question['id_user'], '{FILE_NAME}' => $question['user_photo']], 'users.main', ['thumb_size' => 0, 'no_image_group' => $question['user_group']]); ?>"
                                src="<?php echo getLazyImage(40, 40); ?>"
                                alt="<?php echo cleanOutput("{$question['fname']} {$question['lname']}"); ?>"
                                <?php echo addQaUniqueIdentifier('global__question-image'); ?>
                            />
                            <div class="epl-resources-questions__info">
                                <a
                                    class="epl-resources-questions__user-name"
                                    href="<?php echo __SITE_URL . 'usr/' . strForURL($question['fname'] . ' ' . $question['lname']) . '-' . $question['id_user']; ?>"
                                    target="_blank"
                                    <?php echo addQaUniqueIdentifier('global__question-name'); ?>
                                >
                                    <?php echo cleanOutput("{$question['fname']} {$question['lname']}"); ?>
                                </a>
                                <p class="epl-resources-questions__ask-date" <?php echo addQaUniqueIdentifier('global__question-date'); ?>>
                                    <?php echo translate('epl_ff_resources_community_help_asked_on', [
                                        '{{DATE}}' => getDateFormat($question['date_question'], null, 'F d, Y'),
                                    ]); ?>
                                </p>
                            </div>
                        </div>
                        <p class="epl-resources-questions__description" <?php echo addQaUniqueIdentifier('global__question-text'); ?>>
                            <?php echo cleanOutput($question['text_question']); ?>
                        </p>
                        <div class="epl-resources-questions__row">
                            <p class="epl-resources-questions__category" <?php echo addQaUniqueIdentifier('global__question-type'); ?>>
                                <span class="epl-resources-questions__additional">in</span><?php echo cleanOutput($question['category']['title']); ?>
                            </p>
                            <div class="epl-resources-questions__country">
                                <img
                                    class="epl-resources-questions__country-flag image js-lazy"
                                    data-src="<?php echo getCountryFlag($question['country']); ?>"
                                    src="<?php echo getLazyImage(24, 24); ?>"
                                    alt="<?php echo $question['country']; ?>"
                                    title="<?php echo $question['country']; ?>"
                                    width="24"
                                    height="24"
                                    <?php echo addQaUniqueIdentifier('global__question-country-flag'); ?>
                                />
                                <p class="epl-resources-questions__country-name" <?php echo addQaUniqueIdentifier('global__question-country-name'); ?>>
                                    <?php echo $question['country']; ?>
                                </p>
                            </div>
                            <a
                                class="epl-resources-questions__replies-count"
                                href="<?php echo __COMMUNITY_URL . 'question/' . strForURL($question['title_question']) . '-' . $question['id_question']; ?>"
                                target="_blank"
                                <?php echo addQaUniqueIdentifier('global__question-replies'); ?>
                            >
                                <?php echo translate('epl_ff_resources_community_help_replies', [
                                    '{{COUNT}}' => $question['count_answers'],
                                ]); ?>
                            </a>
                        </div>
                    </div>
                </li>
            <?php } ?>
        </ul>
        <div class="epl-resources-questions__btns">
            <a
                class="btn btn-outline-primary btn-mnw-200"
                href="<?php echo __COMMUNITY_URL; ?>"
                target="_blank"
                <?php echo addQaUniqueIdentifier('epl-resources__community-help-center_more-questions-btn'); ?>
            >
                <?php echo translate('epl_ff_resources_community_help_more_btn'); ?>
            </a>
        </div>
    </div>
</section>

<section class="epl-resources-faq">
    <div class="container-center">
        <div class="epl-desc-text-b epl-desc-text-b--mw-650 epl-desc-text-b--tac">
            <h2 class="epl-desc-text-b__ttl">
                <?php echo translate('epl_ff_resources_faq_title'); ?>
            </h2>
            <p class="epl-desc-text-b__text">
                <?php echo translate('epl_ff_resources_faq_subtitle'); ?>
            </p>
        </div>

        <form
            id="js-faq-search-form"
            class="epl-resources-faq-form"
            method="post"
            action="faq/all"
            autocomplete="off"
        >
            <div class="epl-resources-faq-form__inputs">
                <input autocomplete="off" type="text" class="hidden">
                <input
                    class="epl-resources-faq-form__input"
                    type="text"
                    name="keywords"
                    maxlength="50"
                    placeholder="<?php echo translate('epl_ff_resources_faq_keyword_input_placeholder', null, true); ?>"
                    autocomplete="off"
                    autocapitalize="off"
                    autocorrect="off"
                    autofill="off"
                >
            </div>

            <button class="btn btn-primary epl-resources-faq-form__submit-btn" type="submit">
                <?php echo translate('epl_ff_resources_faq_search_btn'); ?>
            </button>

            <span class="epl-resources-faq-form__delimeter">
                <?php echo translate('epl_ff_resources_faq_search_delimiter'); ?>
            </span>

            <button
                class="btn btn-outline-primary js-fancybox"
                type="button"
                data-type="ajax"
                data-src="<?php echo __CURRENT_SUB_DOMAIN_URL . 'contact/popup_forms/contact_us'; ?>"
                data-title="<?php echo translate('contact_us', null, true); ?>"
                data-wr-class="fancybox-contact-us"
                data-mw="775"
            >
                <?php echo translate('epl_ff_resources_faq_ask_btn'); ?>
            </button>
        </form>

        <div class="epl-desc-text-b epl-desc-text-b--tac">
            <h3 class="epl-desc-text-b__ttl-block">
                <?php echo translate('epl_ff_resources_faq_categories_title'); ?>
            </h3>
        </div>

        <div class="epl-resources-faq__categories">
            <ul class="epl-resources-faq-categories-list">
                <?php foreach ($faqTags as $tag) { ?>
                    <li class="epl-resources-faq-categories-list__item">
                        <a
                            class="epl-resources-faq-categories-list__link"
                            href="<?php echo __SITE_URL . "faq/all/tag/{$tag['slug']}"; ?>"
                            target="_blank"
                        >
                            <h4 class="epl-resources-faq-categories-list__ttl" <?php echo addQaUniqueIdentifier('epl-resources__faq_category-title'); ?>>
                                <?php echo $tag['name']; ?>
                            </h4>
                            <p class="epl-resources-faq-categories-list__count" <?php echo addQaUniqueIdentifier('epl-resources__faq_category-count-questions'); ?>>
                                <?php echo translate('epl_ff_resources_faq_categories_count', [
                                    '{{COUNT}}' => $faqTagsCounters[$tag['id_tag']]['counter'],
                                ]); ?>
                            </p>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <div class="epl-desc-text-b epl-desc-text-b--tac">
            <h3 class="epl-desc-text-b__ttl-block">
                <?php echo translate('epl_ff_resources_faq_popular_questions_title'); ?>
            </h3>
        </div>

        <ul class="epl-faq-list">
            <?php foreach ($faqList as $faqKey => $faqListItem) {?>
                <li class="js-accordion-item epl-faq-list__item" <?php echo addQaUniqueIdentifier("epl-resources__faq-list-btn-{$faqKey}"); ?>>
                    <div class="epl-faq-list__ttl-wrap">
                        <h4 class="epl-faq-list__ttl" <?php echo addQaUniqueIdentifier('epl-resources__faq_popular-questions-title'); ?>>
                            <?php echo $faqListItem['question']; ?>
                        </h4>
                        <div class="epl-faq-list__btn">
                            <?php echo widgetGetSvgIconEpl('plus', 14, 0, 'epl-faq-list__plus'); ?>
                            <?php echo widgetGetSvgIconEpl('minus', 14, 0, 'epl-faq-list__minus'); ?>
                        </div>
                    </div>
                    <div class="epl-faq-list__text ep-tinymce-text js-accordion-text-wr" <?php echo addQaUniqueIdentifier('epl-resources__faq_popular-questions-text'); ?>>
                        <?php echo $faqListItem['answer']; ?>
                    </div>
                </li>
            <?php }?>
        </ul>

        <div class="epl-resources-faq__btns">
            <a
                class="btn btn-outline-primary btn-mnw-200"
                href="<?php echo __SITE_URL . 'faq/all'; ?>"
                target="_blank"
                <?php echo addQaUniqueIdentifier('epl-resources__more-faq-btn'); ?>
            >
                <?php echo translate('epl_ff_resources_faq_more_btn'); ?>
            </a>
        </div>
    </div>
</section>

<section class="epl-resources-guides">
    <div class="container-1420">
        <div class="epl-desc-text-b epl-desc-text-b--mw-650 epl-desc-text-b--tac">
            <h2 class="epl-desc-text-b__ttl">
                <?php echo translate('epl_ff_resources_user_guides_title'); ?>
            </h2>
            <p class="epl-desc-text-b__text">
                <?php echo translate('epl_ff_resources_user_guides_subtitle'); ?>
            </p>
        </div>

        <?php if (!logged_in()) { ?>
        <div class="epl-resources-guide">
            <div class="epl-resources-guide__img">
                <picture class="epl-resources-guide__picture">
                    <source
                        media="(max-width: 575px)"
                        srcset="<?php echo getLazyImage(545, 376); ?>"
                        data-srcset="<?php echo asset('public/build/images/epl/resources/registration_guide_bg_mobile.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 1200px)"
                        srcset="<?php echo getLazyImage(738, 350); ?>"
                        data-srcset="<?php echo asset('public/build/images/epl/resources/registration_guide_bg_tablet.jpg'); ?>"
                    >
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(450, 422); ?>"
                        data-src="<?php echo asset('public/build/images/epl/resources/registration_guide_bg.jpg'); ?>"
                        alt="<?php echo translate('epl_ff_resources_registration_guide_title', null, true); ?>"
                    >
                </picture>
            </div>

            <div class="epl-resources-guide__detail">
                <h3 class="epl-resources-guide__ttl">
                    <?php echo translate('epl_ff_resources_registration_guide_title'); ?>
                </h3>

                <p class="epl-resources-guide__desc">
                    <?php echo translate('epl_ff_resources_registration_guide_desc'); ?>
                </p>

                <div class="epl-resources-guide__info">
                    <h4 class="epl-resources-guide__info-ttl">
                        <?php echo translate('epl_ff_resources_registration_guide_info_list_title'); ?>
                    </h4>

                    <ul class="epl-resources-guide-info-list">
                        <li class="epl-resources-guide-info-list__item">
                            <?php echo translate('epl_ff_resources_registration_guide_info_list_item1'); ?>
                        </li>
                        <li class="epl-resources-guide-info-list__item">
                            <?php echo translate('epl_ff_resources_registration_guide_info_list_item2'); ?>
                        </li>
                        <li class="epl-resources-guide-info-list__item">
                            <?php echo translate('epl_ff_resources_registration_guide_info_list_item3'); ?>
                        </li>
                        <li class="epl-resources-guide-info-list__item">
                            <?php echo translate('epl_ff_resources_registration_guide_info_list_item4'); ?>
                        </li>
                    </ul>
                </div>

                <div class="epl-resources-guide__btns">
                    <button
                        class="btn btn-primary call-action"
                        type="button"
                        data-js-action="epl-resources:download-pdf-guide"
                        data-guide-name="registration"
                        data-lang="en"
                        data-group="all"
                        <?php echo addQaUniqueIdentifier('epl-resources__registration-guide_download-btn'); ?>
                    >
                        <?php echo translate('epl_ff_resources_registration_guide_download_btn'); ?>
                    </button>

                    <button
                        class="btn btn-outline-primary js-fancybox-video"
                        type="button"
                        data-title="<?php echo translate('epl_ff_resources_registration_guide_title', null, true); ?>"
                        data-src="https://youtu.be/vnqwhhBFEhI"
                        <?php echo addQaUniqueIdentifier('epl-resources__registration-guide_video-btn'); ?>
                    >
                        <?php echo translate('epl_ff_resources_registration_guide_video_btn'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php } ?>

        <div class="epl-resources-guide epl-resources-guide--reverse">
            <div class="epl-resources-guide__img">
                <picture class="epl-resources-guide__picture">
                    <source
                        media="(max-width: 575px)"
                        srcset="<?php echo getLazyImage(545, 376); ?>"
                        data-srcset="<?php echo asset('public/build/images/epl/resources/profile_completion_guide_bg_mobile.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 1200px)"
                        srcset="<?php echo getLazyImage(738, 350); ?>"
                        data-srcset="<?php echo asset('public/build/images/epl/resources/profile_completion_guide_bg_tablet.jpg'); ?>"
                    >
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(450, 422); ?>"
                        data-src="<?php echo asset('public/build/images/epl/resources/profile_completion_guide_bg.jpg'); ?>"
                        alt="<?php echo translate('epl_ff_resources_profile_completion_guide_title', null, true); ?>"
                    >
                </picture>
            </div>

            <div class="epl-resources-guide__detail">
                <h3 class="epl-resources-guide__ttl">
                    <?php echo translate('epl_ff_resources_profile_completion_guide_title'); ?>
                </h3>
                <p class="epl-resources-guide__desc">
                    <?php echo translate('epl_ff_resources_profile_completion_guide_desc'); ?>
                </p>

                <div class="epl-resources-guide__info">
                    <h4 class="epl-resources-guide__info-ttl">
                        <?php echo translate('epl_ff_resources_profile_completion_guide_info_list_title'); ?>
                    </h4>

                    <ul class="epl-resources-guide-info-list">
                        <li class="epl-resources-guide-info-list__item">
                            <?php echo translate('epl_ff_resources_profile_completion_guide_info_list_item1'); ?>
                        </li>
                        <li class="epl-resources-guide-info-list__item">
                            <?php echo translate('epl_ff_resources_profile_completion_guide_info_list_item2'); ?>
                        </li>
                        <li class="epl-resources-guide-info-list__item">
                            <?php echo translate('epl_ff_resources_profile_completion_guide_info_list_item3'); ?>
                        </li>
                        <li class="epl-resources-guide-info-list__item">
                            <?php echo translate('epl_ff_resources_profile_completion_guide_info_list_item4'); ?>
                        </li>
                    </ul>
                </div>

                <div class="epl-resources-guide__btns">
                    <button
                        class="btn btn-primary call-action"
                        type="button"
                        data-js-action="epl-resources:download-pdf-guide"
                        data-guide-name="profile_completion"
                        data-lang="en"
                        data-group="all"
                        <?php echo addQaUniqueIdentifier('epl-resources__profile-completion-guide_download-btn'); ?>
                    >
                        <?php echo translate('epl_ff_resources_profile_completion_guide_download_btn'); ?>
                    </button>
                    <button
                        class="btn btn-outline-primary js-fancybox-video"
                        type="button"
                        data-title="<?php echo translate('epl_ff_resources_profile_completion_guide_title', null, true); ?>"
                        data-src="https://youtu.be/ovGKdkdpJ9g"
                        <?php echo addQaUniqueIdentifier('epl-resources__profile-completion_video-btn'); ?>
                    >
                        <?php echo translate('epl_ff_resources_profile_completion_guide_video_btn'); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="epl-resources-guide">
            <div class="epl-resources-guide__img">
                <picture class="epl-resources-guide__picture">
                    <source
                        media="(max-width: 575px)"
                        srcset="<?php echo getLazyImage(545, 376); ?>"
                        data-srcset="<?php echo asset('public/build/images/epl/resources/document_upload_guide_bg_mobile.jpg'); ?>"
                    >
                    <source
                        media="(max-width: 1200px)"
                        srcset="<?php echo getLazyImage(738, 350); ?>"
                        data-srcset="<?php echo asset('public/build/images/epl/resources/document_upload_guide_bg_tablet.jpg'); ?>"
                    >
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(450, 493); ?>"
                        data-src="<?php echo asset('public/build/images/epl/resources/document_upload_guide_bg.jpg'); ?>"
                        alt="<?php echo translate('epl_ff_resources_document_upload_guide_title', null, true); ?>"
                    >
                </picture>
            </div>

            <div class="epl-resources-guide__detail">
                <h3 class="epl-resources-guide__ttl">
                    <?php echo translate('epl_ff_resources_document_upload_guide_title'); ?>
                </h3>

                <p class="epl-resources-guide__desc">
                    <?php echo translate('epl_ff_resources_document_upload_guide_desc'); ?>
                </p>

                <div class="epl-resources-guide__info">
                    <h4 class="epl-resources-guide__info-ttl">
                        <?php echo translate('epl_ff_resources_document_upload_guide_info_list_title'); ?>
                    </h4>

                    <ul class="epl-resources-guide-info-list">
                        <li class="epl-resources-guide-info-list__item">
                            <?php echo translate('epl_ff_resources_document_upload_guide_info_list_item1'); ?>
                        </li>
                        <li class="epl-resources-guide-info-list__item">
                            <?php echo translate('epl_ff_resources_document_upload_guide_info_list_item2'); ?>
                        </li>
                        <li class="epl-resources-guide-info-list__item">
                            <?php echo translate('epl_ff_resources_document_upload_guide_info_list_item3'); ?>
                        </li>
                        <li class="epl-resources-guide-info-list__item">
                            <?php echo translate('epl_ff_resources_document_upload_guide_info_list_item4'); ?>
                        </li>
                    </ul>
                </div>

                <form id="js-epl-resources-document-upload-form" class="epl-resources-guide-form">
                    <div class="epl-resources-guide-form__select form-group">
                        <label class="input-label" for="js-epl-resources-ug-select-lang">
                            <?php echo translate('epl_ff_resources_document_upload_guide_select_lang_label'); ?>
                        </label>
                        <select
                            id="js-epl-resources-ug-select-lang"
                            name="language"
                            <?php echo addQaUniqueIdentifier('epl-resources__document-upload-guide_lang-select'); ?>
                        >
                            <?php foreach ($documentUploadLangs as $k => $lang) { ?>
                                <option value="<?php echo $k; ?>"><?php echo $lang; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <button
                        id="js-document-upload-download-btn"
                        class="epl-resources-guide-form__btn btn btn-primary"
                        type="submit"
                        data-guide-name="document_upload"
                        data-group="freight_forwarder"
                        <?php echo addQaUniqueIdentifier('epl-resources__document-upload-guide_download-btn'); ?>
                    >
                        <?php echo translate('epl_ff_resources_document_upload_guide_download_btn'); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="epl-resources-epu <?php echo logged_in() ? 'footer-connect' : ''; ?>">
    <div class="epl-resources-epu__content container-center">
        <picture class="epl-resources-epu__bg">
            <source
                media="(max-width: 575px)"
                srcset="<?php echo getLazyImage(575, 400); ?>"
                data-srcset="<?php echo asset('public/build/images/epl/resources/epu_bg_mobile.jpg'); ?>"
            >
            <source
                media="(max-width: 768px)"
                srcset="<?php echo getLazyImage(768, 400); ?>"
                data-srcset="<?php echo asset('public/build/images/epl/resources/epu_bg_tablet.jpg'); ?>"
            >
            <img
                class="image js-lazy"
                src="<?php echo getLazyImage(1920, 400); ?>"
                data-src="<?php echo asset('public/build/images/epl/resources/epu_bg.jpg'); ?>"
                alt="<?php echo translate('epl_ff_resources_epu_title'); ?>"
            >
        </picture>
        <div class="epl-desc-text-b epl-desc-text-b--mw-650 epl-desc-text-b--tac epl-desc-text-b--white">
            <h2 class="epl-desc-text-b__ttl"><?php echo translate('epl_ff_resources_epu_title'); ?></h2>
            <p class="epl-desc-text-b__text"><?php echo translate('epl_ff_resources_epu_subtitle'); ?></p>
        </div>
        <a
            class="btn btn-outline-white btn-mnw-200"
            href="<?php echo __SITE_URL . 'landing/university'; ?>"
            target="_blank"
        >
            <?php echo translate('epl_ff_resources_epu_join_btn'); ?>
        </a>
    </div>
</section>

<?php
    if (!logged_in()) {
        views('new/epl/get_started_view');
    }
?>
