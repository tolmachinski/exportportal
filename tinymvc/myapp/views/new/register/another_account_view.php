<div class="account-registration-another">
    <label class="input-label"><?php echo translate('register_choose_additional_type_label'); ?>:</label>

    <div id="js-another-account-checkbox" class="account-registration-another__checkbox">
        <?php if(
                $registered_user_type == 'seller'
                || $registered_user_type == 'manufacturer'
        ){?>
        <div class="custom-checkbox-wrap">
            <label class="js-register-another-account custom-checkbox" data-value="buyer" <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-checkbox-add-buyer-account")?>>
                <input type="checkbox" name="type_another_account" value="buyer">
                <div class="custom-checkbox__text"><?php echo translate('register_buyer_word'); ?></div>
            </label>
        </div>
        <?php }?>

        <?php if(
                $registered_user_type == 'buyer'
                || $registered_user_type == 'manufacturer'
        ){?>
        <div class="custom-checkbox-wrap">
            <label class="js-register-another-account custom-checkbox" data-value="seller" <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-checkbox-add-seller-account")?>>
                <input type="checkbox" name="type_another_account" value="seller">
                <div class="custom-checkbox__text"><?php echo translate('register_seller_word'); ?></div>
            </label>
        </div>
        <?php }?>

        <?php if(
                $registered_user_type == 'buyer'
                || $registered_user_type == 'seller'
            ){?>
        <div class="custom-checkbox-wrap">
            <label class="js-register-another-account custom-checkbox" data-value="manufacturer" <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-checkbox-add-manufacturer-account")?>>
                <input type="checkbox" name="type_another_account" value="manufacturer">
                <div class="custom-checkbox__text"><?php echo translate('register_manufacturer_word'); ?></div>
            </label>
        </div>
        <?php }?>

        <div class="custom-checkbox-wrap">
            <label class="js-register-another-account custom-checkbox" data-value="all" <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-add-all-accounts")?>>
                <input type="checkbox" name="type_another_account" value="all">
                <div class="custom-checkbox__text"><?php echo translate('register_both_word'); ?></div>
            </label>
        </div>
    </div>

    <div id="js-another-account-hidden">
        <?php if(
                $registered_user_type == 'seller'
                || $registered_user_type == 'manufacturer'
        ){?>
            <?php views()->display('new/register/another_account_form_view', array('title' => translate('register_buyer_word'), 'input_name' => 'buyer')); ?>
        <?php }?>

        <?php if(
                $registered_user_type == 'buyer'
                || $registered_user_type == 'manufacturer'
        ){?>
            <?php views()->display('new/register/another_account_form_view', array('title' => translate('register_seller_word'), 'input_name' => 'seller')); ?>
        <?php }?>

        <?php if(
                $registered_user_type == 'buyer'
                || $registered_user_type == 'seller'
            ){?>
            <?php views()->display('new/register/another_account_form_view', array('title' => translate('register_manufacturer_word'), 'input_name' => 'manufacturer')); ?>
        <?php }?>
    </div>

    <div class="account-registration-actions">
        <div class="account-registration-actions__left">
            <button
                class="btn btn-dark call-action"
                data-js-action="register-forms:prev-additional-register-steps"
                type="button"
                <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-back-btn")?>>
                <?php echo translate('register_form_btn_back');?>
            </button>
        </div>

        <div class="account-registration-actions__right">
            <button
                class="btn btn-primary call-action"
                data-js-action="register-forms:next-additional-register-steps"
                data-step="validate_step_additional"
                <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-next-btn")?>>
                    <?php echo translate('register_form_btn_next');?>
            </button>
        </div>
    </div>
</div>
