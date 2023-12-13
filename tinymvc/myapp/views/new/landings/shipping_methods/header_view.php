<!-- Section Header -->
<section class="main-page-header">
    <div class="main-page-header__container main-page-header__container--small">
        <h1 class="main-page-header__title">
            <?php echo translate('shipping_methods_header_title'); ?>
        </h1>

        <p class="main-page-header__description">
            <?php echo translate('shipping_methods_header_text'); ?>
        </p>

        <button
            class="main-page-header__btn btn btn-primary btn-new18 call-action"
            data-js-action="best-practices:download-pdf"
            data-guide-name="best_practices"
            data-lang="en"
            data-group="all"
            <?php echo addQaUniqueIdentifier("page__shipping-metods__main-header_download-btn"); ?>
        >
            <?php echo translate('shipping_methods_header_download_button'); ?>
        </button>
    </div>
    <picture class="main-page-header__picture">
        <source
            media="(max-width: 575px)"
            srcset="<?php echo asset('public/build/images/landings/shipping_methods/header-mobile.jpg'); ?>"
            data-srcset="<?php echo asset('public/build/images/landings/shipping_methods/header-mobile.jpg'); ?> 1x,
            <?php echo asset('public/build/images/landings/shipping_methods/header-mobile@2x.jpg'); ?> 2x"
        >
        <source
            media="(max-width: 991px)"
            srcset="<?php echo asset('public/build/images/landings/shipping_methods/header-tablet.jpg'); ?>"
            data-srcset="<?php echo asset('public/build/images/landings/shipping_methods/header-tablet.jpg'); ?> 1x,
            <?php echo asset('public/build/images/landings/shipping_methods/header-tablet@2x.jpg'); ?> 2x"
        >
        <img
            class="main-page-header__image"
            src="<?php echo asset('public/build/images/landings/shipping_methods/header.jpg'); ?>"
            srcset="<?php echo asset('public/build/images/landings/shipping_methods/header.jpg'); ?> 1x,
            <?php echo asset('public/build/images/landings/shipping_methods/header@2x.jpg'); ?> 2x"
            alt="<?php echo translate('shipping_methods_header_title'); ?>"
        >
        </picture>
</section>
<!-- End Section Header -->

<?php encoreEntryLinkTags("shipping_methods_page"); ?>
<?php encoreEntryScriptTags("shipping_methods_page"); ?>
