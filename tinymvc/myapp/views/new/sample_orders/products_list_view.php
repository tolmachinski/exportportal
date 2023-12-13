<?php if (!empty($products)) { ?>
    <?php
        foreach ($products ?? [] as $product) {
            $indexProduct = cleanOutput(md5($product['id']));

            $image = getDisplayImageLink(
                    ['{ID}' => $product['id'], '{FILE_NAME}' => $product['photo_name']],
                    'items.main',
                    ['thumb_size' => 1, 'no_image_group' => 'other']
                );
    ?>
        <a
            class="input-search-products__item flex-card js-product"
            href="#"
            data-product="<?php echo cleanOutput(json_encode([
                'id'       => $product['id'],
                'url'      => makeItemUrl($product['id'], $product['title'] ?? null),
                'index'    => $indexProduct,
                'title'    => $title = cleanOutput($product['title'] ?? null),
                'image'    => $image,
                'price'    => moneyToDecimal($product['final_price'] ?? $product['price'] ?? 0, false),
                'quantity' => (int) ($product['quantity'] ?? 0),
            ])); ?>"
            data-id="<?php echo $indexProduct;?>"
            data-idproduct="<?php echo $product['id'];?>"
        >
            <div class="input-search-products__img image-card3 flex-card__fixed">
                <span class="link">
                    <img class="image" src="<?php echo $image; ?>" alt="<?php echo $title; ?>">
                </span>
            </div>

            <div class="input-search-products__name flex-card__float">
                <div class="grid-text">
                    <div class="grid-text__item">
                        <?php echo $title; ?>
                    </div>
                </div>
            </div>
        </a>
        <?php echo $delimiter ?? ''; ?>
    <?php } ?>
<?php } else { ?>
    <span class="input-search-products__item flex-card">
        <div class="input-search-products__name flex-card__float">No items found</div>
    </span>
<?php } ?>
