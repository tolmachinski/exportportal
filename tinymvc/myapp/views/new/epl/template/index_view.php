<?php
    $templateMap = \App\Common\THEME_MAP.'/epl/template/';

    views()->display($templateMap.'session_preview_view');
?>
<!DOCTYPE html>
<html lang="<?php echo __SITE_LANG;?>">
    <?php
        views()->display($templateMap.'head_view');
    ?>

    <body>
        <?php
            views()->display($templateMap.'header_view');
        ?>
        <?php views()->display('new/template_views/bad_browser_view'); ?>
        <main class="epl-content <?php echo isset($isRegisterPage) ? 'epl-content--no-indent-lg' : ''; ?>">
            <?php
                $issetHeaderOutContant = false;
                if(!empty($templateViews['headerOutContent'])) {
                    views()->display(\App\Common\THEME_MAP . '/'. $templateViews['headerOutContent']);
                    $issetHeaderOutContant = true;

                    if (isset($webpackData) && !isset($templateViews['customEncoreLinks'])) {
                        encoreLinks();
                    }
                }
            ?>
            <?php
                if(
                    !empty($templateViews['headerContent'])
                    || !empty($templateViews['mainContent'])
                ){
            ?>
                <div class="container-center-sm">
                    <?php
                        if (!empty($templateViews['headerContent'])) {
                            views()->display(\App\Common\THEME_MAP . '/' . $templateViews['headerContent']);
                        }

                        if (
                            isset($webpackData)
                            && !$issetHeaderOutContant
                            && !isset($templateViews['customEncoreLinks'])
                        ) {
                            encoreLinks();
                        }
                    ?>

                    <?php
                        if (!empty($templateViews['mainContent'])) {
                            views()->display(\App\Common\THEME_MAP . '/' . $templateViews['mainContent']);
                        }
                    ?>
                </div>
            <?php }?>

            <?php
                if(!empty($templateViews['mainOutContent'])) {
                    views()->display(\App\Common\THEME_MAP . '/' . $templateViews['mainOutContent']);
                }

                if (
                    isset($webpackData)
                    && !$issetHeaderOutContant
                    && !isset($templateViews['customEncoreLinks'])
                    && empty($templateViews['headerContent'])
                ) {
                    encoreLinks();
                }
            ?>
        </main>
        <?php views()->display($templateMap.'footer_view'); ?>
    </body>
</html>

