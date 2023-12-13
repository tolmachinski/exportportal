<?php
    $mainPhotoLink = '';
    if (!empty($photo_main)) {
        if (!isset($photo_main['photo_type'])) {
            $mainPhotoLink = getDisplayImageLink(['{ID}' => $item['id'], '{FILE_NAME}' => $photo_main['photo_name']], 'items.main');
        } elseif (isset($photo_main['photo_type']) && 'temp' == $photo_main['photo_type']) {
            $mainPhotoLink = __SITE_URL . $photo_main['photo_name'];
        } else {
            $mainPhotoLink = __SITE_URL . 'public/img/no_image/group/main-image.svg';
        }
    }

    $itemParams = [
        'discount'      => $item['discount'],
        'price'         => $item['price'],
        'finalPrice'    => $item['final_price'],
        'mainPhotoLink' => $mainPhotoLink,
    ];
    $defaultVariant = [];
    if (!empty($itemVariants['variants'])) {
        $defaultVariant = $itemVariants['variants'][array_key_first($itemVariants['variants'])];

        $itemParams = [
            'discount'      => $defaultVariant['discount'],
            'price'         => $defaultVariant['price'],
            'finalPrice'    => $defaultVariant['final_price'],
            'mainPhotoLink' => $mainPhotoLink,
        ];

        if ('main' !== $defaultVariant['image'] && $defaultVariant['image'] !== $photo_main['photo_name']) {
            if ($preview_item && !isset($photos[$defaultVariant['image']]['id'])) {
                //if is this an image from temp
                $itemParams['mainPhotoLink'] = __SITE_URL . $photos[$defaultVariant['image']]['photo_name'];
            } else {
                $itemParams['mainPhotoLink'] = getDisplayImageLink(['{ID}' => $item['id'], '{FILE_NAME}' => $defaultVariant['image']], 'items.photos');
            }
        }
    }

    echo dispatchDynamicFragment(
        'item-detail:data',
        [
            [
                'photos'                => $photos,
                'itemId'                => $item['id'],
                'systmessFillOptions'   => translate('systmess_info_fill_all_specific_item_options'),
                'maxSaleQuantity'       => $item['max_sale_q'],
                'minSaleQuantity'       => $item['min_sale_q'],
                'itemVariants'          => json_encode($itemVariants) ?? '',
                'defaultVariant'        => json_encode($defaultVariant) ?? '',
            ],
        ],
        true
    );
?>

<?php if(!isset($webpackData)) { ?>
    <script src="<?php echo fileModificationTime('public/plug/js/notify/notify-out-of-stock.js'); ?>"></script>
    <script src="<?php echo fileModificationTime('public/plug/lodash-custom-4-17-5/lodash.custom.min.js'); ?>"></script>
<?php } ?>

<?php if ($item['is_restricted'] && !cookies()->exist_cookie('ep_age_verification')) { ?>
    <?php if(!isset($webpackData)) { ?>
        <script src="<?php echo fileModificationTime('public/plug/js/categories/open-age-verification.js'); ?>"></script>
        <script>
            $(function() {
                openAgeVerificationModal(null, true);
            });
        </script>
    <?php } else { ?>
        <?php echo dispatchDynamicFragment("popup:open-age-verification", [ 'detail' => [ 'redirectClose' => true ] ], true); ?>
    <?php } ?>
<?php } ?>

<?php
if (!isset($photo_main['photo_type'])) {
	$item_img_seo_link = getDisplayImageLink(array('{ID}' => $item['id'], '{FILE_NAME}' => $photo_main['photo_name']), 'items.main');
} else if (isset($photo_main['photo_type']) && $photo_main['photo_type'] == 'temp') {
	$item_img_seo_link = __SITE_URL . $photo_main['photo_name'];
} else {
	$item_img_seo_link = __SITE_URL . 'public/img/no_image/group/main-image.svg';
}

    $item_clean_title = cleanOutput(strip_tags($item['title']));
?>

<script type="application/ld+json">
{
    "@context": "http://schema.org",
    "@type": "Product",
    "name": "<?php echo $item_clean_title; ?>",
    "image": "<?php echo $item_img_seo_link;?>",
    "description": "<?php echo strip_tags(truncWords($item['description'])); ?>",
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "<?php echo $item['rating'] ?>",
        "reviewCount": "<?php echo $item['rev_numb'] ?>"
    },
    "offers": {
        "@type": "Offer",
        "availability": "http://schema.org/InStock",
        "price": "<?php echo $itemParams['finalPrice']; ?>",
        "priceCurrency": "<?php echo $item['curr_code']; ?>",
        "seller": {
            "@type": "Organization",
            "name": "<?php echo $company_user['name_company'] ?>"
        }
    }
<?php if(!empty($reviews)){?>
    ,
<?php $count_reviews = count($reviews);?>
    "review": [
        <?php foreach($reviews as $key => $review){?>
        {
            "@type": "Review",
            "author": "<?php echo $review['fname'].' '.$review['lname'];?>",
            "datePublished": "<?php echo formatDate($review['rev_date']);?>",
            "description": "<?php echo $review['rev_text'];?>",
            "name": "<?php echo $review['rev_title'];?>",
            "reviewRating": {
                "@type": "Rating",
                "ratingValue": "<?php echo $review['rev_raiting'];?>"
            }
        }<?php if(($count_reviews-1) !== $key){?>,<?php }?>
        <?php }?>
    ]
<?php }?>
}
</script>

<input id="js-item-detail-order-price" type="hidden" name="price" value="<?php echo $itemParams['finalPrice'];?>">
<input id="js-item-detail-weight" type="hidden" name="weight" value="<?php echo $item['weight']?>">

<div class="product__header-top">
    <div class="flex--1 mnw-0 product__title">
        <?php if (isset($page_name)) {?>
            <span class="product__sub-ttl">
                <?php
                    switch ($page_name) {
                        case 'reviews'            :    echo 'Reviews:'; break;
                        case 'reviews_ep'        :     echo 'EP Reviews:'; break;
                        case 'reviews_external'    :    echo 'External Reviews:'; break;
                        case 'questions'        :     echo 'Questions:'; break;
                        case 'comments'            :     echo 'Comments:'; break;
                    }
                ?>
            </span>
        <?php }?>

        <div class="title-public pt-0">
            <h1 class="title-public__txt" itemprop="name" <?php echo addQaUniqueIdentifier("item__title")?>>
                <?php echo $item['title']; ?>
            </h1>
        </div>
    </div>

    <?php if ($featured && $featured_status == 'active') {?>
        <span class="product__status-featured">featured</span>
    <?php }?>
    <?php if (is_my($item['id_seller'])) {?>
        <div class="product__edit-btn-wrap">
            <button class="product__edit-btn btn btn-light btn-block flex-display flex-jc--c fancyboxAddItem fancybox.ajax js-fancyboxEditItem" href="<?=  __SITE_URL . 'items/add/' . strForURL($item['title']) . '-'. $item['id']; ?>" <?php echo addQaUniqueIdentifier("page__item__edit-btn")?> data-title="Edit item" title="Edit item">
                <i class="ep-icon ep-icon_pencil"></i>
                <span>Edit item</span>
            </button>
        </div>
    <?php }?>
</div>

<div class="product__flex-card">
    <div class="product__flex-card-fixed">
        <div class="product-gallery">
            <div class="display-n">
                <img
                    itemprop="image"
                    src="<?php echo $mainPhotoLink;?>"
                    alt="<?php echo $item_clean_title;?>"
                />
            </div>

            <div class="js-product-gallery-main product-gallery__main image-card3">
                <?php if ($itemParams['discount']) { ?>
                    <div class="js-product-gallery-label-discount product-gallery__label" <?php echo addQaUniqueIdentifier("item__discount")?>>
                        <span>- <?php echo $itemParams['discount']; ?>%</span>
                    </div>
                <?php } ?>

                <?php if ((bool) (int) ($item['samples'] ?? 0) && $item['is_out_of_stock']) { ?>
                    <div class="product-gallery__label product-gallery__label--stock-out tt-uppercase">
                        <span>Samples only</span>
                    </div>
                <?php } ?>

                <?php if ( !$item['samples'] && $item['is_out_of_stock']) { ?>
                    <div class="product-gallery__label product-gallery__label--stock-out tt-uppercase">
                        <span>Out of stock</span>
                    </div>
                <?php } ?>


                <?php if (empty($itemParams['mainPhotoLink'])) { ?>
                    <span class="link">
                        <img
                            class="image"
                            src="<?php echo __SITE_URL . 'public/img/no_image/group/main-image.svg';?>"
                            alt="Main image"
                            <?php echo addQaUniqueIdentifier('item__main-image'); ?>
                        />
                    </span>
                <?php } else { ?>
                    <a
                        class="link fancyboxGallery"
                        data-title="<?php echo $item_clean_title; ?>"
                        href="<?php echo $itemParams['mainPhotoLink']; ?>"
                    >
                        <img
                            class="image"
                            src="<?php echo $itemParams['mainPhotoLink'];?>"
                            width="423"
                            height="338"
                            alt="<?php echo $item_clean_title;?>"
                            <?php echo addQaUniqueIdentifier('item__main-image');?>
                        >
                    </a>
                <?php } ?>
            </div>

            <span class="js-product-main-image-link display-n" data-href="<?php echo $mainPhotoLink?>" data-title="<?php echo $item_clean_title ?>"></span>

            <?php
                if(!empty($photos)){
                    $thumbs = [];

                    $photoIndex = 0;
                    foreach ($photos as $key => $photo) {
                        $photoIndex += 1;

                        if(!isset($photo['photo_type'])){
                            $thumb_photo_link = getDisplayImageLink(array('{ID}' => $item['id'], '{FILE_NAME}' => $photo['photo_name']), 'items.photos');
                            $thumb_photo = getDisplayImageLink(array('{ID}' => $item['id'], '{FILE_NAME}' => $photo['photo_name']), 'items.photos', array( 'thumb_size' => 1 ));
                        }else if(isset($photo['photo_type']) && $photo['photo_type'] == 'temp'){
                            $thumb_photo_link = __SITE_URL.$photo['photo_name'];
                            $thumb_photo = __SITE_URL.$photo['photo_name'];
                        }else{
                            $mainPhotoLink = __SITE_URL.'public/img/no_image/no-image-512x512.png';
                            $thumb_photo = __SITE_URL.'public/img/no_image/no-image-166x138.png';
                        }
                        $imageTemplate = $photoIndex > 3 ?
                            '<img class="image js-lazy" src="' .getLazyImage(100, 80) .'" data-src="' . $thumb_photo . '" height="80" alt="'.$item_clean_title.'" ' . addQaUniqueIdentifier('item__gallery-image') . '>' :
                            '<img class="image" src="' . $thumb_photo . '" height="80" alt="'.$item_clean_title.'" ' . addQaUniqueIdentifier('item__gallery-image') . '>';

					    $thumbs[] = '<div class="product-gallery__additional-item" data-index="' . $key . '">
                                        <a class="link fancyboxGallery" data-title="' . $item_clean_title . '" rel="galleryItem" href="' . $thumb_photo_link . '">'.
                                            $imageTemplate
                                        .'</a>
                                    </div>';
				    } ?>

                <div class="product-gallery__additional">
                    <div id="js-product-gallery-additional" class="product-gallery__additional-list">
                        <?php echo implode('', $thumbs); ?>
                    </div>
                </div>
            <?php }?>
        </div>
    </div>

    <?php
        if ($webpackData) {
            echo encoreLinks();
        }
    ?>

    <div class="product__flex-card-float">
        <div class="detail-info product__counters">
            <div class="product__viewed" <?php echo addQaUniqueIdentifier("item__counter_views")?>><?php echo $item['views']; ?> views</div>
        </div>

        <form id="js-order-now-form">
            <div class="detail-info pb-20">
                <div
                    class="w-100pr"
                    itemprop="offers" itemscope itemtype="http://schema.org/Offer"
                >
                    <div class="product__param">
                        <span class="product__param-name">
                            Price:
                            <a
                                class="info-dialog"
                                data-message="This is the intended price for sale without discount. In case a Discount Price will be added, the Price will be visually cut for customers."
                                data-title="What is: Price?"
                                title="What is: Price?"
                                href="#"
                            >
                                <i class="ep-icon ep-icon_info fs-16"></i>
                            </a>
                        </span>
                        <div class="product__param-detail">
                            <div
                                class="js-product-price-old product__price-old<?php echo $itemParams['discount']? "" : " display-n_i"; ?>"
                                <?php echo addQaUniqueIdentifier('item__price'); ?>
                            >
                                <?php echo get_price($itemParams['price']); ?>
                            </div>

                            <div
                                class="js-product-price-new-block js-product-price-new product__price-new<?php echo $itemParams['discount']? " display-n_i" : ""; ?>"
                                <?php echo addQaUniqueIdentifier('item__price'); ?>
                            >
                                <?php echo get_price($itemParams['finalPrice']); ?>
                            </div>

                            <div class="product__param-val">
                                /&nbsp;<span <?php echo addQaUniqueIdentifier('item__val-for-price')?>><?php echo $item['unit_name'] ?></span>
                            </div>
                        </div>

                        <?php if (!$itemParams['discount']) { ?>
                            <?php if(cookies()->getCookieParam('currency_key') !== 'USD'){?>
                                <div class="product__real-price">*Real price for payment is $ <span class="js-item-real-price" <?php echo addQaUniqueIdentifier('item__price')?>><?php echo get_price($itemParams['finalPrice'], false);?></span></div>
                            <?php } ?>
                        <?php } ?>
                    </div>

                    <div class="js-product-param-discount product__param <?php if (!$itemParams['discount']) { ?>display-n_i<?php } ?>">
                        <span class="product__param-name">
                            Discount price:
                            <a
                                class="info-dialog"
                                data-message="Price with a discount, if set it will represent the intended price for sale."
                                data-title="What is: Discount Price?"
                                title="What is: Discount Price?"
                                href="#"
                            >
                                <i class="ep-icon ep-icon_info fs-16"></i>
                            </a>
                        </span>
                        <div class="product__param-detail">
                            <span
                                class="js-product-price-new product__price-new"
                                <?php echo addQaUniqueIdentifier('item__price')?>
                            ><?php echo get_price($itemParams['finalPrice']); ?></span>

                            <div class="product__param-val">
                                /&nbsp;<span <?php echo addQaUniqueIdentifier('item__val-for-price')?>><?php echo $item['unit_name'] ?></span>
                            </div>
                        </div>

                        <?php if(cookies()->getCookieParam('currency_key') !== 'USD'){?>
                            <div class="product__real-price">*Real price for payment is $ <span class="js-item-real-price" <?php echo addQaUniqueIdentifier('item__price')?>><?php echo get_price($itemParams['finalPrice'], false);?></span></div>
                        <?php } ?>
                    </div>

                    <div class="display-n">
                        <span itemprop="price" content="<?php echo $itemParams['finalPrice']; ?>"><?php echo $itemParams['finalPrice']; ?></span>
                        <span itemprop="priceCurrency" content="<?php echo $item['curr_code']; ?>"><?php echo $item['curr_code']; ?></span>
                    </div>

                    <link itemprop="availability" href="http://schema.org/InStock" />

                    <span class="display-n">
                        <span itemprop="seller" itemscope itemtype="http://schema.org/Organization">
                            <span itemprop="name"><?php echo $company_user['name_company'] ?></span>
                        </span>
                    </span>

                    <?php if (is_buyer()) { ?>
                        <?php if (!$inDroplist) { ?>
                            <button
                                class="js-fancybox-validate-modal js-add-to-droplist fancybox.ajax product__droplist
                                <?= ((int) $item['draft'] || (int) $item['is_out_of_stock'] || (int) $item['blocked'] || !$item['moderation_is_approved']) ? "product__droplist--disable" : "" ?>
                                <?= empty($itemVariants['variants']) && !$item['is_out_of_stock'] ? "product__droplist--quantity" : "" ?>"
                                data-fancybox-href="<?php echo __SITE_URL . "items/ajax_add_to_droplist/{$item['id']}";?>"
                                data-mw="470"
                                data-class-modificator="droplist"
                                data-item-id="<?php echo $item['id'];?>"
                                data-title="<?php echo translate('items_droplist_popup_header') ?>"
                                type="button"
                                <?php echo addQaUniqueIdentifier('page__item-detail__add_to_droplist')?>>
                                    <?php echo widgetGetSvgIcon('bell-stroke-v2', 18, 18) ?>
                                    <span class="product__droplist-text"><?php echo translate('items_add_to_droplist_btn') ?></span>
                            </button>
                        <?php } else { ?>
                            <button
                                class="product__droplist js-confirm-dialog <?= empty($itemVariants['variants']) && !$item['is_out_of_stock'] ? "product__droplist--quantity" : "" ?>"
                                data-title="<?php echo translate('items_droplist_remove_ttl') ?>"
                                data-message="<?php echo  translate('items_droplist_remove_subttl') ?>"
                                data-js-action="remove:droplist-item"
                                data-item-id="<?php echo $item['id'];?>"
                                type="button"
                                <?php echo addQaUniqueIdentifier('page__item-detail__remove_from_droplist')?>>
                                    <?php echo widgetGetSvgIcon('bell-stroke-v2', 18, 18) ?>
                                    <span class="product__droplist-text"><?php echo translate('items_remove_from_droplist_btn') ?></span>
                            </button>
                        <?php } ?>
                    <?php } ?>
                </div>

                <input type="hidden" name="item" value="<?php echo $item['id'];?>">

                <?php if(!empty($itemVariants['variants'])){ ?>
                    <div class="dropdown fs-0 <?php echo !$item['is_out_of_stock'] ? '' : 'd-none' ?>">
                        <a class="dropdown-toggle fs-18 lh-20 txt-blue2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-title="Select params" href="#">
                            Choose options <i class="ep-icon ep-icon_arrow-down lh-20 fs-9 ml-5"></i>
                        </a>
                        <div class="dropdown-menu">
                            <div class="js-product-variant-wr product__variant-wr p-15">
                            <?php foreach($itemVariants['properties'] as $propertiesKey => $propertiesItem) {?>
                                <div class="js-product-variant product__variant" data-group="<?php echo $propertiesItem['id'];?>">
                                    <span class="product__variant-name"><?php echo cleanOutput($propertiesItem['name'])?>:</span>

                                    <ul class="product__variant-param clearfix">
                                    <?php foreach($propertiesItem['property_options'] as $propertyOptionsKey => $propertyOptionsItem){?>
                                        <?php
                                            $usageOption = $itemVariants['optionUsages'][$propertyOptionsItem['id']];
                                            if(isset($usageOption) && !empty($usageOption)){
                                                $usageOptionImplode = implode(" ", $usageOption);
                                        ?>
                                            <li
                                                class="js-product-variant-param-item product__variant-param-item variant-enable <?php echo $usageOptionImplode;?>"
                                                data-option="<?php echo $propertyOptionsItem['id'];?>"
                                                data-variants="<?php echo $usageOptionImplode;?>"
                                            >
                                                <?php echo cleanOutput($propertyOptionsItem['name']);?>
                                            </li>
                                        <?php }?>
                                    <?php }?>
                                    </ul>
                                </div>
                            <?php }?>
                            </div>
                        </div>
                    </div>

                    <div class="js-product-variant-selected product__variant-selected-wr <?php echo !$item['is_out_of_stock'] ? '' : 'd-none'; ?>">
                        <?php
                            $defaultVariantUsed = [];
                            foreach($defaultVariant['property_options'] as $variantsKey => $variantsItem) {
                                if (in_array($variantsItem['id_property'], $defaultVariantUsed)) {
                                    continue;
                                }

                                $defaultVariantUsed[] = $variantsItem['id_property'];
                        ?>
                            <span
                                class="js-product-variant-selected-item product__variant-selected"
                                data-property="<?php echo $variantsItem['id_property'];?>"
                                data-variant="<?php echo $variantsItem['id_variant'];?>"
                                data-option="<?php echo $variantsItem['id'];?>"
                            >
                                <span
                                    class="product__variant-selected-name"
                                    <?php echo addQaUniqueIdentifier("item__option-key")?>
                                >
                                    <?php echo cleanOutput($variantsItem['propertyName'])?>:
                                </span>

                                <span
                                    class="js-product-variant-selected-param product__variant-selected-param"
                                    <?php echo addQaUniqueIdentifier("item__option-val")?>
                                >
                                    <?php echo cleanOutput($variantsItem['name']);?>
                                </span>
                            </span>
                        <?php } ?>
                    </div>
                    <div id="js-order-variation"></div>
                <?php } ?>

                <?php if(!$item['is_out_of_stock']) { ?>
                    <div class="product__param2">
                        <div class="product__param2 pt-20">
                            <span class="product__param-name pb-10">Quantity:</span>

                            <div class="product__param3">
                                <div class="flex-display">
                                    <div class="product__param-spinner js-product-params-spinner">
                                        <div class="spinner-custom">
                                            <input
                                                id="js-quantity-order"
                                                class="validate[required,min[<?php echo $item['min_sale_q']; ?>],max[<?php echo $item['max_sale_q']; ?>],custom[positive_integer]]"
                                                name="quantity"
                                                type="number"
                                                step="1"
                                                min="<?php echo $item['min_sale_q']; ?>"
                                                max="<?php echo $item['max_sale_q']; ?>"
                                                value="<?php echo $item['min_sale_q']; ?>"
                                                <?php echo addQaUniqueIdentifier('item__quantity'); ?>
                                            >
                                        </div>
                                    </div>

									<div class="product__param-val text-nowrap lh-22 pull-left" <?php echo addQaUniqueIdentifier('item__min-max-counter'); ?>>
										Max <?php echo $item['max_sale_q']; ?> <span <?php echo addQaUniqueIdentifier('item__val-for-price'); ?>> <?php echo $item['unit_name']; ?></span><br>
										Min <?php echo $item['min_sale_q']; ?> <span <?php echo addQaUniqueIdentifier('item__val-for-price'); ?>> <?php echo $item['unit_name']; ?></span>
									</div>
								</div>
							</div>
						</div>

                        <div class="product__param-total">
                            <div class="flex-display flex-ai--c">
                                <span class="product__param-name">Total weight:</span>
                                <span><span class="txt-black pr-5 fs-18 lh-20" id="js-total-weight-b" <?php echo addQaUniqueIdentifier('item__weight'); ?>><?php echo ceil($item['weight'] * $item['min_sale_q']); ?></span> kg</span>
                            </div>

                            <div class="flex-display flex-ai--c">
                                <span class="product__param-name">Total price:</span>
                                <span class="txt-black fs-18 lh-20" id="js-total-price-b" <?php echo addQaUniqueIdentifier('item__price'); ?>><?php echo get_price($itemParams['finalPrice'] * $item['min_sale_q']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if($item['is_out_of_stock']){?>
                <div class="info-alert-b">
                    <i class="ep-icon ep-icon_info-stroke"></i>
                    <span>This item is out of stock.
                        <?php if(logged_in()) { ?>
                            <a class="confirm-dialog"
                               data-message="<?php echo translate('systmess_confirm_get_email_items_available', null, true);?>"
                               data-callback="notifyOutOfStock"
                               data-resource="<?php echo $item['id'];?>"
                               data-href="<?php echo __SITE_URL . 'items/ajax_item_operation/email_when_available'; ?>">
                               Click here to be notified when it's available.
                            </a>
                        <?php } ?>
                        <?php if((bool) (int) $item['samples'] ?? 0){ ?>
                            Samples are available for this item.
                            <?php if(logged_in()) { ?>
                                Place a sample order below.
                            <?php } ?>
                        <?php } ?>
                    </span>
                </div>
                <?php }?>
                <div class="dropdown mt-20">
                    <a class="dropdown-toggle fs-18 txt-blue2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                        Shipping <i class="ep-icon ep-icon_arrow-down fs-9 ml-5"></i>
                    </a>
                    <div class="dropdown-menu">
                        <div class="aproximate p-10">
                            <label class="txt-blue2">Approximate shipping cost</label>

                            <ul class="clearfix">
                                <li> <span>Departure point:</span> <strong><?php echo (!empty($location['country']) || !empty($location['city']))?$location['country'] . ', ' . $location['city'] : $location;?></strong></li>
                                <?php if(!empty($user_logged_locations)){?>
                                <li class="pt-5"> <span>Destination point:</span>
                                    <strong><?php echo $user_logged_locations['name_country']
                                            . ($user_logged_locations['name_state'] ? ', ' . $user_logged_locations['name_state'] : '')
                                            . ', ' . $user_logged_locations['name_city']; ?></strong>
                                </li>
                                <li>
                                    <a class="fancybox.ajax fancyboxValidateModal btn btn-primary mt-15" href="<?php echo __SITE_URL . 'shippers/popup_forms/create_estimate/item';?>" data-before-callback="get_items" data-items="<?php echo $item['id']?>" data-title="Create shipping estimate"><i class="ep-icon ep-icon_truck-move"></i> Request shipping estimate</a>
                                </li>
                                <?php }?>
                            </ul>

                            <div class="txt-red pt-15 lh-22">
                                <strong>Note:</strong> Please observe that this is just an approximate shipping cost based on origin and destination address, the final cost for the shipping will be emailed to you after confirmed shipper
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-info">

                <?php if ((bool) (int) $item['samples'] && $item['is_out_of_stock']) { ?>
                    <div class="product__terms">
                        <?php echo translate('item_detail_terms_and_condition',
                            array(
                                '[[LINK_START]]' => '<a class="fancybox fancybox.ajax"
                                   data-w="1040"
                                   data-mw="1040"
                                   data-h="400"
                                   data-title="Export Portal Terms and Conditions"
                                   href="' . __SITE_URL . 'terms_and_conditions/tc_order_now">',
                                '[[LINK_END]]' => '</a>'
                            )); ?>
                    </div>
                <?php } ?>

                <div class="product__actions">
                    <?php $show_purchase_options = !empty(array_filter(
                        array_map('intval', array($item['samples'], $item['offers'], $item['estimate'], $item['inquiry'], $item['po']))
                    )); ?>

                    <?php if ((bool) (int) $item['samples'] && $item['is_out_of_stock']) { ?>
                        <div class="product__actions-item">
                            <?php if(logged_in()){?>
                                <button
                                    class="btn btn-primary btn-block text-nowrap fancybox.ajax fancyboxValidateModal"
                                    data-fancybox-href="<?php echo getUrlForGroup("/sample_orders/popup_forms/request_order/{$item['id']}"); ?>"
                                    data-title="Request & Quote for Sample Order"
                                    title="Request & Quote for Sample Order"
                                    type="button"
                                >
                                    Place a sample order
                                </button>
                            <?php } else{?>
                                <button
                                    class="btn btn-primary btn-block text-nowrap call-systmess"
                                    data-message="<?php echo translate('systmess_error_should_be_logged', null, true);?>"
                                    data-type="error"
                                    type="button"
                                >
                                    Place a sample order
                                </button>
                            <?php }?>
                        </div>
                    <?php } ?>

                    <?php if ($item['order_now'] && !$item['is_out_of_stock']) { ?>
                        <div class="product__actions-item">
                            <?php if(logged_in()){?>
                                <button
                                    class="btn btn-primary btn-block text-nowrap js-confirm-dialog"
                                    data-message="<?php echo translate('systmess_info_fill_all_specific_item_options', null, true);?>"
                                    data-js-action="item-detail:add-to-basket"
                                    type="button"
                                >
                                    Add to basket
                                </button>
                            <?php } else{?>
                                <button class="btn btn-primary btn-block text-nowrap js-require-logged-systmess" type="button">
                                    Add to basket
                                </button>
                            <?php }?>
                        </div>
                    <?php } ?>

                    <?php if ($show_purchase_options && !$item['is_out_of_stock']) { ?>
                        <div class="product__actions-item">
                            <div class="btn-group btn-block">
                                <button
                                    class="btn btn-dark btn-block flex--1 dropdown-toggle text-nowrap"
                                    type="button"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false">
                                    Other Purchasing Options
                                </button>
                                <button
                                    class="btn btn-dark mw-55 bdl-1-white info-dialog-ajax"
                                    data-title="Other Purchasing Options"
                                    title="Other Purchasing Options"
                                    data-href="<?php echo __SITE_URL . 'info_block/ajax_operation/view/item_puchasing_options';?>"
                                    type="button"
                                >
                                    <i class="ep-icon ep-icon_info fs-16"></i>
                                </button>

                                <div class="dropdown-menu">
                                    <?php if ((bool) (int) ($item['samples'] ?? 0)) { ?>
                                        <?php if (logged_in()) { ?>
                                            <?php if (have_right('request_sample_order')) { ?>
                                                <button
                                                    class="dropdown-item cur-pointer pl-52 fancybox.ajax fancyboxValidateModal"
                                                    data-fancybox-href="<?php echo getUrlForGroup("/sample_orders/popup_forms/request_order/{$item['id']}"); ?>"
                                                    data-title="Request & Quote for Sample Order"
                                                    title="Request & Quote for Sample Order"
                                                    type="button"
                                                >
                                                    <span class="txt">Sample Order</span>
                                                </button>
                                            <?php } ?>

                                            <?php if (have_right('create_sample_order')) { ?>
                                                <?php if (is_my((int) $item['id_seller'])) { ?>
                                                    <button
                                                        class="dropdown-item cur-pointer pl-52 fancybox.ajax fancyboxValidateModal"
                                                        data-fancybox-href="<?php echo getUrlForGroup("/sample_orders/popup_forms/create_order/{$item['id']}"); ?>"
                                                        data-title="Create Sample Order"
                                                        title="Create Sample Order"
                                                        type="button"
                                                    >
                                                        <span class="txt">Sample Order</span>
                                                    </button>
                                                <?php } else { ?>
                                                    <button
                                                        class="dropdown-item cur-pointer pl-52 call-systmess"
                                                        data-message="<?php echo translate('sample_orders_create_access_denied_only_seller', null, true);?>"
                                                        data-type="warning"
                                                        type="button"
                                                    >
                                                        <span class="txt">Sample Order</span>
                                                    </button>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <button
                                                class="dropdown-item cur-pointer pl-52 js-require-logged-systmess"
                                                type="button"
                                            >
                                                <span class="txt">Sample Order</span>
                                            </button>
                                        <?php } ?>
                                    <?php } ?>

                                    <?php if ($item['offers']) { ?>
                                        <?php if(logged_in()){?>
                                            <button
                                                class="dropdown-item pl-52 fancybox.ajax fancyboxValidateModal"
                                                data-w="100%"
                                                data-before-callback="checkIfOptionsFilled"
                                                data-title="Send offer"
                                                data-fancybox-href="<?php echo __SITE_URL; ?>offers/popup_forms/add_offer_form/<?php echo $item['id']; ?>"
                                                type="button"
                                            >
                                                <span class="txt">Send Offer</span>
                                            </button>
                                        <?php } else { ?>
                                            <button class="dropdown-item pl-52 js-require-logged-systmess" type="button">
                                                <span class="txt">Send Offer</span>
                                            </button>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if($item['estimate']) { ?>
                                        <?php if(logged_in()) { ?>
                                            <button
                                                class="dropdown-item pl-52 fancybox.ajax fancyboxValidateModal"
                                                data-w="100%"
                                                data-before-callback="checkIfOptionsFilled"
                                                data-title="Get estimate"
                                                data-fancybox-href="<?php echo __SITE_URL; ?>estimate/popup_forms/add_estimate_form/<?php echo $item['id']; ?>"
                                                type="button"
                                            >
                                                <span class="txt">Get Estimate</span>
                                            </button>
                                        <?php } else { ?>
                                            <button class="dropdown-item pl-52 js-require-logged-systmess" type="button">
                                                <span class="txt">Get Estimate</span>
                                            </button>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if ($item['inquiry']) { ?>
                                        <?php if(logged_in()) { ?>
                                            <button
                                                class="dropdown-item pl-52 fancybox.ajax fancyboxValidateModal"
                                                data-w="100%"
                                                data-before-callback="checkIfOptionsFilled"
                                                data-title="Send inquiry"
                                                data-fancybox-href="<?php echo __SITE_URL; ?>inquiry/popup_forms/add_inquiry_form/<?php echo $item['id']; ?>"
                                                type="button"
                                            >
                                                <span class="txt">Send Inquiry</span>
                                            </button>
                                        <?php } else{?>
                                            <button class="dropdown-item pl-52 js-require-logged-systmess" type="button">
                                                <span class="txt">Send Inquiry</span>
                                            </button>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if ($item['po']) { ?>
                                        <?php if(logged_in()) { ?>
                                            <button
                                                class="dropdown-item pl-52 fancybox.ajax fancyboxValidateModal"
                                                data-w="100%"
                                                data-before-callback="checkIfOptionsFilled"
                                                data-title="Producing Request"
                                                data-fancybox-href="<?php echo __SITE_URL; ?>po/popup_forms/add_po_form/<?php echo $item['id']; ?>"
                                                type="button"
                                            >
                                                <span class="txt">Producing Request</span>
                                            </button>
                                        <?php } else { ?>
                                            <button class="dropdown-item pl-52 js-require-logged-systmess" type="button">
                                                <span class="txt">Producing Request</span>
                                            </button>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="product__actions-item">
                        <div class="dropdown">
                            <button
                                class="btn btn-light btn-block dropdown-toggle flex-display flex-jc--c"
                                type="button"
                                id="js-dropdown-item-detail-more"
                                data-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false"
                            >
                                <span class="text-nowrap">More actions <span class="d-none-tablet-max-991">on item</span></span>

                                <i class="ep-icon ep-icon_menu-circles pl-10"></i>
                            </button>
                            <div
                                class="dropdown-menu"
                                aria-labelledby="js-dropdown-item-detail-more"
                            >
                                <?php
                                    $listenerClass = logged_in() ? 'call-action' : 'js-require-logged-systmess';
                                    if ($saved) {
                                ?>
                                    <button
                                        class="js-products-favorites-btn dropdown-item <?php echo $listenerClass;?>"
                                        title="<?php echo translate('item_card_remove_from_favorites_tag_title', null, true);?>"
                                        data-js-action="favorites:remove-product"
                                        data-item="<?php echo $item['id'];?>"
                                        type="button"
                                    >
                                        <i class="wr-ep-icon-svg wr-ep-icon-svg--h26"><?php echo getEpIconSvg('favorite', [17, 17]);?></i>
                                        <span>Favorited</span>
                                    </button>
                                <?php } else { ?>
                                    <button
                                        class="js-products-favorites-btn dropdown-item <?php echo $listenerClass;?>"
                                        title="<?php echo translate('item_card_add_to_favorites_tag_title', null, true);?>"
                                        data-js-action="favorites:save-product"
                                        data-item="<?php echo $item['id'];?>"
                                        type="button"
                                    >
                                        <i class="wr-ep-icon-svg wr-ep-icon-svg--h26"><?php echo getEpIconSvg('favorite-empty', [17, 17]);?></i>
                                        <span>Favorite</span>
                                    </button>
                                <?php }?>

                                <?php if (config('env.SHOW_COMPARE_FUNCTIONALITY')) {?>
                                    <button
                                        class="dropdown-item i-compare dn-md_i call-function"
                                        data-callback="compare_item"
                                        data-item="<?php echo $item['id']; ?>"
                                        type="button"
                                    >
                                        <i class="ep-icon ep-icon_balance"></i> <span>Compare item</span>
                                    </button>
                                <?php }?>

								<button class="dropdown-item call-function call-action" title="Share" data-callback="userSharePopup" data-js-action="user:share-popup" data-type="item" data-item="<?php echo $item['id']; ?>" type="button">
									<i class="ep-icon ep-icon_share-stroke3"></i> Share this
								</button>

								<a class="dropdown-item i-compare js-customs-calculator" href="https://customsdutyfree.com/duty-calculator" rel="nofollow" target="_blank">
									<i class="ep-icon ep-icon_customs-calculator"></i> <span>Customs calculator</span>
								</a>

                                <a class="dropdown-item" href="<?php echo $more_recomended_link;?>" target="_blank">
                                    <i class="ep-icon ep-icon_approximate"></i> View similar products
                                </a>

                                <button
                                    class="dropdown-item fancybox.ajax fancyboxValidateModal"
                                    data-fancybox-href="<?php echo __SITE_URL . 'complains/popup_forms/add_complain/item/' . $item['id'] . '/' . $item['id_seller'];?>" data-title="Report this product"
                                    type="button"
                                >
                                    <i class="ep-icon ep-icon_warning-circle-stroke"></i> Report this product
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
