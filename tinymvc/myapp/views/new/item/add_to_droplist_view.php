<div class="wr-modal-flex inputs-40 droplist-modal">
    <form class="modal-flex__form validateModal droplist-modal__wrap" method="POST" data-js-action="droplist-popup:form-submit">
        <div class="droplist-modal__subttl"><?php echo translate('items_droplist_popup_subttl') ?></div>
        <div class="modal-flex__content droplist-modal__item-wrap">
            <div class="droplist-modal__item-wrap-title"><?php echo translate('items_droplist_popup_item_detail') ?></div>
            <div class="droplist-modal__item-row">
                <div class="droplist-modal__item-img">
                    <img class="image" src="<?php echo $item['main_photo']['url']; ?>" alt="<?php echo $photo['main_photo']['photo_name']; ?>" <?php echo addQaUniqueIdentifier('popup__add-to-droplist__img'); ?> width="87" height="65"/>
                </div>
                <div class="droplist-modal__item-content">
                    <div class="droplist-modal__item-title" <?php echo addQaUniqueIdentifier('popup__add-to-droplist__ttl-item'); ?>><?php echo $item['title']; ?> </div>
                    <div class="droplist-modal__item-price" <?php echo addQaUniqueIdentifier('popup__add-to-droplist__price-item'); ?>><?php echo \get_price($item['final_price']); ?></div>
                </div>
            </div>
            <div class="droplist-modal__choose-wrap">
                <div class="droplist-modal__choose-title"><?php echo translate('items_droplist_popup_choose_ttl') ?></div>
                <div class="droplist-modal__choose-checkboxes-wrap">
                    <label class="custom-checkbox">
                        <input type="checkbox" name="notification-types[]" value="<?php echo $notificationTypes['Website']['value']; ?>" class="validate[required]" />
                        <span class="custom-checkbox__text"><?php echo translate('items_droplist_popup_choose_first_checkbox') ?></span>
                    </label>
                </div>
                <div class="droplist-modal__choose-checkboxes-wrap">
                    <label class="custom-checkbox">
                        <input type="checkbox" name="notification-types[]" value="<?php echo $notificationTypes['Email']['value']; ?>" class="validate[required]" />
                        <span class="custom-checkbox__text"><?php echo translate('items_droplist_popup_choose_second_checkbox') ?></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="modal-flex__btns droplist-modal__btn-wrap">
            <button class="btn btn-primary" type="submit"><?php echo translate('items_droplist_popup_submit_btn') ?></button>
            <button class="btn btn-light js-close-fancybox"><?php echo translate('items_droplist_popup_cancel_btn') ?></button>
        </div>
        <input type="hidden" name="item-id" value="<?php echo $item['id']; ?>" />
    </form>

    <?php
    echo dispatchDynamicFragment(
        "popup:drop-list",
        null,
        true
    );
    ?>
</div>
