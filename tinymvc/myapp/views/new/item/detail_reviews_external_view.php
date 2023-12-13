<?php if (!empty($item['description'])) { ?>
    <div class="display-n" itemprop="description">
        <?php echo strip_tags(truncWords($item['description'])); ?>
    </div>
<?php } ?>

<a class="reviews-f" name="reviews-f"></a>

<div class="product-detail__comments-page mt-20">
    <div class="title-public pt-0">
        <h2 class="title-public__txt">
            <?php echo translate('item_external_reviews_title'); ?>
        </h2>
    </div>

    <ul class="product-comments">
        <?php if (!empty($reviews_external)) { ?>
            <?php views()->display('new/user/reviews_external/item_view', ['reviews' => $reviews_external]); ?>
        <?php } else { ?>
            <li>
                <div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i><?php echo translate('item_external_reviews_no_reviews_title'); ?></div>
            </li>
        <?php } ?>
    </ul>
</div>
