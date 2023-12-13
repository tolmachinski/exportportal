<?php tmvc::instance()->controller->view->display('new/google_recaptcha/script_inclusions');?>

<div class="container-center-sm mt-25">
	<div class="main-forgot-wr">
		<form class="main-forgot__item main-login-form validengine js_forgot_password_form js_first_step_block" method="post" data-callback="restore_password">
			<div class="main-login-form__ttl">
				<h1><?php echo translate('auth_form_forgot_password_header');?></h1>
				<h3><?php echo translate('auth_form_forgot_password_sub_header');?></h3>
				<h4><?php echo translate('auth_form_forgot_password_sub_header_text');?></h4>
			</div>

			<input
                class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces]]"
                type="text"
                name="user_email"
                placeholder="<?php echo translate('auth_form_forgot_password_input_email_placeholder', null, true);?>"
                value="<?php echo $user_email?>"
                <?php echo addQaUniqueIdentifier('page__forgot-password__email-input'); ?>
            >

			<button class="btn btn-primary btn-block" type="submit" <?php echo addQaUniqueIdentifier('page__forgot-password__submit-btn'); ?>>
                <?php echo translate('auth_form_forgot_password_submit_btn');?>
            </button>
		</form>

		<div class="main-forgot__item main-login-form js_second_step_block display-n">
			<div class="main-login-form__ttl">
				<h2><?php echo translate('auth_form_forgot_sent_header');?></h2>
				<h4><?php echo translate('auth_form_forgot_sent_header_sub');?></h4>
			</div>

			<span class="btn btn-outline-dark btn-block js_return_to_forgot_form_btn"><?php echo translate('auth_form_forgot_sent_resend_email');?></span>

			<a class="btn btn-primary btn-block mt-15" href="<?php echo __SITE_URL . 'login';?>"><?php echo translate('auth_form_forgot_sent_btn_login');?></a>
        </div>
	</div>
</div>

<script>

var restore_password = function(form) {

    googleRecaptchaValidation(recaptcha_parameters, form).then(function(form) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: "<?php echo __SITE_URL . 'authenticate/ajax_forgot';?>",
            data: $(form).serialize(),
            beforeSend: function() {
                showLoader(".main-forgot__item");
            },
            success: function(resp) {
                hideLoader(".main-forgot__item");

                systemMessages(resp.message, resp.mess_type);

                if (resp.mess_type == 'success') {
                    $('.js_forgot_password_form')[0].reset();
                    $('.js_first_step_block').hide();
                    $('.js_second_step_block').show();
                }
            }
        });
    });
}

$('.js_return_to_forgot_form_btn').on('click', function(event){
	$('.js_second_step_block').hide();
	$('.js_first_step_block').show();
});

</script>

