<section class="what-is-epu epu-section">
    <div class="what-is-epu__content">
        <h1 class="what-is-epu__title"><?php echo translate("university_what_is_epu_title"); ?></h1>
        <h3 class="what-is-epu__description"><?php echo translate("university_what_is_epu_description"); ?></h3>
            <a class="what-is-epu__link btn btn-primary" href="https://app.smartsheet.com/b/form/9849d38ec11949caa92697d34363fe92"><?php echo translate("university_what_is_epu_link"); ?></a>
    </div>
    <div class="what-is-epu__background">
        <picture>
            <source srcset="<?php echo asset("public/build/images/landings/university/header-mobile.jpg")?>" media="(max-width: 575px)">
            <source srcset="<?php echo asset("public/build/images/landings/university/header-tablet-970.jpg")?>" media="(max-width: 970px)">
            <source srcset="<?php echo asset("public/build/images/landings/university/header-tablet.jpg")?>" media="(max-width: 1199px)">
            <img src="<?php echo asset("public/build/images/landings/university/header.jpg")?>" alt="<?php echo translate("university_what_is_epu_title"); ?>">
        </picture>
    </div>
</section>

<?php encoreEntryLinkTags("university_page"); ?>
<?php encoreLinks(); ?>
