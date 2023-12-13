<div class="container-center-sm mt-25">
	<div class="main-forgot-wr">
		<div class="main-forgot__item main-login-form">
			<div class="main-login-form__ttl">
				<h1><?php echo translate('auth_form_forgot_sent_header');?></h1>
				<h4><?php echo translate('auth_form_forgot_sent_header_sub');?></h4>
			</div>

			<a class="btn btn-outline-dark btn-block" href="<?php echo __SITE_URL . 'authenticate/forgot';?>"><?php echo translate('auth_form_forgot_sent_resend_email');?></a>

			<a class="btn btn-primary btn-block mt-15" href="<?php echo __SITE_URL . 'login';?>"><?php echo translate('auth_form_forgot_sent_btn_login');?></a>
		</div>
	</div>
</div>
