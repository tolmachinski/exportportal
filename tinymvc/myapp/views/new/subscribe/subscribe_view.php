<h3 class="minfo-sidebar-ttl">
    <span class="minfo-sidebar-ttl__txt"><?php echo translate('blog_sidebar_subscribe_header'); ?></span>
</h3>

<form class="minfo-form validengine relative-b" data-callback="subscribeFormCallBack" data-js-action="form:submit_form_subscribe">
    <input class="minfo-form__input validate[required,custom[noWhitespaces],custom[emailWithWhitespaces]]"
            <?php echo addQaUniqueIdentifier('global__sidebar__subscribe-input') ?>
            name="email"
            maxlength="50"
            placeholder="Email"
            type="text"
    >
    <input type="hidden" name="current_url" value="<?php echo __CURRENT_URL_NO_QUERY; ?>">
    <label class="custom-checkbox pb-10">
        <input class="validate[required]" <?php echo addQaUniqueIdentifier('global__sidebar__subscribe-checkbox') ?> type="checkbox" name="terms_cond">
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
    <button class="minfo-form__btn btn btn-primary btn-block" <?php echo addQaUniqueIdentifier('global__sidebar__subscribe-btn') ?> type="submit"><?php echo translate('general_button_subscribe_text'); ?></button>
</form>

<?php if(!isset($webpackData)){?>
    <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/subscribe/index.js'); ?>"></script>
<?php }?>
