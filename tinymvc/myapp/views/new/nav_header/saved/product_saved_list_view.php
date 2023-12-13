<div class="js-epuser-saved-content epuser-subline-nav2__content epuser-popup__overflow">
	<?php if (!empty($items)) { ?>
        <div class="container-fluid-modal">
            <div class="products-mini products-mini--2">
                <?php foreach ($items as $mini_item) { ?>
                    <?php
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
                    <div class="products-mini__wrapper">
                        <div class="products-mini__item flex-card">
                            <div class="products-mini__img flex-card__fixed image-card2">
                                <div
                                    class="products-mini__status"
                                    <?php echo addQaUniqueIdentifier('global__mini-item_badge')?>
                                >
                                    <?php if ($mini_item['discount']) { ?>
                                        <div
                                            class="products-mini__status-item"
                                            <?php echo addQaUniqueIdentifier('global__mini-item_discount')?>
                                        >-<?php echo $mini_item['discount']; ?>%</div>
                                    <?php } ?>
                                </div>

                                <a class="link" href="<?php echo $mini_item['url']; ?>" target="_blank">
                                    <img class="image" src="<?php echo $mini_item['image']; ?>" alt="<?php echo $mini_item["title"]; ?>" />
                                </a>
                            </div>
                            <div class="products-mini__detail flex-card__float">
                                <a class="products-mini__name" href="<?php echo $mini_item['url']; ?>" target="_blank">
                                    <?php echo $mini_item['title']; ?>
                                </a>

                                <div class="products-mini__price">
                                    <div
                                        class="products-mini__price-new"
                                    >
                                        <?php echo implode(' - ', array_filter([$mini_item['card_prices']['min_final_price'] ?: null, $mini_item['card_prices']['max_final_price'] ?: null]));?>
                                    </div>
                                    <?php if (!empty($mini_item['card_prices']['min_price'])) {?>
                                        <div
                                            class="products-mini__price-old"
                                        >
                                            <?php echo implode(' - ', array_filter([$mini_item['card_prices']['min_price'] ?: null, $mini_item['card_prices']['max_price'] ?: null]));?>
                                        </div>
                                    <?php } ?>
                                </div>

                                <div class="products-mini__row">
                                    <div
                                        class="products-mini__country"
                                        <?php echo addQaUniqueIdentifier('global__mini-item_country')?>
                                    >
                                        <img
                                            class="image"
                                            width="24"
                                            height="24"
                                            src="<?php echo getCountryFlag($mini_item['country_name']);?>"
                                            alt="<?php echo $mini_item['country_name'];?>"
                                            title="<?php echo $mini_item['country_name'];?>"
                                        />
                                        <div class="products-mini__country-name">
                                            <?php echo $mini_item['country_name'];?>
                                        </div>
                                    </div>

                                    <div class="dropdown">
                                        <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                                            <i class="ep-icon ep-icon_menu-circles"></i>
                                        </a>

                                        <div class="dropdown-menu">
                                            <button
                                                class="dropdown-item call-function call-action"
                                                data-callback="remove_header_product"
                                                data-js-action="saved:remove-header-product"
                                                data-product="<?php echo $mini_item['id_item']; ?>"
                                                type="button"
                                            >
                                                <i class="ep-icon ep-icon_trash-stroke"></i>
                                                <span>Remove product</span>
                                            </button>
                                            <button
                                                class="dropdown-item call-function call-action"
                                                title="Share"
                                                data-callback="userSharePopup"
                                                data-js-action="user:share-popup"
                                                data-type="item"
                                                data-item="<?php echo $mini_item['id_item'];?>"
                                                type="button"
                                            >
                                                <i class="ep-icon ep-icon_share-stroke3"></i> Share this item
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
	<?php } else { ?>
		<div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke"></i> <span>No saved search info.</span></div>
	<?php } ?>
</div>

<?php if (!empty($items)) { ?>
	<div class="js-epuser-saved-page epuser-subline-additional2">
		<div></div>

		<div class="flex-display">
			<?php
				app()->view->display('new/nav_header/pagination_block_view', array(
					'count_total' => $counter,
					'per_page' => $per_page,
					'cur_page' => $curr_page,
					'type' => 'items'
				));
			?>
		</div>
	</div>
<?php } ?>
