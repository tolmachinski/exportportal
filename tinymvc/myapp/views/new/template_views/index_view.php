<?php
    $templateMap = \App\Common\THEME_MAP.'/template_views/';
    if(!isset($webpackData)){
        views()->display($templateMap.'session_preview_view');
    }
?>
<!DOCTYPE html>
<?php
// For classes to ep-header to fix jumping of content
$checkBannerBecomeCertified = (verifyNeedCertifyUpgrade() && !cookies()->exist_cookie('showTopBannerBecomeCertified')) ? "html--banner" : "";
$checkMaintenance = (
    'on' === config('env.MAINTENANCE_MODE') && validateDate(config('env.MAINTENANCE_START'), DATE_ATOM) && !isExpiredDate(DateTime::createFromFormat(DATE_ATOM, config('env.MAINTENANCE_START'), new DateTimeZone('UTC')))
) ? "html--maintenance" : "" ;
?>
<html lang="<?php echo __SITE_LANG;?>" class="<?php echo $checkBannerBecomeCertified?> <?php echo $checkMaintenance?>">
    <?php
        views()->display($templateMap.'head_view');
    ?>

    <body>
        <?php views()->display('new/template_views/bad_browser_view'); ?>
        <?php views()->display('new/template_views/tag_manager_body_view');?>

        <?php
            if (!isset($webpackData)) {
                views()->display('new/template_views/header_scripts_view');
                views()->display('new/template_views/header_init_scripts_view');
            }

            views()->display('new/template/components/header_global_view');

            if (isset($webpackData)) {
                encoreEntryLinkTags($webpackData['pageConnect']);
                encoreEntryScriptTags($webpackData['pageConnect']);
            }
        ?>
        <?php if (!isset($webpackData)) { ?>
            <link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/styles_new.css'); ?>" />
        <?php } ?>

        <main class="ep-content">
            <?php
                $issetEncoreLinks = false;
                $issetHeaderOutContant = false;
                if(!empty($templateViews['headerOutContent'])) {
                    views()->display(\App\Common\THEME_MAP . '/'. $templateViews['headerOutContent']);
                    $issetHeaderOutContant = true;

                    if (!isset($templateViews['customEncoreLinks'])) {
                        encoreLinks();
                        $issetEncoreLinks = true;
                    }
                }
            ?>
            <link rel="stylesheet" type="text/css" media="screen" href="<?php echo asset("public/build/styles_user_pages.css");?>" />

            <?php
                if(
                    !empty($templateViews['headerContent'])
                    || !empty($templateViews['footerContent'])
                    || !empty($templateViews['sidebarLeftContent'])
                    || !empty($templateViews['mainContent'])
                    || !empty($templateViews['sidebarRightContent'])
                    || !empty($templateViews['lgSidebarRightContent'])
                ){
            ?>
                <div class="container-center-sm">
                    <?php
                        if(!empty($templateViews['headerContent'])) {
                            views()->display(\App\Common\THEME_MAP . '/' . $templateViews['headerContent']);
                        }

                        if (
                            !$issetEncoreLinks
                            && !isset($templateViews['customEncoreLinks'])
                        ) {
                            encoreLinks();
                            $issetEncoreLinks = true;
                        }
                    ?>

                    <?php if(
                            !empty($templateViews['sidebarLeftContent'])
                            || !empty($templateViews['mainContent'])
                            || !empty($templateViews['sidebarRightContent'])
                        ){?>
                        <div class="main-flex-card">
                            <?php if(!empty($templateViews['mainContent'])){?>
                                <div class="main-flex-card__float">
                                    <?php views()->display(\App\Common\THEME_MAP . '/' . $templateViews['mainContent']); ?>
                                </div>
                            <?php }?>
                            <?php if(!empty($templateViews['sidebarLeftContent'])){?>
                                <div class="main-flex-card__fixed-left" id="main-flex-card__fixed-left">
                                    <?php views()->display(\App\Common\THEME_MAP . '/' . $templateViews['sidebarLeftContent']); ?>
                                </div>
                            <?php }?>
                            <?php if(!empty($templateViews['sidebarRightContent'])){?>
                                <div class="main-flex-card__fixed-right" id="main-flex-card__fixed-right">
                                    <?php views()->display(\App\Common\THEME_MAP . '/' . $templateViews['sidebarRightContent']); ?>
                                </div>
                            <?php }?>
                            <?php if(!empty($templateViews['lgSidebarRightContent'])){?>
                                <div class="main-flex-card__fixed-right dn-xl" id="main-flex-card__fixed-right">
                                    <?php views()->display(\App\Common\THEME_MAP . '/' . $templateViews['lgSidebarRightContent']); ?>
                                </div>
                            <?php }?>
                        </div>
                    <?php }?>

                    <?php
                        if(!empty($templateViews['footerContent'])) {
                            views()->display(\App\Common\THEME_MAP . '/' . $templateViews['footerContent']);
                        }
                    ?>
                </div>
            <?php }?>
            <?php
                if(!empty($templateViews['mainOutContent'])) {
                    views()->display(\App\Common\THEME_MAP . '/' . $templateViews['mainOutContent']);
                }
            ?>
            <?php
                if(!empty($templateViews['footerOutContent'])) {
                    views()->display(\App\Common\THEME_MAP . '/' . $templateViews['footerOutContent']);
                }

                if (
                    !$issetEncoreLinks
                    && !isset($templateViews['customEncoreLinks'])
                ) {
                    encoreLinks();
                }
            ?>
        </main>
        <?php views()->display('new/template/components/footer_global_view'); ?>
    </body>
</html>

