<div class="userinfo-header">
	<div class="userinfo-header__detail">
		<div class="userinfo-header__inner" id="dedicated-about">
			<h1 class="userinfo-header__ttl"><?php echo translate('learn_more_block_register_shipper_header');?></h1>
            <h2 class="userinfo-header__subtitle"><?php echo translate('landing_block_about_us_header')?></h2>
			<div class="userinfo-header__detail-bottom">
				<div class="userinfo-header__txt">
                    <?php echo translate('langing_block_about_us_text_shipper');?>
				</div>
				<a class="btn btn-primary btn-lg btn-public" href="<?php echo __SITE_URL . 'learn_more';?>"><?php echo translate('learn_more_block_register_shipper_btn');?></a>
			</div>
		</div>
	</div>

	<img class="image" src="<?php echo __IMG_URL . 'public/img/users_description/header-shipper.jpg';?>" alt="<?php echo translate('learn_more_block_register_shipper_header');?>">
</div>

<div class="container-center-sm">
	<div class="public-twoblocks" id="dedicated-product">
		<div class="public-twoblocks__item public-twoblocks__img">
			<img class="image" src="<?php echo __IMG_URL . 'public/img/users_description/img-product4.jpg';?>" alt="<?php echo translate('langing_block_product_header');?>">
		</div>
		<div class="public-twoblocks__item">
			<div class="public-twoblocks__txt">
				<h2 class="public-twoblocks__ttl"><?php echo translate('buying_header_product');?></h2>
				<p class="public-twoblocks__paragraph">
                    <?php echo translate('langing_block_product_text_shipper');?>
				</p>
				<a class="btn btn-outline-dark btn-lg btn-public" href="<?php echo __SITE_URL . 'items/latest';?>"><?php echo translate('manufacturer_btn_find_merchandise');?></a>
			</div>
		</div>
	</div>

	<div class="public-twoblocks" id="dedicated-security-and-verification">
		<div class="public-twoblocks__item">
			<div class="public-twoblocks__txt">
				<h2 class="public-twoblocks__ttl"><?php echo translate('buying_header_security_verification');?></h2>
				<p class="public-twoblocks__paragraph">
                    <?php echo translate('shipper_block_text_security_verification');?>
				</p>
				<a class="btn btn-outline-dark btn-lg btn-public" href="<?php echo __SITE_URL . 'security';?>"><?php echo translate('buying_btn_security_verification');?></a>
			</div>
		</div>
		<div class="public-twoblocks__item public-twoblocks__img">
			<img class="image" src="<?php echo __IMG_URL . 'public/img/users_description/img-security-and-verefication3.jpg';?>" alt="<?php echo translate('buying_header_security_verification');?>">
		</div>
	</div>

	<div class="public-twoblocks" id="dedicated-customs-and-warehouse">
		<div class="public-twoblocks__item public-twoblocks__img">
			<img class="image" src="<?php echo __IMG_URL . 'public/img/users_description/img-customs2.jpg';?>" alt="<?php echo translate('langing_block_customs_header');?>">
		</div>
		<div class="public-twoblocks__item">
			<div class="public-twoblocks__txt">
				<h2 class="public-twoblocks__ttl"><?php echo translate('shipping_header_customs_warehouse');?></h2>
				<p class="public-twoblocks__paragraph">
					<?php echo translate('shipper_block_text_customs');?>
				</p>
				<a class="btn btn-outline-dark btn-lg btn-public" href="<?php echo __SITE_URL . 'faq';?>"><?php echo translate('buying_btn_customs_warehouse');?></a>
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

<div class="userinfo-delivery" id="dedicated-delivery">
	<img class="image" src="<?php echo __IMG_URL . 'public/img/users_description/ShipperDescription.jpg';?>" alt="<?php echo translate('langing_block_delivery_header');?>">
	<div class="userinfo-delivery__info userinfo-delivery__info--black">
		<div class="container-center-sm">
			<div class="userinfo-delivery__detail userinfo-delivery__detail--left">
				<h2 class="userinfo-delivery__ttl"><?php echo translate('langing_block_delivery_header');?></h2>
				<p class="userinfo-delivery__txt">
                    <?php echo translate('langing_block_delivery_text_shipper');?>
				</p>

				<a class="btn btn-outline-light btn-lg btn-public" href="<?php echo __SHIPPER_URL . 'register/ff'; ?>"><?php echo translate('shipper_btn_register_today');?></a>
			</div>
		</div>
	</div>
</div>

<div class="userinfo-blueb"  id="dedicated-payment">
	<div class="container-center-sm">
		<h2 class="userinfo-blueb__ttl"><?php echo translate('langing_block_payment_header');?></h2>
		<p class="userinfo-blueb__txt">
			<?php echo translate('manufacturer_block_text_payment');?>
		</p>
		<a class="btn btn-primary btn-lg btn-public" href="<?php echo __SHIPPER_URL . 'register/ff'; ?>"><?php echo translate('shipper_btn_register_now');?></a>
	</div>
</div>
