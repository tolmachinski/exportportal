<div
    id="company-edit--form--wrapper"
    class="wr-modal-flex edit-info-popup"
    style="position:relative;"
    data-form="#company-edit--form"
    data-navigation="#company-edit--form--navigation"
    <?php echo addQaUniqueIdentifier('popup__company-edit__wrapper'); ?>
>
    <div class="modal-flex__form">
        <ul
            id="company-edit--form--navigation"
            role="tablist"
            class="nav tabs-circle tabs-circle--no-click"
            <?php echo addQaUniqueIdentifier('popup__company-edit__navigation'); ?>
        >
            <li class="tabs-circle__item">
                <a
                    role="tab"
                    class="link active"
                    href="#company-edit--form--navigation--step-1"
                    aria-controls="title"
                    data-toggle="tab"
                    <?php echo addQaUniqueIdentifier('popup__company-edit__navigation_link-information'); ?>
                >
                    <div class="tabs-circle__point">
                        <i class="ep-icon ep-icon_ok-stroke2"></i>
                    </div>

                    <div class="tabs-circle__txt">
                        <?php echo translate('company_info_edit_popup_information_tab_title', null, true); ?>
                    </div>
                </a>
            </li>

            <li class="tabs-circle__item">
                <a
                    role="tab"
                    class="link"
                    href="#company-edit--form--navigation--step-2"
                    aria-controls="title"
                    data-toggle="tab"
                    <?php echo addQaUniqueIdentifier('popup__company-edit__navigation_link-address'); ?>
                >
                    <div class="tabs-circle__point">
                        <i class="ep-icon ep-icon_ok-stroke2"></i>
                    </div>

                    <div class="tabs-circle__txt">
                        <?php echo translate('company_info_edit_popup_address_tab_title', null, true); ?>
                    </div>
                </a>
            </li>

            <li class="tabs-circle__item">
                <a
                    role="tab"
                    class="link"
                    href="#company-edit--form--navigation--step-3"
                    aria-controls="title"
                    data-toggle="tab"
                    <?php echo addQaUniqueIdentifier('popup__company-edit__navigation_confirmation-address'); ?>
                >
                    <div class="tabs-circle__point">
                        <i class="ep-icon ep-icon_ok-stroke2"></i>
                    </div>

                    <div class="tabs-circle__txt">
                        <?php echo translate('company_info_edit_popup_confirmation_tab_title', null, true); ?>
                    </div>
                </a>
            </li>
        </ul>

        <form
            id="company-edit--form"
            class="edit-info-popup__form validateModal inputs-40 js-ep-self-autotrack"
            autocomplete="off"
            data-js-action="company:edit-form.submit"
            data-tracking-events="submit"
            <?php echo addQaUniqueIdentifier('popup__company-edit__form'); ?>
        >
            <div class="tab-content">
                <div
                    id="company-edit--form--navigation--step-1"
                    role="tabpanel"
                    class="tab-pane fade show active"
                    <?php echo addQaUniqueIdentifier('popup__company-edit__form_navigation-step-one'); ?>
                >
                    <label class="ep-label ep-label--popup ep-label--required">
                        <?php echo translate('company_info_edit_popup_company_name_label'); ?>
                    </label>
                    <input
                        id="company-edit--form-field--legal-name"
                        type="text"
                        name="legal_name"
                        class="ep-input ep-input--popup validate[required,custom[companyTitle],minSize[3],maxSize[50]]"
                        value="<?php echo cleanOutput($company['legalName']); ?>"
                        placeholder="<?php echo translate('company_info_edit_popup_legal_name_input_placeholder', null, true); ?>"
                        <?php echo addQaUniqueIdentifier('popup__company-edit__form_legal-name-input'); ?>
                    >

                    <label class="ep-label ep-label--popup ep-label--required">
                        <?php echo translate('company_info_edit_popup_display_company_name_label'); ?>
                    </label>
                    <input
                        id="company-edit--form-field--display-name"
                        type="text"
                        name="display_name"
                        class="ep-input ep-input--popup validate[required,custom[companyTitle],minSize[3],maxSize[50]]"
                        value="<?php echo cleanOutput($company['displayName']); ?>"
                        placeholder="<?php echo translate('company_info_edit_popup_display_name_input_placeholder', null, true); ?>"
                        <?php echo addQaUniqueIdentifier('popup__company-edit__form_display-name-input'); ?>
                    >

                    <label class="ep-label ep-label--popup ep-label--required">
                        <?php echo translate('company_info_edit_popup_type_label'); ?>
                    </label>
                    <div class="input-group">
                        <select
                            id="company-edit--form-field--type"
                            name="type"
                            class="validate[required]"
                            <?php echo addQaUniqueIdentifier('popup__company-edit__form_type-select'); ?>
                        >
                            <option selected disabled>
                                <?php echo translate('company_info_edit_popup_type_option_placeholder', null, true); ?>
                            </option>
                            <?php foreach ($company['types'] as $type) { ?>
                                <option value="<?php echo cleanOutput($type['id']); ?>" <?php if ($type['selected']) { ?>selected<?php } ?>>
                                    <?php echo cleanOutput($type['name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <label class="ep-label ep-label--popup ep-label--required">
                        <?php echo translate('company_info_edit_popup_phone_label'); ?>
                    </label>
                    <div
                        id="company-edit--form-field--phone-codes--container"
                        class="input-group js-select-phone-codes-lazy-block"
                        data-parent=".fancybox-wrap"
                        data-list-field="#company-edit--form-field--phone-codes"
                        data-number-field="#company-edit--form-field--phone-number"
                        <?php if (!empty($company['phone']['selected'])) { ?>
                        data-lazy-placeholder="#company-edit--form-field--phone-codes--lazy-placeholder"
                        data-lazy="true"
                        <?php } ?>
                    >
                        <div
                            class="input-group-prepend wr-select2-h50 select-country-code-group"
                            <?php echo addQaUniqueIdentifier('popup__company-edit__form_phone-code-wrapper'); ?>
                        >
                            <div class="notranslate">
                                <select
                                    id="company-edit--form-field--phone-codes"
                                    name="phone_code"
                                    class="js-contact-country-code ep-select ep-select--popup select-country-code validate[required] select2-hidden-accessible"
                                    data-validation-type="phoneCodeListType"
                                    data-validation-rules="validate[required]"
                                    data-validation-container="company-edit--form-field--phone-codes--container"
                                    data-override-location="true"
                                    <?php echo addQaUniqueIdentifier('popup__company-edit__form_phone-code-select'); ?>
                                >
                                    <?php foreach ($company['phone']['codeList'] as $phoneCode) { ?>
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

                                <?php if (!empty($company['phone']['selected'])) { ?>
                                    <span
                                        id="company-edit--form-field--phone-codes--lazy-placeholder"
                                        class="select2 select2-container select2-container--default call-action"
                                        dir="ltr"
                                    >
                                        <span class="selection">
                                            <span class="select2-selection select2-selection--single" tabindex="0">
                                                <span
                                                    class="select2-selection__rendered"
                                                    title="<?php echo cleanOutput(trim("{$company['phone']['selected']['name']} {$company['phone']['selected']['countryName']}")); ?>"
                                                >
                                                    <img
                                                        class="select-country-flag"
                                                        src="<?php echo cleanOutput($company['phone']['selected']['countryFlag']); ?>"
                                                        alt="<?php echo cleanOutput($company['phone']['selected']['countryName']); ?>"
                                                        width="32"
                                                        height="32"
                                                    >
                                                    <span><?php echo cleanOutput($company['phone']['selected']['name']); ?></span>
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
                            id="company-edit--form-field--phone-number"
                            type="text"
                            name="phone"
                            class="ep-input ep-input--popup validate[required]"
                            value="<?php echo cleanOutput($company['phone']['number']); ?>"
                            maxlength="25"
                            placeholder="<?php echo translate('company_info_edit_popup_phone_placeholder'); ?>"
                            data-current-mask="<?php echo cleanOutput($company['phone']['selected']['mask'] ?? null); ?>"
                            <?php echo addQaUniqueIdentifier('popup__company-edit__form_phone-input'); ?>
                        >
                    </div>

                    <label class="ep-label ep-label--popup">
                        <?php echo translate('company_info_edit_popup_fax_label'); ?>
                    </label>
                    <div
                        id="company-edit--form-field--fax-codes--container"
                        class="js-select-phone-codes-lazy-block input-group"
                        data-list-field="#company-edit--form-field--fax-codes"
                        data-number-field="#company-edit--form-field--fax-number"
                        data-parent=".fancybox-wrap"
                        <?php if (!empty($company['fax']['selected'])) { ?>
                        data-lazy-placeholder="#company-edit--form-field--fax-codes--lazy-placeholder"
                        data-lazy="true"
                        <?php } ?>
                    >
                        <div
                            class="input-group-prepend wr-select2-h50 select-country-code-group"
                            <?php echo addQaUniqueIdentifier('popup__company-edit__form_fax-code-wrapper'); ?>
                        >
                            <div class="notranslate">
                                <select
                                    id="company-edit--form-field--fax-codes"
                                    name="fax_code"
                                    class="js-contact-country-code select-country-code validate[required] ep-select select2-hidden-accessible"
                                    data-validation-type="faxCodeListType"
                                    data-validation-rules="validate[required]"
                                    data-validation-container="company-edit--form-field--fax-codes--container"
                                    ata-override-location="false"
                                    <?php echo addQaUniqueIdentifier('popup__company-edit__form_fax-code-select'); ?>
                                >
                                    <?php foreach ($company['fax']['codeList'] as $faxCode) { ?>
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

                                <?php if (!empty($company['fax']['selected'])) { ?>
                                    <span
                                        id="company-edit--form-field--fax-codes--lazy-placeholder"
                                        class="select2 select2-container select2-container--default call-action"
                                        dir="ltr"
                                    >
                                        <span class="selection">
                                            <span class="select2-selection select2-selection--single" tabindex="0">
                                                <span
                                                    class="select2-selection__rendered"
                                                    title="<?php echo cleanOutput(trim("{$company['fax']['selected']['name']} {$company['fax']['selected']['countryName']}")); ?>"
                                                >
                                                    <img
                                                        class="select-country-flag"
                                                        src="<?php echo cleanOutput($company['fax']['selected']['countryFlag']); ?>"
                                                        alt="<?php echo cleanOutput($company['fax']['selected']['countryName']); ?>"
                                                        width="32"
                                                        height="32"
                                                    >
                                                    <span><?php echo cleanOutput($company['fax']['selected']['name']); ?></span>
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
                            id="company-edit--form-field--fax-number"
                            type="text"
                            name="fax"
                            class="ep-input ep-input--popup"
                            value="<?php echo cleanOutput($company['fax']['number']); ?>"
                            maxlength="25"
                            placeholder="<?php echo translate('company_info_edit_popup_fax_placeholder'); ?>"
                            data-current-mask="<?php echo cleanOutput($company['fax']['selected']['mask'] ?? null); ?>"
                            <?php echo addQaUniqueIdentifier('popup__company-edit__form_fax-input'); ?>
                        >
                    </div>

                    <div class="edit-info-popup-actions">
                        <div class="edit-info-popup-actions__left"></div>
                        <div class="edit-info-popup-actions__right">
                            <button
                                type="button"
                                class="btn btn-primary btn-block call-action"
                                data-step="validate_step_1_all"
                                data-js-action="company:edit-form.next-step"
                                <?php echo addQaUniqueIdentifier('popup__company-edit__form_navigation-first-step-next-button'); ?>
                            >
                                <?php echo translate('company_info_edit_popup_next_btn'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    id="company-edit--form--navigation--step-2"
                    role="tabpanel"
                    class="tab-pane fade js-location-block"
                    data-countries="#company-edit--form-field--countries"
                    data-states="#company-edit--form-field--states"
                    data-cities="#company-edit--form-field--cities"
                    data-address="#company-edit--form-field--address"
                    data-map="#company-edit--form-field--map"
                    data-lat="#company-edit--form-field--marker-latitude"
                    data-lng="#company-edit--form-field--marker-longitude"
                    <?php echo addQaUniqueIdentifier('popup__company-edit__form_navigation-step-two'); ?>
                >
                    <div class="edit-info-popup__row edit-info-popup__row--fdc wr-select2-h50">
                        <div class="edit-info-popup__column">
                            <div id="company-edit--form-field--countries--container">
                                <label class="ep-label ep-label--popup ep-label--required">
                                    <?php echo translate('company_info_edit_popup_country_label'); ?>
                                </label>
                                <select
                                    id="company-edit--form-field--countries"
                                    name="country"
                                    class="ep-select ep-select--popup validate[required]"
                                    <?php echo addQaUniqueIdentifier('popup__company-edit__form_country-select'); ?>
                                >
                                    <?php echo getCountrySelectOptions($company['location']['countries'] ?? [], $company['location']['selectedCountry'] ?? 0); ?>
                                </select>
                            </div>
                        </div>
                        <div class="edit-info-popup__column">
                            <div id="company-edit--form-field--states--container">
                                <label class="ep-label ep-label--popup ep-label--required">
                                    <?php echo translate('company_info_edit_popup_state_region_label'); ?>
                                </label>
                                <div class="notranslate">
                                    <select
                                        id="company-edit--form-field--states"
                                        name="region"
                                        class="ep-select ep-select--popup validate[required]"
                                        <?php echo addQaUniqueIdentifier('popup__company-edit__form_state-select'); ?>
                                    >
                                        <option value="" disabled>
                                            <?php echo translate('company_info_edit_popup_state_select_placeholder', null, true); ?>
                                        </option>
                                        <?php foreach ($company['location']['states'] as $state) { ?>
                                            <option value="<?php echo cleanOutput($state['id']); ?>" <?php if ($state['selected']) { ?>selected<?php } ?>>
                                                <?php echo cleanOutput($state['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="edit-info-popup__row edit-info-popup__row--fdc wr-select2-h50">
                        <div class="edit-info-popup__column">
                            <div id="company-edit--form-field--cities--container" class="wr-select2-h50">
                                <label class="ep-label ep-label--popup ep-label--required">
                                    <?php echo translate('company_info_edit_popup_city_label'); ?>
                                </label>
                                <div>
                                    <select
                                        id="company-edit--form-field--cities"
                                        name="city"
                                        class="ep-select ep-select--popup validate[required] select-city"
                                        <?php echo addQaUniqueIdentifier('popup__company-edit__form_city-select'); ?>
                                    >
                                        <option value="">
                                            <?php echo translate('company_info_edit_popup_city_select_placeholder', null, true); ?>
                                        </option>
                                        <?php if (null !== $company['location']['city']) { ?>
                                            <option value="<?php echo cleanOutput($company['location']['city']['id']); ?>" selected>
                                                <?php echo cleanOutput($company['location']['city']['city']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="edit-info-popup__column">
                            <label class="ep-label ep-label--popup ep-label--required">
                                <?php echo translate('company_info_edit_popup_zip_postal_code_label'); ?>
                            </label>
                            <input
                                id="company-edit--form-field--postal-code"
                                type="text"
                                name="postal_code"
                                class="ep-input ep-input--popup validate[required,custom[zip_code],maxSize[20]]"
                                value="<?php echo cleanOutput($company['location']['postalCode']); ?>"
                                maxlength="20"
                                placeholder="<?php echo translate('company_info_edit_popup_postal_code_input_placeholder', null, true); ?>"
                                <?php echo addQaUniqueIdentifier('popup__company-edit__form_zip-input'); ?>
                            >
                        </div>
                    </div>

                    <label class="ep-label ep-label--popup ep-label--required">
                        <?php echo translate('company_info_edit_popup_address_label'); ?>
                    </label>
                    <input
                        id="company-edit--form-field--address"
                        type="text"
                        name="address"
                        class="ep-input ep-input--popup validate[required,minSize[3],maxSize[255]]"
                        value="<?php echo cleanOutput($company['location']['address']); ?>"
                        placeholder="<?php echo translate('company_info_edit_popup_address_input_placeholder', null, true); ?>"
                        <?php echo addQaUniqueIdentifier('popup__company-edit__form_address-input'); ?>
                    >

                    <label class="ep-label--popup ep-label ep-label--mb-3">
                        <?php echo translate('company_info_edit_popup_map_coordinates_label'); ?>
                    </label>
                    <p class="edit-info-popup__note">
                        <?php echo translate('company_info_edit_popup_map_coordinates_notes_text'); ?>
                    </p>
                    <input
                        id="company-edit--form-field--marker-latitude"
                        type="hidden"
                        name="latitude"
                        class="ep-input ep-input--popup validate[required,custom[number],maxSize[21]]"
                        value="<?php echo cleanOutput($company['location']['marker']['latitude']); ?>"
                        <?php echo addQaUniqueIdentifier('popup__company-edit__form_latitude-input'); ?>
                    >
                    <input
                        id="company-edit--form-field--marker-longitude"
                        type="hidden"
                        name="longitude"
                        class="ep-input ep-input--popup validate[required,custom[number],maxSize[21]]"
                        value="<?php echo cleanOutput($company['location']['marker']['longitude']); ?>"
                        <?php echo addQaUniqueIdentifier('popup__company-edit__form_longitude-input'); ?>
                    >
                    <div
                        id="company-edit--form-field--map"
                        class="edit-info-popup__map h-300"
                        <?php echo addQaUniqueIdentifier('popup__company-edit__form__map-block'); ?>
                    ></div>

                    <div class="edit-info-popup-actions">
                        <div class="edit-info-popup-actions__left">
                            <button
                                type="button"
                                class="btn btn-dark call-action"
                                data-js-action="company:edit-form.previous-step"
                                <?php echo addQaUniqueIdentifier('popup__company-edit__form_navigation-second-step-prev-button'); ?>
                            >
                                <?php echo translate('company_info_edit_popup_back_btn'); ?>
                            </button>
                        </div>

                        <div class="edit-info-popup-actions__right">
                            <button
                                type="button"
                                class="btn btn-primary call-action"
                                data-step="validate_step_2"
                                data-js-action="company:edit-form.next-step"
                                <?php echo addQaUniqueIdentifier('popup__company-edit__form_navigation-second-step-next-button'); ?>
                            >
                                <?php echo translate('company_info_edit_popup_next_btn'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    id="company-edit--form--navigation--step-3"
                    role="tabpanel"
                    class="tab-pane fade"
                    <?php echo addQaUniqueIdentifier('popup__company-edit__form_navigation-step-three'); ?>
                >
                    <?php if (!$canRequestEdit) { ?>
                        <div class="edit-info-popup__row">
                            <div class="info-alert-b">
                                <i class="ep-icon ep-icon_info-stroke"></i>
                                <span><?php echo translate('company_info_edit_popup_not_verified_text'); ?></span>
                            </div>
                        </div>
                    <?php } else { ?>
                        <label class="ep-label ep-label--popup ep-label--mb-3 ep-label--required">
                            <?php echo translate('company_info_edit_popup_attachments_width_proof_of_changes_label'); ?>
                        </label>
                        <p class="edit-info-popup__text">
                            <?php echo translate('company_info_edit_popup_attachments_width_proof_of_changes_text'); ?>
                        </p>

                        <?php foreach ($documents as $document) { ?>
                            <div class="edit-info-popup__documents">
                                <div>
                                    <span class="edit-info-popup__documents-text">
                                        <?php echo cleanOutput($document['title'] ?? translate('company_info_edit_popup_documents_unknown_title')); ?>
                                    </span>
                                    <a
                                        href="#"
                                        class="info-dialog ep-icon ep-icon_info edit-info-section__info"
                                        data-message="<?php echo cleanOutput($document['description']); ?>"
                                        data-title="<?php echo cleanOutput($document['title'] ?? translate('company_info_edit_popup_documents_unknown_title')); ?>"
                                        title="<?php echo translate(
                                            'company_info_edit_popup_documents_entry_label',
                                            ['[[TITLE]]' => $document['title'] ?? translate('company_info_edit_popup_documents_unknown_title')],
                                            true
                                        ); ?>"
                                        <?php echo addQaUniqueIdentifier('popup__company-edit__form_documents-information-button'); ?>
                                    ></a>
                                </div>
                                <div role="group" class="btn-group edit-info-popup__buttons js-button-group">
                                    <a
                                        class="btn btn-primary mnw-130 js-button-upload call-action"
                                        data-js-action="company:edit-form.add-document"
                                        data-document="<?php echo cleanOutput($document['id']); ?>"
                                        data-title="<?php echo translate('company_info_edit_popup_upload_the_document_popup_title', null, true); ?>"
                                        data-url="<?php echo cleanOutput($document['url']); ?>"
                                        title="<?php echo translate('company_info_edit_popup_upload_the_document_button_title', null, true); ?>"
                                        <?php echo addQaUniqueIdentifier('popup__company-edit__form_documents-upload-button'); ?>
                                    >
                                        <?php echo translate('company_info_edit_popup_upload_the_document_button_text'); ?>
                                    </a>

                                    <span
                                        class="lh-40 mnw-130 tac bg-gray-lighter js-label-upload display-n"
                                        <?php echo addQaUniqueIdentifier('popup__company-edit__form_documents-upload-information-label'); ?>
                                    >
                                        <?php echo translate('company_info_edit_popup_uploaded_btn'); ?>
                                    </span>

                                    <a
                                        class="btn btn-dark js-button-remove call-action display-n"
                                        data-js-action="company:edit-form.remove-document"
                                        title="<?php echo translate('company_info_edit_popup_delete_file_button_title', null, true); ?>"
                                        <?php echo addQaUniqueIdentifier('popup__company-edit__form_documents-delete-button'); ?>
                                    >
                                        <i class="ep-icon ep-icon_trash-stroke"></i>
                                    </a>
                                </div>
                            </div>
                        <?php } ?>

                        <label class="ep-label ep-label--popup ep-label--required">
                            <?php echo translate('company_info_edit_popup_reason_for_change_label'); ?>
                        </label>
                        <textarea
                            id="company-edit--form-field--reason"
                            name="reason"
                            class="h-93 validate[required,maxSize[500]] textcounter-popup edit-info-section__textarea js-reason-counter"
                            data-max="500"
                            placeholder="<?php echo translate('company_info_edit_popup_reason_input_placeholder', null, true); ?>"
                            <?php echo addQaUniqueIdentifier('popup__company-edit__form_reason-textarea'); ?>
                        ></textarea>
                    <?php } ?>

                    <div class="edit-info-popup-actions">
                        <div class="edit-info-popup-actions__left">
                            <button
                                type="button"
                                class="btn btn-dark call-action"
                                data-js-action="company:edit-form.previous-step"
                                <?php echo addQaUniqueIdentifier('popup__company-edit__form_navigation-third-step-prev-button'); ?>
                            >
                                <?php echo translate('company_info_edit_popup_back_btn'); ?>
                            </button>
                        </div>

                        <div class="edit-info-popup-actions__right">
                            <button
                                type="submit"
                                class="btn btn-primary call-action"
                                data-js-action="company:edit-form.validate-step"
                                <?php echo addQaUniqueIdentifier('popup__company-edit__form_submit-button'); ?>
                            >
                                <?php echo translate('company_info_edit_popup_submit_btn'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php echo dispatchDynamicFragment('lazy-loading:company-main-edit', [
    '#company-edit--form--wrapper',
    ['url' => $editUrl, 'marker' => $company['location']['marker'] ?? null],
]); ?>
