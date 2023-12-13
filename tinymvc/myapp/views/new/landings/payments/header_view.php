<section class="what-is-epp">
    <div class="what-is-epp__content container-center-sm">
        <h1 class="what-is-epp__title"><?php echo translate("payments_what_is_epp_title"); ?></h1>
        <h3 class="what-is-epp__description"><?php echo translate("payments_what_is_epp_description"); ?></h3>
        <?php if (logged_in()) { ?>
            <a class="what-is-epp__link btn btn-primary js-require-logout-systmess" href="javascript:void(0)"><?php echo translate("payments_what_is_epp_link")?></a>
        <?php } else { ?>
            <a class="what-is-epp__link btn btn-primary" href="<?php echo __SITE_URL?>register"><?php echo translate("payments_what_is_epp_link")?></a>
        <?php } ?>
    </div>
    <div class="what-is-epp__background">
        <picture>
            <source srcset="<?php echo asset("public/build/images/landings/payments/header-mobile.jpg")?>" media="(max-width: 575px)">
            <source srcset="<?php echo asset("public/build/images/landings/payments/header-tablet.jpg")?>" media="(max-width: 1199px)">
            <img src="<?php echo asset("public/build/images/landings/payments/header.jpg")?>" alt="<?php echo translate("payments_what_is_epp_title")?>">
        </picture>
    </div>
</section>

<?php encoreEntryLinkTags("payments_page"); ?>
<?php encoreLinks(); ?>
