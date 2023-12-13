<script>
    $(document).ready(function() {
        $('body').on('click', '#search_help_form', function(e) {
            e.preventDefault();

            var $form = $(this).closest('form');
            if (!$($form).validationEngine('validate')) {
                return false;
            }

            var form_action = '<?php echo __SITE_URL; ?>help/search/';
            var keywords = $form.find('input[name=keywords]').val().replace(/(\s)+/g, "$1");
            if (keywords != '' && keywords != '+')
                form_action += '?keywords=' + keywords;

            window.location = form_action;
        });
    });
</script>

<h3 class="minfo-sidebar-ttl mt-25">
    <span class="minfo-sidebar-ttl__txt"><?php echo translate('help_search_btn');?></span>
    <div class="minfo-sidebar-ttl__line"></div>
</h3>

<div class="minfo-sidebar-box">
    <div class="minfo-sidebar-box__desc">
        <form class="validengine_search minfo-form mb-0" method="POST">
            <input type="text" class="validate[required, minSize[<?php echo config('help_search_min_keyword_length'); ?>]] mb-10" name="keywords" maxlength="50" <?php if (isset($keywords)) { ?> value="<?php echo $keywords ?>" <?php } ?> placeholder="<?php echo translate('help_search_placeholder'); ?>">
            <button class="btn btn-dark btn-block minfo-form__btn2" id="search_help_form" type="submit"><?php echo translate('help_search_btn'); ?></button>
        </form>
    </div>
</div>

<h3 class="minfo-sidebar-ttl">
    <span class="minfo-sidebar-ttl__txt">Nothing found?</span>
    <div class="minfo-sidebar-ttl__line"></div>
</h3>

<div class="minfo-new-sidebar">
    <div class="minfo-sidebar-box">
        <div class="minfo-sidebar-box__desc">
            <div class="minfo-sidebar-box__text txt-medium lh-24">
                <div class="fs-16 txt-medium mb-5">Fell free to contact us anytime via phone or email.</div>
                <div class="mt-20">
                    <div class="txt-gray mb-5">Email Us</div>
                    <div>info@exportportal.com</div>
                </div>
                <div class="mt-20 mb-20">
                    <div class="txt-gray mb-5">Call Us</div>
                    <div class="mb-5">International call</div>
                    <div class="mb-5">+1 (818) 691-0079</div>
                    <div class="mb-5">Free call</div>
                    <div>+1 (800) 289-0015</div>
                </div>
            </div>

            <a class="btn btn-primary btn-block" href="<?php echo __SITE_URL;?>contact">Contact Us</a>
            <div class="tac pt-10 pb-10">or</div>
            <a class="btn btn-light btn-block call-action" data-js-action="zoho-chat:show" title="Chat" href="<?php echo __SITE_URL;?>contact">Chat now</a>
        </div>
    </div>
</div>
