<?php if(filter_var(config('env.GLOBAL_CUSTOM_TRACKING_ENABLED'), FILTER_VALIDATE_BOOLEAN)) { ?>
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

<?php if(filter_var(config('env.GOOGLE_TAG_MANAGER_ENABLED'), FILTER_VALIDATE_BOOLEAN)){ ?>
    <?php if(isset($googleAnalyticsEvents) && filter_var($googleAnalyticsEvents, FILTER_VALIDATE_BOOLEAN)){ ?>
        <?php $googleAnalyticsID = config('env.' . strtoupper(__CURRENT_SUB_DOMAIN) . '_GOOGLE_ANALITYC', config('env.WWW_GOOGLE_ANALITYC')); ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $googleAnalyticsID?>"></script>
        <script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', '<?php echo $googleAnalyticsID?>');</script>
    <?php }else{ ?>
        <?php $googleTagManagerID = config('env.' . strtoupper(__CURRENT_SUB_DOMAIN) . '_GOOGLE_TAG_MANAGER_ID', config('env.WWW_GOOGLE_TAG_MANAGER_ID')); ?>
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','<?php echo $googleTagManagerID;?>');</script>
        <!-- End Google Tag Manager -->
    <?php } ?>
<?php } ?>
