<div
    class="js-epl-login-delog epl-login-delog<?php echo isset($showSimpleDelog) ? ' epl-login-delog--show-simple' : ''; ?>"
    <?php echo addQaUniqueIdentifier('epl-login__form-clean-session-block'); ?>
>
    <h2 class="epl-login-delog__ttl"><?php echo translate('epl_clean_session_ttl'); ?></h2>
    <div>
        <p class="epl-login-delog__txt">
            <?php echo translate('epl_clean_session_text1'); ?>
        </p>
        <p class="epl-login-delog__txt">
            <?php echo translate('epl_clean_session_text2'); ?>
        </p>
    </div>

    <button
        id="js-epl-clean-session-btn"
        class="btn btn-primary epl-login-delog__btn call-action"
        type="button"
        data-js-action="epl-login:clean-session"
        <?php echo addQaUniqueIdentifier('epl-login__form-clear-session-btn'); ?>
    >
        <?php echo translate('epl_clean_session_ttl'); ?>
    </button>

    <button
        id="js-epl-login-another-account"
        class="btn btn-outline-primary epl-login-delog__btn call-action"
        type="button"
        data-js-action="epl-login:login-another-account"
        <?php echo addQaUniqueIdentifier('epl-login__form-another-account-btn'); ?>
    >
        <?php echo translate('epl_clean_session_switch_account_btn'); ?>
    </button>
</div>
