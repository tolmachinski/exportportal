<div class="wr-modal-flex inputs-40 droplist-modal">
    <form class="modal-flex__form validateModal droplist-modal__wrap" method="POST" data-js-action="droplist-edit-popup:form-submit">
        <div class="droplist-modal__subttl"><?php echo translate('items_droplist_popup_edit_item_detail') ?></div>
        <div class="modal-flex__content">
            <div class="droplist-modal__choose-checkboxes-wrap">
                <label class="custom-checkbox">
                    <input type="checkbox" name="notification-types[]" value="<?php echo $notificationTypes['Website']['value']; ?>" class="validate[required]" <?php if ($selected == $notificationTypes['Website']['label'] || $selected == $notificationTypes['Both']['label']) { echo "checked"; } ?>/>
                    <span class="custom-checkbox__text"><?php echo translate('items_droplist_popup_choose_first_checkbox') ?></span>
                </label>
            </div>
            <div class="droplist-modal__choose-checkboxes-wrap">
                <label class="custom-checkbox">
                    <input type="checkbox" name="notification-types[]" value="<?php echo $notificationTypes['Email']['value'] ?>" class="validate[required]" <?php if($selected == $notificationTypes['Email']['label'] || $selected == $notificationTypes['Both']['label']) { echo "checked"; } ?> />
                    <span class="custom-checkbox__text"><?php echo translate('items_droplist_popup_choose_second_checkbox') ?></span>
                </label>
            </div>
        </div>
        <div class="modal-flex__btns droplist-modal__btn-wrap droplist-modal__btn-wrap--edit-modal">
            <button class="btn btn-primary" type="submit"><?php echo translate('edit_item_droplist_popup_submit_btn') ?></button>
            <button class="btn btn-light js-close-fancybox"><?php echo translate('items_droplist_popup_cancel_btn') ?></button>
        </div>
        <input type="hidden" name="droplist-id" value="<?php echo $droplistItem['id']; ?>" />
    </form>
    <?php echo dispatchDynamicFragment("popup:drop-list-edit", null, true); ?>
</div>
