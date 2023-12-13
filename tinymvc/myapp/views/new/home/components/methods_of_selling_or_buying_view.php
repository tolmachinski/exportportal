<section class="home-section selling-methods container-1420">
    <div class="section-header section-header--title-only">
        <h2 class="section-header__title"><?php echo $sectionTitle; ?></h2>
    </div>

    <div class="selling-methods__content<?php echo count($methodsList) > 3 ? ' selling-methods__content--sm-items' : ''; ?>">
        <?php foreach ($methodsList as $method) { ?>
            <div class="selling-methods__item">
                <picture class="selling-methods__picture">
                    <source
                        media="(max-width: 575px)"
                        srcset="<?php echo getLazyImage(545, 170); ?>"
                        data-srcset="<?php echo $method['picture']['mobile']; ?> 1x, <?php echo $method['picture']['mobile@2x']; ?> 2x"
                    >
                    <source
                        media="(min-width: 768px) and (max-width: 991px)"
                        srcset="<?php echo getLazyImage(236, 170); ?>"
                        data-srcset="<?php echo $method['picture']['tablet']; ?> 1x, <?php echo $method['picture']['tablet@2x']; ?> 2x"
                    >
                    <img
                        class="selling-methods__image js-lazy"
                        src="<?php echo getLazyImage(453, 170); ?>"
                        data-src="<?php echo $method['picture']['desktop']; ?>"
                        data-srcset="<?php echo $method['picture']['desktop']; ?> 1x, <?php echo $method['picture']['desktop@2x']; ?> 2x"
                        alt="<?php echo $method['title']; ?>"
                    >
                </picture>
                <h3 class="selling-methods__title"><?php echo $method['title']; ?></h3>
                <p class="selling-methods__description"><?php echo $method['description']; ?></p>
            </div>
        <?php } ?>
    </div>
</section>
