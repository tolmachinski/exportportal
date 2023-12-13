<?php $qa_popup = isset($popup_login) && (int) $popup_login === 1 ? '_popup' : '';?>
<?php $atasPrefixType = $qa_popup ? 'popup__' : 'page__'; ?>
<form
	class="main-login-form <?php echo (empty($popup_login) ? 'validengine' : 'validateModal');?>"
	<?php if(empty($popup_login)){?>data-sto="-60"<?php }?>
	method="post"
    data-js-action="login:authentification"
>
	<div class="main-login-form__ttl">
		<h1 <?php echo (!empty($popup_login) ? 'class="display-n"' : '');?>><?php echo translate('auth_form_txt_login');?></h1>
		<h2 class="h3-title"><?php echo translate('auth_form_login_welcome_text');?></h2>
	</div>

	<?php if(!empty($referer)){?>
		<input type="hidden" name="referer" value="<?php echo $referer; ?>"/>
	<?php }?>
    <label class="main-login-hidden-label" for="login-email"><?php echo translate('auth_form_login_input_email_placeholder', null, true);?></label>
	<input id="login-email" <?php echo addQaUniqueIdentifier("login__form-email{$qa_popup}")?> class="validate[required,custom[noWhitespaces],custom[emailWithWhitespaces]]" type="text" name="email" placeholder="<?php echo translate('auth_form_login_input_email_placeholder', null, true);?>" value="<?php $email?>"/>
    <label class="main-login-hidden-label" for="login-password"><?php echo translate('auth_form_login_input_password_placeholder', null, true);?></label>
	<span class="view-password">
		<span
            id="js-btn-view-pass"
            class="ep-icon ep-icon_invisible call-action"
            data-js-action="login:view-password"
        ></span>
		<input id="login-password" <?php echo addQaUniqueIdentifier("login__form-password{$qa_popup}")?> type="password" name="password" class="validate[required]" placeholder="<?php echo translate('auth_form_login_input_password_placeholder', null, true);?>"/>
	</span>

	<button <?php echo addQaUniqueIdentifier("login__form-submit{$qa_popup}")?> class="btn btn-primary btn-block" type="submit" name="login"><?php echo translate('auth_form_login_btn_login');?></button>

	<div class="flex-display flex-ai--c w-100pr pt-20 h-45">
		<label <?php echo addQaUniqueIdentifier("login__form-checkbox{$qa_popup}")?> class="w-50pr main-login-form__stay-signed custom-checkbox">
			<input type="checkbox" name="remember" value="1"/>
            <span class="custom-checkbox__text"><?php echo translate('auth_form_login_label_keep_signedin');?></span>
		</label>

		<div class="w-50pr tar">
			<a href="<?php echo __SITE_URL . 'authenticate/forgot'?>" <?php echo addQaUniqueIdentifier("{$atasPrefixType}login__form_forgot-password-link"); ?>>
				<?php echo translate('auth_form_login_link_forgot_password');?>
			</a>
		</div>
	</div>

	<?php
	if(config('oauth_facebook_status') || config('oauth_google_status') || config('oauth_linkedin_status')){?>
		<div class="main-login-form__social">
			<?php if(config('oauth_facebook_status')){?>
				<a class="link call-function ep-icon ep-icon_facebook-square txt-facebook" data-callback="socialRegister" title="Register with Facebook" data-type="fb"></a>
			<?php }?>
			<?php if(config('oauth_google_status')){?>
				<a class="link call-function ep-icon ep-icon_google-plus-square txt-google-plus" data-callback="socialRegister" title="Register with Google+" data-type="go"></a>
			<?php }?>
			<?php if(config('oauth_linkedin_status')){?>
				<a class="link call-function ep-icon ep-icon_linkedin-square txt-linkedin" data-callback="socialRegister" title="Register with LinkedIn" data-type="ln"></a>
			<?php }?>
		</div>
	<?php }?>

	<div class="main-login-form__sign-up">
		<?php echo translate('auth_form_login_sign_up_text');?>
		<a href="<?php echo get_static_url('register/index');?>"><?php echo translate('register_button_text');?></a>
	</div>

	<?php views()->display('new/authenticate/clean_session_view')?>
</form>

<?php
    echo dispatchDynamicFragment(
        "login:stay-signed-check",
        null,
        true
    );
?>
