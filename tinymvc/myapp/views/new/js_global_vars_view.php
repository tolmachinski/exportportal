<script>
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
        "__backstop_test_mode": { writable: false, value: Boolean(<?php echo (int) filter_var(config("env.BACKSTOP_TEST_MODE"), FILTER_VALIDATE_BOOLEAN); ?>) && new URL(globalThis.location.href).searchParams.has("backstop") },
        "__disable_popup_system": { writable: false, value: Boolean(<?php echo (int) filter_var(config("env.DISABLE_POPUP_SYSTEM"), FILTER_VALIDATE_BOOLEAN); ?>) },
    });
</script>

<?php // No image observer //
    if (!filter_var(config('env.BACKSTOP_TEST_MODE'), FILTER_VALIDATE_BOOLEAN)) {  ?>
        <script>(()=>{var e=new Map,t=null;new MutationObserver(t=>{t.forEach(t=>{(t.addedNodes||[]).forEach(t=>{1===t.nodeType&&Array.from(document.querySelectorAll(".js-fs-image:not([no-image]), .js-fs-image-wrapper img:not([no-image])")).forEach(t=>{t.setAttribute("no-image",1);var s=t.dataset.fsw||t.clientWidth,r=t.dataset.fsh||t.clientHeight,i=t.onerror,a=t.classList.contains("js-lazy")&&!t.classList.contains("loaded")?t.dataset.src:t.src,c=__site_url+"public/build/images/placeholder/no-image.824c1d869855087d8c77d916763097bf.svg",l={img:{src:a,w:s,h:r},sources:{}};t.parentNode.querySelectorAll("source").forEach(e=>{let t=e.dataset.fsw,s=e.dataset.fsh;l.sources[e.media]={w:t,h:s,r:t/s}}),e.set(t,l),t.onerror=()=>{var r=e.get(t),a=!1;if("function"==typeof i&&i(),t.parentNode.querySelectorAll("source").forEach(e=>{e.srcset=c,!a&&window.matchMedia(e.media).matches&&(a=r.sources[e.media])}),a)a.w!=t.clientWidth?t.style.height=a.h/a.w*t.clientWidth+"px":t.style.height=a.h+"px",t.style.width=a.w+"px";else{t.style.width=s+"px";let l=parseInt(t.style.width)||t.clientWidth;l>t.parentElement.clientWidth&&(l=t.parentElement.clientWidth),r.img.w!=t.clientWidth?t.style.height=r.img.h/r.img.w*l+"px":t.style.height=r.img.h+"px"}t.src=c,t.srcset&&(t.srcset=c),t.classList.add("image-error")}})})})}).observe(document,{attributes:!1,childList:!0,subtree:!0}),globalThis.addEventListener("resize",()=>{clearTimeout(t),t=setTimeout(()=>{[...e.entries()].forEach(([e,t])=>{e.style.width=null,e.style.height=null,e.src=t.img.src,e.srcset&&(e.srcset=t.img.src),e.parentNode.querySelectorAll("source").forEach(e=>{e.srcset=t.img.src})})},300)})})();</script>
<?php } ?>
