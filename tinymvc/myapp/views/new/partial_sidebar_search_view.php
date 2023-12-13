<h3 class="minfo-sidebar-ttl">
    <span class="minfo-sidebar-ttl__txt"><?php echo $partial_search_params['title'];?></span>
</h3>

<div class="minfo-sidebar-box">
    <div class="minfo-sidebar-box__desc">
        <form class="minfo-form mb-0 validengine_search" action="<?php echo $partial_search_params['action']; ?>" method="GET">
            <input
                class="js-partial-search-input minfo-form__input2 validate[required, minSize[2], maxSize[50]]"
                <?php echo addQaUniqueIdentifier('global__sidebar__search-input') ?>
                type="text"
                name="keywords"
                maxlength="50"
                value="<?php echo empty($partial_search_params['keywords']) ? '' : cleanOutput($partial_search_params['keywords']);?>"
                placeholder="<?php echo $partial_search_params['input_text_placeholder'];?>"
                <?php echo addQaUniqueIdentifier("global__sidebar__form_keywords-input")?>
            >
            <button class="btn btn-dark btn-block minfo-form__btn2" type="submit" <?php echo addQaUniqueIdentifier("global__sidebar__form_search-btn")?>><?php echo $partial_search_params['btn_text_submit'];?></button>
        </form>
    </div>
</div>

<script>
    var partialSearchInputKeywords = '',
        $partialSearchInput,
        partialSearchLink;
    $(function(){
        $partialSearchInput = $('.js-partial-search-input');
        partialSearchInputKeywords = encodeURIComponent($partialSearchInput.val());
        partialSearchLink = $partialSearchInput.closest('form').attr('action');

        $("body").on('change', '.js-partial-search-input', function() {
            var $this = $(this);
            var $close = $this.siblings('.ep-icon');

            if ($this.val() == '') {
                $close.addClass('d-none');
            } else {
                $close.removeClass('d-none');
            }
        });
    });

    var cleanPartialSearchBtn = function($this){
        if(partialSearchInputKeywords !== ''){
            window.location.href = partialSearchLink;
        }else{
            $partialSearchInput.val('');
            $this.addClass('d-none');
        }
    }
</script>
