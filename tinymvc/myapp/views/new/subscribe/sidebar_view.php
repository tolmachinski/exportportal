<div class="filter-block">
    <h2 class="filter-block__ttl filter-block__ttl--margin"><?php echo translate('general_subscribe_sidebar_title'); ?></h2>

    <form
        class="validengine"
        data-callback="subscribeFormCallBack"
        data-js-action="form:submit_form_subscribe"
    >
        <input
            class="ep-input validate[required,custom[noWhitespaces],custom[emailWithWhitespaces]]"
            name="email"
            maxlength="50"
            placeholder="<?php echo translate('general_subscribe_sidebar_input_email_placeholder'); ?>"
            type="text"
            <?php echo addQaUniqueIdentifier('global__subscribe_input'); ?>
        >

        <input type="hidden" name="current_url" value="<?php echo __CURRENT_URL_NO_QUERY; ?>">

        <label class="filter-block__checkbox-item custom-checkbox">
            <input
                class="validate[required]"
                type="checkbox"
                name="terms_cond"
                <?php echo addQaUniqueIdentifier('global__subscribe_checkbox'); ?>
            >
            <span class="custom-checkbox__text-agreement">
                <?php echo translate('label_i_agree_with'); ?>
                <a
                    class="fs-14 fancybox fancybox.ajax"
                    data-w="1040"
                    data-mw="1040"
                    data-h="400"
                    data-title="<?php echo translate('ep_general_terms_and_conditions', null, true); ?>"
                    href="<?php echo __SITE_URL . 'terms_and_conditions/tc_subscription_terms_of_conditions'; ?>"
                >
                    <?php echo translate('label_terms_and_conditions'); ?>
                </a>
            </span>
        </label>

        <button
            class="btn btn-default btn-block btn-new16 filter-block__btn"
            type="submit"
            <?php echo addQaUniqueIdentifier('global__subscribe_button_submit'); ?>
        ><?php echo translate('general_button_subscribe_text'); ?></button>
    </form>
</div>
