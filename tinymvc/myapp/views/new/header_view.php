<?php
    $templateMap = \App\Common\THEME_MAP.'/template_views/';
    views()->display($templateMap.'session_preview_view');

    $checkBannerBecomeCertified = (verifyNeedCertifyUpgrade() && !cookies()->exist_cookie('showTopBannerBecomeCertified')) ? 'html--banner' : '';
    $checkMaintenance = ('on' === config('env.MAINTENANCE_MODE') && validateDate(config('env.MAINTENANCE_START'), DATE_ATOM)
    && !isExpiredDate(DateTime::createFromFormat(DATE_ATOM, config('env.MAINTENANCE_START'), new DateTimeZone('UTC')))) ? 'html--maintenance' : '';
?>
<!DOCTYPE html>
<html lang="<?php echo __SITE_LANG;?>" class="<?php echo "{$checkBannerBecomeCertified} {$checkMaintenance}"; ?>">
    <?php
        views()->display($templateMap.'head_view');
    ?>

    <body>
        <?php views()->display('new/template_views/bad_browser_view'); ?>
        <?php views()->display('new/template_views/tag_manager_body_view');?>

        <?php
            if (empty($webpackData)) {
                views()->display('new/template_views/header_scripts_view');
                views()->display('new/template_views/header_init_scripts_view');
            }
            views()->display('new/template/components/header_global_view');

            if (empty($includeTemplate) && empty($webpackData["customEncoreLinks"])) {
                encoreLinks();
            }

            if (!empty($webpackData)) {
                encoreEntryLinkTags($webpackData['pageConnect']);
                encoreEntryScriptTags($webpackData['pageConnect']);
            }
        ?>

        <link rel="stylesheet" type="text/css" media="screen" href="<?php echo asset("public/build/styles_user_pages.css");?>" />

        <?php if (empty($includeTemplate) && empty($webpackData)) { ?>
            <link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/styles_new.css'); ?>" />
        <?php } ?>

        <main class="ep-content">

