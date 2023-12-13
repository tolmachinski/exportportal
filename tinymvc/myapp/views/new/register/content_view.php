<?php
    encoreEntryLinkTags("register_forms");

    if (!empty($webpackData)) {
        encoreLinks();
    }
?>

<?php if ('seller' == $register_type) {?>
    <div class="account-certified-banner">
        <div class="container-center-sm">
            <div class="account-certified-banner__inner">
                <picture class="account-certified-banner__bg">
                        <source media="(max-width: 575px)" srcset="<?php echo getLazyImage(290, 186); ?>" data-srcset="<?php echo asset("public/build/images/register_forms/certified-banner/get-certified-mobile.png"); ?> 1x, <?php echo asset("public/build/images/register_forms/certified-banner/get-certified-mobile@2x.png"); ?> 2x">
                        <source media="(max-width: 991px)" srcset="<?php echo getLazyImage(738, 250); ?>" data-srcset="<?php echo asset("public/build/images/register_forms/certified-banner/get-certified-tablet.png"); ?> 1x, <?php echo asset("public/build/images/register_forms/certified-banner/get-certified-tablet@2x.png"); ?> 2x">
                        <img class="image js-lazy" src="<?php echo getLazyImage(439, 398); ?>" data-src="<?php echo asset("public/build/images/register_forms/certified-banner/get-certified.png"); ?>" data-srcset="<?php echo asset("public/build/images/register_forms/certified-banner/get-certified.png"); ?> 1x, <?php echo asset("public/build/images/register_forms/certified-banner/get-certified@2x.png"); ?> 2x" alt="<?php echo translate('register_certified_banner_title') ?>">
                </picture>
                <div class="account-certified-banner__body">
                    <h3 class="account-certified-banner__title"><?php echo translate('register_certified_banner_title') ?></h3>
                    <p class="account-certified-banner__text">
                    <?php echo translate('register_certified_banner_text') ?>
                    </p>
                    <a class="btn btn-primary" href="<?php echo __SITE_URL . 'about/certification_and_upgrade_benefits' ?>"><?php echo translate('register_certified_banner_btn') ?></a>
                </div>
            </div>
        </div>
    </div>
<?php }?>

<div class="account-suppliers">
    <div class="container-center-sm">
        <div class="account-suppliers__inner">
            <div class="account-suppliers__img">
                <img
                    class="image js-lazy"
                    src="<?php echo getLazyImage(576, 608);?>"
                    data-src="<?php echo asset('public/build/images/register_forms/smiling-man.png'); ?>"
                    width="576"
                    height="608"
                    alt="<?php echo translate('register_benefits_header_buyer_sub_text');?>"
                >
            </div>
            <div class="account-suppliers__block">
                <div class="account-suppliers__undertitle"><?php echo $content["undertitle"];?></div>
                <h2 class="account-suppliers__title"><?php echo $content["title"];?></h2>
                <div class="account-suppliers__list-block">
                    <div class="account-suppliers__subtitle"><?php echo $content["subtitle"];?></div>
                    <ul class="account-suppliers__list">
                        <?php foreach($content["advantages"] as $key => $advantage ) { ?>
                            <li class="<?php echo $key !== 0 ? "mt-30" : ""; ?>">
                                <i class="ep-icon <?php echo $advantage["icon"]?> fs-30 mr-15"></i><?php echo $advantage["text"]?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="account-benefits">
    <h2 class="account-benefits__title"><?php echo $content["benefits_title"]?></h2>
    <div class="row row-eq-height container-center-sm pl-0 pr-0">
        <?php foreach ($content["benefits"] as $benefit) { ?>
            <div class="col-sm-4 account-benefits__block">
                <i class="ep-icon <?php echo $benefit["icon"]; ?> fs-40"></i>
                <h2 class="pt-25"><?php echo $benefit["title"];?></h2>
                <h4 class="pt-20"><?php echo $benefit["subtitle"];?></h4>
                <p class="pt-15 txt-gray"><?php echo $benefit["text"];?></p>
            </div>
        <?php } ?>
    </div>
</div>

<div class="account-testimonials footer-connect">
    <h2 class="account-testimonials__slide-title"><?php echo translate('register_testiminials_header');?></h2>
    <div id="js-testimonials-slick" class="account-testimonials__slider-container" <?php echo addQaUniqueIdentifier("register__reviews-slider")?>>
        <?php foreach ($content["slides"] as $slide) {?>
            <div class="account-testimonials__slide" style="width: 740px;">
                <p class="account-testimonials__slide-text">“<?php echo $slide["text"];?>”</p>
                <div class="account-testimonials__slider-img-block">
                    <div class="account-testimonials__slider-image mr-15">
                        <img
                            class="js-lazy"
                            src="<?php echo $slide["image_lazy"];?>"
                            data-src="<?php echo $slide["image_link"];?>"
                            alt="<?php echo $slide["name"]?>"
                        >
                    </div>
                    <div class="account-testimonials__slider-name">- <?php echo $slide["name"]?></div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

