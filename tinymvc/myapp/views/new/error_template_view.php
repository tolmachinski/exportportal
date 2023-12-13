<?php
    $templateMap = \App\Common\THEME_MAP.'/template_views/';
    views()->display($templateMap.'session_preview_view');
?>

<!DOCTYPE html>
<html lang="en">
<?php
    views()->display($templateMap.'head_view');
?>
<body>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo asset("public/build/styles_user_pages_general.css");?>" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/styles_new.css'); ?>" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo asset("public/build/styles_user_pages.css");?>" />
    <?php tmvc::instance()->controller->view->display($content); ?>
</body>
</html>
