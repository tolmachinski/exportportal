<div class="register-users-banner container-1420">
    <div class="register-users-banner__wrap">
        <div class="register-users-banner__text-wrap">
            <h2 class="register-users-banner__ttl"><?php echo translate('register_users_banner_ttl');?></h2>
            <div class="register-users-banner__txt"><?php echo translate('register_users_banner_txt');?></div>
        </div>
        <div class="register-users-banner__img-wrap">
            <div class="register-users-banner__img-first">
                <img
                    class="image js-lazy"
                    data-src="<?php echo asset("public/build/images/register/register_banner_first.jpg"); ?>"
                    src="<?php echo getLazyImage(268, 268); ?>"
                    alt="<?php echo translate('register_banner_img_first', null, true);?>">
            </div>
            <div class="register-users-banner__img-second">
                <img
                    class="image js-lazy"
                    data-src="<?php echo asset("public/build/images/register/register_banner_second.jpg"); ?>"
                    src="<?php echo getLazyImage(355, 268); ?>"
                    alt="<?php echo translate('register_banner_img_second', null, true);?>">
            </div>
        </div>
    </div>
</div>

<div class="register-users-blocks container-1420">
    <div class="register-users-blocks__wrap">
        <h2 class="register-users-blocks__ttl"><?php echo translate('register_block_users_ttl');?></h2>
        <div class="register-users-blocks__users-wrap">
            <div class="register-users-blocks__user">
                <div class="register-users-blocks__user-img-wrap">
                    <picture class="register-users-blocks__user-img">
                        <source
                            media="(max-width: 424px)"
                            srcset="<?php echo getLazyImage(290, 290);?>"
                            data-srcset="<?php echo asset("public/build/images/register/users/buyer-mobile-small.jpg"); ?> 1x, <?php echo asset("public/build/images/register/users/buyer-mobile-small@2x.jpg"); ?> 2x"
                        >
                        <source
                            media="(max-width: 767px)"
                            srcset="<?php echo getLazyImage(395, 290);?>"
                            data-srcset="<?php echo asset("public/build/images/register/users/buyer-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/register/users/buyer-mobile@2x.jpg"); ?> 2x"
                        >
                        <source
                            media="(max-width: 1024px)"
                            srcset="<?php echo getLazyImage(361, 290);?>"
                            data-srcset="<?php echo asset("public/build/images/register/users/buyer-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/register/users/buyer-tablet@2x.jpg"); ?> 2x"
                        >
                        <img
                            class="image js-lazy"
                            src="<?php echo getLazyImage(675, 300);?>"
                            data-src="<?php echo asset("public/build/images/register/users/buyer-1920.jpg"); ?>"
                            srcset="<?php echo asset("public/build/images/register/users/buyer-1920.jpg"); ?> 1x, <?php echo asset("public/build/images/register/users/buyer-1920@2x.jpg"); ?> 2x"
                            alt="<?php echo translate('registation_get_started-ttl', null, true); ?>"
                        >
                    </picture>
                </div>
                <div class="register-users-blocks__ttl-wrap">
                    <h3 class="register-users-blocks__ttl-type"><?php echo translate('register_block_buyer_ttl');?></h3>
                </div>
                <div class="register-users-blocks__content">
                    <ul class="register-users-blocks__list-info">
                        <li class="register-users-blocks__list-info-item">
                            <span class="register-users-blocks__icon">&#10003;</span> <span><?php echo translate('register_block_buyer_txt_first');?></span>
                        </li>
                        <li class="register-users-blocks__list-info-item">
                            <span class="register-users-blocks__icon">&#10003;</span> <span><?php echo translate('register_block_buyer_txt_second');?></span>
                        </li>
                        <li class="register-users-blocks__list-info-item">
                            <span class="register-users-blocks__icon">&#10003;</span> <span><?php echo translate('register_block_buyer_txt_third');?></span>
                        </li>
                        <li class="register-users-blocks__list-info-item">
                            <span class="register-users-blocks__icon">&#10003;</span> <span><?php echo translate('register_block_buyer_txt_forth');?></span>
                        </li>
                    </ul>
                    <div class="register-users-blocks__buttons-wrap">
                        <a href="<?php echo get_static_url('register/buyer');?>" class="register-users-blocks__btn btn btn-primary" <?php echo addQaUniqueIdentifier("page__registration__buyer-register")?>><?php echo translate('register_block_register_btn');?></a>
                        <a href="<?php echo get_static_url('buying');?>" class="register-users-blocks__btn btn btn-light" <?php echo addQaUniqueIdentifier("page__registration__learn-more")?>><?php echo translate('register_buying_btn');?></a>
                    </div>
                </div>
            </div>
            <div class="register-users-blocks__user">
                <div class="register-users-blocks__user-img-wrap">
                    <picture class="register-users-blocks__user-img">
                        <source
                            media="(max-width: 424px)"
                            srcset="<?php echo getLazyImage(290, 290);?>"
                            data-srcset="<?php echo asset("public/build/images/register/users/seller-mobile-small.jpg"); ?> 1x, <?php echo asset("public/build/images/register/users/seller-mobile-small@2x.jpg"); ?> 2x"
                        >
                        <source
                            media="(max-width: 767px)"
                            srcset="<?php echo getLazyImage(395, 290);?>"
                            data-srcset="<?php echo asset("public/build/images/register/users/seller-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/register/users/seller-mobile@2x.jpg"); ?> 2x"
                        >
                        <source
                            media="(max-width: 1024px)"
                            srcset="<?php echo getLazyImage(361, 290);?>"
                            data-srcset="<?php echo asset("public/build/images/register/users/seller-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/register/users/seller-tablet@2x.jpg"); ?> 2x"
                        >
                        <img
                            class="image js-lazy"
                            src="<?php echo getLazyImage(675, 300);?>"
                            data-src="<?php echo asset("public/build/images/register/users/seller-1920.jpg"); ?>"
                            srcset="<?php echo asset("public/build/images/register/users/seller-1920.jpg"); ?> 1x, <?php echo asset("public/build/images/register/users/seller-1920@2x.jpg"); ?> 2x"
                            alt="<?php echo translate('registation_get_started-ttl', null, true); ?>"
                        >
                    </picture>
                </div>
                <div class="register-users-blocks__ttl-wrap">
                    <h3 class="register-users-blocks__ttl-type"><?php echo translate('register_block_seller_ttl');?></h3>
                </div>
                <div class="register-users-blocks__content">
                    <ul class="register-users-blocks__list-info">
                        <li class="register-users-blocks__list-info-item">
                            <span class="register-users-blocks__icon">&#10003;</span> <span><?php echo translate('register_block_seller_txt_first');?></span>
                        </li>
                        <li class="register-users-blocks__list-info-item">
                            <span class="register-users-blocks__icon">&#10003;</span> <span><?php echo translate('register_block_seller_txt_second');?></span>
                        </li>
                        <li class="register-users-blocks__list-info-item">
                            <span class="register-users-blocks__icon">&#10003;</span> <span><?php echo translate('register_block_seller_txt_third');?></span>
                        </li>
                        <li class="register-users-blocks__list-info-item">
                            <span class="register-users-blocks__icon">&#10003;</span> <span><?php echo translate('register_block_seller_txt_forth');?></span>
                        </li>
                    </ul>
                    <div class="register-users-blocks__buttons-wrap">
                        <a href="<?php echo get_static_url('register/seller');?>" class="register-users-blocks__btn btn btn-primary" <?php echo addQaUniqueIdentifier("page__registration__seller-register")?>><?php echo translate('register_block_register_btn');?></a>
                        <a href="<?php echo get_static_url('selling');?>" class="register-users-blocks__btn btn btn-light" <?php echo addQaUniqueIdentifier("page__registration__learn-more")?>><?php echo translate('register_selling_btn');?></a>
                    </div>
                </div>
            </div>
            <div class="register-users-blocks__user">
                <div class="register-users-blocks__user-img-wrap">
                    <picture class="register-users-blocks__user-img">
                        <source
                            media="(max-width: 424px)"
                            srcset="<?php echo getLazyImage(290, 290);?>"
                            data-srcset="<?php echo asset("public/build/images/register/users/manufacturer-mobile-small.jpg"); ?> 1x, <?php echo asset("public/build/images/register/users/manufacturer-mobile-small@2x.jpg"); ?> 2x"
                        >
                        <source
                            media="(max-width: 767px)"
                            srcset="<?php echo getLazyImage(395, 290);?>"
                            data-srcset="<?php echo asset("public/build/images/register/users/manufacturer-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/register/users/manufacturer-mobile@2x.jpg"); ?> 2x"
                        >
                        <source
                            media="(max-width: 1024px)"
                            srcset="<?php echo getLazyImage(361, 290);?>"
                            data-srcset="<?php echo asset("public/build/images/register/users/manufacturer-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/register/users/manufacturer-tablet@2x.jpg"); ?> 2x"
                        >
                        <img
                            class="image js-lazy"
                            src="<?php echo getLazyImage(675, 300);?>"
                            data-src="<?php echo asset("public/build/images/register/users/manufacturer-1920.jpg"); ?>"
                            srcset="<?php echo asset("public/build/images/register/users/manufacturer-1920.jpg"); ?> 1x, <?php echo asset("public/build/images/register/users/manufacturer-1920@2x.jpg"); ?> 2x"
                            alt="<?php echo translate('registation_get_started-ttl', null, true); ?>"
                        >
                    </picture>
                </div>
                <div class="register-users-blocks__ttl-wrap">
                    <h3 class="register-users-blocks__ttl-type"><?php echo translate('register_block_manufacturer_ttl');?></h3>
                </div>
                <div class="register-users-blocks__content">
                    <ul class="register-users-blocks__list-info">
                        <li class="register-users-blocks__list-info-item">
                            <span class="register-users-blocks__icon">&#10003;</span> <span><?php echo translate('register_block_manufacturer_txt_first');?></span>
                        </li>
                        <li class="register-users-blocks__list-info-item">
                            <span class="register-users-blocks__icon">&#10003;</span> <span><?php echo translate('register_block_manufacturer_txt_second');?></span>
                        </li>
                        <li class="register-users-blocks__list-info-item">
                            <span class="register-users-blocks__icon">&#10003;</span> <span><?php echo translate('register_block_manufacturer_txt_third');?></span>
                        </li>
                        <li class="register-users-blocks__list-info-item">
                            <span class="register-users-blocks__icon">&#10003;</span> <span><?php echo translate('register_block_manufacturer_txt_forth');?></span>
                        </li>
                    </ul>
                    <div class="register-users-blocks__buttons-wrap">
                        <a href="<?php echo get_static_url('register/manufacturer');?>" class="register-users-blocks__btn btn btn-primary" <?php echo addQaUniqueIdentifier("page__registration__manufacturer-register")?>><?php echo translate('register_block_register_btn');?></a>
                        <a href="<?php echo get_static_url('manufacturer_description');?>" class="register-users-blocks__btn btn btn-light" <?php echo addQaUniqueIdentifier("page__registration__learn-more")?>><?php echo translate('register_manufacturer_description_btn');?></a>
                    </div>
                </div>
            </div>
            <div class="register-users-blocks__user">
                <div class="register-users-blocks__user-img-wrap">
                    <picture class="register-users-blocks__user-img">
                        <source
                            media="(max-width: 424px)"
                            srcset="<?php echo getLazyImage(290, 290);?>"
                            data-srcset="<?php echo asset("public/build/images/register/users/freight-forwarder-mobile-small.jpg"); ?> 1x, <?php echo asset("public/build/images/register/users/freight-forwarder-mobile-small@2x.jpg"); ?> 2x"
                        >
                        <source
                            media="(max-width: 767px)"
                            srcset="<?php echo getLazyImage(395, 290);?>"
                            data-srcset="<?php echo asset("public/build/images/register/users/freight-forwarder-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/register/users/freight-forwarder-mobile@2x.jpg"); ?> 2x"
                        >
                        <source
                            media="(max-width: 1024px)"
                            srcset="<?php echo getLazyImage(361, 290);?>"
                            data-srcset="<?php echo asset("public/build/images/register/users/freight-forwarder-tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/register/users/freight-forwarder-tablet@2x.jpg"); ?> 2x"
                        >
                        <img
                            class="image js-lazy"
                            src="<?php echo getLazyImage(675, 300);?>"
                            data-src="<?php echo asset("public/build/images/register/users/freight-forwarder-1920.jpg"); ?>"
                            srcset="<?php echo asset("public/build/images/register/users/freight-forwarder-1920.jpg"); ?> 1x, <?php echo asset("public/build/images/register/users/freight-forwarder-1920@2x.jpg"); ?> 2x"
                            alt="<?php echo translate('registation_get_started-ttl', null, true); ?>"
                        >
                    </picture>
                </div>
                <div class="register-users-blocks__ttl-wrap">
                    <h3 class="register-users-blocks__ttl-type"><?php echo translate('register_block_freight-forwarder_ttl');?></h3>
                </div>
                <div class="register-users-blocks__content">
                    <ul class="register-users-blocks__list-info">
                        <li class="register-users-blocks__list-info-item">
                            <span class="register-users-blocks__icon">&#10003;</span> <span><?php echo translate('register_block_freight-forwarder_txt_first');?></span>
                        </li>
                        <li class="register-users-blocks__list-info-item">
                            <span class="register-users-blocks__icon">&#10003;</span> <span><?php echo translate('register_block_freight-forwarder_txt_second');?></span>
                        </li>
                        <li class="register-users-blocks__list-info-item">
                            <span class="register-users-blocks__icon">&#10003;</span> <span><?php echo translate('register_block_freight-forwarder_txt_third');?></span>
                        </li>
                        <li class="register-users-blocks__list-info-item">
                            <span class="register-users-blocks__icon">&#10003;</span> <span><?php echo translate('register_block_freight-forwarder_txt_forth');?></span>
                        </li>
                    </ul>
                    <div class="register-users-blocks__buttons-wrap">
                        <a href="<?php echo __SHIPPER_URL . 'register/ff'; ?>" class="register-users-blocks__btn btn btn-primary" <?php echo addQaUniqueIdentifier("page__registration__ff-register")?>><?php echo translate('register_block_register_btn');?></a>
                        <a href="<?php echo get_static_url('shipper_description');?>" class="register-users-blocks__btn btn btn-light" <?php echo addQaUniqueIdentifier("page__registration__learn-more")?>><?php echo translate('register_shipper_description_btn');?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php views('new/register/bottom_view'); ?>
