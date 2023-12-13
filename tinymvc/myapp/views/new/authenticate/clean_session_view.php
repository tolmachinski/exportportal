<div class="js-main-login-delog main-login-delog<?php echo isset($show_form) ? ' main-login-delog--show-simple' : '';?>">
    <p class="main-login-delog__txt">
        <?php echo translate('auth_form_login_clear_session_text');?>
    </p>

    <span
        id="js-clean-session-btn"
        class="btn btn-primary btn-block cur-pointer js-clean-session-btn call-action"
        data-user="<?php echo isset($id_user) ? $id_user : ''; ?>"
        <?php if(isset($choose_another_account)) { ?>
            data-js-action="login:clean-session-by-id"
        <?php } else{ ?>
            data-js-action="login:clean-session"
        <?php } ?>
    >
        <?php echo translate('auth_form_login_clear_session_btn'); ?>
    </span>

    <div class="main-login-delog__or">
        <span><?php echo translate('header_navigation_login_or'); ?></span>
    </div>

    <span
        id="js-choose-another-account"
        class="btn btn-primary btn-block cur-pointer js-choose-another-account call-action"
        <?php if (isset($choose_another_account)) { ?>
            data-js-action="login:choose-another-account"
        <?php } else { ?>
            data-js-action="login:login-another-account"
        <?php } ?>
    >
        <?php echo translate('auth_form_login_signin_other_account'); ?>
    </span>
</div>

<?php echo dispatchDynamicFragment("login:clean-session", null, true); ?>
