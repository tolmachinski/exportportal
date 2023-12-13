<?php
$user_link_start = $user_link_end = "";
if ($is_link_user) {
    $user_link_start = '<a href="' . $base_company_url . '"' . addQaUniqueIdentifier('seller__wall_item_name') . ' >';
    $user_link_end = '</a>';
}

if (!(int)$wall_item['is_removed']) {
    $dropdown_params = [
        'wall_item' => $wall_item,
        'share_product' => $data['id_item']
    ];

    $link_start = '<a class="link" target="_blank" href="' . __SITE_URL . 'item/' . strForURL($data['title'] . ' ' . $data['id_item']) . '" ' . addQaUniqueIdentifier('seller__wall_item_title') . '>';
    $link_end = '</a>';
    $additional_class = '';
} else {
    $dropdown_params = array(
        'wall_item' => $wall_item
    );

    $link_start = '<span>';
    $link_end = '</span>';
    $additional_class = ' spersonal-history--removed';
}
?>

<div class="detail-info">
    <div class="spersonal-history<?php echo $additional_class; ?>" <?php echo addQaUniqueIdentifier('seller__wall_item'); ?>>
        <div class="spersonal-history__top">
            <div class="spersonal-history__top-ttl">
                <?php echo $user_link_start; ?><strong><?php echo $company['name_company']; ?></strong><?php echo $user_link_end; ?>
                <?php echo $wall_item['operation'] === 'add' ? 'added a new' : 'edited an'; ?> item in <a class="link" target="_blank" href="<?php echo $base_company_url; ?>/products" <?php echo addQaUniqueIdentifier('seller__wall_item_group'); ?>>Store</a>
            </div>
            <?php tmvc::instance()->controller->view->display('new/user/seller/wall/item_drop_down_view', $dropdown_params); ?>
        </div>

        <div class="spersonal-history__content">

            <div class="spersonal-history-product flex-card">
                <div class="spersonal-history-product__img flex-card__fixed image-card3">
                    <?php if ($data['discount'] > 0) { ?>
                        <div class="spersonal-history-product__descount" <?php echo addQaUniqueIdentifier('seller__wall_item_discount'); ?>>- <?php echo $data['discount']; ?>%</div>
                    <?php } ?>
                    <span class="link">
                        <?php $item_img_link = getDisplayImageLink(array('{ID}' => $wall_item['id'], '{ID_SELLER}' => $wall_item['id_seller'], '{FILE_NAME}' => $data['main_photo']), 'items.wall', array('thumb_size' => 3)); ?>
                        <img class="image" src="<?php echo $item_img_link; ?>" alt="<?php echo $data['title']; ?>" <?php echo addQaUniqueIdentifier('seller__wall-item-img'); ?>>
                    </span>
                </div>
                <div class="spersonal-history-product__detail flex-card__float">
                    <h2 class="spersonal-history-item__ttl">
                        <?php echo $link_start; ?>
                        <?php echo $data['title']; ?>
                        <?php echo $link_end; ?>
                    </h2>
                    <div class="spersonal-history-product__price">
                        <strong <?php echo addQaUniqueIdentifier('seller__wall_item_new-price'); ?>>$<?php echo number_format($data['final_price'], 2, '.', ' '); ?></strong>
                        <?php if ($data['discount'] > 0) { ?>
                            <span <?php echo addQaUniqueIdentifier('seller__wall_item_old-price'); ?>>$<?php echo number_format($data['price'], 2, '.', ' '); ?></span>
                        <?php } ?>
                    </div>

                    <div class="spersonal-history-product__params">
                        <?php if (empty($data['variants'])) { ?>
                            <div><?php echo $data['category_name']; ?></div>
                        <?php } else { ?>
                            <?php foreach ($data['variants'] as $variant) { ?>
                                <div class="spersonal-history-product__params-one">
                                    <span title="<?php echo $variant['name']; ?>" <?php echo addQaUniqueIdentifier('seller__wall_item_option-value'); ?>><?php echo $variant['name']; ?>:</span>
                                    <?php foreach ($variant['variants'] as $variant_atom) { ?>
                                        <strong title="<?php echo $variant_atom; ?>" <?php echo addQaUniqueIdentifier('seller__wall_item_option-value'); ?>><?php echo $variant_atom; ?></strong>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>

                    <?php if (empty($data['photos'])) { ?>
                        <?php echo $link_start; ?>More<?php echo $link_end; ?>
                    <?php } else { ?>
                        <div class="spersonal-history-product__thumbs">
                            <?php foreach ($data['photos'] as $photo) { ?>
                                <div class="spersonal-history-product__thumb">
                                    <img class="image" <?php echo addQaUniqueIdentifier('seller__wall-item-thumb-img'); ?> src="<?php echo getDisplayImageLink(array('{ID}' => $wall_item['id'], '{ID_SELLER}' => $wall_item['id_seller'], '{FILE_NAME}' => $photo), 'items.wall', array('thumb_size' => 1)); ?>" alt="<?php echo $data['title']; ?>">
                                </div>
                            <?php } ?>

                            <?php
                            $remained_count = $data['photos_count'] - 3;
                            if ($remained_count > 0) { ?>
                                <div class="spersonal-history-product__thumb">
                                    <?php echo $link_start; ?>+<?php echo $remained_count; ?><?php echo $link_end; ?>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>

        </div>
    </div>
</div>
