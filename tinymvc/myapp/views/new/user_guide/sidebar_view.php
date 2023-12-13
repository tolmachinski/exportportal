<script>
    $(document).ready(function() {
        $('body').on('click', '#search_doc_form', function(e) {
            e.preventDefault();
            var $form = $(this).closest('form');

            if (!$($form).validationEngine('validate')) {
                return false;
            }

            var form_action = '<?php echo __SITE_URL; ?>user_guide/search/';
            var keywords = $form.find('input[name=keywords]').val().replace(/(\s)+/g, "$1");
            if (keywords != '' && keywords != '+')
                form_action += '?keywords=' + keywords;

            window.location = form_action;
        });
    });
</script>

<?php if(!empty($search_params)){?>
    <h3 class="minfo-sidebar-ttl">
        <span class="minfo-sidebar-ttl__txt">Active Filters</span>
    </h3>

    <div class="minfo-sidebar-box">
        <div class="minfo-sidebar-box__desc">
            <ul class="minfo-sidebar-params">
                <?php foreach($search_params as $item){?>
                    <li class="minfo-sidebar-params__item">
                        <div class="minfo-sidebar-params__ttl">
                            <div class="minfo-sidebar-params__name"><?php echo $item['param']?>:</div>
                        </div>

                        <ul class="minfo-sidebar-params__sub">
                            <li class="minfo-sidebar-params__sub-item">
                                <div class="minfo-sidebar-params__sub-ttl"><?php echo $item['title']?></div>
                                <a class="minfo-sidebar-params__sub-close ep-icon ep-icon_remove-stroke" href="<?php echo $item['link']; if($item['param'] != 'Keywords') echo $get_params;?>"></a>
                            </li>
                        </ul>
                    </li>
                <?php } ?>

                <li>
                    <a class="btn btn-light btn-block txt-blue2" href="user_guide">Clear all</a>
                </li>
            </ul>
        </div>
    </div>
<?php } ?>

<h3 class="minfo-sidebar-ttl">
    <span class="minfo-sidebar-ttl__txt"><?php echo translate('help_search_btn');?></span>
</h3>
<div class="minfo-sidebar-box">
    <div class="minfo-sidebar-box__desc">
        <form class="validengine_search minfo-form mb-0" action="" method="POST">
            <input type="text" class="validate[required, minSize[<?php echo config('help_search_min_keyword_length'); ?>]] mb-10" maxlength="50" name="keywords" <?php if (isset($keywords)) { ?> value="<?php echo $keywords ?>" <?php } ?> placeholder="<?php echo translate('help_search_placeholder'); ?>">
            <button class="btn btn-dark btn-block minfo-form__btn2" id="search_doc_form" type="submit"><?php echo translate('help_search_btn'); ?></button>
        </form>
    </div>
</div>

<?php views()->display('new/subscribe/subscribe_view'); ?>
