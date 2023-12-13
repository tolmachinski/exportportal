<div
    id="js-epl-step-register-2"
    class="account-registration-step js-account-registration-step"
    <?php echo addQaUniqueIdentifier("{$companyType}-registration__step-2") ?>
>
    <div class="form-group">
        <label class="input-label"><?php echo translate('epl_register_company_name_label'); ?></label>
        <input
            type="text"
            name="company_legal_name"
            placeholder="<?php echo translate('epl_register_company_name_placeholder', null, true); ?>"
            <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-company-name") ?>
        >
    </div>

    <div class="form-group">
        <label class="input-label">
            <?php echo translate('epl_register_displayed_company_name'); ?>
            <button
                class="info-dialog js-info-dialog"
                type="button"
                data-message="<?php echo translate('epl_register_displayed_company_name_info', null, true); ?>"
                data-title="<?php echo translate('epl_register_displayed_company_name', null, true); ?>"
                title="<?php echo translate('epl_register_displayed_company_name', null, true); ?>"
            >
                <?php echo widgetGetSvgIconEpl("info", 16, 16, "info-dialog-icon") ?>
            </button>
        </label>
        <input
            type="text"
            name="company_name"
            placeholder="<?php echo translate('epl_register_displayed_company_name_placeholder', null, true); ?>"
            <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-company-name-displayed") ?>
        >
    </div>

    <div class="form-group">
        <label class="input-label"><?php echo translate('epl_register_number_of_office_locations_label'); ?></label>
        <input
            class="input-number"
            type="text"
            inputmode="decimal"
            name="company_offices_number"
            placeholder="<?php echo translate('epl_register_number_of_office_locations_placeholder', null, true); ?>"
            <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-number-of-office") ?>
        >
    </div>

    <div class="form-group">
        <label class="input-label">
            <?php echo translate('epl_register_annual_teu'); ?>
            <button
                class="info-dialog js-info-dialog"
                type="button"
                data-message="<?php echo translate('epl_register_annual_teu_info', null, true); ?>"
                data-title="<?php echo translate('epl_register_annual_teu', null, true); ?>"
                title="<?php echo translate('epl_register_annual_teu', null, true); ?>">
                <?php echo widgetGetSvgIconEpl("info", 16, 16, "info-dialog-icon") ?>
            </button>
        </label>
        <input
            class="input-number"
            type="text"
            inputmode="decimal"
            name="company_teu"
            placeholder="<?php echo translate('epl_register_annual_teu_placeholder', null, true); ?>"
            <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-annual-teu") ?>
        >
    </div>

    <div class="account-registration-actions">
        <div class="account-registration-actions__left">
            <button
                class="btn btn-outline-primary call-action"
                type="button"
                data-js-action="register-forms:prev-register-steps"
                <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-back-btn") ?>
            >
                <?php echo translate('epl_register_back_btn'); ?>
            </button>
        </div>

        <div class="account-registration-actions__right">
            <button
                class="btn btn-primary call-action"
                type="button"
                data-js-action="register-forms:next-register-steps"
                data-step="validate_step_2_shipper"
                <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-next-btn") ?>
            >
                <?php echo translate('epl_register_next_btn'); ?>
            </button>
        </div>
    </div>
</div>
