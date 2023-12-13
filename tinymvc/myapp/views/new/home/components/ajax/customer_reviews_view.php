<?php if (!empty($epReviews)) { ?>
    <?php foreach ($epReviews as $epReview) { ?>
        <div class="ep-customer-reviews__item" <?php echo addQaUniqueIdentifier('global__reviews-slide'); ?>>
            <div class="ep-customer-reviews__inner">
                <div class="ep-customer-reviews__user-img">
                    <?php $userName = $epReview['user']['fname'] . ' ' . $epReview['user']['lname']; ?>
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(100, 100); ?>"
                        data-src="<?php echo getUserAvatar($epReview['user']['idu'], $epReview['user']['user_photo'], (int) $epReview['user']['user_group'], 0); ?>"
                        width="100"
                        height="100"
                        alt="<?php echo cleanOutput($userName); ?>"
                        <?php echo addQaUniqueIdentifier('global__reviews-slider_image'); ?>
                    >
                </div>

                <div class="ep-customer-reviews__bg">
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(90, 76); ?>"
                        data-src="<?php echo asset('public/build/images/about/reviews/quote.png'); ?>"
                        width="90"
                        height="76"
                        alt="<?php echo cleanOutput($userName); ?>"
                    >
                </div>

                <div class="ep-customer-reviews__user-info">
                    <div
                        class="ep-customer-reviews__user-name"
                        <?php echo addQaUniqueIdentifier('global__reviews-slider_name'); ?>
                    >
                        <?php echo $userName; ?>
                    </div>
                    <div
                        class="ep-customer-reviews__user-group
                        <?php echo is_certified((int) $epReview['user']['user_group']) ? ' ep-customer-reviews__user-group--certified' : ''; ?>"
                        <?php echo addQaUniqueIdentifier('global__reviews-slider_group'); ?>
                    >
                        <?php echo $epReview['user']['gr_name']; ?>
                    </div>
                </div>

                <p class="ep-customer-reviews__text" <?php echo addQaUniqueIdentifier('global__reviews-slider_text'); ?>>
                    <?php echo cleanOutput($epReview['message']); ?>
                </p>
            </div>
        </div>
    <?php } ?>
<?php } ?>
