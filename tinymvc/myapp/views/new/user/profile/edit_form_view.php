<div
    id="user-preferences--form--wrapper"
    class="wr-modal-flex edit-info-popup"
    style="position:relative;"
    data-form="#user-preferences--form"
    data-navigation="#user-preferences--form--navigation"
>
    <div class="modal-flex__form">
        <ul id="user-preferences--form--navigation" class="nav tabs-circle tabs-circle--no-click" role="tablist">
            <li class="tabs-circle__item">
                <a
                    role="tab"
                    class="link active"
                    href="#user-preferences--form--navigation--step-1"
                    aria-controls="title"
                    data-toggle="tab"
                >
                    <div class="tabs-circle__point">
                        <i class="ep-icon ep-icon_ok-stroke2"></i>
                    </div>

                    <div class="tabs-circle__txt">
                        <?php echo translate('user_preferences_profile_edit_form_information_tab_title', null, true); ?>
                    </div>
                </a>
            </li>

            <li class="tabs-circle__item">
                <a
                    role="tab"
                    class="link"
                    href="#user-preferences--form--navigation--step-2"
                    aria-controls="title"
                    data-toggle="tab"
                >
                    <div class="tabs-circle__point">
                        <i class="ep-icon ep-icon_ok-stroke2"></i>
                    </div>

                    <div class="tabs-circle__txt">
                        <?php echo translate('user_preferences_profile_edit_form_address_tab_title', null, true); ?>
                    </div>
                </a>
            </li>

            <li class="tabs-circle__item">
                <a
                    role="tab"
                    class="link"
                    href="#user-preferences--form--navigation--step-3"
                    aria-controls="title"
                    data-toggle="tab"
                >
                    <div class="tabs-circle__point">
                        <i class="ep-icon ep-icon_ok-stroke2"></i>
                    </div>

                    <div class="tabs-circle__txt">
                        <?php echo translate('user_preferences_profile_edit_form_confirmation_tab_title', null, true); ?>
                    </div>
                </a>
            </li>
        </ul>

        <form
            id="user-preferences--form"
            class="edit-info-popup__form validateModal inputs-40"
            autocomplete="off"
            data-js-action="user:profile-form.submit"
        >
            <div class="tab-content">
                <div
                    id="user-preferences--form--navigation--step-1"
                    role="tabpanel"
                    class="tab-pane fade show active"
                    <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__step-1'); ?>
                >
                    <label class="ep-label ep-label--popup ep-label--required">
                        <?php echo translate('user_preferences_edit_popup_first-name_label'); ?>
                    </label>
                    <input
                        id="user-preferences--form-field--first-name"
                        type="text"
                        name="first_name"
                        class="ep-input ep-input--popup validate[required,custom[validUserName],minSize[2],maxSize[50]]"
                        value="<?php echo cleanOutput($profile['firstName']); ?>"
                        placeholder="<?php echo translate('user_preferences_profile_edit_form_first_name_input_placeholder', null, true); ?>"
                        <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__form-field__first-name-input'); ?>
                    >

                    <label class="ep-label ep-label--popup ep-label--required">
                        <?php echo translate('user_preferences_edit_popup_last-name_label'); ?>
                    </label>
                    <input
                        id="user-preferences--form-field--last-name"
                        type="text"
                        name="last_name"
                        class="ep-input ep-input--popup validate[required,custom[validUserName],minSize[2],maxSize[50]]"
                        value="<?php echo cleanOutput($profile['lastName']); ?>"
                        placeholder="<?php echo translate('user_preferences_profile_edit_form_last_name_input_placeholder', null, true); ?>"
                        <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__form-field__last-name-input'); ?>
                    >
                    <div>
                        <label class="edit-info-popup__legal-checkbox custom-checkbox" <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__form-field__legal-name-checkbox'); ?>>
                            <input
                                id="js-user-preferences-legal-name-checkbox"
                                name="has_legal_name"
                                type="checkbox"
                                data-js-action="user:profile-form.toggle-legal-name"
                                data-target="#user-preferences--form-field--legal-name--container"
                                <?php if (!empty($profile['legalName'])) { ?>checked<?php } ?>
                                <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__form-field__legal-name-input-checkbox'); ?>
                            >
                            <span class="custom-checkbox__text"><?php echo translate('user_preferences_profile_edit_form_legal_name_checkbox_label', null, true); ?></span>
                        </label>
                    </div>

                    <div
                        id="user-preferences--form-field--legal-name--container"
                        <?php if (empty($profile['legalName'])) { ?>style="display: none;"<?php } ?>
                    >
                        <label class="ep-label ep-label--popup ep-label--required">
                            <?php echo translate('user_preferences_edit_popup_legal-name_label'); ?>
                        </label>
                        <input
                            id="user-preferences--form-field--legal-name"
                            <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__form-field__legal-name-input'); ?>
                            class="ep-input ep-input--popup validate[required,minSize[2],maxSize[100]]"
                            type="text"
                            name="legal_name"
                            value="<?php echo cleanOutput($profile['legalName']); ?>"
                            placeholder="<?php echo translate('user_preferences_profile_edit_form_legal_name_input_placeholder', null, true); ?>"
                        >
                    </div>

                    <label class="ep-label ep-label--popup ep-label--required">
                        <?php echo translate('user_preferences_edit_popup_phone_label'); ?>
                    </label>
                    <div
                        id="user-preferences--form-field--phone-codes--container"
                        class="input-group js-select-phone-codes-lazy-block"
                        data-list-field="#user-preferences--form-field--phone-codes"
                        data-number-field="#user-preferences--form-field--phone-number"
                        data-parent=".fancybox-wrap"
                        <?php if (!empty($profile['phone']['selected'])) { ?>
                        data-lazy-placeholder="#user-preferences--form-field--phone-codes--lazy-placeholder"
                        data-lazy="true"
                        <?php } ?>
                    >
                        <div
                            class="input-group-prepend wr-select2-h50 select-country-code-group"
                            <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__form-field__phone-code-input'); ?>
                        >
                            <div class="notranslate">
                                <select
                                    id="user-preferences--form-field--phone-codes"
                                    name="phone_code"
                                    class="js-contact-country-code ep-select ep-select--popup select-country-code validate[required] select2-hidden-accessible"
                                    data-validation-type="phoneCodeListType"
                                    data-validation-rules="validate[required]"
                                    data-validation-container="user-preferences--form-field--phone-codes--container"
                                    data-override-location="true"
                                >
                                    <?php foreach ($profile['phone']['codeList'] as $phoneCode) { ?>
                                        <option
                                            value="<?php echo cleanOutput($phoneCode['id']); ?>"
                                            data-phone-mask="<?php echo cleanOutput($phoneCode['mask']); ?>"
                                            data-country-flag="<?php echo cleanOutput($phoneCode['countryFlag']); ?>"
                                            data-country-name="<?php echo cleanOutput($phoneCode['countryName']); ?>"
                                            data-country="<?php echo cleanOutput($phoneCode['countryId']); ?>"
                                            data-code="<?php echo cleanOutput($phoneCode['name']); ?>"
                                            <?php if ($phoneCode['selected']) { ?>selected<?php } ?>
                                        >
                                            <?php echo cleanOutput(trim("{$phoneCode['name']} {$phoneCode['countryName']}")); ?>
                                        </option>
                                    <?php } ?>
                                </select>

                                <?php if (!empty($profile['phone']['selected'])) { ?>
                                    <span
                                        id="user-preferences--form-field--phone-codes--lazy-placeholder"
                                        class="select2 select2-container select2-container--default call-action"
                                        style="width: auto;"
                                        dir="ltr"
                                    >
                                        <span class="selection">
                                            <span class="select2-selection select2-selection--single" tabindex="0">
                                                <span
                                                    class="select2-selection__rendered"
                                                    title="<?php echo cleanOutput(trim("{$profile['phone']['selected']['name']} {$profile['phone']['selected']['countryName']}")); ?>"
                                                >
                                                    <img
                                                        class="select-country-flag"
                                                        src="<?php echo cleanOutput($profile['phone']['selected']['countryFlag']); ?>"
                                                        alt="<?php echo cleanOutput($profile['phone']['selected']['countryName']); ?>"
                                                        width="32"
                                                        height="32"
                                                    >
                                                    <span><?php echo cleanOutput($profile['phone']['selected']['name']); ?></span>
                                                </span>
                                                <span class="select2-selection__arrow" role="presentation">
                                                    <b role="presentation"></b>
                                                </span>
                                            </span>
                                        </span>
                                        <span class="dropdown-wrapper" aria-hidden="true"></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>

                        <input
                            id="user-preferences--form-field--phone-number"
                            type="tel"
                            name="phone"
                            class="ep-input ep-input--popup validate[required]"
                            value="<?php echo cleanOutput($profile['phone']['number']); ?>"
                            maxlength="25"
                            placeholder="<?php echo translate('user_preferences_edit_popup_phone_placeholder'); ?>"
                            data-current-mask="<?php echo cleanOutput($profile['phone']['selected']['mask'] ?? null); ?>"
                            <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__form-field__phone-input'); ?>
                        >
                    </div>

                    <label class="ep-label ep-label--popup">
                        <?php echo translate('user_preferences_edit_popup_fax_label'); ?>
                    </label>
                    <div
                        id="user-preferences--form-field--fax-codes--container"
                        class="js-select-phone-codes-lazy-block input-group"
                        data-list-field="#user-preferences--form-field--fax-codes"
                        data-number-field="#user-preferences--form-field--fax-number"
                        data-parent=".fancybox-wrap"
                        <?php if (!empty($profile['fax']['selected'])) { ?>
                        data-lazy-placeholder="#user-preferences--form-field--fax-codes--lazy-placeholder"
                        data-lazy="true"
                        <?php } ?>
                    >
                        <div
                            class="input-group-prepend wr-select2-h50 select-country-code-group"
                            <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__form-field__fax-code-select'); ?>
                        >
                            <div class="notranslate">
                                <select
                                    id="user-preferences--form-field--fax-codes"
                                    name="fax_code"
                                    class="js-contact-country-code select-country-code validate[required] ep-select select2-hidden-accessible"
                                    data-validation-type="faxCodeListType"
                                    data-validation-rules="validate[required]"
                                    data-validation-container="user-preferences--form-field--fax-codes--container"
                                    ata-override-location="false"
                                >
                                    <?php foreach ($profile['fax']['codeList'] as $faxCode) { ?>
                                        <option
                                            value="<?php echo cleanOutput($faxCode['id']); ?>"
                                            data-phone-mask="<?php echo cleanOutput($faxCode['mask']); ?>"
                                            data-country-flag="<?php echo cleanOutput($faxCode['countryFlag']); ?>"
                                            data-country-name="<?php echo cleanOutput($faxCode['countryName']); ?>"
                                            data-country="<?php echo cleanOutput($faxCode['countryId']); ?>"
                                            data-code="<?php echo cleanOutput($faxCode['name']); ?>"
                                            <?php if ($faxCode['selected']) { ?>selected<?php } ?>
                                        >
                                            <?php echo cleanOutput(trim("{$faxCode['name']} {$faxCode['countryName']}")); ?>
                                        </option>
                                    <?php } ?>
                                </select>

                                <?php if (!empty($profile['fax']['selected'])) { ?>
                                    <span
                                        id="user-preferences--form-field--fax-codes--lazy-placeholder"
                                        class="select2 select2-container select2-container--default call-action"
                                        style="width: auto;"
                                        dir="ltr"
                                    >
                                        <span class="selection">
                                            <span class="select2-selection select2-selection--single" tabindex="0">
                                                <span
                                                    class="select2-selection__rendered"
                                                    title="<?php echo cleanOutput(trim("{$profile['fax']['selected']['name']} {$profile['fax']['selected']['countryName']}")); ?>"
                                                >
                                                    <img
                                                        class="select-country-flag"
                                                        src="<?php echo cleanOutput($profile['fax']['selected']['countryFlag']); ?>"
                                                        alt="<?php echo cleanOutput($profile['fax']['selected']['countryName']); ?>"
                                                        width="32"
                                                        height="32"
                                                    >
                                                    <span><?php echo cleanOutput($profile['fax']['selected']['name']); ?></span>
                                                </span>
                                                <span class="select2-selection__arrow" role="presentation">
                                                    <b role="presentation"></b>
                                                </span>
                                            </span>
                                        </span>
                                        <span class="dropdown-wrapper" aria-hidden="true"></span>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>

                        <input
                            id="user-preferences--form-field--fax-number"
                            type="tel"
                            name="fax"
                            class="ep-input ep-input--popup"
                            value="<?php echo cleanOutput($profile['fax']['number']); ?>"
                            maxlength="25"
                            placeholder="<?php echo translate('user_preferences_edit_popup_fax_placeholder'); ?>"
                            data-current-mask="<?php echo cleanOutput($profile['fax']['selected']['mask'] ?? null); ?>"
                            <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__form-field__fax-input'); ?>
                        >
                    </div>

                    <div class="edit-info-popup-actions">
                        <div class="edit-info-popup-actions__right">
                            <button
                                type="button"
                                class="btn btn-primary call-action"
                                data-step="validate_step_1_all"
                                data-js-action="user:profile-form.next-step"
                                <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__next-btn'); ?>
                            >
                                <?php echo translate('user_preferences_edit_popup_next_btn'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- END tab 1 -->

                <div
                    id="user-preferences--form--navigation--step-2"
                    role="tabpanel"
                    class="tab-pane fade js-location-block"
                    data-countries="#user-preferences--form-field--countries"
                    data-states="#user-preferences--form-field--states"
                    data-cities="#user-preferences--form-field--cities"
                    <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__step-2'); ?>
                >
                    <div id="user-preferences--form-field--countries--container">
                        <label class="ep-label ep-label--popup ep-label--required">
                            <?php echo translate('user_preferences_edit_popup_country_label'); ?>
                        </label>
                        <select
                            id="user-preferences--form-field--countries"
                            name="country"
                            class="ep-select ep-select--popup validate[required]"
                            <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__form-field__country-select'); ?>
                        >
                            <?php echo getCountrySelectOptions($profile['location']['countries'] ?? [], $profile['location']['selectedCountry'] ?? 0); ?>
                        </select>
                    </div>

                    <div id="user-preferences--form-field--states--container">
                        <label class="ep-label ep-label--popup ep-label--required">
                            <?php echo translate('user_preferences_edit_popup_state_label'); ?>
                        </label>
                        <div class="notranslate">
                            <select
                                id="user-preferences--form-field--states"
                                name="region"
                                class="ep-select ep-select--popup validate[required]"
                                <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__form-field__state-select'); ?>
                            >
                                <option value="" disabled>
                                    <?php echo translate('user_preferences_profile_edit_form_state_select_placeholder', null, true); ?>
                                </option>
                                <?php foreach ($profile['location']['states'] as $state) { ?>
                                    <option value="<?php echo cleanOutput($state['id']); ?>" <?php if ($state['selected']) { ?>selected<?php } ?>>
                                        <?php echo cleanOutput($state['name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div id="user-preferences--form-field--cities--container" class="wr-select2-h50">
                        <label class="ep-label ep-label--popup ep-label--required">
                            <?php echo translate('user_preferences_edit_popup_city_label'); ?>
                        </label>
                        <div>
                            <select
                                id="user-preferences--form-field--cities"
                                name="city"
                                class="ep-select ep-select--popup select-city"
                                <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__form-field__city-select'); ?>
                            >
                                <option value="">
                                    <?php echo translate('user_preferences_profile_edit_form_city_select_placeholder', null, true); ?>
                                </option>
                                <?php if (null !== $profile['location']['city']) { ?>
                                    <option value="<?php echo cleanOutput($profile['location']['city']['id']); ?>" selected>
                                        <?php echo cleanOutput($profile['location']['city']['city']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <label class="ep-label ep-label--popup ep-label--required">
                        <?php echo translate('user_preferences_edit_popup_address_label'); ?>
                    </label>
                    <input
                        type="text"
                        name="address"
                        class="ep-input ep-input--popup validate[required,minSize[3],maxSize[255]]"
                        value="<?php echo cleanOutput($profile['location']['address']); ?>"
                        placeholder="<?php echo translate('user_preferences_profile_edit_form_address_input_placeholder', null, true); ?>"
                        <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__form-field__address-input'); ?>
                    >

                    <label class="ep-label ep-label--popup ep-label--required">
                        <?php echo translate('user_preferences_edit_popup_zip_label'); ?>
                    </label>
                    <input
                        type="text"
                        name="postal_code"
                        class="ep-input ep-input--popup validate[required,custom[zip_code],maxSize[20]]"
                        value="<?php echo cleanOutput($profile['location']['postalCode']); ?>"
                        maxlength="20"
                        placeholder="<?php echo translate('user_preferences_profile_edit_form_postal_code_input_placeholder', null, true); ?>"
                        <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__form-field__zip-input'); ?>
                    >

                    <div class="edit-info-popup-actions">
                        <div class="edit-info-popup-actions__left">
                            <button
                                type="button"
                                class="btn btn-dark call-action"
                                data-js-action="user:profile-form.previous-step"
                                <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__back-btn'); ?>
                            >
                                <?php echo translate('user_preferences_edit_popup_back_btn'); ?>
                            </button>
                        </div>

                        <div class="edit-info-popup-actions__right">
                            <button
                                type="button"
                                class="btn btn-primary call-action"
                                data-step="validate_step_2"
                                data-js-action="user:profile-form.next-step"
                                <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__next-btn'); ?>
                            >
                                <?php echo translate('user_preferences_edit_popup_next_btn'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- END tab 2 -->

                <div
                    id="user-preferences--form--navigation--step-3"
                    role="tabpanel"
                    class="tab-pane fade"
                    <?php echo addQaUniqueIdentifier("user-preferences__edit-profile-form__step-3"); ?>
                >
                    <?php if (!$canRequestEdit) { ?>
                        <div class="edit-info-popup__row">
                            <div class="info-alert-b">
                                <i class="ep-icon ep-icon_info-stroke"></i>
                                <span><?php echo translate('user_preferences_edit_popup_not-verified_text'); ?></span>

                            </div>
                        </div>
                    <?php } else { ?>
                        <label class="ep-label ep-label--mb-3 ep-label--popup edit-info-popup__label--mb-3 ep-label--required">
                            <?php echo translate('user_preferences_edit_popup_attachments_label'); ?>
                        </label>
                        <p class="edit-info-popup__text">
                            <?php echo translate('user_preferences_edit_popup_attachments_text'); ?>
                        </p>

                        <?php foreach ($documents as $document) { ?>
                            <div class="edit-info-popup__documents">
                                <div>
                                    <span class="edit-info-popup__documents-text">
                                        <?php echo cleanOutput($document['title'] ?? translate('user_preferences_profile_edit_form_documents_unknown_title')); ?>
                                    </span>
                                    <a
                                        class="info-dialog ep-icon ep-icon_info edit-info-section__info"
                                        data-message="<?php echo cleanOutput($document['description']); ?>"
                                        data-title="<?php echo cleanOutput($document['title'] ?? translate('user_preferences_profile_edit_form_documents_unknown_title')); ?>"
                                        title="<?php echo translate(
                                            'user_preferences_profile_edit_form_documents_entry_label',
                                            ['[[TITLE]]' => $document['title'] ?? translate('user_preferences_profile_edit_form_documents_unknown_title')],
                                            true
                                        ); ?>"
                                        href="#">
                                    </a>
                                </div>
                                <div role="group" class="btn-group edit-info-popup__buttons js-button-group">
                                    <a
                                        class="btn btn-primary mnw-130 js-button-upload call-action"
                                        data-js-action="user:profile-form.add-document"
                                        data-document="<?php echo cleanOutput($document['id']); ?>"
                                        data-title="<?php echo translate('personal_edit_upload_the_document', null, true); ?>"
                                        data-url="<?php echo cleanOutput($document['url']); ?>"
                                        title="<?php echo translate('personal_edit_upload_the_document', null, true); ?>"
                                        <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__form-field__upload-file-btn'); ?>
                                    >
                                        <?php echo translate('user_preferences_edit_popup_upload-file_btn'); ?>
                                    </a>

                                    <span class="lh-40 mnw-130 tac bg-gray-lighter js-label-upload display-n">
                                        <?php echo translate('user_preferences_edit_popup_uploaded_btn'); ?>
                                    </span>

                                    <a
                                        class="btn btn-dark js-button-remove call-action display-n"
                                        data-js-action="user:profile-form.remove-document"
                                        title="<?php echo translate('user_preferences_profile_edit_form_delete_file_button_title', null, true); ?>"
                                        <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__form-field__delete-file-btn'); ?>
                                    >
                                        <i class="ep-icon ep-icon_trash-stroke"></i>
                                    </a>
                                </div>
                            </div>
                        <?php } ?>

                        <label class="ep-label ep-label--popup ep-label--required">
                            <?php echo translate('user_preferences_edit_popup_reason_for_change'); ?>
                        </label>
                        <textarea
                            id="user-preferences--form-field--reason"
                            name="reason"
                            class="h-93 validate[required,maxSize[500]] textcounter-popup edit-info-section__textarea js-reason-counter"
                            data-max="500"
                            placeholder="<?php echo translate('user_preferences_profile_edit_form_reason_input_placeholder', null, true); ?>"
                            <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__form-field__textarea'); ?>
                        ></textarea>
                    <?php } ?>

                    <div class="edit-info-popup-actions">
                        <div class="edit-info-popup-actions__left">
                            <button
                                type="button"
                                class="btn btn-dark call-action"
                                data-js-action="user:profile-form.previous-step"
                                <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__back-btn'); ?>
                            >
                                <?php echo translate('user_preferences_edit_popup_back_btn'); ?>
                            </button>
                        </div>

                        <div class="edit-info-popup-actions__right">
                            <button
                                type="submit"
                                class="btn btn-primary call-action"
                                data-js-action="user:profile-form.validate-step"
                                <?php echo addQaUniqueIdentifier('user-preferences__edit-profile-form__form-field__submit-btn'); ?>
                            >
                                <?php echo translate('user_preferences_edit_popup_submit_btn'); ?>
                            </button>
                        </div>
                    </div>
                    <!-- END tab 3 -->
                </div>
            </div>
        </form>
    </div>
</div>

<?php echo dispatchDynamicFragment('lazy-loading:personal-edit', ["#user-preferences--form--wrapper", ['url' => $editUrl]]); ?>
