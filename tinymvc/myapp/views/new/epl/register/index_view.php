<div id="js-epl-account-form" class="account-registration footer-connect">
    <div class="account-registration-aside">
        <a class="link" href="<?php echo __SHIPPER_URL; ?>">
            <img
                width="140"
                height="40"
                src="<?php echo asset("public/build/images/epl/logo.svg");?>"
                alt="EPL"
            >
        </a>

        <h2 class="account-registration-aside__ttl"><?php echo translate('epl_register_side_ttl'); ?></h2>
        <p class="account-registration-aside__desc"><?php echo translate('epl_register_side_desc'); ?></p>
        <picture>
            <source media="(max-width: 767px)" srcset="<?php echo getLazyImage(50, 50); ?>">
            <img
                class="account-registration-aside__bg"
                width="580"
                height="950"
                src="<?php echo asset("public/build/images/epl/register/register_page_bg.jpg");?>"
                alt="EPL"
            >
        </picture>
    </div>

    <div class="account-registration__content">
        <div class="account-registration__heading">
            <p class="account-registration__heading-txt"><?php echo translate('epl_register_heading_already_have_account'); ?></p>
            <a class="btn btn-sm btn-outline-primary" href="<?php echo __SHIPPER_URL . 'login'; ?>"><?php echo translate('epl_register_heading_sign_in_btn'); ?></a>
        </div>

        <div id="js-epl-wr-register-form" class="account-registration__inner">
            <h1 class="account-registration__main-title">
                <?php echo translate('register_form_title_ff'); ?>
            </h1>

            <?php views()->display('new/epl/register/register_steps_view'); ?>
        </div>
    </div>
</div>
