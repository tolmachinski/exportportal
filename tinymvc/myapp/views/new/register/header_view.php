<div class="register-main-header container-1420">
    <div class="register-main-header__content">
        <picture class="register-main-header__img">
            <source
                media="(max-width: 375px)"
                srcset="<?php echo asset("public/build/images/headers-info-pages/register_header_small_mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/headers-info-pages/register_header_small_mobile@2x.jpg"); ?> 2x"
            >
            <source
                media="(max-width: 475px)"
                srcset="<?php echo asset("public/build/images/headers-info-pages/register_header_mobile.jpg"); ?> 1x, <?php echo asset("public/build/images/headers-info-pages/register_header_mobile@2x.jpg"); ?> 2x"
            >
            <source
                media="(max-width: 768px)"
                srcset="<?php echo asset("public/build/images/headers-info-pages/register_header_tablet.jpg"); ?> 1x, <?php echo asset("public/build/images/headers-info-pages/register_header_tablet@2x.jpg"); ?> 2x"
            >
            <source
                media="(max-width: 991px)"
                srcset="<?php echo asset("public/build/images/headers-info-pages/register_header_tablet_991.jpg"); ?> 1x, <?php echo asset("public/build/images/headers-info-pages/register_header_tablet_991@2x.jpg"); ?> 2x"
            >
            <img
                class="image"
                src="<?php echo asset("public/build/images/headers-info-pages/register_header.jpg"); ?>"
                srcset="<?php echo asset("public/build/images/headers-info-pages/register_header@2x.jpg"); ?> 2x"
                alt="<?php echo translate('register_header_img');?>"
            >
        </picture>
        <div class="register-main-header__ttl">
            <h1 class="register-main-header__ttl-txt">
                <?php echo translate('register_header_title');?>
            </h1>
        </div>
    </div>
    <div class="register-main-header__links">
        <div class="register-main-header__link-col">
            <a class="register-main-header__link-block" href="<?php echo get_static_url('register/buyer');?>" <?php echo addQaUniqueIdentifier("page__registration__select-buyer")?>>
                <span class="register-main-header__link-ttl"><?php echo translate('register_header_link_buyer');?></span>
                <div class="register-main-header__link-txt">
                    <?php echo translate('register_header_link_next');?>
                    <?php echo widgetGetSvgIcon('arrowRight', 16, 17);?>
                </div>
            </a>
        </div>
        <div class="register-main-header__link-col">
            <a class="register-main-header__link-block" href="<?php echo get_static_url('register/seller');?>" <?php echo addQaUniqueIdentifier("page__registration__select-seller")?>>
                <span class="register-main-header__link-ttl"><?php echo translate('register_header_link_seller');?></span>
                <div class="register-main-header__link-txt">
                    <?php echo translate('register_header_link_next');?>
                    <?php echo widgetGetSvgIcon('arrowRight', 16, 17);?>
                </div>
            </a>
        </div>
        <div class="register-main-header__link-col">
            <a class="register-main-header__link-block" href="<?php echo get_static_url('register/manufacturer');?>" <?php echo addQaUniqueIdentifier("page__registration__select-manufacturer")?>>
                <span class="register-main-header__link-ttl"><?php echo translate('register_header_link_manufacturer');?></span>
                <div class="register-main-header__link-txt">
                    <?php echo translate('register_header_link_next');?>
                    <?php echo widgetGetSvgIcon('arrowRight', 16, 17);?>
                </div>
            </a>
        </div>
        <div class="register-main-header__link-col">
            <a class="register-main-header__link-block" href="<?php echo __SHIPPER_URL . 'register/ff'; ?>" <?php echo addQaUniqueIdentifier("page__registration__select-shipper")?>>
                <span class="register-main-header__link-ttl"><?php echo translate('register_header_link_shipper');?></span>
                <div class="register-main-header__link-txt">
                    <?php echo translate('register_header_link_next');?>
                    <?php echo widgetGetSvgIcon('arrowRight', 16, 17);?>
                </div>
            </a>
        </div>
    </div>
</div>
