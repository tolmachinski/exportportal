<?php
    $favoriteItems = $savedItems ?? $saved_items;
    $listenerClass = logged_in() ? 'call-action' : 'js-require-logged-systmess';

    $mini_item['card_prices'] = array_map(
        function ($price) use ($mini_item) {
            if (null === $price) {
                return null;
            }

            return !$mini_item['has_variants'] || $price < 10_000 ? get_price($price) : substr(get_price($price), 0, -3);
        },
        $mini_item['card_prices']
    );
?>
<div class="products-mini__item flex-card" <?php echo addQaUniqueIdentifier('global__mini-item')?>>
	<div class="products-mini__img flex-card__fixed image-card2">
        <div
            class="products-mini__status"
            <?php echo addQaUniqueIdentifier('global__mini-item_badge')?>
        >
            <?php if ($mini_item['discount']) {?>
                <div
                    class="products-mini__status-item"
                    <?php echo addQaUniqueIdentifier('global__mini-item_discount')?>
                >-<?php echo $mini_item['discount']; ?>%</div>
            <?php }?>
        </div>

		<a class="link" href="<?php echo $mini_link; ?>">
			<?php
				$item_img_link = getDisplayImageLink(array('{ID}' => $mini_item['id'], '{FILE_NAME}' => $mini_item['photo_name']), 'items.main', array( 'thumb_size' => 1 ));
			?>
			<img
				class="image js-lazy"
                src="<?php echo getLazyImage(135, 135); ?>"
				data-src="<?php echo $item_img_link; ?>"
				alt="<?php echo $mini_item["title"] ?>"
                <?php echo addQaUniqueIdentifier("global__mini-item_image")?>
			/>
		</a>
	</div>
	<div class="products-mini__detail flex-card__float">
		<a class="products-mini__name" href="<?php echo $mini_link; ?>" <?php echo addQaUniqueIdentifier("global__mini-item_title")?>>
			<?php echo $mini_item['title'] ?>
		</a>

		<div class="products-mini__price" <?php echo addQaUniqueIdentifier('global__mini-item_price')?>>
            <div
                class="products-mini__price-new"
                <?php echo addQaUniqueIdentifier('global__mini-item_new-price')?>
            >
                <?php echo implode(' - ', array_filter([$mini_item['card_prices']['min_final_price'] ?: null, $mini_item['card_prices']['max_final_price'] ?: null]));?>
            </div>
			<?php if (!empty($mini_item['card_prices']['min_price'])) {?>
				<div
                    class="products-mini__price-old"
                    <?php echo addQaUniqueIdentifier('global__mini-item_old-price')?>
                >
                    <?php echo implode(' - ', array_filter([$mini_item['card_prices']['min_price'] ?: null, $mini_item['card_prices']['max_price'] ?: null]));?>
                </div>
            <?php }?>
		</div>

		<div class="products-mini__row">
            <div
                class="products-mini__country"
                <?php echo addQaUniqueIdentifier('global__mini-item_country')?>
            >
                <img
                    class="image js-lazy"
                    width="24"
                    height="24"
                    src="<?php echo getLazyImage(24, 24); ?>"
                    data-src="<?php echo getCountryFlag($mini_item['country_name']);?>"
                    alt="<?php echo $mini_item['country_name'];?>"
                    title="<?php echo $mini_item['country_name'];?>"
                />
                <div class="products-mini__country-name">
                    <?php echo $mini_item['country_name'];?>
                </div>
            </div>

            <div class="dropdown dropup">
                <button
                    class="dropdown-toggle"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                    type="button"
                >
                    <i class="ep-icon ep-icon_menu-circles"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-right">
                    <?php if(in_array($mini_item['id'], $favoriteItems)){?>
                        <button
                            class="js-products-favorites-btn dropdown-item <?php echo $listenerClass;?>"
                            title="<?php echo translate('item_card_remove_from_favorites_tag_title', null, true);?>"
                            data-js-action="favorites:remove-product"
                            data-item="<?php echo $mini_item['id'];?>"
                            type="button"
                        >
                            <i class="wr-ep-icon-svg wr-ep-icon-svg--h26"><?php echo getEpIconSvg('favorite', [17, 17]);?></i>
                            <span>Favorited</span>
                        </button>
                    <?php }else{?>
                        <button
                            class="js-products-favorites-btn dropdown-item <?php echo $listenerClass;?>"
                            title="<?php echo translate('item_card_add_to_favorites_tag_title', null, true);?>"
                            data-js-action="favorites:save-product"
                            data-item="<?php echo $mini_item['id'];?>"
                            type="button"
                        >
                            <i class="wr-ep-icon-svg wr-ep-icon-svg--h26"><?php echo getEpIconSvg('favorite-empty', [17, 17]);?></i>
                            <span>Favorite</span>
                        </button>
                    <?php }?>

                    <button
                        class="dropdown-item call-function call-action"
                        title="Share"
                        data-callback="userSharePopup"
                        data-js-action="user:share-popup"
                        data-type="item"
                        data-item="<?php echo $mini_item['id'];?>"
                        type="button"
                    >
                        <i class="ep-icon ep-icon_share-stroke3"></i> Share this item
                    </button>
                </div>
            </div>
		</div>
	</div>
</div>
