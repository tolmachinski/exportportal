<?php
$newYearSnowFlakes = filter_var(config('new_year_theme'), FILTER_VALIDATE_BOOLEAN) && !isBackstopEnabled(); ?>

<footer class="ep-footer<?php echo $newYearSnowFlakes ? ' ep-footer--new-year' : ''; ?>">
    <?php
        if ($isEPL) {
            views()->display("new/epl/fixed_right_block/fixed_right_block_view");
        } else {
            views()->display("new/fixed_right_block/fixed_right_block_view");
        }
    ?>

    <?php
        if ($newYearSnowFlakes) {
            views()->display("new/holidays/snow_flakes_view");
        }
    ?>

    <div class="ep-footer__content container-1420">
        <div class="ep-footer-who-we-are">
            <a class="ep-footer-who-we-are__link" href="<?php echo __SITE_URL; ?>">
                <h2 class="ep-footer-who-we-are__title">EXPORT PORTAL</h2>
                <p class="ep-footer-who-we-are__paragraph">
                    <?php echo translate('footer_ep_subtitle'); ?>
                </p>
            </a>
            <p class="ep-footer-who-we-are__paragraph ep-footer-who-we-are__paragraph--last">
                <?php echo translate('footer_general_member_of_exima_association', [
                    '[[BR]]'         => '<br>',
                    '[[LINK_START]]' => '<a href="https://exima.com/" target="_blank" rel="noopener">',
                    '[[LINK_END]]'   => '</a>',
                ]); ?>
            </p>
        </div>
        <div class="ep-footer-export-import">
            <h3 class="ep-footer__title ep-footer-export-import__title">
                <?php echo translate('title_navigation_footer_export_import'); ?>
            </h3>

            <ul class="ep-footer-export-import__list">
                <li class="ep-footer-export-import__li">
                    <a class="ep-footer-export-import__link" href="<?php echo __SITE_URL . 'export_import'; ?>">
                        Countries
                    </a>
                </li>
                <li class="ep-footer-export-import__li">
                    <a class="ep-footer-export-import__link" href="<?php echo __SITE_URL . 'buying'; ?>">
                        <?php echo translate('header_navigation_link_buying'); ?>
                    </a>
                </li>
                <li class="ep-footer-export-import__li">
                    <a class="ep-footer-export-import__link" href="<?php echo __SITE_URL . 'selling'; ?>">
                        <?php echo translate('header_navigation_link_selling'); ?>
                    </a>
                </li>
                <li class="ep-footer-export-import__li">
                    <a class="ep-footer-export-import__link" href="<?php echo __SITE_URL . 'manufacturer_description'; ?>">
                        <?php echo translate('header_navigation_link_manufacturing'); ?>
                    </a>
                </li>
                <li class="ep-footer-export-import__li">
                    <a class="ep-footer-export-import__link" href="<?php echo __SITE_URL . 'shipper_description'; ?>">
                        <?php echo translate('footer_navigation_link_shipping'); ?>
                    </a>
                </li>
                <li class="ep-footer-export-import__li">
                    <a class="ep-footer-export-import__link" href="<?php echo __SITE_URL . 'security'; ?>">
                        <?php echo translate('footer_navigation_link_security'); ?>
                    </a>
                </li>
            </ul>
        </div>
        <div class="ep-footer-nav">
            <h3 class="ep-footer__title ep-footer-nav__title">
                <?php echo translate('title_navigation_footer_navigate'); ?>
            </h3>

            <ul class="ep-footer-nav__list">
                <li class="ep-footer-nav__li">
                    <a class="ep-footer-nav__link" href="<?php echo __SITE_URL . 'learn_more'; ?>">
                        <?php echo translate('footer_general_btn_learn'); ?>
                    </a>
                </li>
                <li class="ep-footer-nav__li">
                    <a class="ep-footer-nav__link" href="<?php echo __SITE_URL . 'about'; ?>">
                        <?php echo translate('footer_navigation_link_about_us'); ?>
                    </a>
                </li>
                <li class="ep-footer-nav__li">
                    <a class="ep-footer-nav__link" href="<?php echo __SITE_URL . 'about/in_the_news'; ?>">
                        <?php echo translate('footer_navigation_link_in_the_news'); ?>
                    </a>
                </li>
                <li class="ep-footer-nav__li">
                    <a class="ep-footer-nav__link" href="<?php echo __SITE_URL . 'faq'; ?>">
                        <?php echo translate('header_navigation_link_faq'); ?>
                    </a>
                </li>
                <li class="ep-footer-nav__li">
                    <a class="ep-footer-nav__link" href="<?php echo __SITE_URL . 'help'; ?>">
                        <?php echo translate('header_navigation_link_help'); ?>
                    </a>
                </li>
                <li class="ep-footer-nav__li">
                    <a class="ep-footer-nav__link" href="<?php echo __SITE_URL . 'library_accreditation_body'; ?>">
                        <?php echo translate('header_navigation_link_library'); ?>
                    </a>
                </li>
            </ul>
        </div>
        <div class="ep-footer-newsletter">
            <h3 class="ep-footer__title ep-footer-newsletter__title">
                <?php echo translate('title_navigation_footer_newsletter'); ?>
            </h3>

            <form
                class="ep-footer-newsletter__form <?php echo isset($isEPL) ? 'js-footer-form-subscribe' : 'validengine'; ?>"
                action="<?php echo __SITE_URL; ?>"
                method="post"
                data-js-action="form:submit_form_subscribe"
                data-callback="subscribeFormCallBack"
                novalidate
            >
                <div class="ep-footer-newsletter__input-wr">
                    <input
                        class="ep-footer-newsletter__email input-new input-validate[required,custom[noWhitespaces],custom[emailWithWhitespaces]]"
                        name="email"
                        maxlength="50"
                        type="email"
                        placeholder="<?php echo translate('footer_subscribe_form_email_placeholder'); ?>"
                    >
                </div>
                <?php if ($isDwnMPage) { ?>
                    <input type="hidden" name="dm_page" value="<?php echo $isDwnMPage; ?>">
                <?php } ?>
                <input type="hidden" name="current_url" value="<?php echo __CURRENT_URL_NO_QUERY; ?>">

                <button class="ep-footer-newsletter__submit btn btn-primary btn-new16" type="submit">
                    <?php echo translate('general_button_subscribe_text'); ?>
                </button>

                <label class="ep-footer-newsletter__label custom-checkbox">
                    <input
                        class="validate[required]"
                        type="checkbox"
                        name="terms_cond"
                    >
                    <span class="ep-footer-newsletter__text custom-checkbox__text-agreement">
                        <?php echo translate('label_i_agree_with'); ?>
                        <a
                            class="ep-footer-newsletter__terms fancybox fancybox.ajax"
                            data-w="1040"
                            data-mw="1040"
                            data-h="400"
                            data-title="<?php echo translate('home_subscribe_terms_and_conditions_title', null, true); ?>"
                            href="<?php echo __SITE_URL . 'terms_and_conditions/tc_subscription_terms_of_conditions'; ?>"
                            <?php echo isset($isEPL) ? 'target="_blank"' : ''?>
                        >
                            <?php echo translate('label_terms_and_conditions'); ?>
                        </a>
                    </span>
                </label>
            </form>
        </div>
        <div class="ep-footer-partners">
            <h3 class="ep-footer__title ep-footer-partners__title">Partners</h3>

            <div class="ep-footer-partners__links<?php echo isset($isEPL) ? ' ep-footer-partners__links--epl': ''?>">
                <a
                    class="ep-footer-partners__link"
                    href="https://www.paypal.com/us/home"
                    target="_blank"
                    rel="nofollow noopener"
                >
                    <img
                        class="ep-footer-pratners__image js-lazy"
                        src="<?php echo getLazyImage(50, 50); ?>"
                        data-src="<?php echo asset('public/build/images/footer/partners/paypal.svg'); ?>"
                        alt="Paypal"
                    >
                </a>

                <a
                    class="ep-footer-partners__link"
                    href="https://www.fedex.com/apps/fedextrack/?action=track"
                    target="_blank"
                    rel="nofollow noopener"
                >
                    <img
                        class="ep-footer-pratners__image js-lazy"
                        src="<?php echo getLazyImage(50, 50); ?>"
                        data-src="<?php echo asset('public/build/images/footer/partners/fedex.svg'); ?>"
                        alt="FedEx"
                    >
                </a>

                <a
                    class="ep-footer-partners__link"
                    href="https://www.ups.com"
                    target="_blank"
                    rel="nofollow noopener"
                >
                    <img
                        class="ep-footer-pratners__image js-lazy"
                        src="<?php echo getLazyImage(50, 50); ?>"
                        data-src="<?php echo asset('public/build/images/footer/partners/ups.svg'); ?>"
                        alt="UPS"
                    >
                </a>

                <a
                    class="ep-footer-partners__link"
                    href="https://www.usps.com"
                    target="_blank"
                    rel="nofollow noopener"
                >
                    <img
                        class="ep-footer-pratners__image js-lazy"
                        src="<?php echo getLazyImage(50, 50); ?>"
                        data-src="<?php echo asset('public/build/images/footer/partners/us-post-service.svg'); ?>"
                        alt="USPS"
                    >
                </a>

                <a
                    class="ep-footer-partners__link"
                    href="https://www.gls-us.com"
                    target="_blank"
                    rel="nofollow noopener"
                >
                    <img
                        class="ep-footer-pratners__image js-lazy"
                        src="<?php echo getLazyImage(50, 50); ?>"
                        data-src="<?php echo asset('public/build/images/footer/partners/gls.svg'); ?>"
                        alt="GLS"
                    >
                </a>

                <a
                    class="ep-footer-partners__link"
                    href="https://www.lso.com"
                    target="_blank"
                    rel="nofollow noopener"
                >
                    <img
                        class="ep-footer-pratners__image js-lazy"
                        src="<?php echo getLazyImage(50, 50); ?>"
                        data-src="<?php echo asset('public/build/images/footer/partners/lso.svg'); ?>"
                        alt="LSO"
                    >
                </a>

                <a
                    class="ep-footer-partners__link"
                    href="https://en.wikipedia.org/wiki/Wire_transfer"
                    target="_blank"
                    rel="nofollow noopener"
                >
                    <img
                        class="ep-footer-pratners__image js-lazy"
                        src="<?php echo getLazyImage(50, 50); ?>"
                        data-src="<?php echo asset('public/build/images/footer/partners/wire-transfer.svg'); ?>"
                        alt="Wire"
                    >
                </a>
            </div>
        </div>
    </div>

    <div class="ep-footer-socials container-1420">
        <div class="ep-footer-socials__list">
            <a
                class="ep-footer-socials__item facebook"
                aria-label="Facebook link"
                href="<?php echo config('social_facebook', 'https://www.facebook.com/ExportPortal'); ?>"
                target="_blank"
                rel="noopener"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
                    <path
                        d="M230.45,82.122A20,20,0,1,0,207.325,102V87.939h-5.078V82.122h5.078V77.689c0-5.043,2.986-7.829,7.554-7.829a30.587,30.587,0,0,1,4.477.393v4.952h-2.522c-2.484,0-3.259,1.551-3.259,3.144v3.773h5.547l-.887,5.817h-4.66V102A20.09,20.09,0,0,0,230.45,82.122Z"
                        transform="translate(-190.45 -62)"
                        fill="#fff"
                    />
                </svg>
            </a>

            <a
                class="ep-footer-socials__item twitter"
                aria-label="Twitter link"
                href="<?php echo config('social_twitter', 'https://twitter.com/exportportal'); ?>"
                target="_blank"
                rel="noopener"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
                    <path
                        d="M20,0A20,20,0,1,0,40,20,20,20,0,0,0,20,0M30.045,16.208q.014.31.014.623A13.712,13.712,0,0,1,8.951,28.383a9.82,9.82,0,0,0,1.15.067,9.674,9.674,0,0,0,5.988-2.064,4.827,4.827,0,0,1-4.5-3.349,4.812,4.812,0,0,0,2.177-.083A4.823,4.823,0,0,1,9.9,18.227c0-.021,0-.041,0-.062a4.787,4.787,0,0,0,2.184.6,4.825,4.825,0,0,1-1.493-6.436,13.688,13.688,0,0,0,9.938,5.037,4.824,4.824,0,0,1,8.216-4.4A9.661,9.661,0,0,0,31.8,11.8a4.838,4.838,0,0,1-2.12,2.667,9.623,9.623,0,0,0,2.769-.759,9.81,9.81,0,0,1-2.4,2.5"
                        fill="#fff"
                    />
                </svg>
            </a>

            <a
                class="ep-footer-socials__item youtube"
                aria-label="Youtube link"
                href="<?php echo config('social_youtube', 'https://www.youtube.com/channel/UClFAlsiSScHTiwpAoDjFWuA'); ?>"
                target="_blank"
                rel="noopener"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
                    <path
                        d="M20,40A20,20,0,0,1,5.858,5.859,20,20,0,0,1,34.143,34.144,19.87,19.87,0,0,1,20,40Zm0-28c-.075,0-7.527.006-9.376.5A3.017,3.017,0,0,0,8.5,14.641,31.818,31.818,0,0,0,8,20.455a31.818,31.818,0,0,0,.5,5.814A3.014,3.014,0,0,0,10.624,28.4c1.85.5,9.3.5,9.376.5s7.527-.006,9.377-.5A3.012,3.012,0,0,0,31.5,26.269a31.818,31.818,0,0,0,.5-5.814,31.818,31.818,0,0,0-.5-5.814,3.014,3.014,0,0,0-2.121-2.136C27.526,12.006,20.075,12,20,12ZM17.545,24.024V16.887l6.273,3.569-6.272,3.568Z"
                        transform="translate(0 -0.001)"
                        fill="#fff"
                    />
                </svg>
            </a>

            <a
                class="ep-footer-socials__item instagram"
                aria-label="Instagram link"
                href="<?php echo config('social_instagram', 'https://www.instagram.com/export.portal/'); ?>"
                target="_blank"
                rel="noopener"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
                    <defs>
                        <radialGradient id="instagram-gradient" r="150%" cx="30%" cy="107%">
                            <stop stop-color="#fdf497" offset="0" />
                            <stop stop-color="#fdf497" offset="0.05" />
                            <stop stop-color="#fd5949" offset="0.45" />
                            <stop stop-color="#d6249f" offset="0.6" />
                            <stop stop-color="#285AEB" offset="0.9" />
                        </radialGradient>
                    </defs>
                    <path
                        d="M20,40A20,20,0,0,1,5.858,5.858,20,20,0,0,1,34.143,34.143,19.87,19.87,0,0,1,20,40Zm.4-9.6h1.015c.941,0,2.035-.005,3.112-.066a5.534,5.534,0,0,0,5.807-5.807c.069-1.187.067-2.391.065-3.555q0-.287,0-.573,0-.255,0-.509c0-1.185,0-2.41-.065-3.619A5.905,5.905,0,0,0,28.72,12.08a5.905,5.905,0,0,0-4.193-1.614c-1.079-.06-2.173-.066-3.113-.066h-2.03c-.941,0-2.035.005-3.112.066a5.9,5.9,0,0,0-4.192,1.614,5.9,5.9,0,0,0-1.614,4.193c-.068,1.211-.067,2.435-.065,3.619q0,.254,0,.508t0,.508c0,1.186,0,2.411.065,3.62a5.905,5.905,0,0,0,1.614,4.193,5.9,5.9,0,0,0,4.192,1.614c1.08.06,2.174.066,3.114.066H20.4Zm0-4.868a5.123,5.123,0,1,1,3.63-1.5A5.1,5.1,0,0,1,20.4,25.531Zm0-8.465A3.335,3.335,0,1,0,23.731,20.4,3.338,3.338,0,0,0,20.4,17.066Zm5.341-.808a1.2,1.2,0,1,1,1.2-1.2,1.188,1.188,0,0,1-.351.848A1.2,1.2,0,0,1,25.738,16.258Z"
                        fill="#fff"
                    />
                </svg>
            </a>

            <a
                class="ep-footer-socials__item linkedin"
                aria-label="Linkedin link"
                href="<?php echo config('social_linkedin', 'https://www.linkedin.com/company/export-portal-los-angeles/'); ?>"
                target="_blank"
                rel="noopener"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
                    <path
                        d="M20,40A20,20,0,0,1,5.858,5.858,20,20,0,0,1,34.142,34.142,19.869,19.869,0,0,1,20,40Zm-7.79-29.6A1.811,1.811,0,0,0,10.4,12.209V28.59a1.811,1.811,0,0,0,1.809,1.81H28.59a1.812,1.812,0,0,0,1.81-1.81V12.209A1.811,1.811,0,0,0,28.59,10.4ZM27.817,28.2h-2.4a.485.485,0,0,1-.484-.484V23.3c0-.048,0-.105,0-.169a3.246,3.246,0,0,0-.569-2.281,1.546,1.546,0,0,0-1.151-.43c-1.488,0-1.785,1.541-1.843,2.2v5.083a.485.485,0,0,1-.484.484H18.557a.485.485,0,0,1-.484-.484v-9.48a.485.485,0,0,1,.484-.484h2.326a.485.485,0,0,1,.484.484v.82a3.3,3.3,0,0,1,3.1-1.462,3.3,3.3,0,0,1,3.351,2.027,9.263,9.263,0,0,1,.479,3.515v4.58A.485.485,0,0,1,27.817,28.2Zm-11.755,0H13.821a.527.527,0,0,1-.526-.527v-9.4a.527.527,0,0,1,.526-.527h2.241a.527.527,0,0,1,.527.527v9.4A.527.527,0,0,1,16.062,28.2Zm-1.12-11.334a2.129,2.129,0,1,1,2.129-2.129A2.132,2.132,0,0,1,14.942,16.862Z"
                        fill="#fff"
                    />
                </svg>
            </a>

            <a
                class="ep-footer-socials__item pinterest"
                aria-label="Pinterest link"
                href="<?php echo config('social_pinterest', 'https://www.pinterest.com/exportportal'); ?>"
                target="_blank"
                rel="noopener"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
                    <path
                        d="M20.016,0a19.985,19.985,0,0,0-7.3,38.6,19.208,19.208,0,0,1,.066-5.74c.362-1.562,2.339-9.934,2.339-9.934a7.269,7.269,0,0,1-.593-2.961c0-2.78,1.614-4.852,3.624-4.852a2.513,2.513,0,0,1,2.537,2.812c0,1.711-1.087,4.276-1.664,6.661a2.908,2.908,0,0,0,2.965,3.618c3.558,0,6.293-3.75,6.293-9.145,0-4.786-3.443-8.125-8.369-8.125a8.656,8.656,0,0,0-9.044,8.668,7.792,7.792,0,0,0,1.483,4.556.594.594,0,0,1,.132.576c-.148.625-.494,1.99-.56,2.27-.082.362-.3.444-.675.263-2.5-1.168-4.069-4.8-4.069-7.747,0-6.3,4.58-12.089,13.229-12.089,6.936,0,12.339,4.934,12.339,11.546,0,6.891-4.349,12.434-10.379,12.434a5.321,5.321,0,0,1-4.58-2.3L16.54,33.865a21.756,21.756,0,0,1-2.488,5.247A20.007,20.007,0,1,0,20.016,0Z"
                        fill="#fff"
                    />
                </svg>
            </a>

            <a
                class="ep-footer-socials__item vk"
                aria-label="Vkontakte link"
                href="<?php echo config('social_vkontakte', 'https://vk.com/exportportal_ecommerce'); ?>"
                target="_blank"
                rel="noopener"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
                    <path
                        d="M20,40A20,20,0,0,1,5.858,5.858,20,20,0,0,1,34.142,34.142,19.869,19.869,0,0,1,20,40Zm1.536-17.576h0a7.389,7.389,0,0,1,6.205,5.351H32a11.892,11.892,0,0,0-6.161-7.51A11.478,11.478,0,0,0,31.1,12.8H27.232a9.732,9.732,0,0,1-2.274,3.978,5.824,5.824,0,0,1-3.422,1.943V12.8H17.669V23.173a6.2,6.2,0,0,1-3.63-2.837A14.3,14.3,0,0,1,12.108,12.8H8c.2,9.517,4.962,14.975,13.072,14.975h.464v-5.35Z"
                        transform="translate(0 0)"
                        fill="#fff"
                    />
                </svg>
            </a>

            <a
                class="ep-footer-socials__item ok"
                aria-label="Odnoklassniki link"
                href="<?php echo config('social_odnoklassniki', 'https://ok.ru/export.portal'); ?>"
                target="_blank"
                rel="noopener"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
                    <path
                        d="M20,0A20,20,0,1,1,0,20,20,20,0,0,1,20,0Zm2.236,25.251a9.885,9.885,0,0,0,3.958-1.706,1.649,1.649,0,0,0-2.064-2.572,7.152,7.152,0,0,1-4.118,1.2,7.335,7.335,0,0,1-4.15-1.2A1.649,1.649,0,0,0,13.8,23.545a10.118,10.118,0,0,0,4.085,1.725l-3.47,3.63a1.647,1.647,0,1,0,2.37,2.288l3.229-3.441,3.555,3.464A1.648,1.648,0,0,0,25.9,28.877l-3.659-3.626ZM20.1,8.307a6.061,6.061,0,1,0,6.061,6.061A6.061,6.061,0,0,0,20.1,8.307Zm0,8.564A2.5,2.5,0,1,1,22.6,14.365,2.5,2.5,0,0,1,20.1,16.872Z"
                        fill="#fff"
                        fill-rule="evenodd"
                    />
                </svg>
            </a>

            <a
                class="ep-footer-socials__item whatsapp"
                aria-label="Whatsapp link"
                href="https://wa.me/+<?php echo get_only_number(config('ep_phone_whatsapp')); ?>"
                rel="noopener"
                target="_blank"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
                    <g transform="translate(0.001 -0.001)">
                        <path
                            d="M1345,13938a20,20,0,1,1,14.141-5.858A19.871,19.871,0,0,1,1345,13938Zm.042-30.331a10.242,10.242,0,0,0-8.874,15.355l-1.452,5.3,5.429-1.42a10.235,10.235,0,0,0,4.89,1.245h.007a10.242,10.242,0,1,0,0-20.483Zm2.756,15.156a7.122,7.122,0,0,1-2.547-.682,9.954,9.954,0,0,1-4.317-3.785l-.009-.014a.487.487,0,0,0-.044-.063l0-.007a4.879,4.879,0,0,1-1.042-2.641,2.832,2.832,0,0,1,.84-2.075l.006-.007.01-.01a.2.2,0,0,1,.042-.042.931.931,0,0,1,.682-.322h.112c.129,0,.262,0,.378.007.18.007.385.015.574.441.132.292.353.841.515,1.242l.01.023.064.158c.094.229.175.429.2.476a.483.483,0,0,1,.021.447.524.524,0,0,0-.026.051l-.009.02-.013.026a1.411,1.411,0,0,1-.207.33c-.021.022-.041.047-.061.07l-.015.019c-.019.023-.038.047-.057.068a3.618,3.618,0,0,1-.252.291c-.124.124-.264.264-.112.521a7.726,7.726,0,0,0,1.424,1.773,6.615,6.615,0,0,0,1.867,1.186l.018.008c.054.021.112.047.172.077a.639.639,0,0,0,.28.084.369.369,0,0,0,.276-.15c.2-.222.649-.759.808-1a.357.357,0,0,1,.3-.2.834.834,0,0,1,.273.066c.227.082,1.385.652,1.735.825l.014.007.075.038.034.017.025.012a.883.883,0,0,1,.357.231,2.117,2.117,0,0,1-.147,1.217,2.574,2.574,0,0,1-1.724,1.218l-.067.009c-.022,0-.044,0-.066.008l-.086.009A2.961,2.961,0,0,1,1347.8,13922.826Z"
                            transform="translate(-1325 -13898)"
                            fill="#fff"
                        />
                    </g>
                </svg>
            </a>
        </div>
    </div>

    <div class="ep-footer-rights container-1420">
        <div class="ep-footer-rights__wr">
            <p class="ep-footer-rights__description">
                <?php echo translate('all_rights_reserved'); ?> &copy; <?php echo date('Y'); ?> <?php echo translate('export_portal'); ?>
            </p>

            <div class="ep-footer-rights__links">
                <a
                    class="ep-footer-rights__link"
                    href="<?php echo __SITE_URL . 'terms_and_conditions/tc_terms_of_use'; ?>"
                    target="_blank"
                >
                    <?php echo translate('label_terms_of_use'); ?>
                </a>

                <a
                    class="ep-footer-rights__link"
                    href="<?php echo __SITE_URL . 'terms_and_conditions/tc_privacy_policy'; ?>"
                    target="_blank"
                >
                    <?php echo translate('label_privacy_policy'); ?>
                </a>

                <a
                    class="ep-footer-rights__link"
                    href="<?php echo __SITE_URL . 'contact'; ?>"
                >
                    <?php echo translate('contact_us'); ?>
                </a>
            </div>
        </div>
    </div>
</footer>

<?php
    echo rawEncoreEntryLinkTags('footer', null);

    if ($isEPL) {
        views('new/epl/system_messages_view');
    } else {
        views('new/system_messages_view');
    }

    widget_popup_user_preferences();

    encoreEntryScriptTags('global');

    if ($isEPL) {
        encoreEntryLinkTags('epl_app');
    } elseif (!empty($webpackData) && true !== $webpackData) {
        encoreEntryScriptTags('app');
    }

    encoreEntryScriptTags('footer');
    widgetGetPopups();

    if (!empty(session()->popups) && !isBackstopEnabled()) {
        encoreEntryScriptTags('popups_system');
        echo dispatchDynamicFragment(
            'popups_system:init',
            [
                'popups' => session()->popups,
                'params' => ['loggedIn' => logged_in()],
            ],
            true
        );
    }

    if (!isBackstopEnabled() && matrixChatAccessibleForCurrentUser() && ($credentials = currentUserMatrixCredentials())) {
        encoreEntryScriptTags('chat_app');
        echo dispatchDynamicFragment(
            'chat_app:initialization',
            [
                $credentials['username'], // Matrix login
                $credentials['password'], // Matrix password
                config('env.MATRIX_ADMIN_USER_ID'), // Matrix bot id
                !empty($chatApp['openIframe']) ? $chatApp['openIframe'] : '', // Page with chat || other pages
                $credentials['hasKeys'],
                userStatus() !== \App\Common\Contracts\User\UserStatus::ACTIVE(),
                admin_logged_as_user(),
            ],
            true,
        );
    }

    encoreScripts();
?>

<?php if (empty($webpackData)) { ?>
    <script src="<?php echo asset('public/plug/js/subscribe/index.js', 'legacy'); ?>"></script>
<?php } ?>
