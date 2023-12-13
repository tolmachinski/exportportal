<div id="js-passport-template-wrapper" class="promo-materials__item promo-materials__item-passport">
    <div class="promo-materials-title">
        <h2 class="promo-materials-title__txt"><?php echo translate('promo_materials_title_passport'); ?>
            <a class="info-dialog ep-icon ep-icon_info ml-5" data-message="<?php echo translate('promo_materials_title_passport_dialog_message', null, true); ?>" data-title="<?php echo translate('promo_materials_title_passport_dialog_title'); ?>" href="#"></a>
        </h2>
        <div class="promo-materials-title__actions">
            <a class="btn btn-light" href="<?php echo __CURRENT_SUB_DOMAIN_URL . 'promo_materials/passport/pdf'; ?>" target="_blank">
                <i class="ep-icon ep-icon_download-stroke"></i>
            </a>
        </div>
    </div>
    <iframe id="js-passport-template" height="251" src="<?php echo __CURRENT_SUB_DOMAIN_URL . 'promo_materials/passport'; ?>"></iframe>
</div>
