<section
    id="user-preferences--page--wrapper"
    class="preferences edit-info-section container-center-sm"
    data-form="#user-preferences-additional--form"
    data-source-info="#user-preferences-additional__source-type-wrapper"
>
    <div class="edit-info-section__title">
        <h1>
            <?php echo translate('user_preferences_header'); ?>
        </h1>
        <div class="info-alert-b">
            <i class="ep-icon ep-icon_info-stroke"></i>
            <span><?php echo translate('user_preferences_description'); ?></span>
        </div>
    </div>

    <?php if (!empty($sourceAccounts)) { ?>
        <div class="info-alert-b edit-info-section__row">
            <i class="ep-icon ep-icon_info-stroke"></i>
            <span class="txt-bold"><?php echo translate('user_preferences_use_existing_information_title'); ?></span>
            <span><?php echo translate('user_preferences_use_existing_information_content'); ?></span>
            <div class="dropdown">
                <button
                    class="btn btn-primary mt-5 mnw-200"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="true"
                    <?php echo addQaUniqueIdentifier('preferences__personal-info_choose-account-btn'); ?>
                >
                    <span class="pl-5 pr-5"><?php echo translate('user_preferences_use_existing_information_content_label', null, true); ?></span>
                    <i class="ep-icon ep-icon_arrow-down fs-10"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-bottom mnw-200 shadow-none">
                    <?php foreach ($sourceAccounts as $accoutnId => $accountGroupType) { ?>
                        <button
                            class="dropdown-item call-action"
                            data-js-action="user:existing-accounts.choose"
                            data-message="<?php echo translate('copying_existing_information_confirm_dialog', null, true); ?>"
                            data-account="<?php echo cleanOutput($accountId); ?>"
                            data-title="Warning!"
                            title="<?php echo cleanOutput(sprintf('Copy existing information from %s', $accountGroupType)); ?>"
                            <?php echo addQaUniqueIdentifier('preferences__personal-info_choose-account-list'); ?>
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
            <label class="ep-label ep-label--mb-9"><?php echo translate('user_preferences_main-info_table_title'); ?></label>
            <table>
                <tr class="edit-info-section__table-row">
                    <td class="edit-info-section__table-title"><?php echo translate('user_preferences_main-info_table_first-name'); ?></td>
                    <td class="edit-info-section__table-value"><?php echo cleanOutput($profile['firstName']) ?: '&mdash;'; ?></td>
                </tr>
                <tr class="edit-info-section__table-row">
                    <td class="edit-info-section__table-title"><?php echo translate('user_preferences_main-info_table_last-name'); ?></td>
                    <td class="edit-info-section__table-value"><?php echo cleanOutput($profile['lastName']) ?: '&mdash;'; ?></td>
                </tr>
                <tr class="edit-info-section__table-row">
                    <td class="edit-info-section__table-title"><?php echo translate('user_preferences_main_info_table_legal_name'); ?></td>
                    <td class="edit-info-section__table-value"><?php echo cleanOutput($profile['legalName']) ?: '&mdash;'; ?></td>
                </tr>
                <tr class="edit-info-section__table-row">
                    <td class="edit-info-section__table-title"><?php echo translate('user_preferences_main-info_table_phone'); ?></td>
                    <td class="edit-info-section__table-value"><?php echo cleanOutput($profile['phoneNumber']) ?: '&mdash;'; ?></td>
                </tr>
                <tr class="edit-info-section__table-row">
                    <td class="edit-info-section__table-title"><?php echo translate('user_preferences_main-info_table_fax'); ?></td>
                    <td class="edit-info-section__table-value"><?php echo cleanOutput($profile['faxNumber']) ?: '&mdash;'; ?></td>
                </tr>
            </table>
        </div>
        <div class="edit-info-section__table">
            <label class="ep-label ep-label--mb-9"><?php echo translate('user_preferences_address_table_title'); ?></label>
            <table>
                <tr>
                    <td class="edit-info-section__table-title"><?php echo translate('user_preferences_address_table_country'); ?></td>
                    <td class="edit-info-section__table-value"><?php echo cleanOutput($profile['country']) ?: '&mdash;'; ?></td>
                </tr>
                <tr>
                    <td class="edit-info-section__table-title"><?php echo translate('user_preferences_address_table_state-region'); ?></td>
                    <td class="edit-info-section__table-value"><?php echo cleanOutput($profile['state']) ?: '&mdash;'; ?></td>
                </tr>
                <tr>
                    <td class="edit-info-section__table-title"><?php echo translate('user_preferences_address_table_city'); ?></td>
                    <td class="edit-info-section__table-value"><?php echo cleanOutput($profile['city']) ?: '&mdash;'; ?></td>
                </tr>
                <tr>
                    <td class="edit-info-section__table-title"><?php echo translate('user_preferences_address_table_address'); ?></td>
                    <td class="edit-info-section__table-value"><?php echo cleanOutput($profile['address']) ?: '&mdash;'; ?></td>
                </tr>
                <tr>
                    <td class="edit-info-section__table-title"><?php echo translate('user_preferences_address_table_zip-postal-code'); ?></td>
                    <td class="edit-info-section__table-value"><?php echo cleanOutput($profile['postalCode']) ?: '&mdash;'; ?></td>
                </tr>
            </table>

            <?php if ($canRequestEdit || $hasEditRequest) { ?>
                <button
                    type="button"
                    class="edit-info-section__btn btn btn-light js-edit-request-button js-notify info-dialog tac"
                    data-title="<?php echo translate('user_preferences_edits_in_review_info_title', null, true); ?>"
                    data-classes="tac"
                    data-sub-title="<?php echo translate('user_preferences_edits_in_review_info_sub_title', null, true); ?>"
                    <?php if (!$hasEditRequest) { ?>style="display: none;"<?php } ?>
                    <?php echo addQaUniqueIdentifier('preferences__personal-info_in-review-btn'); ?>
                >
                    <?php echo translate('user_preferences_edits_in_review_btn'); ?>
                </button>

                <button
                    type="button"
                    class="edit-info-section__btn btn btn-primary js-edit-request-button fancybox.ajax fancyboxValidateModal"
                    data-mw="678"
                    data-title="<?php echo translate('user_preferences_request_edits_btn_title', null, true); ?>"
                    data-wrap-class="fancybox-edit-personal-info"
                    data-fancybox-href="<?php echo cleanOutput($requestPopupUrl); ?>"
                    <?php if ($hasEditRequest) { ?>style="display: none;"<?php } ?>
                    <?php echo addQaUniqueIdentifier('preferences__personal-info_request-edits-btn'); ?>
                >
                    <?php echo translate('user_preferences_request_edits_btn'); ?>
                </button>
            <?php } else { ?>
                <button
                    type="button"
                    class="edit-info-section__btn btn btn-primary fancybox.ajax fancyboxValidateModal call-action"
                    data-mw="678"
                    data-title="<?php echo translate('user_preferences_edit_btn_title', null, true); ?>"
                    data-wrap-class="fancybox-edit-personal-info"
                    data-fancybox-href="<?php echo cleanOutput($editPopupUrl); ?>"
                    <?php echo addQaUniqueIdentifier('preferences__personal-info_request-edits-btn'); ?>
                >
                    <?php echo translate('user_preferences_edit_btn'); ?>
                </button>
            <?php } ?>
        </div>
    </div>

    <form id="user-preferences-additional--form" class="edit-info-section__form validengine">
        <div class="edit-info-section__row">
            <label class="ep-label">
                <?php echo translate('user_preferences_description-textarea_label'); ?>
            </label>
            <textarea
                <?php echo addQaUniqueIdentifier('preferences__personal-info_description-textarea'); ?>
                name="description"
                class="h-93 validate[maxSize[1000]] textcounter edit-info-section__textarea input-new"
                data-max="1000"
            ><?php echo cleanOutput($profile['description']); ?></textarea>
        </div>

        <?php if ($showAboutFields) { ?>
            <?php views()->display('new/user/find_about_us_view', [
                'wrapperId' => "user-preferences-additional__source-type-wrapper",
                'fieldId'   => "user-preferences-additional__source-type-field",
            ]); ?>
        <?php } ?>

        <?php if (!empty($relatedAccounts)) { ?>
            <div class="edit-info-section__row">
                <label class="ep-label ep-label--mb-11">
                    <?php echo translate('user_preferences_apply_changes_to_other_accounts_label'); ?>
                    <a class="info-dialog ep-icon ep-icon_info edit-info-section__info"
                        data-message="<?php echo translate('user_preferences_apply_changes_to_other_accounts_info_message', null, true); ?>"
                        data-title="<?php echo translate('user_preferences_apply_changes_to_other_accounts_info_title', null, true); ?>"
                        title="<?php echo translate('user_preferences_apply_changes_to_other_accounts_info_hover_title', null, true); ?>"
                        href="#">
                    </a>
                </label>
                <?php foreach ($relatedAccounts as $accountId => $accountType) { ?>
                    <div class="custom-checkbox-wrap">
                        <label class="custom-checkbox">
                            <input
                                name="sync_with_accounts[]"
                                type="checkbox"
                                value="<?php echo cleanOutput($accountId); ?>"
                                <?php echo isset($syncPersonalInfo[$accountId]) ? 'checked' : ''; ?>
                            >
                            <span class="custom-checkbox__text"><?php echo cleanOutput($accountType); ?></span>
                        </label>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <button
            type="submit"
            class="edit-info-section__btn btn btn-primary call-action"
            data-js-action="user:profile-addendum-form.submit"
            <?php echo addQaUniqueIdentifier('preferences__personal-info_submit-btn'); ?>
        >
            <?php echo translate('user_preferences_save_form_btn'); ?>
        </button>
    </form>
</section>
