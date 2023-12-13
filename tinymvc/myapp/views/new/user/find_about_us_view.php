<div
    id="<?php echo $wrapperId; ?>"
    class="edit-info-section__row"
    data-target="#<?php echo $fieldId; ?>"
    data-sources=".js-sources-block"
    data-field-name="find_info"
>
    <label class="input-label input-label--required">
        <?php echo translate('pre_registration_page_register_form_label_find_us'); ?>
    </label>
    <select
        id="<?php echo $fieldId; ?>"
        name="find_type"
        class="validate[required] js-source-type"
        <?php echo addQaUniqueIdentifier("preferences__find-about-us__find-type-select")?>
    >
        <option
            value="<?php echo App\Common\Contracts\User\UserSourceType::NONE(); ?>"
            disabled
            selected>
            <?php echo translate('pre_registration_page_register_form_label_find_us_select_option'); ?>
        </option>
        <option
            value="<?php echo App\Common\Contracts\User\UserSourceType::EMAIL(); ?>"
            data-label="<?php echo translate('pre_registration_page_register_form_label_find_us_option_email', null, true); ?>"
            data-placeholder="<?php echo translate('pre_registration_page_register_form_label_find_us_option_email', null, true); ?>">
            <?php echo translate('pre_registration_page_register_form_label_find_us_option_email'); ?>
        </option>
        <option
            value="<?php echo App\Common\Contracts\User\UserSourceType::EVENT(); ?>"
            data-label="<?php echo translate('pre_registration_page_register_form_label_find_us_option_event', null, true); ?>"
            data-target=".js-referral-name"
            data-placeholder="<?php echo translate('pre_registration_page_register_form_label_find_us_option_event', null, true); ?>">
            <?php echo translate('pre_registration_page_register_form_label_find_us_option_event'); ?>
        </option>
        <option
            value="<?php echo App\Common\Contracts\User\UserSourceType::PRESS_RELEASES(); ?>"
            data-label="<?php echo translate('pre_registration_page_register_form_label_find_us_label_press_releases', null, true); ?>"
            data-target=".js-referral-name"
            data-placeholder="<?php echo translate('pre_registration_page_register_form_label_find_us_placeholder_press_releases', null, true); ?>">
            <?php echo translate('pre_registration_page_register_form_label_find_us_option_press_releases'); ?>
        </option>
        <option
            value="<?php echo App\Common\Contracts\User\UserSourceType::SEARCH_ENGINES(); ?>"
            data-label="<?php echo translate('pre_registration_page_register_form_label_find_us_option_search_engines', null, true); ?>"
            data-target=".js-search-engines"
            data-placeholder="<?php echo translate('pre_registration_page_register_form_label_find_us_option_search_engines', null, true); ?>">
            <?php echo translate('pre_registration_page_register_form_label_find_us_option_search_engines'); ?>
        </option>
        <option
            value="<?php echo App\Common\Contracts\User\UserSourceType::SOCIAL_MEDIA(); ?>"
            data-label="<?php echo translate('pre_registration_page_register_form_label_find_us_option_social_media', null, true); ?>"
            data-target=".js-social-platforms"
            data-placeholder="<?php echo translate('pre_registration_page_register_form_label_find_us_option_social_media', null, true); ?>">
            <?php echo translate('pre_registration_page_register_form_label_find_us_option_social_media'); ?>
        </option>
        <option
            value="<?php echo App\Common\Contracts\User\UserSourceType::CA_IA_REFERRAL(); ?>"
            data-label="<?php echo translate('pre_registration_page_register_form_label_find_us_option_other', null, true); ?>"
            data-target=".js-referral-name"
            data-placeholder="<?php echo translate('pre_registration_page_register_form_label_find_us_option_other', null, true); ?>">
            <?php echo translate('pre_registration_page_register_form_label_find_us_option_other'); ?>
        </option>
    </select>
    <div class="js-sources-block">
        <select
            class="mt-20 validate[required] js-search-engines"
            style="display: none;"
            <?php echo addQaUniqueIdentifier("preferences__find-about-us__search-engines-select")?>
        >
            <option value="google">Google</option>
            <option value="yahoo">Yahoo</option>
            <option value="bing">Bing</option>
            <option value="other" data-target=".js-other-source">Other</option>
        </select>
        <select
            class="mt-20 validate[required] js-social-platforms"
            style="display: none;"
            <?php echo addQaUniqueIdentifier("preferences__find-about-us__socials-select")?>
        >
            <option value="facebook">Facebook</option>
            <option value="linkedin">LinkedIn</option>
            <option value="instagram">Instagram</option>
            <option value="reddit">Reddit</option>
            <option value="twitter">Twitter</option>
            <option value="vk">VK</option>
            <option value="youtube">YouTube</option>
            <option value="quora">Quora</option>
            <option value="whatsapp">WhatsApp</option>
            <option value="other" data-target=".js-other-source">Other</option>
        </select>
        <input
            type="text"
            class="mt-20 validate[required,maxSize[500]] js-referral-name"
            style="display: none;"
            maxlength="500"
            <?php echo addQaUniqueIdentifier("preferences__find-about-referral-input")?>>
        <input
            type="text"
            class="mt-15 validate[required,maxSize[500]] js-other-source"
            style="display: none;"
            maxlength="500"
            placeholder="<?php echo translate('pre_registration_page_register_form_label_find_us_select_other_option'); ?>"
            <?php echo addQaUniqueIdentifier("preferences__find-about-us__other-input")?>>
    </div>
</div>
