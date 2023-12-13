<?php foreach ($reviews as $reviewKey => $review) { ?>
    <li class="product-comments__item" <?php echo addQaUniqueIdentifier("global__reviews")?> id="li-review-<?php echo $review['id_review']; ?>" itemprop="review" itemscope itemtype="http://schema.org/Review">
        <div class="product-comments__object" <?php echo addQaUniqueIdentifier("global__reviews-title")?> itemprop="name"><?php echo $review['rev_title']; ?></div>

        <div class="flex-card">
            <div class="product-comments__img flex-card__fixed image-card3">
                <span class="link">
                    <?php if (!$view_user_review) { ?>
                        <?php
                        $item_img_link = getDisplayImageLink(['{ID}' => $review['id_item'], '{FILE_NAME}' => $review['photo_name']], 'items.main', ['thumb_size' => 1]);
                        ?>
                        <img
                            class="image js-lazy"
                            src="<?php echo getLazyImage(75, 75); ?>"
                            data-src="<?php echo $item_img_link; ?>"
                            alt="<?php echo $review['item_name']; ?>"
                            <?php echo addQaUniqueIdentifier("global__reviews-image")?>
                        />
                    <?php } else { ?>
                        <img
                            class="image js-lazy"
                            src="<?php echo getLazyImage(75, 75); ?>"
                            data-src="<?php echo getDisplayImageLink(['{ID}' => $review['id_user'], '{FILE_NAME}' => $review['user_photo']], 'users.main', ['thumb_size' => 0, 'no_image_group' => $review['user_group']]); ?>"
                            alt="<?php echo $review['fname']; ?>"
                            <?php echo addQaUniqueIdentifier("global__reviews-image")?>
                        />
                    <?php } ?>
                </span>
            </div>

            <div class="product-comments__detail flex-card__float">
                <div class="product-comments__ttl">
                    <div class="">
                        <div class="product-comments__name pt-20">
                            <a class="link" href="<?php echo getUserLink($review['fname'] . ' ' . $review['lname'], $review['id_user'], 'buyer'); ?>"><span itemprop="author" <?php echo addQaUniqueIdentifier("global__reviews-name")?>><?php echo $review['fname'] . ' ' . $review['lname']; ?></span></a>
                        </div>

                        <div class="product-comments__country">
                            <img
                                class="js-lazy"
                                width="24"
                                height="24"
                                src="<?php echo getLazyImage(24, 24); ?>"
                                data-src="<?php echo getCountryFlag($review['user_country']); ?>"
                                alt="<?php echo $review['user_country']; ?>"
                                title="<?php echo $review['user_country']; ?>"
                                <?php echo addQaUniqueIdentifier('global__reviews-country-flag'); ?>
                            />
                            <span <?php echo addQaUniqueIdentifier('global__reviews-country-name'); ?>><?php echo $review['user_country']; ?></span>
                        </div>
                    </div>

                    <div class="product-comments__rating" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
                        <div class="product-comments__rating-center" <?php echo addQaUniqueIdentifier('global__rating-circle'); ?> itemprop="ratingValue"><?php echo $review['rev_raiting']; ?></div>
                        <input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-orange fs-12" data-empty="ep-icon ep-icon_star txt-gray-light fs-12" type="hidden" name="val" value="<?php echo $review['rev_raiting']; ?>" data-readonly>
                    </div>
                </div>


                <div class="flex-display flex-jc--sb pt-20">
                    <div class="">
                        <span class="txt-gray"><?php echo translate('seller_reviews_ep_review_block_about_item'); ?></span>
                        <a class="product-comments__name-link" href="<?php echo __SITE_URL . 'item/' . strForURL($review['item_name']) . '-' . $review['id_item']; ?>">
                            <div class="grid-text">
                                <span class="grid-text__item" <?php echo addQaUniqueIdentifier('global__reviews__item-name'); ?> itemprop="itemReviewed"><?php echo $review['item_name']; ?></span>
                            </div>
                        </a>
                    </div>

                    <span class="product-comments__date" itemprop="datePublished" <?php echo addQaUniqueIdentifier("global__reviews-date")?>><?php echo getDateFormat($review['rev_date'], null, 'M d, Y'); ?></span>
                </div>

                <div class="product-comments__text" itemprop="description" <?php echo addQaUniqueIdentifier("global__reviews-text")?>><?php echo $review['rev_text']; ?></div>

                <?php if (!empty($review['images'])) {?>
                    <div class="product-comments__images<?php echo $isReviewPopup ? ' product-comments__images--sm' : ''; ?>">
                        <?php foreach ($review['images'] as $key => $reviewImage) {?>
                            <div class="product-comments__images-item image-card3">
                                <?php if ($isReviewDetails ?? null) {?>
                                    <span class="product-comments__images-image">
                                        <img
                                            class="image"
                                            <?php echo addQaUniqueIdentifier("global__reviews__image-reviews")?>
                                            src="<?php echo getDisplayImageLink(['{REVIEW_ID}' => $review['id_review'], '{FILE_NAME}' => $reviewImage], 'product_reviews.main', ['thumb_size' => 0]); ?>"
                                            alt="<?php echo "Product review image #{$key}"; ?>"
                                        >
                                    </span>
                                <?php } else {?>
                                    <a class="link fancyboxGallery" data-title="<?php echo "Image #{$key}"; ?>" rel="<?php echo "gallery_review_{$reviewKey}";?>" href="<?php echo getDisplayImageLink(['{REVIEW_ID}' => $review['id_review'], '{FILE_NAME}' => $reviewImage], 'product_reviews.main'); ?>">

                                        <img
                                            class="image js-lazy"
                                            <?php echo addQaUniqueIdentifier("global__reviews__image-reviews")?>
                                            src="<?php echo getLazyImage(135, 108); ?>"
                                            data-src="<?php echo getDisplayImageLink(['{REVIEW_ID}' => $review['id_review'], '{FILE_NAME}' => $reviewImage], 'product_reviews.main', ['thumb_size' => 0]); ?>"
                                            alt="<?php echo "Product review image #{$key}"; ?>"
                                        >
                                    </a>
                                <?php }?>
                            </div>
                        <?php }?>
                    </div>
                <?php }?>

                <div class="product-comments__actions">
                    <div class="product-comments__left">
                        <?php
                        if (empty($review['reply'])) {
                            views('new/users_reviews/did_help_reviews_view', ['review' => $review]);
                        }
                        ?>
                    </div>

                    <?php $_can_edit_review = have_right('write_reviews') && is_my($review['id_user']) && empty($review['reply']) && 'moderated' != $review['rev_status']; ?>
                    <?php $_can_reply_review = empty($review['reply']) && is_privileged('user', $review['id_seller'], 'reply_reviews'); ?>
                    <?php if (logged_in() && ($_can_edit_review || $_can_reply_review || have_right('moderate_content') || !is_my($review['id_user']))) { ?>
                        <div class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#" <?php echo addQaUniqueIdentifier('global__reviews__dropdown-btn'); ?>>
                                <i class="ep-icon ep-icon_menu-circles"></i>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                <?php if ($_can_edit_review) { ?>
                                    <a
                                        class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                        data-title="<?php echo translate('general_button_edit_text', null, true); ?>"
                                        title="<?php echo translate('general_button_edit_text', null, true); ?>"
                                        href="<?php echo __SITE_URL . 'reviews/popup_forms/edit_user_review/' . $review['id_review']; ?>"
                                        <?php echo addQaUniqueIdentifier('global__reviews__dropdown-menu_edit-btn'); ?>
                                    >
                                        <i class="ep-icon ep-icon_pencil"></i> <?php echo translate('general_button_edit_text'); ?>
                                    </a>
                                <?php } ?>

                                <?php if (have_right('moderate_content')) { ?>
                                    <?php if ('moderated' != $review['rev_status']) { ?>
                                        <a class="dropdown-item confirm-dialog" data-callback="moderate_review" data-message="<?php echo translate('seller_ep_reviews_moderate_btn_confirm_message', null, true); ?>" data-review="<?php echo $review['id_review']; ?>" href="#" title="<?php echo translate('seller_ep_reviews_moderate_btn_tag_title', null, true); ?>">
                                            <i class="ep-icon ep-icon_sheild-ok"></i><span class="txt"><?php echo translate('general_button_moderate_text'); ?></span>
                                        </a>
                                    <?php } ?>
                                    <a class="dropdown-item confirm-dialog" data-callback="delete_review" data-message="<?php echo translate('seller_ep_reviews_remove_btn_confirm_message', null, true); ?>" data-review="<?php echo $review['id_review']; ?>" href="#" title="<?php echo translate('general_button_delete_text', null, true); ?>">
                                        <i class="ep-icon ep-icon_trash-stroke"></i><span class="txt"><?php echo translate('general_button_delete_text'); ?></span>
                                    </a>
                                <?php } ?>
                                <?php if ($_can_reply_review) { ?>
                                    <?php $_reply_text_i18n = translate('general_button_add_reply_text', null, true); ?>
                                    <a
                                        class="dropdown-item btn-reply fancybox.ajax fancyboxValidateModal"
                                        data-title="<?php echo $_reply_text_i18n; ?>"
                                        title="<?php echo $_reply_text_i18n; ?>"
                                        href="<?php echo __SITE_URL . 'reviews/popup_forms/leave_reply/' . $review['id_review']; ?>"
                                        <?php echo addQaUniqueIdentifier('global__reviews__dropdown-menu_add-reply-btn'); ?>
                                    >
                                        <i class="ep-icon ep-icon_pencil"></i><span class="txt"><?php echo $_reply_text_i18n; ?></span>
                                    </a>
                                <?php } ?>
                                <?php if (!is_my($review['id_user'])) { ?>
                                    <a
                                        class="dropdown-item fancyboxValidateModal fancybox.ajax"
                                        href="<?php echo __SITE_URL . 'complains/popup_forms/add_complain/review/' . $review['id_review'] . '/' . $review['id_user'] . '/' . $review['id_seller']; ?>"
                                        data-title="<?php echo translate('seller_ep_reviews_report_btn_tag_title', null, true); ?>"
                                        title="<?php echo translate('seller_ep_reviews_report_btn_tag_title', null, true); ?>"
                                        <?php echo addQaUniqueIdentifier('global__reviews__dropdown-menu_report-btn'); ?>
                                    >
                                        <i class="ep-icon ep-icon_warning-circle-stroke"></i><span class="txt"><?php echo translate('general_button_report_text'); ?></span>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <ul class="product-comments product-comments--reply">
                    <?php
                    if (!empty($review['reply'])) {
                        views('new/users_reviews/reply_item_view', ['review' => $review]);
                    }
                    ?>
                </ul>
            </div>
        </div>

    </li>
<?php } ?>

<?php if(have_right('moderate_content')){?>
    <script>
        var moderate_review = function(obj){
			var $this = $(obj);
			var review = $this.data('review');

			$.ajax({
				type: 'POST',
				url: '<?php echo __SITE_URL . 'reviews/ajax_reviews_administration_operation/moderate';?>',
				data: { checked_reviews : review},
				dataType: 'json',
				success: function(data){
					systemMessages( data.message, data.mess_type );

					if(data.mess_type == 'success'){
						$this.remove();
					}
				}
			});
		}

        var delete_review = function(obj){
        var $this = $(obj);
        var review = $this.data('review');

        $.ajax({
            type: 'POST',
            url: '<?php echo __SITE_URL . 'reviews/ajax_review_operation/delete';?>',
            data: { checked_reviews : review },
            dataType: 'json',
            success: function(data){
                systemMessages( data.message, data.mess_type );

                if(data.mess_type == 'success'){
                    globalThis.location.reload();
                }
            }
        });
    }
    </script>
<?php } ?>
