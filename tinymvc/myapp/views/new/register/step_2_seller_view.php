
<div class="form-group">
    <label class="input-label"><?php echo translate('register_label_company_name');?></label>
    <input
        class="validate[required,custom[companyTitle],minSize[3],maxSize[50]]"
        type="text"
        name="company_legal_name"
        placeholder="<?php echo translate('register_label_company_name_placeholder');?>"
        <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-company-name")?>>
</div>

<div class="form-group">
    <label class="input-label">
        <?php echo translate('register_label_company_displayed_name');?><a
            class="info-dialog ep-icon ep-icon_info"
            data-message="<?php echo translate('register_label_company_displayed_name_info');?>"
            data-title="<?php echo translate('register_label_company_displayed_name'); ?>"
            title="<?php echo translate('register_label_company_displayed_name'); ?>"
            href="#"
        ></a>
    </label>
    <input
        class="validate[required,custom[companyTitle],minSize[3],maxSize[50]]"
        type="text"
        name="company_name"
        placeholder="<?php echo translate('register_label_company_displayed_name_placeholder');?>"
        <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-company-name-displayed")?>>
</div>

<?php if ('seller' === $company_type) {?>
    <label class="input-label">
        <?php echo translate('register_label_seller_step_2_official_distributor');?>
        <a class="info-dialog ep-icon ep-icon_info" data-message="<?php echo translate('register_label_seller_step_2_official_distributor_info', null, true);?>" data-title="<?php echo translate('register_label_seller_step_2_official_distributor', null, true);?>" title="<?php echo translate('register_label_seller_step_2_official_distributor', null, true);?>" href="#"></a>
    </label>

    <div id="js-distributor-check" class="account-registration-another__checkbox">
        <div class="account-registration-another__checkbox-item">
            <label class="custom-radio" <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-radio-official-distributor-yes")?>>
                <input class="validate[required]" type="radio" name="is_distributor" value="1">
                <div class="custom-radio__text"><?php echo translate('register_label_seller_step_2_confirm_official_distributor');?></div>
            </label>
        </div>

        <div class="account-registration-another__checkbox-item">
            <label class="custom-radio" <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-radio-official-distributor-no")?>>
                <input class="validate[required]" type="radio" name="is_distributor" value="0" checked>
                <div class="custom-radio__text"><?php echo translate('register_label_seller_step_2_decline_official_distributor');?></div>
            </label>
        </div>
    </div>
<?php }?>

<div class="form-group">
    <label class="input-label"><?php echo translate("multiple_select_label_industries_categories")?></label>
    <div  <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-multiselect-industries")?>>
        <?php widgetIndustriesMultiselect($multipleselect_industries); ?>
    </div>
</div>

<?php views()->display('new/register/another_account_call_view'); ?>

<div class="account-registration-actions">
    <div class="account-registration-actions__left">
        <button
            class="btn btn-dark call-action"
            data-js-action="register-forms:prev-register-steps"
            <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-back-btn")?>
        >
            <?php echo translate('register_form_btn_back');?>
        </button>
    </div>

    <div class="account-registration-actions__right">
        <button
            class="btn btn-primary call-action"
            data-js-action="register-forms:next-register-steps"
            data-step="validate_step_2_seller"
            <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-next-btn")?>
        >
            <?php echo translate('register_form_btn_next');?>
        </button>
    </div>
</div>
