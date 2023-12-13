<script type="text/javascript">
    $(document).ready(function() {
        $('[data-toggle="popover"]').popover({
            trigger: 'hover'
        });
    });
</script>
<?php tmvc::instance()->controller->view->display('new/users_reviews/reviews_scripts_view'); ?>

<?php if (!empty($item['description'])) { ?>
    <div class="display-n" itemprop="description">
        <?php echo strip_tags(truncWords($item['description'])); ?>
    </div>
<?php } ?>

<a class="reviews-f" name="reviews-f"></a>

<div class="product-detail__comments-page mt-20">
    <div class="title-public pt-0">
        <h2 class="title-public__txt">
            <?php echo translate('item_details_all_reviews_title'); ?>
        </h2>
    </div>

    <ul class="product-comments">
        <?php if (!empty($reviews_ep)) { ?>
            <?php views('new/users_reviews/item_view', ['reviews' => $reviews_ep]); ?>
            <?php if ($countProductReviews > $limitReviews) { ?>
                <a class="btn btn-light mw-250 m-auto btn-block mt-20 mb-15" href="<?php echo makeItemUrl($item['id'], $item['title']) . '/reviews_ep'; ?>"><?php echo translate('item_details_show_more_btn'); ?></a>
            <?php } ?>
        <?php } else { ?>
            <li>
                <div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <?php echo translate('item_details_no_ep_reviews_title'); ?></div>
            </li>
        <?php } ?>
    </ul>

    <div class="title-public">
        <h2 class="title-public__txt">
            <?php echo translate('item_details_external_reviews_title'); ?>
        </h2>
    </div>

    <ul class="product-comments">
        <?php if (!empty($reviews_external)) { ?>
            <?php tmvc::instance()->controller->view->display('new/user/reviews_external/item_view', ['reviews' => $reviews_external]); ?>
        <?php } else { ?>
            <li>
                <div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i>
                    <?php echo translate('item_details_no_external_reviews_title'); ?>
                </div>
            </li>
        <?php } ?>
    </ul>

    <?php if (!empty($reviews_external)) { ?>
        <a class="btn btn-outline-dark mw-250 m-auto btn-block mt-20" href="<?php echo __SITE_URL; ?>item/<?php echo strForURL($item['title']); ?> - <?php echo $item['id']; ?>/reviews_external"><?php echo translate('item_details_view_more_external_reviews_title'); ?> &raquo;</a>
    <?php } ?>
</div>
