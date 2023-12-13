
<div
    id="js-find-business-partners-actions"
    class="find-business-partners-actions inputs-40"
>
    <a class="btn btn-primary call-action" data-js-action="popup:close-find-business-partners" href="<?php echo __SITE_URL;?>b2b">
        <?php echo translate('popup_find_business_partners_yes_btn') ?>
    </a>
    <button class="btn btn-light call-action js-close-btn" data-js-action="popup:close-find-business-partners" data-close="false">
        <?php echo translate('popup_find_business_partners_no_btn') ?>
    </button>

    <div class="checkbox-list">
        <div class="checkbox-list__item">
            <label class="checkbox-list__label custom-checkbox">
                <input
                    class="js-find-business-partners"
                    name="dont_show_more"
                    type="checkbox"
                    value="1"
                >
                <span class="custom-checkbox__text">
                    <?php echo translate('popup_find_business_partners_dont_show_again_checkbox') ?>
                </span>
            </label>
        </div>
    </div>
</div>

<?php
    echo dispatchDynamicFragment(
        "popup:find_business_partners",
        [],
        true
    );
?>
