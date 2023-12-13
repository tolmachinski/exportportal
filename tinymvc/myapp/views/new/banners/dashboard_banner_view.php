<div class="dashboard-banner inputs-40" <?php echo addQaUniqueIdentifier('global__dashboard-menu__dashboard-banner'); ?>>
    <div class="dashboard-banner__img">
        <picture>
            <img
                class="image"
                <?php echo addQaUniqueIdentifier('global__dashboard-menu__dashboard-banner_image'); ?>
                src="<?php echo getDisplayImageLink(['{FILE_NAME}' => $dashboardBanner['img']], 'dashboard_banner.main'); ?>"
                alt="image">
        </picture>
    </div>
    <div class="dashboard-banner__block">
        <div class="dashboard-banner__info">
            <div class="dashboard-banner__title" <?php echo addQaUniqueIdentifier('global__dashboard-menu__dashboard-banner_suptitle'); ?>>
                <?php echo cleanOutput($dashboardBanner['subtitle']); ?>
            </div>
            <div class="dashboard-banner__desc" <?php echo addQaUniqueIdentifier('global__dashboard-menu__dashboard-banner_title'); ?>>
                <?php echo cleanOutput($dashboardBanner['title']); ?>
            </div>
        </div>
        <a
            class="btn btn-primary dashboard-banner__btn"
            href="<?php echo cleanOutput($dashboardBanner['url']); ?>"
            <?php echo addQaUniqueIdentifier('global__dashboard-menu__dashboard-banner_btn'); ?>
        >
        <?php echo cleanOutput($dashboardBanner['button_text']); ?>
        </a>
    </div>
</div>
