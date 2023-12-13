<?php if(!isset($webpackData)){?>
    <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/js/subscribe/index.js'); ?>"></script>
<?php }?>

<div class="subscribe <?php echo $additional_class?>">
    <div class="container-center mw-995">
        <div class="subscribe-ttl">
            <div class="txt-bold">
                <?php echo translate('home_subscribe_title'); ?>
            </div>
            <div class="subscribe-ttl__sub">
                <?php echo $subscribe_subtitle; ?>
            </div>
        </div>

        <form
            class="subscribe__form relative-b validengine"
            data-callback="subscribeFormCallBack"
            data-js-action="form:submit_form_subscribe"
            action="<?php echo __SITE_URL;?>"
            method="post"
        >
            <div class="input-group">
                <input class="form-control validate[required,custom[noWhitespaces],custom[emailWithWhitespaces]]" name="email" maxlength="50" placeholder="<?php echo translate('home_subscribe_email_input_placeholder', null, true); ?>" type="text">
                <?php if($isDwnMPage) { ?>
                <input type="hidden" name="dm_page" value="<?php echo $isDwnMPage; ?>">
                <?php } ?>
                <input type="hidden" name="current_url" value="<?php echo __CURRENT_URL_NO_QUERY; ?>">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit"><?php echo translate('home_subscribe_btn_subscribe'); ?></button>
                </span>
            </div>
            <label class="pt-10 custom-checkbox">
                <input class="validate[required]" type="checkbox" name="terms_cond">
                <span class="custom-checkbox__text-agreement">
                    <?php echo translate('label_i_agree_with'); ?>
                    <?php
                        $linkTC = __SITE_URL . 'terms_and_conditions/tc_register_seller';
                        if(isset($webpackData)){
                            $linkTC .= '/webpack';
                        }
                    ?>
                    <a
                        class="fancybox fancybox.ajax"
                        data-w="1040"
                        data-mw="1040"
                        data-h="400"
                        data-title="<?php echo translate('home_subscribe_terms_and_conditions_title', null, true); ?>"
                        href="<?php echo $linkTC; ?>"
                    >
                        <?php echo translate('label_terms_and_conditions'); ?>
                    </a>
                </span>
            </label>
        </form>
    </div>
</div>
