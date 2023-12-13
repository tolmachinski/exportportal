<div class="container-center-sm mt-25">
	<div class="main-forgot-wr">
		<form class="main-forgot__item main-login-form validengine" method="post" data-callback="reset_password">
			<div class="pb-30 tac">
				<h1><?php echo translate('auth_form_reset_password_header');?></h1>
				<h4><?php echo translate('auth_form_reset_password_sub_header');?></h4>
			</div>

			<div class="main-forgot__row">
				<div class="main-forgot__col">
					<span class="view-password">
						<span class="ep-icon ep-icon_invisible call-function" data-callback="viewPassword"></span>
						<input
                            id="password"
                            class="validate[required]"
                            type="password"
                            name="pwd"
                            placeholder="<?php echo translate('auth_form_reset_password_input_new_password_placeholder', null, true);?>"
                            <?php echo addQaUniqueIdentifier('page__forgot-password__new-password-input'); ?>
                        >
					</span>
					<span class="view-password">
						<span class="ep-icon ep-icon_invisible call-function" data-callback="viewPassword"></span>
						<input
                            class="validate[required,equals[password]]"
                            type="password"
                            name="pwd_confirm"
                            placeholder="<?php echo translate('auth_form_reset_password_input_confirm_new_password_placeholder', null, true);?>"
                            <?php echo addQaUniqueIdentifier('page__forgot-password__confirm-password-input'); ?>
                        >
					</span>
				</div>
				<div class="main-forgot__col">
					<?php app()->view->display('new/authenticate/password_security_view'); ?>
				</div>
			</div>

			<input
                type="hidden"
                name="code"
                value="<?php echo $code;?>"
                <?php echo addQaUniqueIdentifier('page__forgot-password__code-input'); ?>
            >
			<input
                type="hidden"
                name="id_principal"
                value="<?php echo $id_principal?>"
                <?php echo addQaUniqueIdentifier('page__forgot-password__id-principal-input'); ?>
            />
			<button
                class="btn btn-primary btn-block"
                type="submit"
                <?php echo addQaUniqueIdentifier('page__forgot-password__submit-btn'); ?>
            >
                <?php echo translate('auth_form_reset_password_submit_btn');?>
            </button>
		</form>
	</div>
</div>

<script>
var reset_password = function(form){
	var fdata = $(form).serialize();

	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: __site_url + 'authenticate/reset_ajax',
		data: fdata,
		beforeSend: function(){
			showLoader(".main-forgot__item");
		},
		success: function(resp){
			hideLoader(".main-forgot__item");

			if (resp.mess_type == 'success') {
				$('html, body').animate({
					scrollTop: $("body").offset().top
				}, 1000);

				$('.main-forgot__item').html('<div class="success-alert-b"><i class="ep-icon ep-icon_ok-circle"></i> <span>'+resp.message+'</span></div>');
			} else {
				systemMessages( resp.message, resp.mess_type );
			}
		}
	});
	return false;
}
</script>

