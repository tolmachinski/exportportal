<div class="benefits container-1420">
    <h2 class="benefits__ttl"><?php echo translate('buying_benefits_ttl');?></h2>
    <div class="benefits__subttl"><?php echo translate('buying_benefits_subttl');?></div>

    <div class="benefits__products">
        <picture class="benefits__products-img">
            <source media="(max-width: 475px)" srcset="<?php echo getLazyImage(365, 260); ?>" data-srcset="<?php echo asset("public/build/images/buying/products-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/buying/products-mobile@2x.jpg"); ?> 2x">
            <source media="(max-width: 991px)" srcset="<?php echo getLazyImage(243, 243); ?>" data-srcset="<?php echo asset("public/build/images/buying/products-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/buying/products-tablet@2x.jpg"); ?> 2x">
            <img class="image js-lazy" src="<?php echo getLazyImage(600, 310);?>" data-src="<?php echo asset('public/build/images/buying/products.jpg'); ?>" alt="<?php echo translate('buying_benefits_product_image');?>" width="600" height="310">
        </picture>
        <div class="benefits__products-content-wrap">
            <h3 class="benefits__products-ttl benefits__item-ttl"><?php echo translate('buying_benefits_product_ttl');?></h3>
            <div class="benefits__products-subttl benefits__item-subttl"><?php echo translate('buying_benefits_product_subttl');?></div>
            <a  href="<?php echo logged_in() ? __SITE_URL . 'items/latest' : __SITE_URL . 'register/buyer';?>" class="benefits__products-btn btn btn-primary btn-new18" target="_blank" <?php echo addQaUniqueIdentifier("page__buying_products_btn"); ?>><?php echo translate(logged_in() ? 'buying_benefits_product_btn' : 'buying_benefits_product_upload_btn');?></a>
        </div>
    </div>

    <div class="benefits__secure-items">
        <div class="benefits__secure-item">
            <picture class="benefits__secure-item-img">
                <source media="(max-width: 991px)" srcset="<?php echo getLazyImage(395, 290); ?>" data-srcset="<?php echo asset("public/build/images/buying/advanced-security-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/buying/advanced-security-tablet@2x.jpg"); ?> 2x">
                <img class="image js-lazy" src="<?php echo getLazyImage(453, 300);?>" data-src="<?php echo asset('public/build/images/buying/advanced-security.jpg'); ?>" alt="<?php echo translate('buying_benefits_advanced_security_img');?>" width="453" height="300">
            </picture>
            <div class="benefits__secure-text-wrap">
                <h3 class="benefits__secure-item-ttl benefits__item-ttl"><?php echo translate('buying_benefits_advanced_security_ttl');?></h3>
                <div class="benefits__secure-item-subttl benefits__item-subttl"><?php echo translate('buying_benefits_advanced_security_subttl');?></div>
                <div class="benefits__secure-item-subttl benefits__item-subttl"><?php echo translate('buying_benefits_advanced_security_subttl_second');?></div>
                <a href="<?php echo __SITE_URL; ?>security" class="benefits__secure-item-btn btn btn-outline-primary btn-new18" target="_blank" <?php echo addQaUniqueIdentifier("page__buying_advanced_security_btn"); ?>><?php echo translate('buying_benefits_learn_more');?></a>
            </div>
        </div>
        <div class="benefits__secure-item">
            <picture class="benefits__secure-item-img">
                <source media="(max-width: 991px)" srcset="<?php echo getLazyImage(395, 290); ?>" data-srcset="<?php echo asset("public/build/images/buying/delivery-secure-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/buying/delivery-secure-tablet@2x.jpg"); ?> 2x">
                <img class="image js-lazy" src="<?php echo getLazyImage(453, 300);?>" data-src="<?php echo asset('public/build/images/buying/delivery-secure.jpg'); ?>" alt="<?php echo translate('buying_benefits_delivery_secure_img');?>" width="453" height="300">
            </picture>
            <div class="benefits__secure-text-wrap">
                <h3 class="benefits__secure-item-ttl benefits__item-ttl"><?php echo translate('buying_benefits_delivery_secure_ttl');?></h3>
                <div class="benefits__secure-item-subttl benefits__item-subttl"><?php echo translate('buying_benefits_delivery_secure_subttl');?></div>
                <div class="benefits__secure-item-subttl benefits__item-subttl"><?php echo translate('buying_benefits_delivery_secure_subttl_second');?></div>
                <a href="<?php echo __SITE_URL; ?>faq/all" class="benefits__secure-item-btn btn btn-outline-primary btn-new18" target="_blank" <?php echo addQaUniqueIdentifier("page__buying_delivery_btn"); ?>><?php echo translate('buying_benefits_learn_more');?></a>
            </div>
        </div>
        <div class="benefits__secure-item">
            <picture class="benefits__secure-item-img">
                <source media="(max-width: 400px)" srcset="<?php echo getLazyImage(290, 290); ?>" data-srcset="<?php echo asset("public/build/images/buying/warehouse-security-mobile-small.jpg"); ?> 1x, <?php echo asset("public/build/images/buying/warehouse-security-mobile-small@2x.jpg"); ?> 2x">
                <source media="(max-width: 575px)" srcset="<?php echo getLazyImage(395, 290); ?>" data-srcset="<?php echo asset("public/build/images/buying/warehouse-security-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/buying/warehouse-security-mobile@2x.jpg"); ?> 2x">
                <source media="(max-width: 991px)" srcset="<?php echo getLazyImage(300, 439); ?>" data-srcset="<?php echo asset("public/build/images/buying/warehouse-security-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/buying/warehouse-security-tablet@2x.jpg"); ?> 2x">
                <img class="image js-lazy" src="<?php echo getLazyImage(453, 300);?>" data-src="<?php echo asset('public/build/images/buying/warehouse-security.jpg'); ?>" alt="<?php echo translate('buying_benefits_warehouse_security_img');?>" width="453" height="300">
            </picture>
            <div class="benefits__secure-text-wrap">
                <h3 class="benefits__secure-item-ttl benefits__item-ttl"><?php echo translate('buying_benefits_warehouse_secure_ttl');?></h3>
                <div class="benefits__secure-item-subttl benefits__item-subttl"><?php echo translate('buying_benefits_warehouse_secure_subttl');?></div>
                <div class="benefits__secure-item-subttl benefits__item-subttl"><?php echo translate('buying_benefits_warehouse_secure_subttl_second');?></div>
                <a href="<?php echo __SITE_URL; ?>shipper_description" class="benefits__secure-item-btn btn btn-outline-primary btn-new18" target="_blank" <?php echo addQaUniqueIdentifier("page__buying_warehouse_btn"); ?>><?php echo translate('buying_benefits_learn_more');?></a>
            </div>
        </div>
    </div>
    <div class="benefits__shipping">
        <div class="benefits__shipping-content-wrap">
            <h3 class="benefits__shipping-ttl benefits__item-ttl"><?php echo translate('buying_benefits_shipping_methods_ttl');?></h3>
            <div class="benefits__shipping-subttl benefits__item-subttl"><?php echo translate('buying_benefits_shipping_methods_subttl');?></div>
            <a href="<?php echo __SITE_URL . 'landing/shipping_methods'; ?>" class="benefits__shipping-item-btn btn btn-outline-primary btn-new18" target="_blank" <?php echo addQaUniqueIdentifier("page__buying_shipping_btn"); ?>><?php echo translate('buying_benefits_learn_more');?></a>
        </div>
        <picture class="benefits__shipping-img">
            <source media="(max-width: 400px)" srcset="<?php echo getLazyImage(260, 260); ?>" data-srcset="<?php echo asset("public/build/images/buying/shipping-methods-mobile-small.jpg"); ?> 1x, <?php echo asset("public/build/images/buying/shipping-methods-mobile-small@2x.jpg"); ?> 2x">
            <source media="(max-width: 575px)" srcset="<?php echo getLazyImage(365, 260); ?>" data-srcset="<?php echo asset("public/build/images/buying/shipping-methods-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/buying/shipping-methods-mobile@2x.jpg"); ?> 2x">
            <source media="(max-width: 991px)" srcset="<?php echo getLazyImage(315, 315); ?>" data-srcset="<?php echo asset("public/build/images/buying/shipping-methods-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/buying/shipping-methods-tablet@2x.jpg"); ?> 2x">
            <img class="image js-lazy" src="<?php echo getLazyImage(600, 294);?>" data-src="<?php echo asset('public/build/images/buying/shipping-methods.jpg'); ?>" alt="<?php echo translate('buying_benefits_shipping_methods_img');?>" width="600" height="294">
        </picture>
    </div>
</div>


<div class="userinfo-scheme container-1420">
    <h2 class="userinfo-scheme__ttl"><?php echo translate('buying_scheme_ttl');?></h2>
    <div class="userinfo-scheme__row">
        <div class="userinfo-scheme__col">
            <div class="userinfo-scheme__wrap">
                <div class="userinfo-scheme__icon-wrap">
                    <img class="image js-lazy" src="<?php echo getLazyImage(60, 60);?>" data-src="<?php echo asset('public/build/images/buying/users-shopping-stroke.svg'); ?>" alt="<?php echo translate('buying_users_shopping_img');?>">
                </div>
                <span class="userinfo-scheme__name">
                    <?php echo translate('buying_scheme_text_market_customer');?>
                </span>
            </div>
        </div>
        <div class="userinfo-scheme__col">
            <div class="userinfo-scheme__wrap">
                <div class="userinfo-scheme__icon-wrap">
                    <img class="image js-lazy userinfo-scheme__icon-sheild" src="<?php echo getLazyImage(60, 60);?>" data-src="<?php echo asset('public/build/images/buying/sheild-ok2.svg'); ?>" alt="<?php echo translate('buying_sheild_ok_img');?>">
                </div>
                <span class="userinfo-scheme__name">
                    <?php echo translate('buying_scheme_text_make_exporting');?>
                </span>
            </div>
        </div>
        <div class="userinfo-scheme__col">
            <div class="userinfo-scheme__wrap">
                <div class="userinfo-scheme__icon-wrap">
                    <img class="image js-lazy userinfo-scheme__basket" src="<?php echo getLazyImage(60, 60);?>" data-src="<?php echo asset('public/build/images/buying/basket-plus.svg'); ?>" alt="<?php echo translate('buying_basket_img');?>">
                </div>
                <span class="userinfo-scheme__name">
                    <?php echo translate('buying_scheme_text_products');?>
                </span>
            </div>
        </div>
        <div class="userinfo-scheme__col">
            <div class="userinfo-scheme__wrap">
                <div class="userinfo-scheme__icon-wrap">
                    <img class="image js-lazy userinfo-scheme__icon-paper" src="<?php echo getLazyImage(60, 60);?>" data-src="<?php echo asset('public/build/images/buying/paper-stroke.svg'); ?>" alt="<?php echo translate('buying_paper_img');?>">
                </div>
                <span class="userinfo-scheme__name">
                    <?php echo translate('buying_scheme_text_bulk');?>
                </span>
            </div>
        </div>
        <div class="userinfo-scheme__col">
            <div class="userinfo-scheme__wrap">
                <div class="userinfo-scheme__icon-wrap">
                    <img class="image js-lazy" src="<?php echo getLazyImage(60, 60);?>" data-src="<?php echo asset('public/build/images/buying/support-stroke.svg'); ?>" alt="<?php echo translate('buying_support_img');?>">
                </div>
                <span class="userinfo-scheme__name">
                    <?php echo translate('buying_scheme_text_purchase');?>
                </span>
            </div>
        </div>
        <div class="userinfo-scheme__col">
            <div class="userinfo-scheme__wrap">
                <div class="userinfo-scheme__icon-wrap">
                    <img class="image js-lazy userinfo-scheme__card-lock-icon" src="<?php echo getLazyImage(60, 60);?>" data-src="<?php echo asset('public/build/images/buying/card-lock2.svg'); ?>" alt="<?php echo translate('buying_card_lock_img');?>">
                </div>
                <span class="userinfo-scheme__name">
                    <?php echo translate('buying_scheme_text_pay_securely');?>
                </span>
            </div>
        </div>
        <div class="userinfo-scheme__col bdb-none">
            <div class="userinfo-scheme__wrap">
                <div class="userinfo-scheme__icon-wrap">
                    <img class="image js-lazy userinfo-scheme__box-code2" src="<?php echo getLazyImage(60, 60);?>" data-src="<?php echo asset('public/build/images/buying/box-code2.svg'); ?>" alt="<?php echo translate('buying_box2_img');?>">
                </div>
                <span class="userinfo-scheme__name">
                    <?php echo translate('buying_scheme_text_receive_papers');?>
                </span>
            </div>
        </div>
        <div class="userinfo-scheme__col bdb-none">
            <div class="userinfo-scheme__wrap">
                <div class="userinfo-scheme__icon-wrap">
                    <img class="image js-lazy userinfo-scheme__icon-box" src="<?php echo getLazyImage(60, 60);?>" data-src="<?php echo asset('public/build/images/buying/box-icon.svg'); ?>" alt="<?php echo translate('buying_box_img');?>">
                </div>
                <span class="userinfo-scheme__name">
                    <?php echo translate('buying_scheme_text_get_products');?>
                </span>
            </div>
        </div>
    </div>
</div>


<div class="build-export container-1420">
    <div class="build-export__content-wrap">
        <h3 class="build-export__ttl"><?php echo translate('buying_build_export_ttl');?></h3>
        <div class="build-export__subttl"><?php echo translate('buying_build_export_subttl');?></div>
        <a class="btn btn-primary btn-new18" href="<?php echo logged_in() ? __SITE_URL . 'about' : __SITE_URL . 'register/buyer'; ?>" target="_blank" <?php echo addQaUniqueIdentifier("page__buying_build_export_btn"); ?>><?php echo translate(logged_in() ? 'buying_build_export_btn' : 'buying_build_export_about_btn');?></a>
    </div>

    <picture class="build-export__background">
        <source media="(max-width: 400px)" srcset="<?php echo getLazyImage(290, 600); ?>" data-srcset="<?php echo asset("public/build/images/buying/build-business-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/buying/build-business-mobile-small@2x.jpg"); ?> 2x">
        <source media="(max-width: 575px)" srcset="<?php echo getLazyImage(395, 508); ?>" data-srcset="<?php echo asset("public/build/images/buying/build-business-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/buying/build-business-mobile@2x.jpg"); ?> 2x">
        <source media="(max-width: 991px)" srcset="<?php echo getLazyImage(738, 455); ?>" data-srcset="<?php echo asset("public/build/images/buying/build-business-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/buying/build-business-tablet@2x.jpg"); ?> 2x">
        <img class="image js-lazy" src="<?php echo getLazyImage(1420, 434);?>" data-src="<?php echo asset('public/build/images/buying/build-business.jpg'); ?>" alt="<?php echo translate('buying_build_business_img_alt');?>" width="1920" height="434">
    </picture>
</div>


<?php
encoreEntryLinkTags('buying');
encoreEntryScriptTags('buying');
?>
