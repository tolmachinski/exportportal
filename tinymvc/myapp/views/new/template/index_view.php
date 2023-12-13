<?php
    // For classes to ep-header to fix jumping of content
    $checkBannerBecomeCertified = (verifyNeedCertifyUpgrade() && !cookies()->exist_cookie('showTopBannerBecomeCertified')) ? 'html--banner' : '';
    $checkMaintenance = ('on' === config('env.MAINTENANCE_MODE') && validateDate(config('env.MAINTENANCE_START'), DATE_ATOM)
    && !isExpiredDate(DateTime::createFromFormat(DATE_ATOM, config('env.MAINTENANCE_START'), new DateTimeZone('UTC')))) ? 'html--maintenance' : '';
?>
<!DOCTYPE html>
<html lang="<?php echo __SITE_LANG; ?>" class="<?php echo $checkBannerBecomeCertified; ?> <?php echo $checkMaintenance; ?>">
    <head itemscope itemtype="http://schema.org/WebSite">
        <base href="<?php echo __SITE_URL; ?>" />

        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no" />

        <?php widgetMetaHeader($metaParams ?? [], $metaData ?? [], 'new/'); ?>

        <meta name="p:domain_verify" content="65e19a1e8a4d0fa30bb8e1758b7166f8" />
        <meta name="msvalidate.01" content="B055EDD80CC73148C91A728997D2DDE0" />

        <script>
            <?php echo getPublicScriptContent(asset('plug/general/lang/' . __SITE_LANG . '.js')); ?>
            Object.defineProperties(window, {
                "__site_url": { writable: false, value: "<?php echo __SITE_URL; ?>" },
                "__blog_url": { writable: false, value: "<?php echo __BLOG_URL; ?>" },
                "__img_url": { writable: false, value: "<?php echo __IMG_URL; ?>" },
                "__files_url": { writable: false, value: "<?php echo __FILES_URL; ?>" },
                "__site_lang": { writable: false, value: "<?php echo __SITE_LANG; ?>" },
                "__bloggers_url": { writable: false, value: "<?php echo __BLOGGERS_URL; ?>" },
                "__community_url": { writable: false, value: "<?php echo __COMMUNITY_URL; ?>" },
                "__js_cookie_domain": { writable: false, value: "<?php echo __JS_COOKIE_DOMAIN; ?>" },
                "__js_cookie_secure": { writable: false, value: "<?php echo (int) !DEBUG_MODE; ?>" },
                "__tracking_selector": { writable: false, value: '.js-ep-self-autotrack' },
                "__debug_mode": { writable: false, value: Boolean(~~<?php echo (int) DEBUG_MODE; ?>) },
                "__group_site_url": { writable: false, value: "<?php echo getUrlForGroup(); ?>" },
                "__current_sub_domain_url": { writable: false, value: "<?php echo __CURRENT_SUB_DOMAIN_URL; ?>" },
                "__is_main_domain": { writable: false, value: Boolean(~~"<?php echo isMainDomain(); ?>") },
                "__page_hash": { writable: false, value: "<?php echo getPageHash(); ?>" },
                "__shipper_url": { writable: false, value: "<?php echo __SHIPPER_URL; ?>" },
                "__logged_in": { writable: false, value: Boolean(~~"<?php echo logged_in(); ?>") },
                "__shipper_page": { writable: false, value: Boolean(~~"<?php echo __CURRENT_SUB_DOMAIN === getSubDomains()['shippers']; ?>") },
                "__backstop_test_mode": { writable: false, value: Boolean(<?php echo (int) filter_var(config('env.BACKSTOP_TEST_MODE'), FILTER_VALIDATE_BOOLEAN); ?>) && new URL(globalThis.location.href).searchParams.has("backstop") },
            });
        </script>
        <?php // No image observer //
            if (!filter_var(config('env.BACKSTOP_TEST_MODE'), FILTER_VALIDATE_BOOLEAN)) {  ?>
                <script>(()=>{var e=new Map,t=null;new MutationObserver(t=>{t.forEach(t=>{(t.addedNodes||[]).forEach(t=>{1===t.nodeType&&Array.from(document.querySelectorAll(".js-fs-image:not([no-image]), .js-fs-image-wrapper img:not([no-image])")).forEach(t=>{t.setAttribute("no-image",1);var s=t.dataset.fsw||t.clientWidth,r=t.dataset.fsh||t.clientHeight,i=t.onerror,a=t.classList.contains("js-lazy")&&!t.classList.contains("loaded")?t.dataset.src:t.src,c=__site_url+"public/build/images/placeholder/no-image.824c1d869855087d8c77d916763097bf.svg",l={img:{src:a,w:s,h:r},sources:{}};t.parentNode.querySelectorAll("source").forEach(e=>{let t=e.dataset.fsw,s=e.dataset.fsh;l.sources[e.media]={w:t,h:s,r:t/s}}),e.set(t,l),t.onerror=()=>{var r=e.get(t),a=!1;if("function"==typeof i&&i(),t.parentNode.querySelectorAll("source").forEach(e=>{e.srcset=c,!a&&window.matchMedia(e.media).matches&&(a=r.sources[e.media])}),a)a.w!=t.clientWidth?t.style.height=a.h/a.w*t.clientWidth+"px":t.style.height=a.h+"px",t.style.width=a.w+"px";else{t.style.width=s+"px";let l=parseInt(t.style.width)||t.clientWidth;l>t.parentElement.clientWidth&&(l=t.parentElement.clientWidth),r.img.w!=t.clientWidth?t.style.height=r.img.h/r.img.w*l+"px":t.style.height=r.img.h+"px"}t.src=c,t.srcset&&(t.srcset=c),t.classList.add("image-error")}})})})}).observe(document,{attributes:!1,childList:!0,subtree:!0}),globalThis.addEventListener("resize",()=>{clearTimeout(t),t=setTimeout(()=>{[...e.entries()].forEach(([e,t])=>{e.style.width=null,e.style.height=null,e.src=t.img.src,e.srcset&&(e.srcset=t.img.src),e.parentNode.querySelectorAll("source").forEach(e=>{e.srcset=t.img.src})})},300)})})();</script>
        <?php } ?>
        <?php if (filter_var(config('env.GLOBAL_CUSTOM_TRACKING_ENABLED'), FILTER_VALIDATE_BOOLEAN)) { ?>
            <script>
                (function(e,a,t,n,o,r){var c=a.createElement(t),d=a.getElementsByTagName(t)[0],l=function(){e[o]=e[o]||(e['CustomAnalytica']['default']());r()};
                c.async=1,c.src=n,c.readyState?c.onreadystatechange=function(){"loaded"!=c.readyState&&"complete"!=c.readyState||(c.onreadystatechange=null,l())}:c.onload=l,
                d.parentNode.insertBefore(c,d)}(window, document, 'script', '<?php echo asset('public/plug/analytics-1-2-0/analytics.js', 'legacy'); ?>', '__analytics', function() {
                    __analytics.identifyProject('<?php echo config('env.GLOBAL_CUSTOM_TRACKING_CODE'); ?>', 'Export Portal');
                    __analytics.initialize();
                    __analytics.pageview();
                    __analytics.autotrack(__tracking_selector);
                }));
            </script>
        <?php } ?>

        <?php if (filter_var(config('env.GOOGLE_TAG_MANAGER_ENABLED'), FILTER_VALIDATE_BOOLEAN)) { ?>
            <?php if (isset($googleAnalyticsEvents) && filter_var($googleAnalyticsEvents, FILTER_VALIDATE_BOOLEAN)) { ?>
                <?php $googleAnalyticsID = config('env.' . strtoupper(__CURRENT_SUB_DOMAIN) . '_GOOGLE_ANALITYC', config('env.WWW_GOOGLE_ANALITYC')); ?>
                <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $googleAnalyticsID; ?>"></script>
                <script>
                    window.dataLayer = window.dataLayer || [];
                    function gtag() {
                        dataLayer.push(arguments);
                    }
                    gtag("js", new Date());
                    gtag("config", "<?php echo $googleAnalyticsID; ?>");
                </script>
            <?php } else { ?>
                <?php $googleTagManagerID = config('env.' . strtoupper(__CURRENT_SUB_DOMAIN) . '_GOOGLE_TAG_MANAGER_ID', config('env.WWW_GOOGLE_TAG_MANAGER_ID')); ?>
                <!-- Google Tag Manager -->
                <script>
                    (function (w, d, s, l, i) {
                        w[l] = w[l] || [];
                        w[l].push({ "gtm.start": new Date().getTime(), event: "gtm.js" });
                        var f = d.getElementsByTagName(s)[0],
                            j = d.createElement(s),
                            dl = l != "dataLayer" ? "&l=" + l : "";
                        j.async = true;
                        j.src = "https://www.googletagmanager.com/gtm.js?id=" + i + dl;
                        f.parentNode.insertBefore(j, f);
                    })(window, document, "script", "dataLayer", "<?php echo $googleTagManagerID; ?>");
                </script>
                <!-- End Google Tag Manager -->
            <?php } ?>
        <?php } ?>

        <?php if (logged_in()) { ?>
            <meta name="csrf-token" content="<?php echo session()->get('csrfToken'); ?>" />
        <?php } ?>

        <link rel="preconnect" href="<?php echo preconnect(__FILES_URL); ?>" />

        <style><?php echo sprintf(
            '%s%s',
            getPublicStyleContent("/build/css/{$styleCritical}.critical.min.css", false),
            logged_in() ? getPublicStyleContent(asset("public/build/ep_general_critical_styles_logged_user.css"), false, false) : '',
        );?></style>
    </head>

    <body>
        <!-- Header -->
        <?php
            views('new/template/components/header_global_view', ['newTemplate' => true]);
        ?>
        <!-- End Header -->

        <?php
            if (!isset($customEncoreLinks)) {
                echo encoreLinks();
            }
        ?>
        <link crossorigin="anonymous" rel="stylesheet" href="<?php echo asset("public/build/styles_user_pages.css");?>" />

        <!-- Main Content -->
        <main class="ep-content">
            <div id="js-bad-browser-wr">
                <script type="text/template" id="js-bad-browser">
                    <?php
                        views('new/template_views/bad_browser_template_view', [
                            'title'     => translate('browser_out_of_date_title'),
                            'paragraph' => translate('browser_out_of_date_paragrapth'),
                        ]);
                    ?>

                    (function () {
                        var d = document;
                        if (!("noModule" in d.createElement("script"))) {
                            d.querySelector("body").outerHTML = d.getElementById("js-bad-browser-wr").outerHTML;
                            var c = d.createElement("link"),
                                b = d.querySelector("body");
                            c.rel = "stylesheet";
                            c.href = "<?php echo asset('public/build/badbrowser.css'); ?>";
                            c.onload = function () {
                                b.insertAdjacentHTML("beforeend", d.getElementById("js-bad-browser").textContent.trim());
                            };
                            b.appendChild(c);
                        }
                    })();
                </script>
                <noscript>
                    <link rel="preload stylesheet" as="style" href="<?php echo asset('public/build/badbrowser.css'); ?>" />

                    <?php
                        views('new/template_views/bad_browser_template_view', [
                            'title'     => translate('browser_no_script_title'),
                            'paragraph' => translate('browser_no_script_paragrapth'),
                        ]);
                    ?>
                </noscript>
            </div>

            <?php if (!isset($googleAnalyticsEvents) && filter_var(config('env.GOOGLE_TAG_MANAGER_ENABLED'), FILTER_VALIDATE_BOOLEAN)) { ?>
                <?php $googleTagManagerID = config('env.' . strtoupper(__CURRENT_SUB_DOMAIN) . '_GOOGLE_TAG_MANAGER_ID', config('env.WWW_GOOGLE_TAG_MANAGER_ID')); ?>
                <!-- Google Tag Manager (noscript) -->
                <noscript>
                    <iframe
                        src="https://www.googletagmanager.com/ns.html?id=<?php echo $googleTagManagerID; ?>"
                        height="0"
                        width="0"
                        style="display: none; visibility: hidden"
                    ></iframe>
                </noscript>
                <!-- End Google Tag Manager (noscript) -->
            <?php } ?>

            <?php views(\App\Common\THEME_MAP . '/' . $content); ?>
        </main>
        <!-- End Main Content -->

        <!-- Footer -->
        <?php
            views('new/template/components/footer_global_view');
            encoreEntryScriptTags('app');
        ?>
        <!-- End Footer -->
    </body>
</html>
