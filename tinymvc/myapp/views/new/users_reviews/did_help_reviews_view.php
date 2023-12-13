<div class="did-help <?php echo isset($helpful_reviews[$review['id_review']]) ? 'rate-didhelp' : '';?>">
    <div class="did-help__txt"><?php echo translate('seller_reviews_reply_did_help_text');?></div>
    <?php
        $disabled_class = is_my($review['id_user']) ? ' disabled' : '';
        $isset_my_helpful_review = isset($helpful_reviews[$review['id_review']]);

        $btn_count_plus_class = ($isset_my_helpful_review && $helpful_reviews[$review['id_review']] == 1) ? ' txt-blue2' : '';
        $btn_count_minus_class = ($isset_my_helpful_review && $helpful_reviews[$review['id_review']] == 0) ? ' txt-blue2' : '';
    ?>
    <span class="didhelp-btn <?php echo logged_in() ? 'js-didhelp-btn ' . $disabled_class : 'js-require-logged-systmess';?>"
        data-item="<?php echo $review['id_review']?>"
        data-page="reviews"
        data-type="review"
        data-action="y">
        <span class="counter-b js-counter-plus" <?php echo addQaUniqueIdentifier("global__reviews-counter")?>><?php echo $review['count_plus']?></span>
        <span class="ep-icon ep-icon_arrow-line-up js-arrow-up<?php echo $btn_count_plus_class;?>"></span>
    </span>
    <span class="didhelp-btn <?php echo logged_in() ? 'js-didhelp-btn ' . $disabled_class : 'js-require-logged-systmess';?>"
        data-item="<?php echo $review['id_review']?>"
        data-page="reviews"
        data-type="review"
        data-action="n">
        <span class="counter-b js-counter-minus" <?php echo addQaUniqueIdentifier("global__reviews-counter")?>><?php echo $review['count_minus']?></span>
        <span class="ep-icon ep-icon_arrow-line-down js-arrow-down<?php echo $btn_count_minus_class;?>"></span>
    </span>
</div>
