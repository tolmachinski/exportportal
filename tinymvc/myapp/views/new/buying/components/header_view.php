<div class="info-header container-1420">
    <div class="info-header__wrap">
        <h1 class="info-header__ttl"><?php echo translate('buying_header_ttl');?></h1>

        <picture class="info-header__background">
            <source media="(max-width: 475px)" srcset="<?php echo asset("public/build/images/buying/header-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/buying/header-mobile@2x.jpg"); ?> 2x">
            <source media="(max-width: 991px)" srcset="<?php echo asset("public/build/images/buying/header-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/buying/header-tablet@2x.jpg"); ?> 2x">
            <img class="image" src="<?php echo asset('public/build/images/buying/header.jpg'); ?>" alt="<?php echo translate('buying_header_image_alt');?>" width="1920" height="400">
        </picture>
    </div>
</div>
