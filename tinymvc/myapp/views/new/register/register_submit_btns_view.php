
<label class="custom-checkbox mt-20" <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-checkbox-terms")?>>
    <input class="validate[required]" type="checkbox" name="terms_cond" value="1" >
    <div class="custom-checkbox__text-agreement">
        <?php echo translate('register_terms_of_use_part_1'); ?>
        <a class="fancybox fancybox.ajax display-ib txt-underline" data-w="1040" data-mw="1040" data-h="400" data-title="<?php echo translate('label_terms_and_conditions');?>" href="<?php echo __SITE_URL . 'terms_and_conditions/tc_register_seller';?>"><?php echo translate('label_terms_and_conditions');?></a>,
        <a class="fancybox fancybox.ajax display-ib txt-underline" data-w="1040" data-mw="1040" data-h="400" data-title="<?php echo translate('label_privacy_policy');?>" href="<?php echo __SITE_URL . 'terms_and_conditions/tc_privacy_policy';?>"><?php echo translate('label_privacy_policy');?></a>
        <?php echo translate('register_terms_of_use_part_2'); ?>
        <a class="fancybox fancybox.ajax display-ib txt-underline" data-w="1040" data-mw="1040" data-h="400" data-title="<?php echo translate('label_terms_of_use');?>" href="<?php echo __SITE_URL . 'terms_and_conditions/tc_terms_of_use';?>"><?php echo translate('label_terms_of_use');?></a>
    </div>
</label>

<div class="account-registration-actions">
    <div class="account-registration-actions__left">
        <button
            class="btn btn-dark call-action"
            data-js-action="register-forms:prev-register-steps"
            <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-back-btn")?>>
            <?php echo translate('register_form_btn_back');?>
        </button>
    </div>
    <div class="account-registration-actions__right">
        <button
            class="btn btn-success call-action"
            data-js-action="register-forms:validate-tab-submit"
            <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-register-btn")?>>
            <?php echo translate('register_form_btn_register');?>
        </button>
    </div>
</div>
