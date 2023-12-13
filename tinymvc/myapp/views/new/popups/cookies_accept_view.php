<div
    id="js-widget-cookie-container"
    class="widget-cookie-container"
>
    <div>
        <div class="widget-cookie-container__text">
            <span>This website uses cookies to ensure you get the best experience on our website.</span>
        </div>
        <?php
            $linkCP = __SITE_URL . 'cookieconsent';
            if(isset($webpackData)){
                $linkCP .= '/webpack';
            }
        ?>
        <a class="widget-cookie-container__link fancybox fancybox.ajax" href="<?php echo $linkCP; ?>" data-title="Cookie Policy">Learn more</a>
    </div>
    <a
        class="widget-cookie-container__button btn btn-primary btn-block call-action"
        data-callback="submitCookieBanner"
        data-js-action="popup:submit-cookie-banner"
        href="#"
    >Got it!</a>

    <?php
        echo dispatchDynamicFragment(
            "popup:cookies_accept",
            null,
            true
        );
    ?>
</div>


