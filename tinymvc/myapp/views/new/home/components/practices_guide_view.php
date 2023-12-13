<section class="home-section practices-guide <?php echo 'buyer' === $data['group'] ? 'practices-guide--buyer' : ('freight_forwarder' === $data['group'] ? 'practices-guide--ff' : ''); ?> container-1420">
    <div class="practices-guide__content">
        <img
            class="practices-guide__image js-lazy"
            src="<?php echo getLazyImage(190, 268); ?>"
            data-src="<?php echo $data['image']['desktop']; ?>"
            data-srcset="<?php echo $data['image']['desktop']; ?> 1x, <?php echo $data['image']['desktop@2x']; ?> 2x"
            alt="<?php echo $data['title']; ?>"
        >

        <div class="practices-guide__info">
            <h3 class="practices-guide__title"><?php echo $data['title']; ?></h3>
            <p class="practices-guide__description"><?php echo $data['description']; ?></p>
        </div>

        <button
            class="practices-guide__btn btn btn-primary btn-new18 call-action"
            data-js-action="best-practices:download-pdf"
            data-guide-name="best_practices"
            data-lang="en"
            data-group="<?php echo $data['group']; ?>"
            <?php echo addQaUniqueIdentifier("home__{$data['group']}-practices-guide_download-btn"); ?>
        >
            <?php echo translate('home_best_practices_download_btn'); ?>
        </button>
    </div>
</section>
