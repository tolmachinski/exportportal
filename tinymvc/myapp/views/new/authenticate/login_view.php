<div class="main-login footer-connect">
    <picture class="main-login-background">
        <source media="(max-width: 375px)" srcset="<?php echo asset("public/build/images/login/bg-login-xs.jpg"); ?>">
        <source media="(max-width: 575px)" srcset="<?php echo asset("public/build/images/login/bg-login-mobile.jpg"); ?>">
        <source media="(max-width: 991px)" srcset="<?php echo asset("public/build/images/login/bg-login-tablet.jpg"); ?>">
        <img
            class="image image-pixelated image-cover--top"
            src="<?php echo asset("public/build/images/login/bg-login-desktop.jpg")?>"
            alt="login header"
        >
    </picture>
	<div class="main-login-form-wrp">
		<?php app()->view->display('new/authenticate/login_form_view'); ?>
	</div>
</div>
<?php encoreLinks()?>
