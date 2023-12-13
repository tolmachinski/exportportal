<div class="benefits container-1420">
    <h2 class="benefits__ttl"><?php echo translate('selling_benefits_ttl');?></h2>
    <p class="benefits__subttl"><?php echo translate('selling_benefits_subttl');?></p>

    <div class="benefits__products">
        <picture class="benefits__products-img">
            <source media="(max-width: 475px)" srcset="<?php echo getLazyImage(365, 260); ?>" data-srcset="<?php echo asset("public/build/images/selling/products-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/selling/products-mobile@2x.jpg"); ?> 2x">
            <source media="(max-width: 991px)" srcset="<?php echo getLazyImage(267, 267); ?>" data-srcset="<?php echo asset("public/build/images/selling/products-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/selling/products-tablet@2x.jpg"); ?> 2x">
            <img class="image js-lazy" src="<?php echo getLazyImage(600, 310);?>" data-src="<?php echo asset('public/build/images/selling/products.jpg'); ?>" alt="<?php echo translate('selling_benefits_product_image');?>" width="600" height="310">
        </picture>
        <div class="benefits__products-content-wrap">
            <h3 class="benefits__products-ttl benefits__item-ttl"><?php echo translate('selling_benefits_product_ttl');?></h3>
            <p class="benefits__products-subttl benefits__item-subttl"><?php echo translate('selling_benefits_product_subttl');?></p>
            <a  href="<?php echo logged_in() ? __SITE_URL . 'items/my' : __SITE_URL . 'register/seller';?>" class="benefits__products-btn btn btn-primary btn-new18" target="_blank" <?php echo addQaUniqueIdentifier("page__selling_products_btn"); ?>><?php echo translate(logged_in() ? 'selling_benefits_product_upload_btn' : 'selling_benefits_product_btn');?></a>
        </div>
    </div>

    <div class="benefits__secure-items">
        <div class="benefits__secure-item">
            <picture class="benefits__secure-item-img">
                <source media="(max-width: 991px)" srcset="<?php echo getLazyImage(395, 290); ?>" data-srcset="<?php echo asset("public/build/images/selling/advanced-security-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/selling/advanced-security-tablet@2x.jpg"); ?> 2x">
                <img class="image js-lazy" src="<?php echo getLazyImage(453, 300);?>" data-src="<?php echo asset('public/build/images/selling/advanced-security.jpg'); ?>" alt="<?php echo translate('selling_benefits_advanced_security_img');?>" width="453" height="300">
            </picture>
            <div class="benefits__secure-text-wrap">
                <h3 class="benefits__secure-item-ttl benefits__item-ttl"><?php echo translate('selling_benefits_advanced_security_ttl');?></h3>
                <p class="benefits__secure-item-subttl benefits__item-subttl"><?php echo translate('selling_benefits_advanced_security_subttl');?></p>
                <p class="benefits__secure-item-subttl benefits__item-subttl"><?php echo translate('selling_benefits_advanced_security_subttl_second');?></p>
                <a href="<?php echo __SITE_URL; ?>security" class="benefits__secure-item-btn btn btn-outline-primary btn-new18" target="_blank" <?php echo addQaUniqueIdentifier("page__selling_advanced_security_btn"); ?>><?php echo translate('selling_benefits_learn_more');?></a>
            </div>
        </div>
        <div class="benefits__secure-item">
            <picture class="benefits__secure-item-img">
                <source media="(max-width: 991px)" srcset="<?php echo getLazyImage(395, 290); ?>" data-srcset="<?php echo asset("public/build/images/selling/warehouse-security-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/selling/warehouse-security-tablet@2x.jpg"); ?> 2x">
                <img class="image js-lazy" src="<?php echo getLazyImage(453, 300);?>" data-src="<?php echo asset('public/build/images/selling/warehouse-security.jpg'); ?>" alt="<?php echo translate('selling_benefits_warehouse_security_img');?>" width="453" height="300">
            </picture>
            <div class="benefits__secure-text-wrap">
                <h3 class="benefits__secure-item-ttl benefits__item-ttl"><?php echo translate('selling_benefits_warehouse_secure_ttl');?></h3>
                <p class="benefits__secure-item-subttl benefits__item-subttl"><?php echo translate('selling_benefits_warehouse_secure_subttl');?></p>
                <p class="benefits__secure-item-subttl benefits__item-subttl"><?php echo translate('selling_benefits_warehouse_secure_subttl_second');?></p>
                <a href="<?php echo __SITE_URL; ?>faq" class="benefits__secure-item-btn btn btn-outline-primary btn-new18" target="_blank" <?php echo addQaUniqueIdentifier("page__selling_warehouse_btn"); ?>><?php echo translate('selling_benefits_learn_more');?></a>
            </div>
        </div>
        <div class="benefits__secure-item">
            <picture class="benefits__secure-item-img">
                <source media="(max-width: 575px)" srcset="<?php echo getLazyImage(395, 290); ?>" data-srcset="<?php echo asset("public/build/images/selling/delivery-secure-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/selling/delivery-secure-mobile@2x.jpg"); ?> 2x">
                <source media="(max-width: 991px)" srcset="<?php echo getLazyImage(300, 383); ?>" data-srcset="<?php echo asset("public/build/images/selling/delivery-secure-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/selling/delivery-secure-tablet@2x.jpg"); ?> 2x">
                <img class="image js-lazy" src="<?php echo getLazyImage(453, 300);?>" data-src="<?php echo asset('public/build/images/selling/delivery-secure.jpg'); ?>" alt="<?php echo translate('selling_benefits_delivery_secure_img');?>" width="453" height="300">
            </picture>
            <div class="benefits__secure-text-wrap">
                <h3 class="benefits__secure-item-ttl benefits__item-ttl"><?php echo translate('selling_benefits_delivery_secure_ttl');?></h3>
                <p class="benefits__secure-item-subttl benefits__item-subttl"><?php echo translate('selling_benefits_delivery_secure_subttl');?></p>
                <p class="benefits__secure-item-subttl benefits__item-subttl"><?php echo translate('selling_benefits_delivery_secure_subttl_second');?></p>
                <a href="<?php echo __SITE_URL; ?>shipper_description" class="benefits__secure-item-btn btn btn-outline-primary btn-new18" target="_blank" <?php echo addQaUniqueIdentifier("page__selling_delivery_btn"); ?>><?php echo translate('selling_benefits_learn_more');?></a>
            </div>
        </div>
    </div>
    <div class="benefits__shipping">
        <div class="benefits__shipping-content-wrap">
            <h3 class="benefits__shipping-ttl benefits__item-ttl"><?php echo translate('selling_benefits_shipping_methods_ttl');?></h3>
            <p class="benefits__shipping-subttl benefits__item-subttl"><?php echo translate('selling_benefits_shipping_methods_subttl');?></p>
            <a href="<?php echo __SITE_URL . 'shipper_description'; ?>" class="benefits__shipping-item-btn btn btn-outline-primary btn-new18" target="_blank" <?php echo addQaUniqueIdentifier("page__selling_shipping_btn"); ?>><?php echo translate('selling_benefits_learn_more');?></a>
        </div>
        <picture class="benefits__shipping-img">
            <source media="(max-width: 575px)" srcset="<?php echo getLazyImage(365, 260); ?>" data-srcset="<?php echo asset("public/build/images/selling/shipping-methods-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/selling/shipping-methods-mobile@2x.jpg"); ?> 2x">
            <source media="(max-width: 991px)" srcset="<?php echo getLazyImage(365, 260); ?>" data-srcset="<?php echo asset("public/build/images/selling/shipping-methods-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/selling/shipping-methods-tablet@2x.jpg"); ?> 2x">
            <img class="image js-lazy" src="<?php echo getLazyImage(600, 334);?>" data-src="<?php echo asset('public/build/images/selling/shipping-methods.jpg'); ?>" alt="<?php echo translate('selling_benefits_shipping_methods_img');?>" width="600" height="334">
        </picture>
    </div>
</div>
