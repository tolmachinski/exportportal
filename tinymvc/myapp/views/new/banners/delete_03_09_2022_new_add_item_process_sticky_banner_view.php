<div class="sticky-banner">
    <button class="btn btn-primary sticky-banner__btn"><?php echo translate('sticky_banner_add_item_process_btn'); ?></button>
    <div id="js-sticky-banner__item" class="sticky-banner__item">
        <div class="sticky-banner__img">
            <img class="image" width="294" height="160" src="<?php echo __SITE_URL . 'public/img/banners/add_items.jpg'; ?>" alt="New and improved add item process">
        </div>
        <div class="sticky-banner__description">
            <div class="sticky-banner__title"><?php echo translate('sticky_banner_add_item_process_title'); ?></div>
            <div class="sticky-banner__desc"><?php echo translate('sticky_banner_add_item_process_desc'); ?></div>
            <a <?php if (logged_in() && have_right('manage_personal_items')) { ?>
                    class="btn btn-primary mt-0 mnw-234"
                    href="<?php echo __SITE_URL . 'items/my?popup_add=open'; ?>"
                <?php } elseif (!logged_in() || !have_right('manage_personal_items')) { ?>
                    class="btn btn-primary mt-0 mnw-234 fancybox.ajax fancyboxValidateModal"
                    href="<?php echo __SITE_URL . 'login'; ?>"
                    data-mw="400"
                    data-title="Login"
                <?php } ?>
            >
                <?php echo translate('sticky_banner_add_item_process_btn_add_item'); ?>
            </a>
            <a class="sticky-banner__close-btn ep-icon ep-icon_remove-stroke call-action" data-js-action="banner-add-item:hide" href="#"></a>
        </div>
    </div>
</div>
