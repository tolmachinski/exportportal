<div class="epl-forgot footer-connect">
    <div class="container-center">
        <picture class="epl-forgot__bg">
            <source media="(max-width: 575px)" srcset="<?php echo asset("public/build/images/epl/header-img-mobile.jpg"); ?>">
            <source media="(max-width: 1024px)" srcset="<?php echo asset("public/build/images/epl/header-img-tablet.jpg"); ?>">
            <img
                class="image"
                width="1920"
                height="400"
                src="<?php echo asset("public/build/images/epl/header-img.jpg"); ?>"
                alt="Reset password header"
            >
        </picture>

        <?php if (!isset($error)) { ?>
            <div class="epl-forgot__content js-epl-forgot-content">
                <div class="epl-forgot__heading">
                    <div class="epl-forgot__icon"><?php echo widgetGetSvgIconEpl("warning-circle-stroke", 60, 60); ?></div>
                    <h1 class="epl-forgot__ttl"><?php echo translate('epl_auth_reset_ttl'); ?></h1>
                    <p class="epl-forgot__desc"><?php echo translate('epl_auth_reset_desc'); ?></p>
                </div>

                <form id="js-epl-reset-form" class="epl-forgot__form" method="post" autocomplete="off">
                    <div class="view-password">
                        <input
                            id="js-password"
                            type="password"
                            name="pwd"
                            placeholder="<?php echo translate('epl_auth_reset_password_placeholder', null, true); ?>"
                            <?php echo addQaUniqueIdentifier("reset-password__form_password-input"); ?>
                        >
                        <button class="js-view-password-btn" type="button" tabindex="-1">
                            <i class="ep-icon ep-icon_invisible"></i>
                        </button>
                        <?php views()->display('new/epl/authenticate/password_strength_view'); ?>
                    </div>

                    <div class="view-password">
                        <input
                            type="password"
                            name="pwd_confirm"
                            placeholder="<?php echo translate('epl_auth_reset_confirm_password_placeholder', null, true); ?>"
                            <?php echo addQaUniqueIdentifier("reset-password__form_confirm-password-input"); ?>
                        >
                        <button class="js-view-password-btn" type="button" tabindex="-1">
                            <i class="ep-icon ep-icon_invisible"></i>
                        </button>
                    </div>

                    <input type="hidden" name="code" value="<?php echo $code;?>">
                    <input type="hidden" name="id_principal" value="<?php echo $idPrincipal?>" />
                    <button
                        class="btn btn-primary epl-forgot__submit-btn"
                        type="submit"
                        <?php echo addQaUniqueIdentifier("reset-password__form_submit-btn"); ?>
                    >
                        <?php echo translate('epl_auth_reset_submit_btn'); ?>
                    </button>
                </form>
            </div>
        <?php } else { ?>
            <div class="epl-forgot__heading">
                <div class="epl-forgot__icon"><?php echo widgetGetSvgIconEpl("warning-circle-stroke", 60, 60); ?></div>
                <h1 class="epl-forgot__ttl epl-forgot__ttl--upper"><?php echo translate('epl_auth_reset_expire_ttl'); ?></h1>
                <p class="epl-forgot__desc"><?php echo translate('epl_auth_reset_expire_desc'); ?></p>
            </div>
        <?php } ?>
    </div>
</div>

<?php if (!isset($error)) { ?>
<script type="text/template" id="js-epl-password-changed-content">
    <div class="epl-forgot__heading">
        <div class="epl-forgot__icon epl-forgot__icon--success"><?php echo widgetGetSvgIconEpl("success-circle", 60, 60); ?></div>
        <h2 class="epl-forgot__ttl"><?php echo translate('epl_auth_reset_password_changed_ttl'); ?></h2>
        <p class="epl-forgot__desc">
            <?php echo translate('epl_auth_reset_password_changed_desc', [
                '{{START_TAG}}' => '<span>',
                '{{END_TAG}}'   => '</span>']);
            ?>
        </p>
        <a
            class="btn btn-primary epl-forgot__login-btn"
            href="<?php echo __SHIPPER_URL . 'login'; ?>"
            <?php echo addQaUniqueIdentifier("reset-password__sign-in-btn"); ?>
        >
            <?php echo translate('epl_auth_reset_password_changed_sing_in_btn'); ?>
        </a>
    </div>
</script>

<?php echo dispatchDynamicFragment("epl-reset-pswd:init-validation", null, true); ?>
<?php } ?>
