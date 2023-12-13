
<?php if(!isset($googleAnalyticsEvents) && filter_var(config('env.GOOGLE_TAG_MANAGER_ENABLED'), FILTER_VALIDATE_BOOLEAN)){ ?>
    <?php $googleTagManagerID = config('env.' . strtoupper(__CURRENT_SUB_DOMAIN) . '_GOOGLE_TAG_MANAGER_ID', config('env.WWW_GOOGLE_TAG_MANAGER_ID')); ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $googleTagManagerID; ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
<?php } ?>
