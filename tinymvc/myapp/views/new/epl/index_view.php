<?php
    $aboutList = [
        [
            "icon"  => widgetGetSvgIconEpl("blockchain", 30),
            "ttl"   => translate('epl_home_about_list_element_1_ttl'),
            "text"  => translate('epl_home_about_list_element_1_text'),
        ],
        [
            "icon"  => widgetGetSvgIconEpl("credit-card-safe", 33),
            "ttl"   => translate('epl_home_about_list_element_2_ttl'),
            "text"  => translate('epl_home_about_list_element_2_text'),
        ],
        [
            "icon"  => widgetGetSvgIconEpl("press", 27),
            "ttl"   => translate('epl_home_about_list_element_3_ttl'),
            "text"  => translate('epl_home_about_list_element_3_text'),
        ],
    ];
?>

<div id="about">
    <div
        id="js-epl-about-b"
        class="epl-about-b"
    >
        <div class="epl-about-b__inner container-center">
            <div class="epl-desc-text-b">
                <h2 class="epl-desc-text-b__ttl"><?php echo translate('epl_home_about_ttl'); ?></h2>
                <p class="epl-desc-text-b__text"><?php echo translate('epl_home_about_text_1'); ?></p>
                <p class="epl-desc-text-b__text"><?php echo translate('epl_home_about_text_2'); ?></p>
                <a
                    class="btn btn-outline-primary btn-lg btn-mnw-200"
                    href="<?php echo __SITE_URL . 'about'; ?>"
                    <?php echo addQaUniqueIdentifier("epl-about-b__btn")?>
                ><?php echo translate('epl_home_about_btn'); ?></a>
            </div>

            <div class="epl-about-b__image">
                <picture>
                    <source
                        media="(max-width: 575px)"
                        srcset="<?php echo getLazyImage(545, 481);?>"
                        data-srcset="<?php echo asset("public/build/images/epl/about-img-mobile.jpg"); ?>"
                    >
                    <source
                        media="(max-width: 1024px)"
                        srcset="<?php echo getLazyImage(737, 650);?>"
                        data-srcset="<?php echo asset("public/build/images/epl/about-img-tablet.jpg"); ?>"
                    >
                    <source
                        media="(max-width: 1200px)"
                        srcset="<?php echo getLazyImage(263, 500);?>"
                        data-srcset="<?php echo asset("public/build/images/epl/about-img-1200.jpg"); ?>"
                    >
                    <img
                        class="image js-lazy"
                        width="360"
                        height="544"
                        src="<?php echo getLazyImage(360, 544);?>"
                        data-src="<?php echo asset("public/build/images/epl/about-img.jpg"); ?>"
                        alt="Shipping portal"
                    >
                </picture>
            </div>

            <ul class="epl-about-list">
                <?php foreach($aboutList as $aboutListItem){?>
                    <li class="epl-about-list__item">
                        <div class="epl-about-list__icon"><?php echo $aboutListItem["icon"]; ?></div>
                        <div class="epl-about-list__ttl"><?php echo $aboutListItem["ttl"];?></div>
                        <div class="epl-about-list__text"><?php echo $aboutListItem["text"];?></div>
                    </li>
                <?php }?>
            </ul>
        </div>
    </div>
</div>

<?php
    $toolsList = [
        [
            "icon"  => widgetGetSvgIconEpl("mobile-secure", 35),
            "ttl"   => translate('epl_home_tools_list_element_1_ttl'),
            "text"  => translate('epl_home_tools_list_element_1_text'),
        ],
        [
            "icon"  => widgetGetSvgIconEpl("browser", 41),
            "ttl"   => translate('epl_home_tools_list_element_2_ttl'),
            "text"  => translate('epl_home_tools_list_element_2_text'),
        ],
        [
            "icon"  => widgetGetSvgIconEpl("document", 32),
            "ttl"   => translate('epl_home_tools_list_element_3_ttl'),
            "text"  => translate('epl_home_tools_list_element_3_text'),
        ],
        [
            "icon"  => widgetGetSvgIconEpl("browser-secure", 47),
            "ttl"   => translate('epl_home_tools_list_element_4_ttl'),
            "text"  => translate('epl_home_tools_list_element_4_text'),
        ],
        [
            "icon"  => widgetGetSvgIconEpl("bell-clock", 40),
            "ttl"   => translate('epl_home_tools_list_element_5_ttl'),
            "text"  => translate('epl_home_tools_list_element_5_text'),
        ],
        [
            "icon"  => widgetGetSvgIconEpl("low", 40),
            "ttl"   => translate('epl_home_tools_list_element_6_ttl'),
            "text"  => translate('epl_home_tools_list_element_6_text'),
        ],
    ];
?>

<div id="tools">
    <div
        id="js-epl-tools-b"
        class="epl-tools-b"
    >
        <div class="container-center">
            <div class="epl-desc-text-b epl-desc-text-b--tac">
                <h2 class="epl-desc-text-b__ttl"><?php echo translate('epl_home_tools_ttl'); ?></h2>
                <p class="epl-desc-text-b__text"><?php echo translate('epl_home_tools_text_1'); ?></p>
            </div>

            <ul class="epl-tools-list">
                <?php foreach($toolsList as $toolsListItem){?>
                    <li class="epl-tools-list__item">
                        <div class="epl-tools-list__inner">
                            <div class="epl-tools-list__icon"><?php echo $toolsListItem["icon"]; ?></div>
                            <div class="epl-tools-list__ttl"><?php echo $toolsListItem["ttl"];?></div>
                            <div class="epl-tools-list__text"><?php echo $toolsListItem["text"];?></div>
                        </div>
                    </li>
                <?php }?>
            </ul>
        </div>
    </div>
</div>

<?php
    $faqList = [
        [
            "ttl"   => translate('epl_home_faq_list_element_1_ttl'),
            "text"  => translate('epl_home_faq_list_element_1_text'),
        ],
        [
            "ttl"   => translate('epl_home_faq_list_element_2_ttl'),
            "text"  => translate('epl_home_faq_list_element_2_text'),
        ],
        [
            "ttl"   => translate('epl_home_faq_list_element_3_ttl'),
            "text"  => translate('epl_home_faq_list_element_3_text'),
        ],
    ];
?>

<div id="faq">
    <div
        id="js-epl-faq-b"
        class="epl-faq-b"
    >
        <div class="container-center">
            <div class="epl-desc-text-b epl-desc-text-b--mw-650 epl-desc-text-b--tac">
                <h2 class="epl-desc-text-b__ttl"><?php echo translate('epl_home_faq_ttl'); ?></h2>
                <p class="epl-desc-text-b__text"><?php echo translate('epl_home_faq_text_1'); ?></p>
            </div>

            <ul class="epl-faq-list">
                <?php foreach($faqList as $faqKey => $faqListItem){?>
                    <li class="js-accordion-item epl-faq-list__item" <?php echo addQaUniqueIdentifier("epl-faq-list__btn-{$faqKey}"); ?>>
                        <div class="epl-faq-list__ttl-wrap">
                            <h3 class="epl-faq-list__ttl"><?php echo $faqListItem["ttl"];?></h3>
                            <div class="epl-faq-list__btn">
                                <?php echo widgetGetSvgIconEpl("plus", 14, 0, "epl-faq-list__plus"); ?>
                                <?php echo widgetGetSvgIconEpl("minus", 14, 0, "epl-faq-list__minus"); ?>
                            </div>
                        </div>
                        <div class="epl-faq-list__text js-accordion-text-wr"><?php echo $faqListItem["text"];?></div>
                    </li>
                <?php }?>
            </ul>

            <div class="epl-faq-b__center">
                <a
                    class="btn btn-outline-primary btn-lg btn-mnw-200"
                    href="<?php echo __SITE_URL . 'faq/all'; ?>"
                    <?php echo addQaUniqueIdentifier("epl-faq-b__btn")?>
                ><?php echo translate('epl_home_faq_btn'); ?></a>
            </div>
        </div>
    </div>
</div>

<?php
    $feedbacksList = [
        [
            "text"      => translate('epl_home_feedbacks_list_element_1_text'),
            "avatar"    => asset("public/build/images/epl/feedback/user1.jpg"),
            "name"      => "Joe MacIntyre",
            "country"   => "United States of America",
            "flag"      => getCountryFlag('United States of America'),
        ],
        [
            "text"      => translate('epl_home_feedbacks_list_element_2_text'),
            "avatar"    => asset("public/build/images/epl/feedback/user2.jpg"),
            "name"      => "Amandeep Patil",
            "country"   => "India",
            "flag"      => getCountryFlag('India'),
        ],
        [
            "text"      => translate('epl_home_feedbacks_list_element_3_text'),
            "avatar"    => asset("public/build/images/epl/feedback/user3.jpg"),
            "name"      => "Oliver Davies",
            "country"   => "United Kingdom",
            "flag"      => getCountryFlag('United Kingdom'),
        ],
    ];
?>

<div id="testimonials">
    <div
        id="js-epl-testimonials-b"
        class="epl-testimonials-b"
    >
        <div class="container-center">
            <div class="epl-desc-text-b epl-desc-text-b--mw-650 epl-desc-text-b--tac epl-desc-text-b--white">
                <h2 class="epl-desc-text-b__ttl"><?php echo translate('epl_home_feedbacks_ttl'); ?></h2>
                <p class="epl-desc-text-b__text"><?php echo translate('epl_home_feedbacks_text_1'); ?></p>
            </div>

            <div id="js-epl-testimonials-slider" class="epl-testimonials-list">
                <?php foreach($feedbacksList as $feedbacksListItem){?>
                    <div class="epl-testimonials-list__item">
                        <div class="epl-testimonials-list__inner">
                            <div class="epl-testimonials-list__text"><?php echo $feedbacksListItem["text"];?></div>
                            <div class="epl-testimonials-list__user">
                                <div class="epl-testimonials-list__user-img">
                                    <img
                                        class="image js-lazy"
                                        width="60"
                                        height="60"
                                        src="<?php echo getLazyImage(60, 60);?>"
                                        data-src="<?php echo $feedbacksListItem["avatar"];?>"
                                        alt="<?php echo $feedbacksListItem["name"];?>"
                                    >
                                </div>
                                <div class="epl-testimonials-list__user-info">
                                    <div class="epl-testimonials-list__user-name">
                                        <?php echo $feedbacksListItem["name"];?>
                                    </div>
                                    <div class="epl-testimonials-list__user-country">
                                        <img
                                            class="image js-lazy"
                                            width="24"
                                            height="24"
                                            src="<?php echo getLazyImage(24, 24);?>"
                                            data-src="<?php echo $feedbacksListItem["flag"];?>"
                                            alt="<?php echo $feedbacksListItem["country"];?>"
                                        >
                                        <?php echo $feedbacksListItem["country"];?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php }?>
            </div>
        </div>

        <picture class="epl-testimonials-b__bg">
            <source
                media="(max-width: 425px)"
                srcset="<?php echo getLazyImage(320, 691);?>"
                data-srcset="<?php echo asset("public/build/images/epl/about-us-bg-mobile.jpg"); ?>"
            >
            <source
                media="(max-width: 768px)"
                srcset="<?php echo getLazyImage(768, 663);?>"
                data-srcset="<?php echo asset("public/build/images/epl/about-us-bg-tablet.jpg"); ?>"
            >
            <img
                class="image js-lazy"
                width="1920"
                height="683"
                src="<?php echo getLazyImage(1920, 683);?>"
                data-src="<?php echo asset("public/build/images/epl/about-us-bg.jpg"); ?>"
                alt="Feedbacks"
            >
        </picture>
    </div>
</div>

<?php
    $partnersList = [
        [
            "logo"      => asset("public/build/images/epl/partners/exima.png"),
            "width"     => 140,
            "height"    => 47,
            "ttl"       => "Exima",
            "link"      => "https://exima.com/",
        ],
        [
            "logo"      => asset("public/build/images/epl/partners/fedex.png"),
            "width"     => 101,
            "height"    => 31,
            "ttl"       => "Fedex",
            "link"      => "https://www.fedex.com/apps/fedextrack/?action=track",
        ],
        [
            "logo"      => asset("public/build/images/epl/partners/paypal.png"),
            "width"     => 130,
            "height"    => 35,
            "ttl"       => "PayPal",
            "link"      => "https://www.paypal.com/us/home",
        ],
        [
            "logo"      => asset("public/build/images/epl/partners/ups.png"),
            "width"     => 36,
            "height"    => 43,
            "ttl"       => "UPS",
            "link"      => "https://www.ups.com/",
        ],
        [
            "logo"      => asset("public/build/images/epl/partners/lso.png"),
            "width"     => 140,
            "height"    => 30,
            "ttl"       => "LSO",
            "link"      => "https://www.lso.com/",
        ],
        [
            "logo"      => asset("public/build/images/epl/partners/gls.png"),
            "width"     => 130,
            "height"    => 35,
            "ttl"       => "GLS",
            "link"      => "https://www.gso.com/",
        ],
        [
            "logo"      => asset("public/build/images/epl/partners/usps.png"),
            "width"     => 187,
            "height"    => 33,
            "ttl"       => "USPS",
            "link"      => "https://www.usps.com/",
        ],
    ];
?>

<div id="partners">
    <div
        id="js-epl-partners-b"
        class="epl-partners-b"
    >
        <div class="container-center">
            <div class="epl-desc-text-b epl-desc-text-b--mw-650 epl-desc-text-b--tac">
                <h2 class="epl-desc-text-b__ttl"><?php echo translate('epl_home_partners_ttl'); ?></h2>
                <p class="epl-desc-text-b__text"><?php echo translate('epl_home_partners_text_1'); ?></p>
            </div>

            <ul class="epl-partners-list">
                <?php foreach($partnersList as $partnerKey => $partnersListItem){?>
                    <li class="epl-partners-list__item">
                        <a
                            class="link"
                            href="<?php echo $partnersListItem["link"];?>"
                            target="_blank"
                            rel="nofollow noopener"
                            <?php echo addQaUniqueIdentifier("epl-partners-b__btn-{$partnerKey}")?>
                        >
                            <img
                                class="image js-lazy"
                                width="<?php echo $partnersListItem["width"];?>"
                                height="<?php echo $partnersListItem["height"];?>"
                                src="<?php echo getLazyImage($partnersListItem["width"], $partnersListItem["height"]);?>"
                                data-src="<?php echo $partnersListItem["logo"];?>"
                                alt="<?php echo $partnersListItem["ttl"];?>"
                            >
                        </a>
                    </li>
                <?php }?>
            </ul>
        </div>
    </div>
</div>

<?php views('new/epl/get_started_view'); ?>
