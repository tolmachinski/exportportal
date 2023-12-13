<div class="subscribe-benefits">
    <div class="subscribe-benefits__items subscribe-benefits-list">
        <?php foreach ($benefits as $benefit) { ?>
            <div class="subscribe-benefits-list__item">
                <img class="subscribe-benefits-list__icon" src="<?php echo $benefit['icon']; ?>" alt="<?php echo $benefit['desc']; ?>">
                <div class="subscribe-benefits-list__desc"><?php echo $benefit['desc']; ?></div>
            </div>
        <?php } ?>
    </div>

    <form
        id="js-subscribe-benefits-form"
        class="subscribe-benefits-form inputs-40"
        data-js-action="popup:subscribe-form-submit"
    >
        <div class="input-group">
            <input
                class="form-control validate[required,custom[noWhitespaces],custom[emailWithWhitespaces]]"
                name="email"
                maxlength="100"
                placeholder="<?php echo translate('home_subscribe_email_input_placeholder', null, true); ?>"
                type="text"
            >
            <input type="hidden" name="current_url" value="<?php echo __CURRENT_URL_NO_QUERY; ?>">
            <span class="input-group-btn">
                <button class="btn btn-dark" type="submit"><?php echo translate('home_subscribe_btn_subscribe'); ?></button>
            </span>
        </div>

        <label class="subscribe-benefits-form__checkbox custom-checkbox">
            <input class="validate[required]" type="checkbox" name="terms_cond">
            <span class="custom-checkbox__text-agreement">
                <?php echo translate('label_i_agree_with'); ?>

                <a href="<?php echo __SITE_URL . 'terms_and_conditions/tc_register_seller'; ?>" target="_blank" >
                    <?php echo translate('label_terms_and_conditions'); ?>
                </a>
            </span>
        </label>
    </form>
</div>

<?php
    echo dispatchDynamicFragment(
        "popup:subscribe",
        null,
        true
    );
?>

