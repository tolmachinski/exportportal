
<div
    id="js-giveaway-contests-actions"
    class="giveaway-contests-actions inputs-40"
>
    <?php if (!logged_in()) { ?>
        <a class="btn btn-primary call-action" data-js-action="popup:close-giveaway-contests" href="<?php echo __SITE_URL;?>register">Register now</a>
        <a class="btn btn-light call-action" data-js-action="popup:close-giveaway-contests" href="<?php echo __GIVEAWAY_URL;?>">Learn more</a>
    <?php } else { ?>
        <a class="btn btn-primary call-action" data-js-action="popup:close-giveaway-contests" href="<?php echo __GIVEAWAY_URL;?>">Get started</a>
    <?php } ?>

    <div class="checkbox-list">
        <div class="checkbox-list__item">
            <label class="checkbox-list__label custom-checkbox">
                <input
                    class="js-giveaway-contests"
                    name="dont_show_more"
                    type="checkbox"
                    value="1"
                ><span class="custom-checkbox__text">Don’t show it again</span>
            </label>
        </div>
    </div>
</div>

<?php
    echo dispatchDynamicFragment(
        "popup:giveaway_contest",
        [],
        true
    );
?>
