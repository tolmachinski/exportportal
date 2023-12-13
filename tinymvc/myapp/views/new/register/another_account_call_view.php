<div class="account-registration-another__toggle">
    <div class="account-registration-another__toggle-left">
        <a
            class="link call-action"
            data-js-action="register-forms:select-another-account"
            data-step="validate_step_2_<?php echo $register_type;?>"
            <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-add-account")?>
            href="#"><i class="ep-icon ep-icon_plus-circle"></i><?php echo translate('register_add_another_account_label'); ?></a>
        <span class="account-registration-another__toggle-optional-txt">(<?php echo translate('register_optional_word'); ?>)</span>
    </div>

    <a
        class="info-dialog ep-icon ep-icon_info"
        data-message="<?php echo translate('register_add_another_account_label_info'); ?>"
        data-title="<?php echo translate('register_add_another_account_label_info_title'); ?>"
        title="<?php echo translate('register_add_another_account_label_info_title'); ?>"
        href="#"></a>
</div>
