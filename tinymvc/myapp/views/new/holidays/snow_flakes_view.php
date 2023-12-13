<div id="js-holiday-snowflakes" class="holiday-snowflakes" aria-hidden="true">
    <div class="holiday-snowflake"></div>
</div>

<picture class="ep-footer--new-year__bg">
    <source srcset="<?php echo getLazyImage(1920, 567); ?>" data-srcset="<?php echo asset("public/build/images/footer/ep-footer-new-year-background-d.jpg"); ?>" media="(min-width: 992px)">
    <source srcset="<?php echo getLazyImage(991, 1035); ?>" data-srcset="<?php echo asset("public/build/images/footer/ep-footer-new-year-background-t.jpg"); ?>" media="(min-width: 576px)">
    <img class="ep-footer--new-year__bg-image js-lazy" src="<?php echo getLazyImage(575, 1542); ?>" data-src="<?php echo asset("public/build/images/footer/ep-footer-new-year-background-m.jpg"); ?>" alt="new year footer background">
</picture>

<?php echo dispatchDynamicFragment("holidays:new-year-snow-flakes", null, true); ?>
