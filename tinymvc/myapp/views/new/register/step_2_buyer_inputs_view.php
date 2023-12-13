<?php
    if(!isset($suffix)){
        $suffix = '';
    }

    $qa_aditional = ($suffix !== '') ? "additional-buyer-" : "";
?>
<label class="input-label">
    <?php echo translate('register_label_buyer_step_2_setup_account');?><a
        class="js-information-dialog ep-icon ep-icon_info"
        data-message="<?php echo translate('register_label_buyer_step_2_account_info');?>"
        data-title="<?php echo translate('register_label_buyer_step_2_account_type');?>"
        data-keep-modal="1"
        title="<?php echo translate('register_label_buyer_step_2_account_type');?>"
        href="#"></a>
</label>

<div id="js-buyer-type-check" class="account-registration-another__checkbox">
    <div class="account-registration-another__checkbox-item">
        <label class="checkbox-group checkbox-group--inline custom-radio" <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-{$qa_aditional}radio-personal")?>>
            <input class="validate[required]" type="radio" name="type_buyer<?php echo $suffix;?>" value="0">
            <div class="account-registration-another__checkbox-txt custom-radio__text"><?php echo translate('register_label_buyer_step_2_type_personal');?></div>
        </label>
    </div>

    <div class="account-registration-another__checkbox-item">
        <label class="checkbox-group checkbox-group--inline mb-0 custom-radio" <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-{$qa_aditional}radio-business")?>>
            <input class="validate[required]" type="radio" name="type_buyer<?php echo $suffix;?>" value="1">
            <div class="account-registration-another__checkbox-txt custom-radio__text"><?php echo translate('register_label_buyer_step_2_type_business');?></div>
        </label>
    </div>
</div>

<div id="js-buyer-entity-form" class="display-n">
    <div class="form-group">
        <label class="input-label"><?php echo translate('register_label_company_name');?></label>
        <input
            type="text"
            class="validate[required,custom[companyTitle],minSize[3],maxSize[50]]"
            name="company_legal_name<?php echo $suffix;?>"
            placeholder="<?php echo translate('register_label_company_name_placeholder');?>"
            <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-{$qa_aditional}company-name")?>>
    </div>

    <div class="form-group">
        <label class="input-label">
            <?php echo translate('register_label_company_displayed_name');?><a
                class="js-information-dialog ep-icon ep-icon_info"
                data-message="<?php echo translate('register_label_company_displayed_name_info');?>"
                data-title="<?php echo translate('register_label_company_displayed_name');?>"
                data-keep-modal="1"
                title="<?php echo translate('register_label_company_displayed_name');?>"
                href="#"></a>
        </label>
        <input
            class="validate[required,custom[companyTitle],minSize[3],maxSize[50]]"
            type="text"
            name="company_name<?php echo $suffix;?>"
            placeholder="<?php echo translate('register_label_company_displayed_name_placeholder');?>"
            <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-{$qa_aditional}company-name-displayed")?>>
    </div>
</div>

<?php
//dump(isset($existing_accounts));
//    if(isset($existing_accounts)){
        echo dispatchDynamicFragment(
            'account:setup-account-type',
            null,
            true
        );
//    }
?>
