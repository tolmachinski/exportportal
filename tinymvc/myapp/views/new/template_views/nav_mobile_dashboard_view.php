<div class="mep-header-login">
    <div class="mep-header-login__menu">
        <div class="mep-header-login__menu-l"></div>
        <div class="mep-header-login__menu-r">
            <button
                class="mep-header-user__preferences call-action notranslate"
                data-js-action="navbar:open-popup-preferences"
                title="<?php echo translate("header_popup_preferences_title", null, true); ?>"
                <?php echo addQaUniqueIdentifier('global__mep-dashboard__user-preferences-btn'); ?>
            >
                <span><?php echo cookies()->getCookieParam('_ulang'); ?></span>
                <span class="mep-header-user__preferences-delimiter">|</span>
                <?php echo cookies()->getCookieParam('currency_key'); ?>
            </button>

            <button
                class="link link--social ep-icon ep-icon_share-stroke2 share-social fancyboxLang"
                data-fancybox-href="#share-social"
                data-title="<?php echo translate('home_share_link_title', null, true); ?>">
            </button>
        </div>
    </div>

    <div class="mep-header-login__content">
        <form
            class="mep-header-login__form validengine"
            method="post"
            data-js-action="login:authentification"
        >
            <div class="mep-header-login__line pt-0">
                <h3 class="mep-header-login__ttl"><?php echo translate('header_mep_sign_in_btn'); ?></h3>
                <span class="mep-header-login__or"><?php echo translate('header_navigation_login_or'); ?></span>
                <a class="link" href="<?php echo get_static_url('register/index'); ?>"><?php echo translate('header_mep_register_btn'); ?></a>
            </div>

            <input
                class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces]]"
                type="email"
                name="email"
                placeholder="<?php echo translate('auth_form_login_input_email_placeholder', null, true); ?>">

            <span class="view-password">
                <button
                    class="ep-icon ep-icon_invisible call-action"
                    data-js-action="login:view-password"
                    type="button"
                ></button>
                <input
                    class="validate[required, minSize[2]]"
                    type="password"
                    name="password"
                    placeholder="<?php echo translate('auth_form_login_input_password_placeholder', null, true); ?>">
            </span>

            <button class="btn btn-primary btn-block js-btn-login" type="submit"><?php echo translate('header_mep_sign_in_btn'); ?></button>

            <div class="mep-header-login__line pt-20 pb-0">
                <label class="custom-checkbox">
                    <input type="checkbox" name="remember" value="1" />
                    <span class="custom-checkbox__text"><?php echo translate('auth_form_login_label_keep_signedin'); ?></span>
                </label>

                <a class="link fs-14" href="<?php echo __SITE_URL . 'authenticate/forgot'; ?>"><?php echo translate('auth_form_login_link_forgot_password'); ?></a>
            </div>

            <?php views()->display('new/authenticate/clean_session_view'); ?>
        </form>
    </div>

    <div class="mep-header-login__switch-account"></div>
    <?php
        echo dispatchDynamicFragment(
            'dashboard:mobile-login',
            null,
            false
        );
    ?>
</div>
