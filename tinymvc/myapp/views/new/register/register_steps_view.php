<div class="relative-b">
    <ul id="js-register-nav-tabs" class="nav tabs-circle tabs-circle--no-click" role="tablist">
        <li class="tabs-circle__item">
            <a class="link active" href="#js-step-register-1" aria-controls="title" role="tab" data-toggle="tab">
                <div class="tabs-circle__point"><i class="ep-icon ep-icon_ok-stroke2"></i></div>

                <div class="tabs-circle__txt"><?php echo translate('register_form_step_1_title'); ?></div>
            </a>
        </li>
        <li id="js-register-nav-has-additional" class="tabs-circle__item">
            <a class="link call-action" href="#js-step-register-2" aria-controls="title" role="tab" data-toggle="tab">
                <div class="tabs-circle__point"><i class="ep-icon ep-icon_ok-stroke2"></i></div>

                <div class="tabs-circle__txt"><?php echo translate('register_form_step_2_title'); ?></div>
            </a>
        </li>

        <li id="js-register-nav-additional" class="tabs-circle__item tabs-circle__item--min display-n">
            <a class="link" href="#js-step-register-another" aria-controls="title" role="tab" data-toggle="tab">
                <div class="tabs-circle__point"></div>
            </a>
        </li>

        <li class="tabs-circle__item">
            <a class="link call-action" data-js-action="lazy-loading:location-module" href="#js-step-register-3" aria-controls="title" role="tab" data-toggle="tab">
                <div class="tabs-circle__point"><i class="ep-icon ep-icon_ok-stroke2"></i></div>
                <div class="tabs-circle__txt"><?php echo translate('register_form_step_3_title'); ?></div>
            </a>
        </li>
    </ul>
</div>

<form
    id="js-register-form"
    class="js-ep-self-autotrack"
    data-sto="-55"
    autocomplete="off"
    data-js-action="register-forms:submit"
    data-tracking-events="submit"
    data-tracking-fields="<?php echo cleanOutput(json_encode(array('email', 'fname', 'lname', 'country_code', 'phone'))); ?>"
    data-tracking-alias="form-register_<?php echo $register_type; ?>"
    >
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade show active" id="js-step-register-1" <?php echo addQaUniqueIdentifier("{$company_type}-registration__step-1")?>>
            <?php views()->display('new/register/step_1_all_view'); ?>

            <div class="account-registration-actions">
                <div class="account-registration-actions__left"></div>
                <div class="account-registration-actions__right">
                    <button
                        class="btn btn-primary btn-block call-action"
                        data-js-action="register-forms:next-register-steps"
                        data-step="validate_step_1_all"
                        <?php echo addQaUniqueIdentifier("{$company_type}-registration__form-next-btn")?>
                        ><?php echo translate('register_form_btn_next');?>
                    </button>
                </div>
            </div>
        </div>
        <!-- END tab 1 -->

        <div role="tabpanel" class="tab-pane fade" id="js-step-register-2" <?php echo addQaUniqueIdentifier("{$company_type}-registration__step-2")?>>
            <?php views()->display('new/register/step_2_' . $register_type . '_view'); ?>
        </div>
        <!-- END tab 2 -->

        <div role="tabpanel" class="tab-pane fade" id="js-step-register-another" <?php echo addQaUniqueIdentifier("{$company_type}-registration__step-2-5")?>>
            <?php views()->display('new/register/another_account_view', array('registered_user_type' => $company_type)); ?>
        </div>
        <!-- END tab another -->

        <div role="tabpanel" class="tab-pane fade" id="js-step-register-3" <?php echo addQaUniqueIdentifier("{$company_type}-registration__step-3")?>>
            <?php views()->display('new/register/step_last_all_view'); ?>
            <?php views()->display('new/register/register_submit_btns_view'); ?>
        </div>
        <!-- END tab 3 -->
    </div>

    <input type="hidden" name="register_type" value="<?php echo $register_type; ?>">
</form>

<?php
    echo dispatchDynamicFragment(
            "register_forms:register_steps",
            [
                translate('register_error_country_code'),
                translate('register_error_phone_mask'),
                translate('register_validate_email_message')
            ],
            true
        );
?>
