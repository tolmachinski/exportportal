<?php if(!isset($webpackData)){?>
    <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/subscribe/index.js'); ?>"></script>
<?php }?>
<div class="subscribe-block">
    <div class="container-center-sm">
        <h3 class="subscribe-block__ttl tac">
            <?php echo translate('subscribe_title'); ?>
        </h3>
        <div class="subscribe-block__subttl tac">
            <?php echo translate('subscribe_subtitle'); ?>
        </div>
        <form
            class="subscribe-block__form validengine"
            data-callback="subscribeFormCallBack"
            data-js-action="form:submit_form_subscribe"
            action="<?php echo __SITE_URL; ?>"
            method="post">

            <div class="input-group">
                <input class="subscribe-block__form-input js-subscribe-block-form-input validate[required,custom[noWhitespaces],custom[emailWithWhitespaces]] form-control"
                       name="email"
                       maxlength="50"
                       placeholder="<?php echo translate('home_subscribe_email_input_placeholder'); ?>"
                       type="text">
                <input type="hidden" name="current_url" value="<?php echo __CURRENT_URL_NO_QUERY; ?>">

                <div class="input-group-append">
                    <button class="subscribe-block__form-btn js-subscribe-block-form-btn btn btn-dark" type="submit" <?php echo addQaUniqueIdentifier("page__learn-more__subscribe-btn") ?>><?php echo translate('general_button_subscribe_text'); ?></button>
                </div>
            </div>

            <label class="custom-checkbox mt-10">
                <input class="validate[required]" type="checkbox" name="terms_cond">
                <span class="custom-checkbox__text-agreement">
                    <?php
                    $termsLink = ' class="fancybox fancybox.ajax" data-w="1040" data-mw="1040" data-h="400" data-title="Terms &amp; Conditions" href="' . __SITE_URL . 'terms_and_conditions/tc_register_seller"';
                    echo translate('learn_more_agree_with_terms_conditions', ['[LINK]' => $termsLink]); ?>
                </span>
            </label>
        </form>
    </div>
</div>
