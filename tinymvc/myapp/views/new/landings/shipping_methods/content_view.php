<section class="shipping-methods">
    <div class="container-1420">
    <h2 class="shipping-methods__title">
        <?php echo translate('shipping_methods_find_ideal_shipping_method_title'); ?>
    </h2>
    <div class="shipping-methods__tabs">
        <?php foreach ($shippingMethods as $shippingMethod) { ?>
            <button
                class="shipping-methods__tabs-link call-action"
                <?php echo addQaUniqueIdentifier("page__shipping-methods__find-ideal-method-btn"); ?>
                data-js-action="shipping-method:scroll-to"
                data-anchor="<?php echo $shippingMethod['type_alias']; ?>"
            >
                <span class="shipping-methods__tabs-name">
                    <?php echo $shippingMethod['type_name']; ?>
                </span>
                <span class="shipping-methods__tabs-alias">
                    (<?php echo $shippingMethod['type_alias']; ?>)
                </span>
            </button>
        <?php } ?>
    </div>

    <ul class="shipping-methods__list">
        <?php foreach ($shippingMethods as $shippingMethod) { ?>
            <li id="<?php echo $shippingMethod['type_alias']; ?>" class="shipping-methods__item" <?php echo addQaUniqueIdentifier('page__shipping-methods__item'); ?>>
                <img
                    class="shipping-methods__item-image js-lazy"
                    <?php echo addQaUniqueIdentifier('page__shipping-methods__find-ideal-method_image'); ?>
                    src="<?php echo getLazyImage(200, 200); ?>"
                    data-src="<?php echo getDisplayImageLink(['{FILE_NAME}' => $shippingMethod['image']], 'shipping_methods.main'); ?>"
                    alt="<?php echo $shippingMethod['type_name']; ?>" >
                <div class="shipping-methods__item-body" <?php echo addQaUniqueIdentifier('page__shipping-methods__item-body'); ?>>
                    <h3 class="shipping-methods__item-title" <?php echo addQaUniqueIdentifier('page__shipping-methods__item-title'); ?>>
                        <span class="shipping-methods__item-name">
                            <?php echo $shippingMethod['type_name']; ?>
                        </span>
                        <span class="shipping-methods__item-alias">
                            (<?php echo $shippingMethod['type_alias']; ?>)
                        </span>
                    </h3>
                    <div class="js-read-more shipping-methods__item-text" <?php echo addQaUniqueIdentifier('page__shipping-methods__read-more-item'); ?>>
                        <?php echo $shippingMethod['full_description']; ?>
                    </div>
                </div>
            </li>
        <?php } ?>
    </ul>
</section>
<section class="shipping-methods">
    <?php
        $practicesGuideData = [
            'title'       => translate('shipping_methods_download_guide_title'),
            'description' => translate('shipping_methods_download_guide_text'),
            'group'       => 'all',
            'image'       => [
                'desktop'    => asset('public/build/images/index/practices-guide/shipping_methods.png'),
                'desktop@2x' => asset('public/build/images/index/practices-guide/shipping_methods@2x.png'),
            ],
        ];

            views('new/home/components/practices_guide_view', ['data' => $practicesGuideData]);
    ?>
</section>
<section class="shipping-methods container-1420">
    <div class="shipping-methods__row">
        <div class="shipping-methods__column">
            <div class="shipping-methods__icon">
                <?php echo widgetGetSvgIcon('question', 75, 75); ?>
            </div>
            <h3 class="shipping-methods__title">
                <?php echo translate('shipping_methods_questions_about_types_title'); ?>
            </h3>
            <p class="shipping-methods__text">
                <?php echo translate('shipping_methods_questions_about_types_text'); ?>
            </p>
            <button
                class="shipping-methods__btn btn btn-primary btn-new18 fancybox.ajax fancyboxValidateModal"
                data-fancybox-href="<?php echo __CURRENT_SUB_DOMAIN_URL . 'contact/popup_forms/contact_us'; ?>"
                data-title="<?php echo translate('shipping_methods_questions_contact_us_title', null, true); ?>"
                data-wrap-class="fancybox-contact-us"
                <?php echo addQaUniqueIdentifier('page__shipping-methods__questions-about-types_contact-us-btn'); ?>
            >
                <?php echo translate('shipping_methods_questions_contact_us_btn'); ?>
            </button>
        </div>
        <div class="shipping-methods__column">
            <div class="shipping-methods__icon">
                <?php echo widgetGetSvgIcon('share', 75, 75); ?>
            </div>
            <h3 class="shipping-methods__title">
                <?php echo translate('shipping_methods_share_this_page_title'); ?>
            </h3>
            <p class="shipping-methods__text">
                <?php echo translate('shipping_methods_share_this_page_text'); ?>
            </p>
            <button
                class="shipping-methods__btn btn btn-primary btn-new18 call-action"
                data-js-action="languages:open-social-modal"
                data-classes="mw-300"
                title="<?php echo translate('general_button_share_text', null, true); ?>"
                <?php echo addQaUniqueIdentifier('page__shipping-methods__share_this-page_share-now-btn'); ?>
            >
                <?php echo translate('shipping_methods_share_btn'); ?>
            </button>
        </div>
    </div>
    </div>
</section>
