<section class="home-section just-for-you">
    <div class="just-for-you__wrap container-1420">
        <div class="just-for-you__content">
            <h2 class="section-header__title"><?php echo translate('home_just-for-you_ttl'); ?></h2>
            <p class="just-for-you__description"><?php echo translate('home_just-for-you_desctiption'); ?></p>
            <a class="btn btn-primary btn-block just-for-you__view-more-btn" href="<?php echo __SITE_URL . 'search?recommended=1';?>" <?php echo addQaUniqueIdentifier("page__home__just-for-you_btn-view-more"); ?>><?php echo translate('home_just-for-you_btn'); ?></a>
        </div>
        <div class="just-for-you__slider products products--slider-full js-just-for-you loading" data-lazy-name="just-for-you" <?php echo addQaUniqueIdentifier("page__home__just-for-you"); ?>></div>
        <a class="btn btn-primary btn-block btn-new16 just-for-you__view-more-adaptive" href="<?php echo __SITE_URL . 'search?recommended=1';?>" <?php echo addQaUniqueIdentifier("page__home__just-for-you_btn-view-more"); ?>><?php echo translate('home_just-for-you_btn'); ?></a>
    </div>
    <?php views('new/partials/ajax_loader_view'); ?>
</section>
