<section class="home-section benefits<?php echo $benefitsData['reversed'] === true ? ' benefits--reversed' : ''; ?> container-1420">
    <div class="section-header <?php echo isset($benefitsData["link"]) ? '' : 'section-header--title-only'; ?>">
        <h2 class="section-header__title"><?php echo $benefitsData['title']; ?></h2>
        <?php if (isset($benefitsData['link']) && !empty($benefitsData['link'])) { ?>
            <a
                class="section-header__link"
                href="<?php echo $benefitsData["link"]["href"]; ?>"
                <?php echo addQaUniqueIdentifier("home__benefits-section-{$benefitsData['atasType']}-title-link"); ?>
            >
                <?php echo $benefitsData["link"]["text"]; ?><?php echo widgetGetSvgIcon("arrowRight", 15, 15)?>
            </a>
        <?php } ?>
        <?php if (isset($benefitsData['subTitle']) && !empty($benefitsData['subTitle'])) { ?>
            <p class="section-header__subtitle"><?php echo $benefitsData['subTitle']; ?></p>
        <?php } ?>
    </div>
    <div class="benefits__content">
        <div class="benefits__info">
            <?php foreach($benefitsData['benefits'] as $benefit) { ?>
                <div class="benefits__item">
                    <img class="benefits__item-image js-lazy" src="<?php echo getLazyImage(45, 45); ?>" data-src="<?php echo $benefit['icon']; ?>" alt="<?php echo $benefit['title']; ?>">
                    <div class="benefits__item-info">
                        <strong class="benefits__item-title"><?php echo $benefit['title']; ?></strong>
                        <p class="benefits__item-paragraph"><?php echo $benefit['paragraph']; ?></p>
                    </div>
                </div>
            <?php }?>
            <?php if (isset($benefitsData['button']) && !empty($benefitsData['button'])) { ?>
                <a class="btn btn-primary btn-block btn-new18" href="<?php echo $benefitsData['button']['href']; ?>" <?php echo addQaUniqueIdentifier("home__benefits-section-{$benefitsData['atasType']}-button"); ?>><?php echo $benefitsData['button']['text']; ?></a>
            <?php } ?>
        </div>
        <picture class="benefits__picture">
            <source srcset="<?php echo getLazyImage(545, 200); ?>" data-srcset="<?php echo $benefitsData['picture']['mobile']?> 1x, <?php echo $benefitsData['picture']['mobile@2x']?> 2x" media="(max-width: 575px)">
            <source srcset="<?php echo getLazyImage(551, 512); ?>" data-srcset="<?php echo $benefitsData['picture']['tablet']?> 1x, <?php echo $benefitsData['picture']['tablet@2x']?> 2x" media="(max-width: 991px)">
            <img class="benefits__image js-lazy" src="<?php echo getLazyImage(960, count($benefitsData['benefits']) > 3 ? 570 : 465); ?>" data-src="<?php echo $benefitsData['picture']['desktop']; ?>" data-srcset="<?php echo $benefitsData['picture']['desktop']; ?> 1x, <?php echo $benefitsData['picture']['desktop@2x']; ?> 2x" alt="<?php echo $benefitsData['title']; ?>">
        </picture>
    </div>
</section>
