<section class="epl-get-started-b footer-connect">
    <div class="epl-get-started-b__inner container-center">
        <div class="epl-desc-text-b epl-desc-text-b--mw-650 epl-desc-text-b--tac">
            <h2 class="epl-desc-text-b__ttl"><?php echo translate('epl_home_get_started_ttl'); ?></h2>
            <p class="epl-desc-text-b__text"><?php echo translate('epl_home_get_started_text_1'); ?></p>
            <a
                class="btn btn-primary btn-lg btn-upper btn-medium btn-mnw-200"
                href="<?php echo __SHIPPER_URL . 'register/ff'; ?>"
                <?php echo addQaUniqueIdentifier("epl-get-started-b__btn")?>
            >
                <?php echo translate('epl_home_get_started_btn'); ?>
            </a>
        </div>
    </div>

    <picture class="epl-get-started-b__bg">
        <source
            media="(max-width: 575px)"
            srcset="<?php echo getLazyImage(575, 290);?>"
            data-srcset="<?php echo asset("public/build/images/epl/get-started-bg-mobile.jpg"); ?>"
        >
        <source
            media="(max-width: 1024px)"
            srcset="<?php echo getLazyImage(1024, 350);?>"
            data-srcset="<?php echo asset("public/build/images/epl/get-started-bg-tablet.jpg"); ?>"
        >
        <img
            class="image js-lazy"
            width="1920"
            height="400"
            src="<?php echo getLazyImage(1920, 400);?>"
            data-src="<?php echo asset("public/build/images/epl/get-started-bg.jpg"); ?>"
            alt="<?php echo translate('epl_home_get_started_ttl', null, true); ?>"
        >
    </picture>
</section>
