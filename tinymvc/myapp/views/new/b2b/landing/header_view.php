<!-- Section Header -->
<section class="main-page-header container-1420">
    <div class="main-page-header__container">
        <h1 class="main-page-header__title">
            <?php echo translate('ep_matchmaking_header_title'); ?>
        </h1>

        <p class="main-page-header__description">
            <?php echo translate('ep_matchmaking_header_description'); ?>
        </p>

        <?php if (!logged_in()) {?>
            <button
                class="main-page-header__btn btn btn-new16 btn-primary call-action"
                <?php echo addQaUniqueIdentifier('page__b2b__header_add-request-btn'); ?>
                data-js-action="b2b:add-request"
                data-title="<?php echo translate('ep_matchmaking_popup_title'); ?>"
                data-sub-title="<?php echo translate('ep_matchmaking_popup_subtitle'); ?>"
                data-image="<?php echo asset('public/build/images/b2b/landing/b2b-popup.jpg'); ?>"
                data-mw="400"
            >
                <?php echo translate('ep_matchmaking_header_add_request_btn'); ?>
            </button>

        <?php } else { ?>
            <a
                class="main-page-header__btn btn btn-new16 btn-primary"
                <?php echo addQaUniqueIdentifier('page__b2b__header_add-request-btn'); ?>
                <?php if(is_buyer() || is_shipper()) { ?>
                    href="<?php echo __SITE_URL . 'b2b/all'; ?>"
                <?php } else { ?>
                    href="<?php echo __SITE_URL . 'b2b/reg'; ?>"
                <?php } ?>
            >
                <?php if(is_buyer() || is_shipper()) { ?>
                    <?php echo translate('ep_matchmaking_header_view_request_btn'); ?>
                <?php } else { ?>
                    <?php echo translate('ep_matchmaking_header_add_request_btn'); ?>
                <?php } ?>
            </a>
        <?php } ?>
    </div>
    <picture class="main-page-header__picture">
        <source
            media="(max-width: 575px)"
            srcset="<?php echo asset('public/build/images/b2b/landing/header/header-mobile.jpg'); ?>"
            data-srcset="<?php echo asset('public/build/images/b2b/landing/header/header-mobile.jpg'); ?> 1x,
            <?php echo asset('public/build/images/b2b/landing/header/header-mobile@2x.jpg'); ?> 2x"
        >
        <source
            media="(max-width: 991px)"
            srcset="<?php echo asset('public/build/images/b2b/landing/header/header-tablet.jpg'); ?>"
            data-srcset="<?php echo asset('public/build/images/b2b/landing/header/header-tablet.jpg'); ?> 1x,
            <?php echo asset('public/build/images/b2b/landing/header/header-tablet@2x.jpg'); ?> 2x"
        >
        <img
            class="main-page-header__image"
            src="<?php echo asset('public/build/images/b2b/landing/header/header.jpg'); ?>"
            srcset="<?php echo asset('public/build/images/b2b/landing/header/header.jpg'); ?> 1x,
            <?php echo asset('public/build/images/b2b/landing/header/header@2x.jpg'); ?> 2x"
            alt="<?php echo translate('ep_matchmaking_header_title'); ?>"
        >
        </picture>
</section>
<!-- End Section Header -->
