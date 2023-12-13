<section class="home-section benefits benefits--epl container-1420">
    <div class="benefits__content">
        <div class="benefits__info">
            <h2 class="benefits__title">
                <?php echo translate('home_visit_epl_title') ?>
            </h2>
            <p class="benefits__description">
                <?php echo translate('home_visit_epl_desc') ?>
            </p>
            <a
                class="btn btn-primary btn-block btn-new18"
                href="<?php echo __SHIPPER_URL; ?>"
                <?php echo addQaUniqueIdentifier("page__home__visit-epl_go-to-epl-button"); ?>>
                <?php echo translate('home_visit_epl_btn') ?>
            </a>
        </div>
        <picture class="benefits__picture">
            <source
                srcset="<?php echo getLazyImage(545, 200); ?>"
                data-srcset="<?php echo asset('public/build/images/index/benefits/epl-benefits-m.jpg'); ?> 1x, <?php echo asset('public/build/images/index/benefits/epl-benefits-m@2x.jpg'); ?> 2x"
                media="(max-width: 425px)">
            <source
                srcset="<?php echo getLazyImage(961, 258); ?>"
                data-srcset="<?php echo asset('public/build/images/index/benefits/epl-benefits-t.jpg'); ?> 1x, <?php echo asset('public/build/images/index/benefits/epl-benefits-t@2x.jpg'); ?> 2x" media="(max-width: 991px)">
            <img
                class="benefits__image js-lazy" src="<?php echo getLazyImage(960, 356); ?>"
                data-src="<?php echo asset('public/build/images/index/benefits/epl-benefits-d.jpg'); ?>"
                data-srcset="<?php echo asset('public/build/images/index/benefits/epl-benefits-d.jpg'); ?> 1x, <?php echo asset('public/build/images/index/benefits/epl-benefits-d@2x.jpg'); ?> 2x"
                alt="<?php echo translate('home_visit_epl_title') ?>">
        </picture>
    </div>
</section>
