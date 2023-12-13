<div class="categories-banner" id="js-categories-banner">
    <div class="categories-banner__description">
        <div class="categories-banner__title">Want to sell more?</div>
        <p class="categories-banner__desc">Add more items for sale today using our new and improved add item process</p>
    </div>

    <div class="categories-banner__images">
        <picture class="categories-banner__picture js-categories-banner__img show">
            <source media="(max-width: 425px), (min-width: 650px) and (max-width: 991px)" srcset="<?php echo getLazyImage(350, 96); ?>" data-srcset="<?php echo __SITE_URL . 'public/img/banners/add_item_process_banner1_small.jpg'; ?>">
            <img class="categories-banner__img js-lazy" src="<?php echo getLazyImage(634, 100); ?>" data-src="<?php echo __SITE_URL . 'public/img/banners/add_item_process_banner1.jpg'; ?>" alt="New and improved add item process one">
        </picture>
        <picture class="categories-banner__picture js-categories-banner__img">
            <source media="(max-width: 425px), (min-width: 650px) and (max-width: 991px)" srcset="<?php echo getLazyImage(350, 96); ?>" data-srcset="<?php echo __SITE_URL . 'public/img/banners/add_item_process_banner2_small.jpg'; ?>">
            <img class="categories-banner__img js-lazy" src="<?php echo getLazyImage(634, 100); ?>" data-src="<?php echo __SITE_URL . 'public/img/banners/add_item_process_banner2.jpg'; ?>" alt="New and improved add item process two">
        </picture>
        <a <?php if (logged_in() && have_right('manage_personal_items')) { ?>
                class="btn btn-primary mt-0 mnw-250 categories-banner__btn"
                href="<?php echo __SITE_URL . 'items/my?popup_add=open'; ?>"
            <?php } elseif (!logged_in() || !have_right('manage_personal_items')) { ?>
                class="btn btn-primary mt-0 mnw-250 categories-banner__btn fancybox.ajax fancyboxValidateModal"
                href="<?php echo __SITE_URL . 'login'; ?>"
                data-mw="400"
                data-title="Login"
            <?php } ?>
        >
            Add Items Now
        </a>
    </div>
</div>
