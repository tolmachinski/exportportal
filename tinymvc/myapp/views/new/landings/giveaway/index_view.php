<?php
    $templateMap = \App\Common\THEME_MAP.'/landings/giveaway/template/';
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
        <main>
            <?php
                $issetHeaderOutContent = false;
                if(!empty($templateViews['headerOutContent'])) {
                    views()->display(\App\Common\THEME_MAP . '/'. $templateViews['headerOutContent']);
                    $issetHeaderOutContent = true;

                    if (isset($webpackData) && !isset($templateViews['customEncoreLinks'])) {
                        encoreLinks();
                    }
                }
            ?>

            <?php
                if(!empty($templateViews['mainOutContent'])) {
                    views()->display(\App\Common\THEME_MAP . '/' . $templateViews['mainOutContent']);
                }

                if (
                    isset($webpackData)
                    && !$issetHeaderOutContent
                    && !isset($templateViews['customEncoreLinks'])
                    && empty($templateViews['headerContent'])
                ) {
                    encoreLinks();
                }
            ?>
        </main>
        <?php views()->display('new/template/components/footer_global_view'); ?>
    </body>
</html>

