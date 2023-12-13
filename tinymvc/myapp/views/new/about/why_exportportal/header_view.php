<div class="public-heading">
    <div class="public-heading__container">

        <?php views()->display('new/about/partial_menu'); ?>

        <h1 class="public-heading__ttl p-0-sm"><?php echo translate('about_us_h1_why_exportportal'); ?></h1>
    </div>

    <picture class="display-b h-100pr">
        <source media="(min-width: 768px) and (max-width: 991px)" srcset="<?php echo asset("public/build/images/about/why_ep/header-tablet.jpg"); ?>">
        <img
            class="image"
            width="768"
            height="500"
            src="<?php echo asset("public/build/images/about/why_ep/header.jpg"); ?>"
            alt="<?php echo translate('about_us_h1_why_exportportal'); ?>"
        >
    </picture>
</div>
