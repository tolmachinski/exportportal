<div class="account-registration-success">
    <div class="account-registration-success__heading">
        <div class="account-registration-success__icon"><?php echo widgetGetSvgIconEpl("success-circle", 60, 60); ?></div>
        <h2 class="account-registration-success__ttl"><?php echo translate('epl_register_success_ttl'); ?></h2>
        <p class="account-registration-success__desc"><?php echo translate('epl_register_success_desc'); ?></p>
    </div>

    <div class="account-registration-success__delimiter"></div>

    <p><?php echo translate('epl_register_confirm_email_txt'); ?></p>
    <p><?php echo translate('epl_register_show_inbox_txt'); ?></p>
    <p class="account-registration-success__indent account-registration-success__txt-bold"><?php echo translate('epl_register_didnt_receive_email'); ?></p>

    <ul class="success-registration-confirm-list">
        <li class="success-registration-confirm-list__item">
            <??>
            <?php
                echo translate('epl_register_correct_email', [
                    '{{EMAIL}}' => sprintf("<span %s>%s</span>", addQaUniqueIdentifier("{$registerType}-registration__form-success_email"), $email)
                ]);
            ?>
            <button
                class="account-registration-success__contact-btn js-fancybox"
                type="button"
                data-type="ajax"
                data-src="<?php echo __CURRENT_SUB_DOMAIN_URL . 'contact/popup_forms/contact_us'; ?>"
                data-title="<?php echo translate('epl_register_contact_us_popup_title', null, true); ?>"
                data-wr-class="fancybox-contact-us"
                data-mw="775"
            >
                <?php echo translate('epl_register_contact_us_btn'); ?>
            </button>
        </li>
        <li class="success-registration-confirm-list__item"><?php echo translate('epl_register_check_spam_folder'); ?></li>
        <li class="success-registration-confirm-list__item"><?php echo translate('epl_register_get_email_again'); ?></li>
    </ul>

    <button
        class="btn btn-outline-primary account-registration-success__request-btn js-fancybox"
        type="button"
        data-type="ajax"
        data-src="<?php echo __CURRENT_SUB_DOMAIN_URL; ?>register/popup_forms/resend_confirmation_email?email=<?php echo $email;?>"
        data-title="<?php echo translate('epl_register_request_confirmation_email', null, true); ?>"
        data-mw="485"
    >
        <?php echo translate('epl_register_request_confirmation_email'); ?>
    </button>

    <p class="account-registration-success__indent">
        <?php echo translate('epl_register_having_trouble', [
            '{{START_TAG}}' => '<span class="account-registration-success__txt-bold">',
            '{{END_TAG}}'   => '</span>'
        ]); ?>
        <button
            class="account-registration-success__contact-btn js-fancybox"
            type="button"
            data-type="ajax"
            data-src="<?php echo __CURRENT_SUB_DOMAIN_URL . 'contact/popup_forms/contact_us'; ?>"
            data-title="<?php echo translate('epl_register_contact_us_popup_title', null, true); ?>"
            data-wr-class="fancybox-contact-us"
            data-mw="775"
            <?php echo addQaUniqueIdentifier("{$registerType}-registration__form-success-contact-btn"); ?>
        >
            <?php echo translate('epl_register_having_trouble_contact_btn'); ?>
        </button>
    </p>
</div>
