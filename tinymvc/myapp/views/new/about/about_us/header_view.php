<div class="public-heading">
    <div class="public-heading__container">

        <?php views()->display('new/about/partial_menu'); ?>

        <h1 class="public-heading__ttl"><?php echo translate('about_us_nav_about_us'); ?></h1>
    </div>

    <picture class="display-b h-100pr">
        <source
            media="(min-width: 768px) and (max-width: 991px)"
            srcset="<?php echo getLazyImage(991, 645); ?>"
            data-srcset="<?php echo asset("public/build/images/about/about_us/header-tablet.jpg"); ?>"
        >
        <img
            class="image js-lazy"
            width="768"
            height="500"
            src="<?php echo getLazyImage(768, 500); ?>"
            data-src="<?php echo asset("public/build/images/about/about_us/header.jpg"); ?>"
            alt="<?php echo translate('about_us_nav_about_us'); ?>"
        >
    </picture>
</div>
