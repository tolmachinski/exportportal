<?php
    tmvc::instance()->controller->view->display(\App\Common\THEME_MAP.'/header_view', ["includeTemplate" => true]);
?>

<?php
if(!empty($header_out_content)) {
    tmvc::instance()->controller->view->display($header_out_content);
}
?>

<div class="container-center-sm">
    <?php
        if(!empty($header_content)) {
            tmvc::instance()->controller->view->display($header_content);
        }
    ?>

    <?php if (!isset($webpackData)) { ?>
        <link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/styles_new.css'); ?>" />
    <?php } ?>

    <?php
        if (!isset($templateViews['customEncoreLinks']) && !isset($customEncoreLinks)) {
            encoreLinks();
        }
    ?>

    <?php if(!empty($sidebar_left_content) || !empty($main_content) || !empty($sidebar_right_content)){?>
        <div class="main-flex-card">
            <?php if(!empty($main_content)){?>
                <div class="main-flex-card__float">
                    <?php tmvc::instance()->controller->view->display($main_content); ?>
                </div>
            <?php }?>
            <?php if(!empty($sidebar_left_content)){?>
                <div class="main-flex-card__fixed-left" id="main-flex-card__fixed-left">
                    <?php tmvc::instance()->controller->view->display($sidebar_left_content); ?>
                </div>
            <?php }?>
            <?php if(!empty($sidebar_right_content)){?>
                <div class="main-flex-card__fixed-right" id="main-flex-card__fixed-right">
                    <?php tmvc::instance()->controller->view->display($sidebar_right_content); ?>
                </div>
            <?php }?>
        </div>
    <?php }?>

    <?php
        if(!empty($footer_content)) {
            views($footer_content);
        }
    ?>
</div>

<?php
    if(!empty($main_out_content)) {
        tmvc::instance()->controller->view->display($main_out_content);
    }
?>

<?php
    if(!empty($footer_out_content)) {
        tmvc::instance()->controller->view->display($footer_out_content);
    }
?>

<?php
    tmvc::instance()->controller->view->display(\App\Common\THEME_MAP.'/footer_view');
?>
