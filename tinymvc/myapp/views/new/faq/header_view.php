<script>
    function publicHeadingSearchFaq($form){
        var form_action = '<?php echo __SITE_URL;?>faq/all/';
        var keywords = $form.find('input[name=keywords]').val().replace(/(\s)+/g,"$1");

        if(keywords != ""){
            form_action += '?keywords='+keywords;
            window.location = form_action;
        }

        return false;
    }
</script>

<div class="public-heading">
    <div class="public-heading__container">
        <?php tmvc::instance()->controller->view->display('new/help/partial_menu');?>

        <div class="public-heading__detail">
            <h1 class="public-heading__min-title">Frequently Asked Questions</h1>

            <form class="public-heading__form-search validengine" data-callback="publicHeadingSearchFaq">
                <input
                    class="validate[required, minSize[<?php echo config('help_search_min_keyword_length'); ?>]]"
                    type="text"
                    name="keywords"
                    placeholder="Enter keyword"
                    maxlength="50"
                    <?php if(isset($keywords)){?> value="<?php echo $keywords?>"<?php } ?>
                >

                <button class="btn btn-primary" type="submit"><?php echo translate('help_search_btn');?></button>
            </form>

            <div class="public-heading__detail-additional">
                <h2 class="public-heading__detail-additional-ttl">Have an additional question?</h2>
                <a class="btn btn-dark mnw-200" href="<?php echo __SITE_URL;?>contact">Ask a question</a>
            </div>
        </div>
    </div>

    <img class="image" src="<?php echo __IMG_URL;?>public/img/headers-info-pages/FAQ_header.jpg" alt="<?php echo translate('help_nav_header_faq');?>">
</div>
