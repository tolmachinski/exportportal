<!-- Section Header -->
<section class="main-page-header">
    <div class="main-page-header__container main-page-header__container--center">
        <h1 class="main-page-header__title">
            <?php echo translate('handmade_discover_handcrafted_items_title'); ?>
        </h1>
        <?php if (!logged_in()) {?>
            <a
                class="main-page-header__btn btn btn-primary btn-new18"
                href="<?php echo __SITE_URL . 'register';?>"
                <?php echo addQaUniqueIdentifier("page__handmade-products__main-header_join-ep-btn"); ?>
            >
                <?php echo translate('handmade_discover_handcrafted_items_join_button'); ?>
            </a>
        <?php }?>

    </div>
    <picture class="main-page-header__picture">
        <source
            media="(max-width: 575px)"
            srcset="<?php echo asset('public/build/images/landings/handmade/header-mobile.jpg'); ?>"
            data-srcset="<?php echo asset('public/build/images/landings/handmade/header-mobile.jpg'); ?> 1x,
            <?php echo asset('public/build/images/landings/handmade/header-mobile@2x.jpg'); ?> 2x"
        >
        <source
            media="(max-width: 991px)"
            srcset="<?php echo asset('public/build/images/landings/handmade/header-tablet.jpg'); ?>"
            data-srcset="<?php echo asset('public/build/images/landings/handmade/header-tablet.jpg'); ?> 1x,
            <?php echo asset('public/build/images/landings/handmade/header-tablet@2x.jpg'); ?> 2x"
        >
        <img
            class="main-page-header__image"
            src="<?php echo asset('public/build/images/landings/handmade/header.jpg'); ?>"
            srcset="<?php echo asset('public/build/images/landings/handmade/header.jpg'); ?> 1x,
            <?php echo asset('public/build/images/landings/handmade/header@2x.jpg'); ?> 2x"
            alt="<?php echo translate('handmade_discover_handcrafted_items_title'); ?>"
        >
        </picture>
</section>
<!-- End Section Header -->
<?php encoreEntryLinkTags('handmade_page'); ?>
<?php encoreEntryScriptTags('handmade_page'); ?>
