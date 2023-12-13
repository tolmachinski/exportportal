
<div class="sidebar-subscribe">
    <h3 class="sidebar-subscribe__ttl"><?php echo translate('sidebar_subscribe_ttl'); ?></h3>

    <form class="sidebar-subscribe__form validengine" data-callback="subscribeFormCallBack" data-js-action="form:submit_form_subscribe">
        <input class="sidebar-subscribe__form-input ep-input validate[required,custom[noWhitespaces],custom[emailWithWhitespaces]]"
               name="email"
               maxlength="50"
               placeholder="<?php echo translate('sidebar_subscribe_email_input_placeholder'); ?>"
               type="text"
        >
        <input type="hidden" name="current_url" value="<?php echo __CURRENT_URL_NO_QUERY; ?>">
        <label class="sidebar-subscribe__checkbox custom-checkbox">
            <input class="validate[required]" type="checkbox" name="terms_cond">
            <span class="custom-checkbox__text-agreement">
                <?php echo translate('label_i_agree_with'); ?>
                <a class="fancybox fancybox.ajax"
                   data-w="1040"
                   data-mw="1040"
                   data-h="400"
                   data-title="<?php echo translate('ep_general_terms_and_conditions', null, true); ?>"
                   href="<?php echo __SITE_URL . 'terms_and_conditions/tc_register_seller'; ?>">
                    <?php echo translate('label_terms_and_conditions'); ?>
                </a>
            </span>
        </label>
        <button class="sidebar-subscribe__form-btn  btn btn-new16 btn-light btn-block" type="submit"><?php echo translate('general_button_subscribe_text'); ?></button>
    </form>
</div>

<?php if(!isset($webpackData)){?>
    <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/subscribe/index.js'); ?>"></script>
<?php }?>
