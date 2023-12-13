<div class="userinfo-header">
	<div class="userinfo-header__detail">
		<div class="userinfo-header__inner">
			<h1 class="userinfo-header__ttl"><?php echo translate('buying_header_buyer'); ?></h1>
            <h2 class="userinfo-header__subtitle"><?php echo translate('landing_block_about_us_header'); ?></h2>
			<div class="userinfo-header__detail-bottom">
				<div class="userinfo-header__txt">
					<?php echo translate('buying_block_text_buyer'); ?>
				</div>
				<a class="btn btn-primary btn-lg btn-public" href="<?php echo __SITE_URL; ?>learn_more"><?php echo translate('learn_more_block_register_buyer_btn'); ?></a>
			</div>
		</div>
	</div>

	<img class="image" src="<?php echo __IMG_URL; ?>public/img/users_description/header-buyer.jpg" alt="<?php echo translate('buying_header_buyer', null, true); ?>">
</div>

<div class="container-center-sm">
	<div class="public-twoblocks">
		<div class="public-twoblocks__item public-twoblocks__img">
			<img class="image" src="<?php echo __IMG_URL; ?>public/img/users_description/img-product.jpg" alt="<?php echo translate('buying_header_product', null, true); ?>">
		</div>
		<div class="public-twoblocks__item">
			<div class="public-twoblocks__txt">
				<h2 class="public-twoblocks__ttl"><?php echo translate('buying_header_product'); ?></h2>
				<p class="public-twoblocks__paragraph">
                    <?php echo translate('buying_block_text_product'); ?>
				</p>
				<a
                    class="btn btn-outline-dark btn-lg btn-public"
                    href="<?php echo __SITE_URL; ?>items/latest"
                ><?php echo translate('buying_block_product_btn'); ?></a>
			</div>
		</div>
	</div>

	<div class="public-twoblocks">
		<div class="public-twoblocks__item">
			<div class="public-twoblocks__txt">
				<h2 class="public-twoblocks__ttl"><?php echo translate('buying_header_security_verification'); ?></h2>
				<p class="public-twoblocks__paragraph">
                    <?php echo translate('buying_block_text_security_verification'); ?>
				</p>
				<a class="btn btn-outline-dark btn-lg btn-public" href="<?php echo __SITE_URL; ?>security"><?php echo translate('buying_btn_security_verification'); ?></a>
			</div>
		</div>
		<div class="public-twoblocks__item public-twoblocks__img">
			<img class="image" src="<?php echo __IMG_URL; ?>public/img/users_description/img-security-and-verefication.jpg" alt="<?php echo translate('buying_header_security_verification', null, true); ?>">
		</div>
	</div>

	<div class="public-twoblocks">
		<div class="public-twoblocks__item public-twoblocks__img">
			<img class="image" src="<?php echo __IMG_URL; ?>public/img/users_description/img-customs-and-warehouse.jpg" alt="<?php echo translate('buying_header_customs_warehouse', null, true); ?>">
		</div>
		<div class="public-twoblocks__item">
			<div class="public-twoblocks__txt">
				<h2 class="public-twoblocks__ttl"><?php echo translate('buying_header_customs_warehouse'); ?></h2>
				<p class="public-twoblocks__paragraph">
					<?php echo translate('buying_block_text_customs_warehouse'); ?>
				</p>
				<a class="btn btn-outline-dark btn-lg btn-public" href="<?php echo __SITE_URL; ?>faq"><?php echo translate('buying_btn_customs_warehouse'); ?></a>
			</div>
		</div>
	</div>

    <div class="public-twoblocks">
		<div class="public-twoblocks__item">
			<div class="public-twoblocks__txt">
				<h2 class="public-twoblocks__ttl">
                    <?php echo translate('selling_buying_shipping_methods_title'); ?>
                </h2>
				<p class="public-twoblocks__paragraph">
                    <?php echo translate('selling_buying_shipping_methods_text'); ?>
				</p>
				<a class="btn btn-outline-dark btn-lg btn-public" href="<?php echo __SITE_URL; ?>landing/shipping_methods">
                    <?php echo translate('selling_buying_shipping_methods_learn_more_btn'); ?>
                </a>
			</div>
		</div>
		<div class="public-twoblocks__item public-twoblocks__img">
            <picture>
                <source
                    media="(max-width: 767px)"
                    srcset="<?php echo getLazyImage(655, 447); ?>"
                    data-srcset="<?php echo asset("public/build/images/users_description/shipping_methods-mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/users_description/shipping_methods-mobile@2x.jpg"); ?> 2x"
                >
                <img
                    class="image js-lazy"
                    src="<?php echo getLazyImage(535, 365); ?>"
                    data-src="<?php echo asset("public/build/images/users_description/shipping_methods.jpg"); ?>"
                    data-srcset="<?php echo asset("public/build/images/users_description/shipping_methods.jpg"); ?> 1x, <?php echo asset("public/build/images/users_description/shipping_methods@2x.jpg"); ?> 2x"
                    alt="<?php echo translate('buying_header_security_verification', null, true); ?>"
                 >
            </picture>
		</div>
	</div>
</div>

<div class="userinfo-delivery">
	<img class="image" src="<?php echo __IMG_URL; ?>public/img/users_description/bg-delivery.jpg" alt="Delivery and Payment">
	<div class="userinfo-delivery__info userinfo-delivery__info--black">
		<div class="container-center-sm">
			<div class="userinfo-delivery__detail">
				<h2 class="userinfo-delivery__ttl"><?php echo translate('buying_header_delivery_payment'); ?></h2>
				<p class="userinfo-delivery__txt">
                    <?php echo translate('buying_block_text_delivery_payment'); ?>
                </p>
				<a class="btn btn-outline-light btn-lg btn-public" href="<?php echo __SITE_URL; ?>shipper_description"><?php echo translate('buying_btn_more_shippers'); ?></a>
			</div>
		</div>
	</div>
</div>

<div class="container-center-sm">
	<div class="userinfo-scheme">
		<div class="userinfo-scheme__row">
			<div class="userinfo-scheme__col">
				<i class="ep-icon ep-icon_users-shopping-stroke"></i>
				<span class="userinfo-scheme__name">
					<?php echo translate('buying_block_text_start_shopping'); ?>
				</span>
			</div>
			<div class="userinfo-scheme__col">
				<i class="ep-icon ep-icon_sheild-ok2"></i>
				<span class="userinfo-scheme__name">
					<?php echo translate('buying_block_text_best_choice'); ?>
				</span>
			</div>
			<div class="userinfo-scheme__col">
				<i class="ep-icon ep-icon_basket-plus"></i>
				<span class="userinfo-scheme__name">
					<?php echo translate('buying_block_text_select_product'); ?>
				</span>
			</div>
		</div>
		<div class="userinfo-scheme__row">
			<div class="userinfo-scheme__col">
				<i class="ep-icon ep-icon_paper-stroke"></i>
				<span class="userinfo-scheme__name">
					<?php echo translate('buying_block_text_place_order'); ?>
				</span>
			</div>
			<div class="userinfo-scheme__col">
				<i class="ep-icon ep-icon_support-stroke"></i>
				<span class="userinfo-scheme__name">
					<?php echo translate('buying_block_text_assistance_available'); ?>
				</span>
			</div>
			<div class="userinfo-scheme__col">
				<i class="ep-icon ep-icon_card-lock2"></i>
				<span class="userinfo-scheme__name">
					<?php echo translate('buying_block_text_pay_secure'); ?>
				</span>
			</div>
		</div>
		<div class="userinfo-scheme__row">
			<div class="userinfo-scheme__col bdb-none">
				<i class="ep-icon ep-icon_box-code2"></i>
				<span class="userinfo-scheme__name">
					<?php echo translate('buying_block_text_papers_tracking'); ?>
				</span>
			</div>
			<div class="userinfo-scheme__col bdb-none">
				<i class="ep-icon ep-icon_box2"></i>
				<span class="userinfo-scheme__name">
					<?php echo translate('buying_block_text_get_product'); ?>
				</span>
			</div>
			<div class="userinfo-scheme__col bdb-none">
                <a class="btn btn-primary btn-lg btn-public btn-public--mw-100p" href="<?php echo __SITE_URL; ?>register/buyer"><?php echo translate('buying_btn_register_new_buyer'); ?></a>
            </div>
        </div>
	</div>
</div>
