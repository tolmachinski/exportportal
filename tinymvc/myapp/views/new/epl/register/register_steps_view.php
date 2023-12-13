<ul id="js-epl-register-steps" class="form-steps">
    <li class="form-steps__item js-epl-register-steps-item active" data-content="#js-epl-step-register-1">
        <div class="form-steps__inner js-epl-register-steps-item-inner">
            <div class="form-steps__point js-form-steps-point">
                <i class="ep-icon ep-icon_ok-stroke2"></i>
            </div>

            <div class="form-steps__txt">
                <?php echo translate('register_form_step_1_title'); ?>
            </div>
        </div>
    </li>

    <li class="form-steps__item js-epl-register-steps-item" data-content="#js-epl-step-register-2">
        <div class="form-steps__inner js-epl-register-steps-item-inner">
            <div class="form-steps__point js-form-steps-point">
                <i class="ep-icon ep-icon_ok-stroke2"></i>
            </div>
            <div class="form-steps__txt">
                <?php echo translate('register_form_step_2_title'); ?>
            </div>
        </div>
    </li>

    <li class="form-steps__item js-epl-register-steps-item" data-content="#js-epl-step-register-3">
        <div class="form-steps__inner js-epl-register-steps-item-inner">
            <div class="form-steps__point js-form-steps-point">
                <i class="ep-icon ep-icon_ok-stroke2"></i>
            </div>
            <div class="form-steps__txt">
                <?php echo translate('register_form_step_3_title'); ?>
            </div>
        </div>
    </li>
</ul>

<form
    id="js-epl-register-form"
    class="account-registration-form js-ep-self-autotrack"
    data-sto="-55"
    autocomplete="off"
    data-tracking-events="submit"
    data-tracking-fields="<?php echo cleanOutput(json_encode(['email', 'fname', 'lname', 'country_code', 'phone'])); ?>"
    data-tracking-alias="form-register_<?php echo $registerType; ?>"
>
    <?php views()->display('new/epl/register/step_1_view'); ?>

    <input id="js-register-type-input" type="hidden" name="register_type" value="<?php echo $registerType; ?>">
</form>

<?php
    echo dispatchDynamicFragment(
        "epl-register:register_steps",
        [
            translate('register_error_country_code'),
            translate('register_error_phone_mask'),
            translate('register_validate_email_message')
        ],
        true
    );
?>
