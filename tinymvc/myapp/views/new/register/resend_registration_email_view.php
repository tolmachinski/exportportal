<div class="js-modal-flex wr-modal-flex inputs-40">
	<form class="modal-flex__form validateModal js-action" data-js-action="registration:resend-confirm-email">
		<div class="modal-flex__content">
            <div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span><?php echo translate('auth_form_resend_confirmation_email_text_info');?></span></div>
            <div class="form-group">
                <label class="input-label"><?php echo translate('register_label_email');?></label>
                <input
                    class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces],maxSize[100]]"
                    type="text"
                    name="email"
                    placeholder="<?php echo translate('register_label_email');?>"
                    value="<?php if(!empty($email)){ echo $email; }?>"
                    <?php if (!empty($email)) { ?>readonly<?php } ?>
                >
            </div>
		</div>
		<div class="modal-flex__btns">
            <div class="modal-flex__btns-left"></div>
            <div class="modal-flex__btns-right">
                <button
                    class="btn btn-primary"
                    type="submit"
                >
                    <?php echo translate('register_form_btn_resend');?>
                </button>
            </div>
		</div>
	</form>
</div>

<?php echo dispatchDynamicFragment(
    "register_forms:resend_email",
    [__SITE_URL . 'register/ajax_operations/resend_confirmation_email'],
    true
); ?>
