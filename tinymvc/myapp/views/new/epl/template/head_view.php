<head itemscope itemtype="http://schema.org/WebSite">
	<base href="<?php echo __SITE_URL;?>">

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">

	<?php widgetMetaHeader($meta_params ?? [], $meta_data ?? [], 'new/epl/template/'); ?>

	<meta name="p:domain_verify" content="65e19a1e8a4d0fa30bb8e1758b7166f8"/>
	<meta name="msvalidate.01" content="B055EDD80CC73148C91A728997D2DDE0" />

    <script><?php echo getPublicScriptContent(asset('plug/general/lang/' . __SITE_LANG . '.js')); ?></script>
	<?php views()->display('new/js_global_vars_view'); ?>
	<?php views()->display('new/js_analytics_view'); ?>

    <?php if (logged_in()) { ?>
        <meta name="csrf-token" content="<?php echo session()->csrfToken; ?>">
    <?php } ?>

    <link rel="preconnect" href="<?php echo preconnect(__FILES_URL); ?>">

    <style><?php echo sprintf(
        '%s%s%s',
        getPublicStyleContent(asset("public/build/epl_styles_general_critical.css"), false, false),
        logged_in() ? getPublicStyleContent(asset("public/build/epl_styles_general_logged_user_critical.css"), false, false) : '',
        isset($webpackData['styleCritical']) ? getPublicStyleContent(asset("public/build/{$webpackData['styleCritical']}.css"), false, false) : '',
    );?></style>

    <?php if (isset($webpackData['dashboardOldPage']) && $webpackData['dashboardOldPage']) { ?>
        <link rel="stylesheet" type="text/css" media="screen" href="<?php echo asset("public/build/epl_styles_old_dashboard.css");?>" />
    <?php } ?>
</head>
