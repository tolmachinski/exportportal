<?php $qaPopup = isset($popupLogin) && (int) $popupLogin === 1 ? '_popup' : ''; ?>
<form
    id="js-epl-login-form<?php echo $qaPopup; ?>"
    class="epl-login-form js-epl-login-form"
    method="POST"
    <?php echo addQaUniqueIdentifier("epl-login__form{$qaPopup}"); ?>
>
    <div class="epl-login-form__content js-epl-login-form-content">
        <?php if (isset($title)) { ?>
            <h1 class="epl-login-form__ttl"><?php echo translate('epl_login_form_ttl'); ?></h1>
        <?php } ?>
        <div class="epl-login-form__desc"><?php echo translate('epl_login_form_desc'); ?></div>

        <?php if (!empty($referer)) { ?>
            <input type="hidden" name="referer" value="<?php echo $referer; ?>" />
        <?php } ?>

        <div>
            <label class="epl-login-hidden-label" for="epl-login-email"><?php echo translate('epl_login_form_email_label'); ?></label>
            <input
                id="epl-login-email"
                type="email"
                name="email"
                placeholder="<?php echo translate('epl_login_form_email_placeholder', null, true); ?>"
                value="<?php $email ?>"
                <?php echo addQaUniqueIdentifier("epl-login__form_email-input{$qaPopup}"); ?>
            />
        </div>

        <div class="view-password">
            <label class="epl-login-hidden-label" for="epl-login-password"><?php echo translate('epl_login_form_password_label'); ?></label>
            <input
                id="epl-login-password"
                type="password"
                name="password"
                placeholder="<?php echo translate('epl_login_form_password_placeholder', null, true); ?>"
                <?php echo addQaUniqueIdentifier("epl-login__form_password-input{$qaPopup}"); ?>
            />
            <button class="js-view-password-btn" type="button" tabindex="-1">
                <i class="ep-icon ep-icon_invisible"></i>
            </button>
        </div>

        <div class="epl-login-form__actions">
            <label <?php echo addQaUniqueIdentifier("epl-login__form_checkbox{$qaPopup}"); ?> class="custom-checkbox">
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                />
                <span class="custom-checkbox__text"><?php echo translate('epl_login_form_stay_signed_in'); ?></span>
            </label>

            <div class="epl-login-form__forgot-link">
                <a href="<?php echo __SHIPPER_URL . 'authenticate/forgot' ?>">
                    <?php echo translate('epl_login_form_forgot_pass_link'); ?>
                </a>
            </div>
        </div>

        <button
            class="btn btn-primary epl-login-form__submit-btn"
            type="submit"
            name="login"
            <?php echo addQaUniqueIdentifier("epl-login__form_submit-btn{$qaPopup}"); ?>
        >
            <?php echo translate('epl_login_form_sign_in_link'); ?>
        </button>

        <div class="epl-login-form__sign-up">
            <?php echo translate('epl_login_form_register_link', [
                '{{START_LINK}}' => '<a href="' . __SHIPPER_URL . 'register">',
                '{{END_LINK}}'   => '</a>']);
            ?>
        </div>
    </div>

    <?php views()->display('new/epl/authenticate/clean_session_view') ?>
</form>

<?php echo dispatchDynamicFragment("epl-login:init-validation", ['formSelector' => "#js-epl-login-form{$qaPopup}"], true); ?>
