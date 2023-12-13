<div class="epl-login footer-connect">
    <picture class="epl-login__bg">
        <source media="(max-width: 575px)" srcset="<?php echo asset("public/build/images/epl/header-img-mobile.jpg"); ?>">
        <source media="(max-width: 1024px)" srcset="<?php echo asset("public/build/images/epl/header-img-tablet.jpg"); ?>">
        <img
            class="image"
            width="1920"
            height="400"
            src="<?php echo asset("public/build/images/epl/header-img.jpg"); ?>"
            alt="Login header"
        >
    </picture>
	<div class="epl-login-form-wrp">
		<?php views('new/epl/authenticate/login_form_view', ['title' => 'Sign in']); ?>
	</div>
</div>
