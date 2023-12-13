
<section
    id="company-edit--page--wrapper"
    class="preferences edit-info-section container-center-sm"
    data-form="#company-edit-additional--form"
>
	<div class="edit-info-section__title">
		<h1>
			<?php echo translate('company_info_header_ttl'); ?>
		</h1>
		<div class="info-alert-b">
			<i class="ep-icon ep-icon_info-stroke"></i>
			<span><?php echo translate('company_info_description'); ?></span>
		</div>
	</div>

    <?php if (!empty($sourceAccounts)) { ?>
        <div
            class="info-alert-b edit-info-section__row edit-info-section__row--mb-29"
            <?php echo addQaUniqueIdentifier('page__company-edit__import-data-block'); ?>
        >
            <i class="ep-icon ep-icon_info-stroke"></i>
            <span class="txt-bold"><?php echo translate('user_preferences_use_existing_information_title'); ?></span>
            <span><?php echo translate('user_preferences_use_existing_information_content'); ?></span>
            <div class="dropdown">
                <button
                    class="btn btn-primary mt-5 mnw-200"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="true"
                    <?php echo addQaUniqueIdentifier('page__company-edit__import-data-block_list-button'); ?>
                >
                    <span class="pl-5 pr-5"><?php echo translate('user_preferences_use_existing_information_content_label', null, true); ?></span>
                    <i class="ep-icon ep-icon_arrow-down fs-10"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-bottom mnw-200 shadow-none">
                    <?php foreach ($sourceAccounts as $accountId => $accountGroupType) { ?>
                        <button
                            class="dropdown-item call-action"
                            data-js-action="company:existing-accounts.choose"
                            data-message="<?php echo translate('copying_existing_information_confirm_dialog', null, true); ?>"
                            data-account="<?php echo cleanOutput($accountId); ?>"
                            data-title="<?php echo translate('copying_existing_information_confirm_dialog_title', null, true); ?>"
                            title="<?php echo translate('copying_existing_information_confirm_button_title', null, ['[[TYPE]]' => $accountGroupType]); ?>"
                            <?php echo addQaUniqueIdentifier('page__company-edit__import-data-block_choose-account-button'); ?>
                        >
                            <span class="txt"><?php echo cleanOutput($accountGroupType); ?></span>
                        </button>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>

	<div class="edit-info-section__row edit-info-section__row--fdr">
		<div class="edit-info-section__table">
			<h3 class="ep-label ep-label--mb-9">
				<?php echo translate('company_info_main_info_table_ttl'); ?>
			</h3>
			<table>
				<tr class="edit-info-section__table-row">
					<td class="edit-info-section__table-title"><?php echo translate('company_info_table_company_name'); ?></td>
					<td class="edit-info-section__table-value"><?php echo cleanOutput($company['legalName']) ?: '&mdash;'; ?></td>
				</tr>
				<tr class="edit-info-section__table-row">
					<td class="edit-info-section__table-title"><?php echo translate('company_info_table_display_company_name'); ?></td>
					<td class="edit-info-section__table-value"><?php echo cleanOutput($company['displayName']) ?: '&mdash;'; ?></td>
				</tr>
				<tr class="edit-info-section__table-row">
					<td class="edit-info-section__table-title"><?php echo translate('company_info_table_type'); ?></td>
					<td class="edit-info-section__table-value"><?php echo cleanOutput($company['typeName']) ?: '&mdash;'; ?></td>
				</tr>
				<tr class="edit-info-section__table-row">
					<td class="edit-info-section__table-title"><?php echo translate('company_info_table_phone'); ?></td>
					<td class="edit-info-section__table-value"><?php echo cleanOutput($company['phoneNumber']) ?: '&mdash;'; ?></td>
				</tr>
				<tr class="edit-info-section__table-row">
					<td class="edit-info-section__table-title"><?php echo translate('company_info_table_fax'); ?></td>
					<td class="edit-info-section__table-value"><?php echo cleanOutput($company['faxNumber']) ?: '&mdash;'; ?></td>
				</tr>
			</table>
		</div>
		<div class="edit-info-section__table">
			<h3 class="ep-label ep-label--mb-9">
				<?php echo translate('company_info_main_info_address_ttl'); ?>
			</h3>

			<table>
				<tr>
					<td class="edit-info-section__table-title"><?php echo translate('company_info_table_country'); ?></td>
					<td class="edit-info-section__table-value"><?php echo cleanOutput($company['country']) ?: '&mdash;'; ?></td>
				</tr>
				<tr>
					<td class="edit-info-section__table-title"><?php echo translate('company_info_table_state_region'); ?></td>
					<td class="edit-info-section__table-value"><?php echo cleanOutput($company['state']) ?: '&mdash;'; ?></td>
				</tr>
				<tr>
					<td class="edit-info-section__table-title"><?php echo translate('company_info_table_city'); ?></td>
					<td class="edit-info-section__table-value"><?php echo cleanOutput($company['city']) ?: '&mdash;'; ?></td>
				</tr>
				<tr>
					<td class="edit-info-section__table-title"><?php echo translate('company_info_table_address'); ?></td>
					<td class="edit-info-section__table-value"><?php echo cleanOutput($company['address']) ?: '&mdash;'; ?></td>
				</tr>
				<tr>
					<td class="edit-info-section__table-title"><?php echo translate('company_info_table_zip_postal'); ?></td>
					<td class="edit-info-section__table-value"><?php echo cleanOutput($company['postalCode']) ?: '&mdash;'; ?></td>
				</tr>
			</table>

            <?php if ($canRequestEdit || $hasEditRequest) { ?>
                <button
                    type="button"
                    class="edit-info-section__btn btn btn-light info-dialog mw-200 tac js-edit-request-button js-notify"
                    data-title="<?php echo translate('company_info_edits_in_review_notification_title', null, true); ?>"
                    data-classes="tac"
                    data-sub-title="<?php echo translate('company_info_edits_in_review_notification_text', null, true); ?>"
                    <?php if (!$hasEditRequest) { ?>style="display: none;"<?php } ?>
                    <?php echo addQaUniqueIdentifier('page__company-edit__edits-review-button'); ?>
                >
                    <?php echo translate('company_info_edits_in_review_btn'); ?>
                </button>

                <button
                    type="button"
                    class="edit-info-section__btn btn btn-primary fancybox.ajax fancyboxValidateModal mw-200 js-edit-request-button"
                    data-mw="678"
                    data-title="<?php echo translate('company_info_redit_popup_title', null, true); ?>"
                    data-wrap-class="fancybox-edit-personal-info"
                    data-fancybox-href="<?php echo cleanOutput($requestPopupUrl); ?>"
                    <?php if ($hasEditRequest) { ?>style="display: none;"<?php } ?>
                    <?php echo addQaUniqueIdentifier('page__company-edit__request-edits-button'); ?>
                >
                    <?php echo translate('company_info_request_edits_btn'); ?>
                </button>


            <?php } else { ?>
                <button
                    type="button"
                    class="edit-info-section__btn btn btn-primary fancybox.ajax fancyboxValidateModal mw-200"
                    data-mw="678"
                    data-title="<?php echo translate('company_info_redit_popup_title', null, true); ?>"
                    data-wrap-class="fancybox-edit-personal-info"
                    data-fancybox-href="<?php echo cleanOutput($editPopupUrl); ?>"
                    <?php echo addQaUniqueIdentifier('page__company-edit__edit-profile-button'); ?>
                >
                    <?php echo translate('company_info_request_edit_button_text'); ?>
                </button>
            <?php } ?>
		</div>
	</div>

	<form
        id="company-edit-additional--form"
        class="edit-info-section__form"
        data-editor="#company-edit-additional--form--description"
        data-js-action="company:addendum-form.submit"
        <?php echo addQaUniqueIdentifier('page__company-edit__addendum-form'); ?>
    >
        <div class="edit-info-section__row">
            <label class="ep-label ep-label--required">
                <?php echo translate('company_info_logo_label'); ?>
            </label>

            <?php views()->display('new/user/seller/company/cropper_view', ['parameters' => $cropperOptions ?? []]); ?>
        </div>

		<div class="edit-info-section__row">
			<label class="ep-label ep-label--required">
                <?php echo translate('company_info_industries_categories_label'); ?>
            </label>

			<?php widgetIndustriesMultiselect([
			    'industries'              => $industries['all'] ?? [],
			    'selected_industries'     => $industries['selected'] ?? [],
			    'categories'              => $categories['all'] ?? [],
			    'selected_categories'     => $categories['selected'] ?? [],
			    'max_selected_industries' => $industries['maximum'] ?? 0,
			    'required'                => 2,
			    'show_disclaimer'         => true,
			    'disclaimer_text'         => translate('multiple_select_edit_company_disclaimer'),
			]); ?>
		</div>

		<div class="edit-info-section__row edit-info-section__row--fdr edit-info-section__row--fdr-tablet">
			<div class="edit-info-section__column edit-info-section__column--mb-29">
				<label class="ep-label ep-label--required">
                    <?php echo translate('company_info_email_label'); ?>
                </label>
				<input
                    id="company-edit-additional--form-field--email"
                    type="text"
                    name="email"
                    class="validate[required,maxSize[100],custom[noWhitespaces],custom[emailWithWhitespaces]] ep-input"
                    value="<?php echo cleanOutput($company['email'] ?? ''); ?>"
                    placeholder="<?php echo translate('company_info_email_placeholder', null, true); ?>"
                    <?php echo addQaUniqueIdentifier('page__company-edit__addendum-form_email-input'); ?>
				>
			</div>
            <?php if ($canHaveIndexName) { ?>
                <div class="edit-info-section__column">
                    <label class="ep-label <?php if (!$hasIndexName) { ?>ep-label--required<?php } ?>">
                        <?php echo translate('company_info_index_name_label_text'); ?>
                        <?php if (!$hasIndexName) { ?>
                            <a
                                href="#"
                                class="ep-icon ep-icon_info info-dialog fs-16"
                                data-title="<?php echo translate('company_info_index_name_info_button_title', null, true); ?>"
                                data-content="#js-personal-link-info">
                            </a>

                            <div class="display-n">
                                <div id="js-personal-link-info">
                                    <ul>
                                        <li><?php echo translate('company_info_index_name_info_line_1', null, true); ?></li>
                                        <li><?php echo translate('company_info_index_name_info_line_2', null, true); ?></li>
                                        <li><?php echo translate('company_info_index_name_info_line_3', null, true); ?></li>
                                        <li><?php echo translate('company_info_index_name_info_line_4', null, true); ?></li>
                                    </ul>
                                </div>
                            </div>
                        <?php } ?>
                    </label>

                    <?php if ($hasIndexName) { ?>
                        <p class="lh-40"><?php echo cleanOutput($company['url'] ?? ''); ?></p>
                    <?php } else { ?>
                        <div class="edit-info-section__company-link js-company-link">
                            <span class="js-company-link-base-url">
                                <?php echo cleanOutput($baseUrl); ?>
                            </span>
                            <input
                                id="company-edit-additional--form-field--index-name"
                                class="js-company-index-name-input validate[required,minSize[5],maxSize[30],custom[companyLink]] ep-input"
                                type="text"
                                name="index_name"
                                placeholder="<?php echo translate('company_info_index_name_placeholder', null, true); ?>"
                                data-prompt-position="bottomLeft:0"
                                <?php echo addQaUniqueIdentifier('page__company-edit__addendum-form_index-name-input'); ?>
                            >
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
		</div>

		<div class="edit-info-section__row edit-info-section__row--fdr edit-info-section__row--fdr-tablet">
			<div class="edit-info-section__column edit-info-section__column--mb-29">
				<label class="ep-label edit-info-section__label">
                    <?php echo translate('company_info_annual_revenue_label'); ?>
                </label>
				<input
                    id="company-edit-additional--form-field--revenue-name"
                    type="text"
                    name="revenue"
                    class="validate[custom[positive_number]] ep-input"
                    value="<?php echo cleanOutput($company['revenue'] ?? ''); ?>"
                    placeholder="<?php echo translate('company_info_annual_revenue_placeholder', null, true); ?>"
                    <?php echo addQaUniqueIdentifier('page__company-edit__addendum-form_revenue-input'); ?>
				>
			</div>
            <div class="edit-info-section__column">
				<label class="ep-label text-nowrap" title="Number of employees">
                    <?php echo translate('company_info_number_of_employees_label'); ?>
                </label>
				<input
                    id="company-edit-additional--form-field--employees-name"
                    type="text"
                    name="employees"
                    class="validate[maxSize[5],custom[positive_integer]] ep-input"
                    value="<?php echo cleanOutput($company['employees'] ?? ''); ?>"
                    placeholder="<?php echo translate('company_info_employees_placeholder', null, true); ?>"
                    <?php echo addQaUniqueIdentifier('page__company-edit__addendum-form_employees-input'); ?>
				>
			</div>
		</div>
		<div
            class="edit-info-section__row"
            <?php echo addQaUniqueIdentifier('page__company-edit__addendum-form_description-tinymce-editor'); ?>
        >
			<label class="ep-label ep-label--required">
                <?php echo translate('company_info_description_label'); ?>
            </label>
			<textarea
                id="company-edit-additional--form--description"
                name="description"
                class="validate[maxSize[20000]] mb-0"
                data-max="20000"
                placeholder="<?php echo translate('company_info_description_placeholder', null, true); ?>"
                <?php echo addQaUniqueIdentifier('page__company-edit__addendum-form_description-textarea'); ?>
			>
				<?php echo $company['description'] ?? ''; ?>
			</textarea>
		</div>
		<div class="edit-info-section__row">
			<label class="ep-label edit-info-section__label">
                <?php echo translate('company_info_video_label'); ?>
            </label>
			<input
                id="company-edit-additional--form-field--video-name"
                type="text"
                name="video"
                class="validate[maxSize[200],custom[url]] ep-input"
                value="<?php echo cleanOutput($company['video'] ?? ''); ?>"
                placeholder="<?php echo translate('company_info_video_url_placeholder')?>"
                <?php echo addQaUniqueIdentifier('page__company-edit__addendum-form_video-input'); ?>
			>
		</div>

        <?php if (!empty($relatedAccounts)) { ?>
            <div class="edit-info-section__row">
                <label class="ep-label ep-label--mb-11">
                    <?php echo translate('company_info_apply_changes_to_my_account_label'); ?>
                    <a
                        class="info-dialog ep-icon ep-icon_info edit-info-section__info"
                        data-message="<?php echo translate('company_edit_apply_changes_to_other_accounts_info_message', null, true); ?>"
                        data-title="<?php echo translate('company_edit_apply_changes_to_other_accounts_info_title', null, true); ?>"
                        title="<?php echo translate('company_edit_apply_changes_to_other_accounts_info_hover_title', null, true); ?>"
                        href="#"
                    ></a>
                </label>
                <?php foreach ($relatedAccounts as $accountId => $accountType) { ?>
                    <label class="custom-checkbox">
                        <input
                            name="sync_with_accounts[]"
                            type="checkbox"
                            value="<?php echo cleanOutput($accountId); ?>"
                            <?php echo isset($syncCompanyInfo[$accountId]) ? 'checked' : ''; ?>
                        >
                        <span class="custom-checkbox__text"><?php echo cleanOutput($accountType); ?></span>
                    </label>
                <?php } ?>
            </div>
        <?php } ?>

        <button
            type="submit"
            class="btn btn-primary edit-info-section__btn"
            <?php echo addQaUniqueIdentifier('page__company-edit__addendum-form_save-button'); ?>
        >
            <?php echo translate('company_info_save_btn'); ?>
        </button>
	</form>
</section>

<script src="https://maps.googleapis.com/maps/api/js?v=3&key=AIzaSyAko8g1LP9autKH12-8d1VkUZn3UaIZB8E"></script>
