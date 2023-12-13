<div class="epl-forgot footer-connect">
    <div class="container-center">
        <picture class="epl-forgot__bg">
            <source media="(max-width: 574px)" srcset="<?php echo asset("public/build/images/epl/header-img-mobile.jpg"); ?>">
            <source media="(max-width: 1024px)" srcset="<?php echo asset("public/build/images/epl/header-img-tablet.jpg"); ?>">
            <img
                class="image"
                width="1920"
                height="400"
                src="<?php echo asset("public/build/images/epl/header-img.jpg"); ?>"
                alt="Reset password header"
            >
        </picture>

        <div class="epl-forgot__content js-epl-forgot-content js-first-step-block">
            <div class="epl-forgot__heading">
                <div class="epl-forgot__icon"><?php echo widgetGetSvgIconEpl("question-circle", 60, 60); ?></div>
                <h1 class="epl-forgot__ttl"><?php echo translate('epl_forgot_password_ttl'); ?></h1>
                <p class="epl-forgot__desc"><?php echo translate('epl_forgot_password_desc'); ?></p>
            </div>

            <form id="js-epl-forgot-form" class="epl-forgot__form" method="post" autocomplete="off">
                <input autocomplete="off" type="text" class="hidden">
                <input
                    type="email"
                    name="user_email"
                    placeholder="<?php echo translate('epl_forgot_password_input_email_placeholder', null, true); ?>"
                    value="<?php echo $userEmail?>"
                    autocomplete="off"
                    autocapitalize="off"
                    autocorrect="off"
                    autofill="off"
                    <?php echo addQaUniqueIdentifier("epl-forgot__form_email-input"); ?>
                >
                <button
                    class="btn btn-primary epl-forgot__submit-btn"
                    type="submit"
                    <?php echo addQaUniqueIdentifier("epl-forgot__form_submit-btn"); ?>
                >
                    <?php echo translate('epl_forgot_password_submit_btn'); ?>
                </button>
            </form>

            <div class="epl-forgot__login-link">
                <?php echo translate('epl_forgot_password_have_account', [
                    '{{START_LINK}}' => '<a class="link" href="' . __SHIPPER_URL . 'login">',
                    '{{END_LINK}}'   => '</a>']);
                ?>
            </div>
        </div>

        <div class="epl-forgot__content epl-forgot__content--second js-second-step-block">
            <div class="epl-forgot__heading">
                <div class="epl-forgot__icon epl-forgot__icon--success"><?php echo widgetGetSvgIconEpl("success-circle", 60, 60); ?></div>
                <h2 class="epl-forgot__ttl"><?php echo translate('epl_forgot_password_sent_ttl'); ?></h2>
                <p class="epl-forgot__desc"><?php echo translate('epl_forgot_password_sent_desc'); ?></p>
            </div>

            <a
                class="btn btn-primary btn-block epl-forgot__login-btn"
                href="<?php echo __SHIPPER_URL . 'login';?>"
                <?php echo addQaUniqueIdentifier("epl-forgot__form_sign-in-btn"); ?>
            >
                <?php echo translate('epl_forgot_password_sign_in_link'); ?>
            </a>

            <button
                class="btn btn-outline-primary btn-block call-action epl-forgot__email-btn"
                type="button"
                data-js-action="epl-forgot:return-to-forgot-form"
                <?php echo addQaUniqueIdentifier("epl-forgot__form_didnt-email-btn"); ?>
            >
                <?php echo translate('epl_forgot_password_didnt_get_email_btn'); ?>
            </button>
        </div>
    </div>
</div>

<?php
    echo dispatchDynamicFragment("epl-forgot:init-validation", null, true);
    encoreLinks();
?>
