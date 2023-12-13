<div class="upgrade-become-header">
    <div class="upgrade-become-header__info">
        <h1 class="upgrade-become-header__ttl"><?php echo translate('upgrade_header_title_text');?></h1>

        <div class="upgrade-become-header__text">
            <p><?php echo translate('upgrade_header_description_text');?></p>

            <?php if(filter_var(config('env.FREE_CERTIFICATION'), FILTER_VALIDATE_BOOLEAN)){?>
                <p class="upgrade-become-header__free-certification"><?php echo translate('upgrade_header_free_certification_text', ['{{DATE}}' => $dateFreePackage]); ?></p>
            <?php }?>
        </div>
    </div>

    <picture class="upgrade-become-header__image">
        <source media="(max-width: 475px)" srcset="<?php echo __SITE_URL;?>public/img/headers-info-pages/upgrade-header-mobile.jpg">
            <source media="(min-width: 476px) and (max-width: 991px)" srcset="<?php echo __SITE_URL;?>public/img/headers-info-pages/upgrade-header-tablet.jpg">
            <img
                class="image"
                src="<?php echo __SITE_URL;?>public/img/headers-info-pages/upgrade-header-desktop.jpg"
                alt="<?php echo translate('upgrade_header_title_text');?>"
            >
    </picture>
</div>
