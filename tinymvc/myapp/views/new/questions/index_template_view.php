<?php
    if (DEBUG_MODE) {
        echo "<!--<pre> session - ";
        print_r(session()->getAll());
        echo 'cookies - ';
        print_r($_COOKIE);
        echo "</pre>-->";
        echo "<!--<pre>";
        !empty($errors) && print_r($errors);
        echo "</pre>-->";
    }
?>
<!DOCTYPE html>
<?php
    // For classes to ep-header to fix jumping of content
    $checkMaintenance = (
        'on' === config('env.MAINTENANCE_MODE') && validateDate(config('env.MAINTENANCE_START'), DATE_ATOM) && !isExpiredDate(DateTime::createFromFormat(DATE_ATOM, config('env.MAINTENANCE_START'), new DateTimeZone('UTC')))
    ) ? "html--maintenance" : "" ;
?>
<html lang="<?php echo __SITE_LANG;?>" class="<?php echo $checkMaintenance; ?>">

<head itemscope itemtype="http://schema.org/WebSite">
    <base href="<?php echo __CURRENT_SUB_DOMAIN_URL; ?>">

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">

    <?php widgetMetaHeader($meta_params ?? [], $meta_data ?? [], 'new/');?>

    <meta name="p:domain_verify" content="65e19a1e8a4d0fa30bb8e1758b7166f8" />
    <meta name="msvalidate.01" content="B055EDD80CC73148C91A728997D2DDE0" />

    <script><?php echo getPublicScriptContent(asset('plug/general/lang/' . __SITE_LANG . '.js')); ?></script>
    <?php views()->display('new/js_global_vars_view'); ?>
    <?php views()->display('new/js_analytics_view'); ?>

    <?php if (logged_in()) {?>
        <meta name="csrf-token" content="<?php echo session()->csrfToken;?>">
    <?php } ?>

    <link rel="preconnect" href="<?php echo preconnect(__FILES_URL); ?>">

    <?php if (isset($webpackData)) { ?>
        <style><?php echo getPublicStyleContent('/build/css/' . $webpackData['styleCritical'] . '.critical.min.css', false); ?></style>
    <?php } ?>
</head>

<body>
    <?php views()->display('new/template_views/tag_manager_body_view'); ?>
    <?php encoreEntryLinkTags('community'); ?>
    <?php encoreEntryScriptTags('app'); ?>
    <?php encoreEntryScriptTags('community'); ?>
    <?php encoreEntryScriptTags('footer'); ?>

    <?php views()->display("new/template_views/autologout_view");?>

    <?php
        if (!empty($header_out_content)) {
            views()->display($header_out_content);
        }
    ?>

    <main class="community-content <?php echo $current_page == 'all' ? 'community-content-all' : ''; ?>">

        <?php echo dispatchDynamicFragment('navbar:notification', array(logged_in()), true); ?>

        <div class="container-center-sm <?php echo ($current_page == 'all' || $search_params) ? 'mt-10' : ''; ?>">
            <?php
                if (!empty($header_content)) {
                    tmvc::instance()->controller->view->display($header_content);
                }
            ?>

            <?php if (!empty($sidebar_left_content) || !empty($main_content) || !empty($sidebar_right_content)) { ?>
                <div class="main-flex-card community-flex-card">
                    <?php if (!empty($sidebar_right_content)) { ?>
                        <div class="main-flex-card__fixed-right main-flex-card__flex-md" id="main-flex-card__fixed-right">
                            <?php views()->display($sidebar_right_content); ?>
                        </div>
                    <?php } ?>
                    <?php if (!empty($main_content)) { ?>
                        <div class="main-flex-card__float">
                            <?php views()->display($main_content); ?>
                        </div>
                    <?php } ?>
                    <?php if (!empty($sidebar_left_content)) { ?>
                        <div class="main-flex-card__fixed-left" id="main-flex-card__fixed-left">
                            <?php views()->display($sidebar_left_content); ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

    </main>
    <?php views()->display(\App\Common\THEME_MAP . '/template/components/footer_global_view'); ?>
</body>

</html>
