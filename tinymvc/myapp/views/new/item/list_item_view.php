<?php

$listenerClass = logged_in() ? 'call-action' : 'js-require-logged-systmess';
$favoriteItems = $savedItems ?? $saved_items;
$removeLazyImg = $removeLazyFirstImg ?? false;

?>
<?php if (!empty($items)) { ?>
    <?php foreach ($items as $key => $item) { ?>
        <div class="products__item<?php echo ($item['highlight'] ? ' products__item--highlight' : '')
                                        . ($item['is_out_of_stock'] && !$item['samples'] ? ' products__item--stock-out' : '')
                                        . (isset($has_hover) && !$has_hover ? ' products__item--no-hover' : '')
                                        . ($isSmCard ? ' products__item--sm' : '');
                                    ?>" <?php echo addQaUniqueIdentifier('global__item') ?> data-it="<?php echo $item['id']; ?>">
            <div class="products__inner">
                <div class="products__status" <?php echo addQaUniqueIdentifier('global__item-badge') ?>>
                    <?php if ($item['discount'] && !$item['is_out_of_stock']) { ?>
                        <div class="products__status-item bg-blue2" <?php echo addQaUniqueIdentifier('global__item-discount') ?>>-<?php echo $item['discount']; ?>%</div>
                    <?php } ?>

                    <?php if ($item['is_out_of_stock']) { ?>
                        <div <?php echo addQaUniqueIdentifier('global__item-badge_' . ($item['samples'] ? 'samples' : 'stock')) ?> class="products__status-item products__status-item--stock-out"><?php echo $item['samples'] ? 'Sample only' : 'Out of stock'; ?></div>
                    <?php } ?>
                </div>

                <div class="js-products-actions products__actions">
                    <button class="products__actions-item products__actions-mobile call-action" data-js-action="item-card:toggle-actions">
                        <?php echo getEpIconSvg('menu-circle', [18, 20]); ?>
                    </button>

                    <div class="js-products-actions-desktop products__actions-desktop">
                        <?php if (in_array($item['id'], !empty($favoriteItems) ? $favoriteItems : [])) { ?>
                            <button class="js-products-favorites-btn products__actions-item <?php echo $listenerClass; ?>" title="<?php echo translate('item_card_remove_from_favorites_tag_title', null, true); ?>" data-js-action="favorites:remove-product" data-item="<?php echo $item['id']; ?>" type="button" <?php echo addQaUniqueIdentifier('global__item-btn-favorite') ?>>
                                <?php echo getEpIconSvg('favorite'); ?>
                            </button>
                        <?php } else { ?>
                            <button class="js-products-favorites-btn products__actions-item <?php echo $listenerClass; ?>" title="<?php echo translate('item_card_add_to_favorites_tag_title', null, true); ?>" data-js-action="favorites:save-product" data-item="<?php echo $item['id']; ?>" type="button" <?php echo addQaUniqueIdentifier('global__item-btn-favorite') ?>>
                                <?php echo getEpIconSvg('favorite-empty'); ?>
                            </button>
                        <?php } ?>

                        <button class="products__actions-item call-function call-action" title="Share" data-callback="userSharePopup" data-js-action="user:share-popup" data-type="item" data-item="<?php echo $item['id']; ?>" type="button" <?php echo addQaUniqueIdentifier('global__item-btn-share') ?>>
                            <?php echo getEpIconSvg(); ?>
                        </button>
                    </div>
                </div>

                <a class="js-products-link products__link" href="<?php echo __SITE_URL . 'item/' . strForURL($item['title']) . '-' . $item['id'] ?>">
                    <span class="products__img image-card3">
                        <span class="link">
                            <?php
                            $itemImgLink = getDisplayImageLink(
                                ['{ID}' => $item['id'], '{FILE_NAME}' => $item['photo_name']],
                                'items.main',
                                [
                                    'thumb_size'     => 3,
                                    'no_image_group' => 'dynamic',
                                    'image_size'     => ['w' => 375, 'h' => 281],
                                ]
                            ); ?>
                            <img <?php if (!$removeLazyImg) { ?> class="image js-lazy" data-src="<?php echo $itemImgLink; ?>" src="<?php echo getLazyImage(375, 281); ?>" <?php } else { ?> class="image" src="<?php echo $itemImgLink; ?>" <?php } ?> data-item="<?php echo $item['id'] ?>" alt="<?php echo cleanOutput($item['title']); ?>" <?php echo addQaUniqueIdentifier('global__item-image') ?> />
                        </span>
                    </span>

                    <span class="products__ttl" <?php echo addQaUniqueIdentifier('global__item-title') ?>><?php echo $item['title']; ?></span>
                </a>

                <div class="products__content">
                    <div class="products__price" <?php echo addQaUniqueIdentifier('global__item-price') ?>>
                        <div class="products__price-new" <?php echo addQaUniqueIdentifier('global__item-new-price') ?>>
                            <?php echo implode(' - ', array_filter([$item['card_prices']['min_final_price'] ?: null, $item['card_prices']['max_final_price'] ?: null])); ?>
                        </div>

                        <?php if (!empty($item['card_prices']['min_price'])) { ?>
                            <div class="products__price-old" <?php echo addQaUniqueIdentifier('global__item-old-price') ?>>
                                <?php echo implode(' - ', array_filter([$item['card_prices']['min_price'] ?: null, $item['card_prices']['max_price'] ?: null])); ?>
                            </div>
                        <?php } ?>
                    </div>

                    <?php if ((!isset($has_hover) || $has_hover) || isset($has_mobile_seller)) { ?>
                        <div class="products__seller">
                            <div class="products__label"><?php echo translate('item_card_label_sold_by'); ?>:</div>
                            <a class="products__seller-name" href="<?php echo getCompanyURL($item['seller']); ?>" <?php echo addQaUniqueIdentifier('global__item-seller-name') ?>><?php echo $item['seller']['name_company'] ?></a>

                            <div class="products__seller-group <?php echo userGroupNameColor($item['seller']['gr_name']); ?>" <?php echo addQaUniqueIdentifier('global__item-account-group') ?>>
                                <?php echo $item['seller']['gr_name'] ?>
                            </div>
                        </div>
                        <div class="products__label">From:</div>
                    <?php } ?>

                        <div
                            class="products__country"
                            <?php echo addQaUniqueIdentifier('global__item-country')?>
                        >
                            <img
                                <?php if (!$removeLazyImg) { ?>
                                    class="image js-lazy"
                                    src="<?php echo getLazyImage(24, 24); ?>"
                                    data-src="<?php echo getCountryFlag($item['country_name']); ?>"
                                <?php } else { ?>
                                    class="image"
                                    src="<?php echo getCountryFlag($item['country_name']); ?>"
                                <?php } ?>
                                width="24"
                                height="24"
                                alt="<?php echo $item['country_name'];?>"
                                title="<?php echo $item['country_name'];?>"
                            />
                            <div class="products__country-name" <?php echo addQaUniqueIdentifier('global__item-country-name'); ?>>
                                <?php echo $item['country_name'];?>
                            </div>
                        </div>

                    <?php if ((!isset($has_hover) || $has_hover) || isset($has_mobile_seller)) { ?>
                        <div class="products__country-original">
                            <div class="products__label">Country of Origin:</div>
                            <div class="products__country-original-name" <?php echo addQaUniqueIdentifier('global__item-country-origin'); ?>>
                                <?php echo $item['origin_country_name']; ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>
<?php } ?>
