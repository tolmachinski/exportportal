<li class="product-comments__item" id="li-review-reply-<?php echo $review['id_review']?>">
	<div class="flex-card">
		<div class="product-comments__detail flex-card__float">
			<div class="product-comments__ttl">
				<span class="product-comments__reply-ttl">
					<?php echo translate('seller_reviews_replied_by', array('{{START_LINK}}' => '<a class="product-comments__name-link" href="' . getUserLink($review['seller_fullname'], $review['id_seller'], 'seller') . '" target="_blank"' . addQaUniqueIdentifier('global__reviews-name') . '>', '{{END_LINK}}' => '</a>'));?>
				</span>

				<span class="product-comments__date" <?php echo addQaUniqueIdentifier('global__reviews-date'); ?>>
                    <?php echo getDateFormat($review['reply_date'], null, 'M d, Y');?>
                </span>
			</div>
			<div class="product-comments__text" <?php echo addQaUniqueIdentifier('global__reviews-text'); ?>><?php echo $review['reply'];?></div>

			<div class="product-comments__actions">
				<?php views('new/users_reviews/did_help_reviews_view');?>

				<?php if ($review['rev_status'] == 'new' && is_privileged('user',$review['id_seller'], 'reply_reviews')) {?>
					<div class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#" <?php echo addQaUniqueIdentifier('global__reviews__reply_dropdown-btn'); ?>>
							<i class="ep-icon ep-icon_menu-circles"></i>
						</a>
						<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
							<?php $_reply_text_i18n = translate('general_button_edit_reply_text');?>
							<a
                                class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                data-title="<?php echo $_reply_text_i18n;?>"
                                title="<?php echo $_reply_text_i18n;?>"
                                href="<?php echo 'reviews/popup_forms/edit_reply/' . $review['id_review'];?>"
                                <?php echo addQaUniqueIdentifier('global__reviews__reply_dropdown-menu_edit-btn'); ?>
                            >
								<i class="ep-icon ep-icon_pencil"></i> <?php echo $_reply_text_i18n;?>
							</a>
						</div>
					</div>
				<?php }?>
			</div>
		</div>
	</div>
</li>

