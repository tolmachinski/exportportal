<div class="upgrade-banner-top js-upgrade-banner-top inputs-40">
    <div class="container-center upgrade-banner-top__inner">
        <picture class="upgrade-banner-top__image">
            <source media="(max-width: 660px)" srcset="<?php echo __SITE_URL;?>public/img/upgrade_page/modals/top-banner-mobile.jpg">
            <source media="(min-width: 661px) and (max-width: 991px)" srcset="<?php echo __SITE_URL;?>public/img/upgrade_page/modals/top-banner-tablet.jpg">
            <img
                class="image"
                src="<?php echo __SITE_URL;?>public/img/upgrade_page/modals/top-banner-desktop.jpg"
                alt="<?php echo translate('upgrade_top_banner_ttl'); ?>"
            >
        </picture>

        <div class="upgrade-banner-top__info">
            <div class="upgrade-banner-top__ttl"><?php echo translate('upgrade_top_banner_ttl'); ?></div>
            <div class="upgrade-banner-top__desc"><?php echo translate('upgrade_top_banner_desc'); ?></div>
        </div>
        <div class="upgrade-banner-top__actions">
            <a
                class="upgrade-banner-top__btn btn btn-primary call-action"
                data-js-action="top-upgrade-banner:link"
                href="<?php echo __SITE_URL; ?>upgrade"
            ><?php echo translate('upgrade_top_banner_btn_start'); ?></a>

            <button
                class="upgrade-banner-top__close ep-icon ep-icon_remove-stroke call-action"
                data-js-action="top-upgrade-banner:close"
                type="button"
            ></button>
        </div>
    </div>
</div>
