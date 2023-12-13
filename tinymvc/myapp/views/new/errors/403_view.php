<link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/403_styles.css');?>" />

<div class="error-page-403 footer-connect">
    <div class="error-page-403__info">
        <h1 class="error-page-403__heading">Error 403: Access Denied</h1>
        <h3 class="error-page-403__subheading">Apologies – access to this page has been restricted.</h3>
        <div class="error-page-403__options">
            <a class="btn btn-primary" href="<?php echo __SITE_URL ?>">Home page</a>
            <button id="js-back-button" class="btn btn-dark">Go back</button>
        </div>
        <div class="error-page-403__notice">If you believe this is an error, please feel free to contact our support team via LiveChat or our email, <a class="txt-black" href="mailto:support@exportportal.com">support@exportportal.com</a></div>
    </div>
</div>

<script>
    document.getElementById("js-back-button").addEventListener("click", function () {
        if (window.history.length < 2) {
            location.href = "<?php echo $referrer; ?>";

            return;
        }
        window.history.back();
    });
</script>
