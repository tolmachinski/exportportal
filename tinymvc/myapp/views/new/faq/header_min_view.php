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

<div class="public-heading-min">
    <div class="container-center-sm">
        <h1 class="public-heading-min__title">Frequently Asked Questions</h1>

        <form class="public-heading-min__form-search validengine" data-callback="publicHeadingSearchFaq">
            <input
                class="validate[required, minSize[<?php echo config('help_search_min_keyword_length'); ?>]]"
                type="text"
                name="keywords"
                placeholder="Enter keyword"
                maxlength="50"
                <?php if(isset($keywords)){?> value="<?php echo $keywords?>"<?php } ?>
            >

            <button class="btn btn-primary" type="submit"><?php echo translate('help_search_btn');?></button>

            <span class="delimeter">or</span>

            <a class="btn btn-dark mnw-200 ml-0" href="<?php echo __SITE_URL;?>contact">Ask a question</a>
        </form>
    </div>
</div>
