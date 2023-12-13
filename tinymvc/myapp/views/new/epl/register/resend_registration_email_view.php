<div class="wr-modal-flex">
    <form id="js-resend-email-form" class="modal-flex__form request-confirmation-email-form">
        <div class="modal-flex__content">
            <p class="request-confirmation-email-form__desc"><?php echo translate("epl_request_confirmation_popup_text"); ?></p>
            <input
                type="email"
                name="email"
                placeholder="<?php echo translate('epl_request_confirmation_popup_input_email_placeholder', null, true); ?>"
                value="<?php if(!empty($email)){ echo $email; }?>"
                <?php if (!empty($email) ) { ?>readonly<?php } ?>
            >
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary request-confirmation-email-form__submit" type="submit">
                    <?php echo translate("epl_resend_confirmation_email_btn"); ?>
                </button>
            </div>
        </div>
    </form>
</div>

<?php echo dispatchDynamicFragment(
    "epl-authorization:resend-confirmation-email",
    null,
    true
); ?>

