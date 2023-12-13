<?php

if (DEBUG_MODE) {
    echo "<!--<pre> session - ";
    print_r(session()->getAll());
    echo 'cookies - ';
    print_r($_COOKIE);
    echo "</pre>-->";
    echo "<!--<pre>";
    if (!empty($errors)) {
        print_r($errors);
    }
    echo "</pre>-->";
}

?>
<!DOCTYPE html>

<html>

<head>
    <base href="<?php echo __CURRENT_SUB_DOMAIN_URL ?>">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
    <meta name="p:domain_verify" content="65e19a1e8a4d0fa30bb8e1758b7166f8" />
    <meta name="msvalidate.01" content="B055EDD80CC73148C91A728997D2DDE0" />

    <?php widgetMetaHeader($meta_params, $meta_data, 'new/'); ?>

    <link rel="stylesheet" type="text/css" href="<?php echo fileModificationTime('public/css/bloggers.min.css'); ?>" />

    <?php tmvc::instance()->controller->view->display('new/js_global_vars_view'); ?>
    <?php tmvc::instance()->controller->view->display('new/js_analytics_view'); ?>

    <?php if (logged_in()) { ?>
        <meta name="csrf-token" content="<?php echo session()->csrfToken;?>">
    <?php } ?>
</head>

<body>
    <?php views()->display('new/template_views/tag_manager_body_view'); ?>

    <header>
        <div class="container">
            <a target="_blank" href="<?php echo __SITE_URL;?>" class="ep_logo">
                <img src="<?php echo fileModificationTime('public/img/bloggers/logo.png', __IMG_URL); ?>" alt="Export Portal">
            </a>
            <form class="header-form code-submit-form js-ep-self-autotrack" action="<?php echo $url['validate_code']; ?>" data-tracking-events="submit" data-tracking-alias="form-bloggers-access-code">
                <input type="text" name="code" placeholder="Enter Access Code">
                <button type="submit">Submit</button>
            </form>
        </div>
    </header>
