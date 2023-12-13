<div>
    <div class="success-alert-b">
        <i class="ep-icon ep-icon_ok-circle"></i>
        <span><?php echo translate('register_thank_you_message'); ?></span>
    </div>

    <div class="ep-tinymce-text pt-20">
        <div class="mb-20 lh-22">
            <?php echo translate('register_text_confirm_email'); ?>
        </div>
        <strong><?php echo translate('register_question_confirm_email'); ?></strong>
        <ul>
            <li><?php echo translate('register_email_link_contact_text', array('[[EMAIL]]' => '<strong ' . addQaUniqueIdentifier("registration__done-email") . '>' . $email . '</strong>', '[[LINK_START]]' => '<a class="fancybox.ajax fancyboxValidateModal" data-wrap-class="fancybox-contact-us" data-title="Contact us" href="'. __SITE_URL . 'contact/popup_forms/contact_us">', '[[LINK_END]]' => '</a>'));?></li>
            <li><?php echo translate('register_check_spam_folder'); ?></li>
            <li><?php echo translate('regiser_otherwise_message'); ?></li>
        </ul>
        <a
            class="btn btn-dark fancyboxValidateModal fancybox.ajax mnw-285"
            data-mw="500"
            href="<?php echo __SITE_URL;?>register/popup_forms/resend_confirmation_email?email=<?php echo $email;?>"
            data-title="<?php echo translate('register_resend_confirmation_title');?>"
        >
            <?php echo translate('register_form_btn_request_email');?>
        </a>

        <div class="mt-30 lh-24">
            <div><strong><?php echo translate('register_having_trouble_question'); ?></strong></div>
            <?php echo translate('register_word_please');?> <a
                    class="fancybox.ajax fancyboxValidateModal"
                    data-title="<?php echo translate('register_form_btn_contact', null, true);?>"
                    href="<?php echo __SITE_URL;?>contact/popup_forms/contact_us"
                >
                    <?php echo translate('register_form_btn_contact');?></a>.
        </div>
    </div>
</div>
