<section class="home-section ep-customer-reviews container-1420">
    <div class="section-header section-header--title-only">
        <h2 class="section-header__title"><?php echo translate('home_customer_reviews_header_title'); ?></h2>
    </div>

    <div class="ep-customer-reviews__content">
        <div
            id="js-customer-reviews-slider"
            class="ep-customer-reviews__slider loading"
            data-lazy-name="customer-reviews"
            <?php echo addQaUniqueIdentifier('global__reviews-slider'); ?>
        ></div>

        <div class="ep-customer-reviews__share">
            <p class="ep-customer-reviews__share-txt">
                <?php echo translate('home_customer_reviews_share_paragraph', [
                    '{{START_TAG}}' => '<span>',
                    '{{END_TAG}}'   => '</span>',
                ]); ?>
            </p>
            <button
                id="js-open-write-review-popup"
                class="ep-customer-reviews__share-btn btn btn-primary btn-new18 fancybox.ajax fancyboxValidateModal"
                href="<?php echo __SITE_URL . 'ep_reviews/popup_forms/add_review'; ?>"
                data-title="Write a Review"
                data-w="540"
                <?php echo addQaUniqueIdentifier('global__reviews-slider_write-review-btn'); ?>
            >
                <?php echo translate('home_customer_reviews_btn'); ?>
            </button>
        </div>
    </div>

    <?php views('new/partials/ajax_loader_view'); ?>
</section>
