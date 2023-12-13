<label class="custom-checkbox" <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-checkbox-terms") ?>>
    <input
        type="checkbox"
        name="terms_cond"
        value="1"
        data-hide-error="true"
    >
    <div class="custom-checkbox__text-agreement">
        <span><?php echo translate('register_terms_of_use_part_1'); ?></span>
        <a href="<?php echo __SITE_URL . 'terms_and_conditions/tc_register_shipper'; ?>" target="_blank"><?php echo translate('label_terms_and_conditions'); ?></a>,
        <a href="<?php echo __SITE_URL . 'terms_and_conditions/tc_privacy_policy'; ?>" target="_blank"><?php echo translate('label_privacy_policy'); ?></a>
        <span><?php echo translate('register_terms_of_use_part_2'); ?></span>
        <a href="<?php echo __SITE_URL . 'terms_and_conditions/tc_terms_of_use'; ?>" target="_blank"><?php echo translate('label_terms_of_use'); ?></a>
    </div>
</label>

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
            data-js-action="register-forms:validate-step-submit"
            <?php echo addQaUniqueIdentifier("{$companyType}-registration__form-register-btn") ?>
        >
            <?php echo translate('epl_register_register_btn'); ?>
        </button>
    </div>
</div>
