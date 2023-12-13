<div id="js-widget-cookie-container">
    <div class="widget-cookie">
        <div class="widget-cookie__img">
            <img class="image" src="<?php echo asset("public/build/images/epl/cookies.png"); ?>" alt="Cookies">
        </div>

        <div class="widget-cookie__info">
            <span class="widget-cookie__text">We use cookies on our website to provide the best experience for you.</span>

            <a class="widget-cookie__link" href="<?php echo __SITE_URL . 'cookieconsent'; ?>" target="_blank">
                Learn more
            </a>
        </div>

        <a
            class="widget-cookie__button btn btn-primary call-action"
            data-callback="submitCookieBanner"
            data-js-action="popup:submit-cookie-banner"
        >Accept</a>
    </div>

    <?php
        echo dispatchDynamicFragment(
            "popup:cookies_accept",
            null,
            true
        );
    ?>
</div>



