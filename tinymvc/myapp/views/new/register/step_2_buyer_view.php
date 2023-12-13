<?php views()->display('new/register/step_2_buyer_inputs_view');?>

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
            data-step="validate_step_2_buyer"
            <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-next-btn")?>
        >
            <?php echo translate('register_form_btn_next');?>
        </button>
    </div>
</div>
