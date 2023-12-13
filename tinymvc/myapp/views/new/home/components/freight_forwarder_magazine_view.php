<section class="home-section world-magazine container-1420">
    <div class="section-header">
        <div class="section-header__column">
            <h2 class="section-header__title">
                <?php echo translate('home_freight_forwarders_magazine_header_title'); ?>
            </h2>
            <p class="section-header__subtitle">
                <?php echo translate('home_freight_forwarders_magazine_header_subtitle'); ?>
            </p>
        </div>
        <a
            class="section-header__link"
            href="<?php echo __SITE_URL . 'landing/epl'; ?>"
            <?php echo addQaUniqueIdentifier('page__home__freight-forwarders-magazine_read-now'); ?>>
            <?php echo translate('home_title_links_read_now'); ?><?php echo widgetGetSvgIcon('arrowRight', 15, 15); ?>
        </a>
    </div>
    <div class="world-magazine__content">
        <picture class="world-magazine__picture">
            <source
                srcset="<?php echo getLazyImage(436, 240); ?>"
                data-srcset="<?php echo asset('public/build/images/index/freight-forwarder-magazine/freigth-forwerder-magazine-bg-m2.jpg'); ?> 1x, <?php echo asset('public/build/images/index/freight-forwarder-magazine/freigth-forwerder-magazine-bg-m2@2x.jpg'); ?> 2x"
                media="(max-width: 393px)">
            <source
                srcset="<?php echo getLazyImage(474, 240); ?>"
                data-srcset="<?php echo asset('public/build/images/index/freight-forwarder-magazine/freigth-forwerder-magazine-bg-m.jpg'); ?> 1x, <?php echo asset('public/build/images/index/freight-forwarder-magazine/freigth-forwerder-magazine-bg-m@2x.jpg'); ?> 2x"
                media="(max-width: 425px)">
            <img
                class="world-magazine__image js-lazy"
                src="<?php echo getLazyImage(646, 467); ?>"
                data-src="<?php echo asset('public/build/images/index/freight-forwarder-magazine/freigth-forwerder-magazine-bg.jpg'); ?>"
                data-srcset="<?php echo asset('public/build/images/index/freight-forwarder-magazine/freigth-forwerder-magazine-bg.jpg'); ?> 1x, <?php echo asset('public/build/images/index/freight-forwarder-magazine/freigth-forwerder-magazine-bg@2x.jpg'); ?> 2x"
                alt="Read the First Freight Forwarder’s World Magazine">
        </picture>
        <div
            class="world-magazine__slider js-freight-forwarders-magazine loading"
            data-lazy-name="freight-forwarders-magazine" <?php echo addQaUniqueIdentifier('page__home__freight-forwarders-magazine-slider'); ?>>
            <?php if(isset($magazines)){
                    foreach($magazines as $magazine){ ?>
                <div
                    class="world-magazine__item"
                    <?php echo addQaUniqueIdentifier('page__home__freight-forwarders-magazine-slider_item')?>>
                    <div class="world-magazine__inner">
                        <a
                            class="world-magazine__link"
                            href="<?php echo $magazine['link']; ?>"
                            target="_blank">
                            <span class="world-magazine__img image-card3">
                                <span class="link">
                                    <img
                                        class="image js-lazy"
                                        data-src="<?php echo $magazine['images']['portrait']; ?>"
                                        src="<?php echo getLazyImage(375, 300); ?>"
                                        width="375"
                                        height="300"
                                        alt="<?php echo $magazine['title']; ?>"
                                        <?php echo addQaUniqueIdentifier('page__home__freight-forwarders-magazine-slider_item-image')?> />
                                </span>
                            </span>
                        </a>
                    </div>
                </div>
            <?php }
            }?>
        </div>
    </div>
    <?php views('new/partials/ajax_loader_view'); ?>
</section>
