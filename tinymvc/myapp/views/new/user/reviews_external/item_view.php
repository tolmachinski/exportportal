<?php foreach ($reviews as $review) {?>
	<li class="product-comments__item" itemprop="review" itemscope itemtype="http://schema.org/Review">
		<div class="flex-card">
			<div class="product-comments__detail flex-card__float">
				<div class="product-comments__ttl">
					<div class="">
						<div class="product-comments__name">
							<span itemprop="author" <?php echo addQaUniqueIdentifier('global__reviews-name'); ?>><?php echo $review['full_name'];?></span>
						</div>
					</div>

					<div class="product-comments__rating" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
						<div class="display-n" itemprop="ratingValue"><?php echo $review['rating'];?></div>
						<input class="rating-bootstrap" data-filled="ep-icon ep-icon_star txt-orange fs-12" data-empty="ep-icon ep-icon_star txt-gray-light fs-12" type="hidden" name="val" value="<?php echo $review['rating'];?>" data-readonly>
					</div>
				</div>

				<div class="flex-display flex-jc--sb pt-20">
					<div class="">
						<span class="txt-gray"><?php echo translate('seller_reviews_external_review_block_about_item');?></span>
						<a class="product-comments__name-link" href="<?php echo __SITE_URL . 'item/' . strForURL($review['item_name']) . '-' . $review['id_item'];?>">
                            <span itemprop="itemReviewed" <?php echo addQaUniqueIdentifier('global__reviews__item-name'); ?>><?php echo $review['item_name'];?></span>
                        </a>
					</div>

					<span class="product-comments__date" itemprop="datePublished" <?php echo addQaUniqueIdentifier('global__reviews-date'); ?>>
                        <?php echo getDateFormat($review['create_date'], null, 'M d, Y');?>
                    </span>
				</div>

				<div class="product-comments__text" itemprop="description" <?php echo addQaUniqueIdentifier('global__reviews-text'); ?>><?php echo $review['description'];?></div>
			</div>
		</div>
	</li>
<?php }?>


