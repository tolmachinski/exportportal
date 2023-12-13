<div class="userinfo-header">
	<div class="userinfo-header__detail">
		<div class="userinfo-header__inner">
            <h1 class="userinfo-header__ttl"><?php echo translate('learn_more_block_register_manufacturer_header');?></h1>
            <h2 class="userinfo-header__subtitle"><?php echo translate('landing_block_about_us_header');?></h2>
			<div class="userinfo-header__detail-bottom">
				<div class="userinfo-header__txt">
					<?php echo translate('manufacturer_block_text_header');?>
				</div>
				<a class="btn btn-primary btn-lg btn-public" href="<?php echo __SITE_URL;?>learn_more"><?php echo translate('learn_more_block_header_manufacturer_btn');?></a>
			</div>
		</div>
	</div>

	<img class="image" src="<?php echo __IMG_URL;?>public/img/users_description/header-manufacturer.jpg" alt="<?php echo translate('learn_more_block_register_manufacturer_header', null, true);?>">
</div>

<div class="container-center-sm">
	<div class="public-twoblocks">
		<div class="public-twoblocks__item public-twoblocks__img">
			<img class="image" src="<?php echo __IMG_URL;?>public/img/users_description/img-product3.jpg" alt="<?php echo translate('langing_block_product_header', null, true);?>">
		</div>
		<div class="public-twoblocks__item">
			<div class="public-twoblocks__txt">
				<h2 class="public-twoblocks__ttl"><?php echo translate('langing_block_product_header');?></h2>
				<p class="public-twoblocks__paragraph">
                    <?php echo translate('langing_block_product_text_manufacturer');?>
				</p>
				<a class="btn btn-outline-dark btn-lg btn-public" href="<?php echo __SITE_URL;?>items/choose_category"><?php echo translate('selling_btn_update_product');?></a>
			</div>
		</div>
	</div>

	<div class="public-twoblocks">
		<div class="public-twoblocks__item">
			<div class="public-twoblocks__txt">
				<h2 class="public-twoblocks__ttl"><?php echo translate('buying_header_security_verification');?></h2>
				<p class="public-twoblocks__paragraph">
					<?php echo translate('manufacturer_block_text_security_verification');?>
				</p>
				<a class="btn btn-primary btn-lg btn-public" href="<?php echo __SITE_URL;?>security"><?php echo translate('buying_btn_security_verification');?></a>
			</div>
		</div>
		<div class="public-twoblocks__item public-twoblocks__img">
			<img class="image" src="<?php echo __IMG_URL;?>public/img/users_description/img-security-and-verefication3.jpg" alt="<?php echo translate('buying_header_security_verification', null, true);?>">
		</div>
	</div>

	<div class="public-twoblocks">
		<div class="public-twoblocks__item public-twoblocks__img">
			<img class="image" src="<?php echo __IMG_URL;?>public/img/users_description/img-customs-and-warehouse2.jpg" alt="<?php echo translate('langing_header_link_customs', null, true);?>">
		</div>
		<div class="public-twoblocks__item">
			<div class="public-twoblocks__txt">
				<h2 class="public-twoblocks__ttl"><?php echo translate('langing_header_link_customs');?></h2>
				<p class="public-twoblocks__paragraph">
                    <?php echo translate('langing_block_customs_text_manufacturer');?>
				</p>
				<a class="btn btn-outline-dark btn-lg btn-public" href="<?php echo __SITE_URL;?>faq"><?php echo translate('buying_btn_customs_warehouse');?></a>
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
	<img class="image" src="<?php echo __IMG_URL;?>public/img/users_description/bg-delivery3.jpg" alt="<?php echo translate('langing_block_payment_header', null, true);?>">
	<div class="userinfo-delivery__info userinfo-delivery__info--white">
		<div class="container-center-sm">
			<div class="userinfo-delivery__detail userinfo-delivery__detail--mr">
				<h2 class="userinfo-delivery__ttl txt-black"><?php echo translate('langing_block_payment_header');?></h2>
				<p class="userinfo-delivery__txt txt-black">
					<?php echo translate('manufacturer_block_text_payment');?>
				</p>

				<a class="btn btn-outline-dark btn-lg btn-public" href="<?php echo __SITE_URL;?>shipper_description"><?php echo translate('manufacturer_btn_about_frieght');?></a>
			</div>
		</div>
	</div>
</div>

<div class="userinfo-blueb">
	<div class="container-center-sm">
		<h2 class="userinfo-blueb__ttl"><?php echo translate('manufacturer_block_text_footer_header');?></h2>
		<p class="userinfo-blueb__txt">
			<?php echo translate('manufacturer_block_text_footer_content');?>
		</p>
		<a class="btn btn-primary btn-lg btn-public" href="<?php echo __SITE_URL;?>register/manufacturer"><?php echo translate('learn_more_block_register_manufacturer_btn');?></a>
	</div>
</div>
