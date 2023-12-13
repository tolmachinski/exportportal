<section class="home-section container-1420">
    <div class="promo-card<?php echo 'buyer' === $data['group'] ? ' promo-card--buyer' : ''; ?>">
        <picture class="promo-card__picture">
            <source
                media="(max-width: 575px)"
                srcset="<?php echo getLazyImage(545, 320); ?>"
                data-srcset="<?php echo $data['picture']['mobile']; ?> 1x, <?php echo $data['picture']['mobile@2x']; ?> 2x"
            >
            <source
                media="(max-width: 991px)"
                srcset="<?php echo getLazyImage(961, 307); ?>"
                data-srcset="<?php echo $data['picture']['tablet']; ?> 1x, <?php echo $data['picture']['tablet@2x']; ?> 2x"
            >
            <source
                media="(max-width: 1199px)"
                srcset="<?php echo getLazyImage(507, 350); ?>"
                data-srcset="<?php echo $data['picture']['1200']; ?> 1x, <?php echo $data['picture']['1200@2x']; ?> 2x"
            >
            <img
                class="promo-card__image js-lazy"
                src="<?php echo getLazyImage(740, 350); ?>"
                data-src="<?php echo $data['picture']['desktop']; ?>"
                data-srcset="<?php echo $data['picture']['desktop']; ?> 1x, <?php echo $data['picture']['desktop@2x']; ?> 2x"
                alt="<?php echo $data['title']; ?>"
            >
        </picture>
        <div class="promo-card__info">
            <h3 class="promo-card__title"><?php echo $data['title']; ?></h3>
            <p class="promo-card__description"><?php echo $data['description']; ?></p>
            <a
                class="promo-card__btn btn btn-primary btn-block btn-new18"
                href="<?php echo $data['link']['href']; ?>"
                <?php echo addQaUniqueIdentifier("home__{$data['group']}-promo-card-btn"); ?>
            >
                <?php echo $data['link']['text']; ?>
            </a>
        </div>
    </div>
</section>
