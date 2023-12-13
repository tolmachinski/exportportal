<?php if (!isset($webpackData)) { ?>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/eptp.css'); ?>" />
<?php } ?>
<header class="eptp__header">
    <picture class="eptp__picture-background">
        <source media="(max-width: 425px)" srcset="<?php echo asset("public/build/images/landings/eptp/eptp_header/eptp_header-mobile.jpg"); ?>">
        <source media="(min-width: 426px) and (max-width: 991px)" srcset="<?php echo asset("public/build/images/landings/eptp/eptp_header/eptp_header-tablet.jpg"); ?>">
        <img class="image" src="<?php echo asset("public/build/images/landings/eptp/eptp_header/eptp_header.jpg"); ?>" width="1920" height="500" alt="">
    </picture>
    <div class=" eptp__container eptp__header-container">
        <h1 class="eptp__header-title">
            ABOUT EPTP
        </h1>
        <p class="eptp__header-text">
            This is a user’s unique digital identity on Export Portal, on which they can market products, post updates, and make connections.
        </p>
    </div>
</header>
