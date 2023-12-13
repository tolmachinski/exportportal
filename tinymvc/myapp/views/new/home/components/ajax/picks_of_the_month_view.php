<?php if(!empty($itemPickOfTheMonth)){?>
    <a href="<?php echo __SITE_URL . 'item/' . strForURL($itemPickOfTheMonth['title']) . '-' . $itemPickOfTheMonth['id']; ?>" class="picks-of-month__item" <?php echo addQaUniqueIdentifier("home__picks-of-month-product"); ?>>
        <div class="picks-of-month__image-wr">
            <?php if ($itemPickOfTheMonth['discount']) { ?>
            <div class="products__status">
                <div class="products__status-item" <?php echo addQaUniqueIdentifier('home__picks-of-month-item-discount'); ?>>- <?php echo $itemPickOfTheMonth['discount']; ?>%</div>
            </div>
            <?php } ?>
            <img class="picks-of-month__image js-lazy" src="<?php echo getLazyImage(130, 130); ?>" data-src="<?php echo getDisplayImageLink(['{ID}' => $itemPickOfTheMonth['id'], '{FILE_NAME}' => $itemPickOfTheMonth['photo_name']], 'items.main', ['thumb_size' => 3]); ?>" alt="<?php echo $itemPickOfTheMonth['title']; ?>" <?php echo addQaUniqueIdentifier('home__picks-of-month-item-image'); ?>>
        </div>
        <div class="picks-of-month__info">
            <h4 class="picks-of-month__info-title" title="<?php echo $itemPickOfTheMonth['title']; ?>" <?php echo addQaUniqueIdentifier('home__picks-of-month-item-title'); ?>><?php echo $itemPickOfTheMonth['title']; ?></h4>
            <div class="picks-of-month__price" <?php echo addQaUniqueIdentifier('home__picks-of-month-item-price'); ?>>
                <span class="picks-of-month__price-new" <?php echo addQaUniqueIdentifier('home__picks-of-month-item-new-price'); ?>><?php echo get_price($itemPickOfTheMonth['final_price']); ?></span>
                <?php if ($itemPickOfTheMonth['discount']) { ?>
                    <span class="picks-of-month__price-old" <?php echo addQaUniqueIdentifier('home__picks-of-month-item-old-price'); ?>><?php echo get_price($itemPickOfTheMonth['price']); ?></span>
                <?php } ?>
            </div>
            <div class="picks-of-month__country"  <?php echo addQaUniqueIdentifier('home__picks-of-month-item-country'); ?>>
                <img class="image js-lazy" width="24" height="24" src="<?php echo getLazyImage(24, 24); ?>" data-src="<?php echo getCountryFlag($itemPickOfTheMonth['country_name']); ?>" alt="<?php echo $itemPickOfTheMonth['country_name']; ?>">
                <span class="picks-of-month__country-name"><?php echo $itemPickOfTheMonth['country_name']; ?></span>
            </div>
        </div>
        <picture class="picks-of-month__background">
            <source srcset="<?php echo getLazyImage(575, 170); ?>" data-srcset="<?php echo asset('public/build/images/index/picks-of-month/top-item-bg-m.jpg');?> 1x, <?php echo asset('public/build/images/index/picks-of-month/top-item-bg-m@2x.jpg');?> 2x" media="(max-width: 575px)">
            <img class="picks-of-month__background-image js-lazy" src="<?php echo getLazyImage(695, 190); ?>" data-src="<?php echo asset('public/build/images/index/picks-of-month/top-item-bg.jpg'); ?>" data-srcset="<?php echo asset('public/build/images/index/picks-of-month/top-item-bg.jpg'); ?> 1x, <?php echo asset('public/build/images/index/picks-of-month/top-item-bg@2x.jpg'); ?> 2x" alt="<?php echo translate('home_picks_of_month_top_product'); ?>">
        </picture>
        <img class="picks-of-month__badge js-lazy" src="<?php echo getLazyImage(146, 147); ?>" data-src="<?php echo asset('public/build/images/index/picks-of-month/top-product-mark.png'); ?>" alt="<?php echo translate('home_picks_of_month_top_product'); ?>">
    </a>
<?php } ?>
<?php if(!empty($companyPickOfTheMonth)){ ?>
    <a href="<?php echo getCompanyURL($companyPickOfTheMonth);?>" class="picks-of-month__item" <?php echo addQaUniqueIdentifier("home__picks-of-month-seller"); ?>>
        <div class="picks-of-month__image-wr">
            <img
                class="picks-of-month__image js-lazy js-fs-image"
                src="<?php echo getLazyImage(130, 130); ?>"
                alt="<?php echo $companyPickOfTheMonth['name_company']; ?>"
                data-src="<?php echo cleanOutput($companyPickOfTheMonth['logoUrl']); ?>"
                data-fsw="130"
                data-fsh="130"
                <?php echo addQaUniqueIdentifier("home__picks-of-month-seller-img"); ?>
            >
        </div>
        <div class="picks-of-month__info">
            <h4 class="picks-of-month__info-title" title="<?php echo $companyPickOfTheMonth['name_company']; ?>" <?php echo addQaUniqueIdentifier("home__picks-of-month-seller-title"); ?>><?php echo $companyPickOfTheMonth['name_company']; ?></h4>
            <div class="picks-of-month__company <?php echo userGroupNameColor($companyPickOfTheMonth['user_group_name']);?>" <?php echo addQaUniqueIdentifier("home__picks-of-month-seller-company-type"); ?>>
                <?php echo $companyPickOfTheMonth['user_group_name']; ?>
            </div>
            <div class="picks-of-month__date" <?php echo addQaUniqueIdentifier("home__picks-of-month-seller-date"); ?>>
                <?php echo translate('text_member_from_date', ['[[DATE]]' => getDateFormat($companyPickOfTheMonth['registration_date'],"Y-m-d H:i:s", "M Y")]); ?>
            </div>
            <div class="picks-of-month__country"  <?php echo addQaUniqueIdentifier('home__picks-of-month-seller-country'); ?>>
                <img class="image js-lazy" width="24" height="24" src="<?php echo getLazyImage(24, 24); ?>" data-src="<?php echo getCountryFlag($companyPickOfTheMonth['country']);?>" alt="<?php echo $companyPickOfTheMonth['country'];?>">
                <span class="picks-of-month__country-name"><?php echo $companyPickOfTheMonth['country']; ?></span>
            </div>
        </div>
        <picture class="picks-of-month__background picks-of-month__background--seller">
            <source srcset="<?php echo getLazyImage(575, 170); ?>" data-srcset="<?php echo asset('public/build/images/index/picks-of-month/top-seller-bg-m.jpg');?> 1x, <?php echo asset('public/build/images/index/picks-of-month/top-seller-bg-m@2x.jpg');?> 2x" media="(max-width: 575px)">
            <img class="js-lazy" src="<?php echo getLazyImage(695, 190); ?>" data-src="<?php echo asset('public/build/images/index/picks-of-month/top-seller-bg.jpg'); ?>" data-srcset="<?php echo asset('public/build/images/index/picks-of-month/top-seller-bg.jpg'); ?> 1x, <?php echo asset('public/build/images/index/picks-of-month/top-seller-bg@2x.jpg'); ?> 2x" alt="<?php echo translate('home_picks_of_month_top_product'); ?>">
        </picture>
        <img class="picks-of-month__badge js-lazy" src="<?php echo getLazyImage(146, 147); ?>" data-src="<?php echo asset('public/build/images/index/picks-of-month/best-seller-mark.png'); ?>" alt="Best Seller">
    </a>
<?php } ?>
