<?php if (!empty($epReviews)) {?>
    <div id="js-reviews-slider" class="ep-reviews" <?php echo addQaUniqueIdentifier('global__reviews-slider'); ?>>
        <?php foreach ($epReviews as $epReview) {?>
            <?php $userName = $epReview['user']['fname'] . ' ' . $epReview['user']['lname'];?>
            <div class="ep-reviews__item" <?php echo addQaUniqueIdentifier('global__reviews-slide'); ?>>
                <div class="ep-reviews__inner">
                    <div class="ep-reviews__user-img">
                        <img
                            class="image js-lazy"
                            src="<?php echo getLazyImage(100, 100); ?>"
                            data-src="<?php echo getUserAvatar($epReview['user']['idu'], $epReview['user']['user_photo'], (int) $epReview['user']['user_group'], 1);?>"
                            width="100"
                            height="100"
                            alt="<?php echo cleanOutput($userName);?>"
                            <?php echo addQaUniqueIdentifier('global__reviews-slider_image'); ?>>
                    </div>

                    <div class="ep-reviews__bg">
                        <img
                            class="image js-lazy"
                            src="<?php echo getLazyImage(90, 76); ?>"
                            data-src="<?php echo asset("public/build/images/about/reviews/quote.png"); ?>"
                            width="90"
                            height="76"
                            alt="<?php echo cleanOutput($userName);?>">
                    </div>

                    <div class="ep-reviews__user-info">
                        <div class="ep-reviews__user-name" <?php echo addQaUniqueIdentifier('global__reviews-slider_name'); ?>><?php echo $userName;?></div>
                        <div class="ep-reviews__user-group<?php echo is_certified((int) $epReview['user']['user_group']) ? ' ep-reviews__user-group--certified' : '';?>" <?php echo addQaUniqueIdentifier('global__reviews-slider_group'); ?>><?php echo $epReview['user']['gr_name'];?></div>
                    </div>

                    <p class="ep-reviews__text" <?php echo addQaUniqueIdentifier('global__reviews-slider_text'); ?>><?php echo cleanOutput($epReview['message']);?></p>
                </div>
            </div>
        <?php } ?>
    </div>
<?php }?>
<div class="ep-reviews__share">
    <p><?php echo translate("about_why_ep_reviews_paragraph"); ?></p>
    <?php
        $btnClasses = "call-action";
        $attributes = "
            data-js-action=\"about-why-ep:log-in-popup\"
            data-title=\"" . translate("about_why_ep_popup_login_title", null, true) . "\"
            data-sub-title=\"" . translate("about_why_ep_popup_login_content", ["[[HREF]]" => "&quot;" . __SITE_URL . "register&quot;", "[[TITLE]]" => "&quot;Go to the registration page&quot;"], true) . "\"
        ";
        if (logged_in()) {
            $btnClasses = "fancybox.ajax fancyboxValidateModal";
            $attributes = "
                href=\"" . __SITE_URL . "ep_reviews/popup_forms/add_review\"
                data-title=\"Write a Review\"
                data-w=\"540\"
            ";
        }
    ?>

    <button id="js-open-write-review-popup" class="btn btn-new16 btn-primary <?php echo $btnClasses; ?>" <?php echo $attributes; ?> <?php echo addQaUniqueIdentifier('global__reviews-slider_write-review-btn'); ?>>
        <?php echo translate("about_why_ep_reviews_button"); ?>
    </button>
</div>
