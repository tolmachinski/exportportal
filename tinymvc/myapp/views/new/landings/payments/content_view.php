<section class="how-it-works container-center-sm">
    <h2 class="how-it-works__title"><?php echo translate("payments_how_it_works_title"); ?></h2>
    <p class="how-it-works__description"><?php echo translate("payments_how_it_works_description"); ?></p>
    <picture
        class="how-it-works__scheme js-scheme"
        <?php echo addQaUniqueIdentifier("payments__how-it-works")?>
        data-href="<?php echo __SITE_URL . asset("public/build/images/landings/payments/transaction@2x.jpg"); ?>"
    >
        <source
            srcset="<?php echo getLazyImage(575, 340)?>"
            data-srcset="<?php echo asset("public/build/images/landings/payments/transaction-mobile.jpg")?> 1x, <?php echo asset("public/build/images/landings/payments/transaction-mobile@2x.jpg")?> 2x"
            media="(max-width: 575px)"
        >
        <source
            srcset="<?php echo getLazyImage(886, 523)?>"
            data-srcset="<?php echo asset("public/build/images/landings/payments/transaction-tablet.jpg")?> 1x, <?php echo asset("public/build/images/landings/payments/transaction-tablet@2x.jpg")?> 2x"
            media="(max-width: 1199px)"
        >
        <img
            class="js-lazy"
            src="<?php echo getLazyImage(1400, 788)?>"
            data-src="<?php echo asset("public/build/images/landings/payments/transaction.jpg")?>"
            data-srcset="<?php echo asset("public/build/images/landings/payments/transaction.jpg")?> 1x, <?php echo asset("public/build/images/landings/payments/transaction@2x.jpg")?> 2x"
            alt="<?php echo translate("payments_how_it_works_title")?>"
        >
    </picture>
</section>

<section class="gain-access container-center-sm">
    <h2 class="gain-access__title"><?php echo translate("payments_gain_access_title"); ?></h2>
    <p class="gain-access__description"><?php echo translate("payments_gain_access_description"); ?></p>
    <div class="gain-access__steps">
        <div class="gain-access__step">
            <div class="gain-access__step-image gain-access__step-image--left">
                <picture>
                    <source
                        srcset="<?php echo getLazyImage(475, 304)?>"
                        data-srcset="<?php echo asset("public/build/images/landings/payments/access-benefit-1-m.jpg")?>"
                        media="(max-width: 425px)"
                    >
                    <source
                        srcset="<?php echo getLazyImage(690, 300)?>"
                        data-srcset="<?php echo asset("public/build/images/landings/payments/access-benefit-1-xm.jpg")?>"
                        media="(max-width: 575px)"
                    >
                    <source
                        srcset="<?php echo getLazyImage(475, 304)?>"
                        data-srcset="<?php echo asset("public/build/images/landings/payments/access-benefit-1-m.jpg")?>"
                        media="(max-width: 767px)"
                    >
                    <source
                        srcset="<?php echo getLazyImage(456, 358)?>"
                        data-srcset="<?php echo asset("public/build/images/landings/payments/access-benefit-1-t.jpg")?>"
                        media="(max-width: 1199px)"
                    >
                    <img
                        class="js-lazy"
                        src="<?php echo getLazyImage(370, 185)?>"
                        data-src="<?php echo asset("public/build/images/landings/payments/access-benefit-1.jpg")?>"
                        alt="<?php echo translate("payments_gain_access_step_one_title"); ?>"
                    >
                </picture>
                <div class="gain-access__step-label"><span>1</span></div>
            </div>
            <h3 class="gain-access__step-title"><?php echo translate("payments_gain_access_step_one_title"); ?></h3>
            <p class="gain-access__step-description gain-access__step-description--first"><?php echo translate("payments_gain_access_step_one_description"); ?></p>
            <?php if (logged_in()) { ?>
                <a class="gain-access__step-link btn btn-primary js-require-logout-systmess" href="javascript:void(0)"><?php echo translate("payments_gain_access_step_one_link")?></a>
            <?php } else { ?>
                <a class="gain-access__step-link btn btn-primary" href="<?php echo __SITE_URL?>register"><?php echo translate("payments_gain_access_step_one_link")?></a>
            <?php } ?>
        </div>
        <div class="gain-access__step">
            <div class="gain-access__step-image">
                <picture>
                <source
                        srcset="<?php echo getLazyImage(475, 304)?>"
                        data-srcset="<?php echo asset("public/build/images/landings/payments/access-benefit-2-m.jpg")?>"
                        media="(max-width: 425px)"
                    >
                    <source
                        srcset="<?php echo getLazyImage(690, 300)?>"
                        data-srcset="<?php echo asset("public/build/images/landings/payments/access-benefit-2-xm.jpg")?>"
                        media="(max-width: 575px)"
                    >
                    <source
                        srcset="<?php echo getLazyImage(475, 304)?>"
                        data-srcset="<?php echo asset("public/build/images/landings/payments/access-benefit-2-m.jpg")?>"
                        media="(max-width: 767px)"
                    >
                    <source
                        srcset="<?php echo getLazyImage(456, 358)?>"
                        data-srcset="<?php echo asset("public/build/images/landings/payments/access-benefit-2-t.jpg")?>"
                        media="(max-width: 1199px)"
                    >
                    <img
                        class="js-lazy"
                        src="<?php echo getLazyImage(370, 185)?>"
                        data-src="<?php echo asset("public/build/images/landings/payments/access-benefit-2.jpg")?>"
                        alt="<?php echo translate("payments_gain_access_step_two_title"); ?>"
                    >
                </picture>
                <div class="gain-access__step-label"><span>2</span></div>
            </div>
            <h3 class="gain-access__step-title"><?php echo translate("payments_gain_access_step_two_title"); ?></h3>
            <p class="gain-access__step-description"><?php echo translate("payments_gain_access_step_two_description"); ?></p>
        </div>
        <div class="gain-access__step">
            <div class="gain-access__step-image gain-access__step-image--right">
                <picture>
                <source
                        srcset="<?php echo getLazyImage(475, 304)?>"
                        data-srcset="<?php echo asset("public/build/images/landings/payments/access-benefit-3-m.jpg")?>"
                        media="(max-width: 425px)"
                    >
                    <source
                        srcset="<?php echo getLazyImage(690, 300)?>"
                        data-srcset="<?php echo asset("public/build/images/landings/payments/access-benefit-3-xm.jpg")?>"
                        media="(max-width: 575px)"
                    >
                    <source
                        srcset="<?php echo getLazyImage(475, 304)?>"
                        data-srcset="<?php echo asset("public/build/images/landings/payments/access-benefit-3-m.jpg")?>"
                        media="(max-width: 767px)"
                    >
                    <source
                        srcset="<?php echo getLazyImage(456, 358)?>"
                        data-srcset="<?php echo asset("public/build/images/landings/payments/access-benefit-3-t.jpg")?>"
                        media="(max-width: 1199px)"
                    >
                    <img
                        class="js-lazy"
                        src="<?php echo getLazyImage(370, 185)?>"
                        data-src="<?php echo asset("public/build/images/landings/payments/access-benefit-3.jpg")?>"
                        alt="<?php echo translate("payments_gain_access_step_three_title"); ?>"
                    >
                </picture>
                <div class="gain-access__step-label"><span>3</span></div>
            </div>
            <h3 class="gain-access__step-title"><?php echo translate("payments_gain_access_step_three_title"); ?></h3>
            <p class="gain-access__step-description"><?php echo translate("payments_gain_access_step_three_description"); ?></p>
        </div>
    </div>
</section>

<section class="become-partner container-center-sm">
    <div class="become-partner__image">
        <picture>
            <source
                srcset="<?php echo getLazyImage(510, 442)?>"
                data-srcset="<?php echo asset("public/build/images/landings/payments/become-partner-m.jpg")?>"
                media="(max-width: 425px)"
            >
            <source
                srcset="<?php echo getLazyImage(920, 400)?>"
                data-srcset="<?php echo asset("public/build/images/landings/payments/become-partner-xm.jpg")?>"
                media="(max-width: 767px)"
            >
            <source
                srcset="<?php echo getLazyImage(678, 1152)?>"
                data-srcset="<?php echo asset("public/build/images/landings/payments/become-partner-t.jpg")?>"
                media="(max-width: 1100px)"
            >
            <img
                class="js-lazy"
                src="<?php echo getLazyImage(810, 702)?>"
                data-src="<?php echo asset("public/build/images/landings/payments/become-partner.jpg")?>"
                alt="<?php echo translate("payments_become_partner_title"); ?>">
        </picture>
    </div>
    <div class="become-partner__info">
        <h2 class="become-partner__title"><?php echo translate("payments_become_partner_title"); ?></h2>
        <p class="become-partner__description"><?php echo translate("payments_become_partner_description"); ?></p>
        <ul class="become-partner__benefits">
            <li>
                <?php echo widgetGetSvgIcon("ok-circle", 20, 20); ?>
                <?php echo translate("payments_become_partner_benefit_one"); ?>
            </li>
            <li>
                <?php echo widgetGetSvgIcon("ok-circle", 20, 20); ?>
                <?php echo translate("payments_become_partner_benefit_two"); ?>
            </li>
            <li>
                <?php echo widgetGetSvgIcon("ok-circle", 20, 20); ?>
                <?php echo translate("payments_become_partner_benefit_three"); ?>
            </li>
            <li>
                <?php echo widgetGetSvgIcon("ok-circle", 20, 20); ?>
                <?php echo translate("payments_become_partner_benefit_four"); ?>
            </li>
        </ul>
    </div>
</section>

<section class="still-questions container-center-sm">
    <picture class="still-questions__image">
        <source
            srcset="<?php echo getLazyImage(510, 440)?>"
            data-srcset="<?php echo asset("public/build/images/landings/payments/questions-m.jpg")?>"
            media="(max-width: 425px)"
        >
        <source
            srcset="<?php echo getLazyImage(920, 400)?>"
            data-srcset="<?php echo asset("public/build/images/landings/payments/questions-xm.jpg")?>"
            media="(max-width: 767px)"
        >
        <source
            srcset="<?php echo getLazyImage(909, 300)?>"
            data-srcset="<?php echo asset("public/build/images/landings/payments/questions-t.jpg")?>"
            media="(max-width: 1199px)"
        >
        <img
            class="js-lazy"
            src="<?php echo getLazyImage(810, 300)?>"
            data-src="<?php echo asset("public/build/images/landings/payments/questions.jpg")?>"
            alt="<?php echo translate("payments_question_title")?>"
        >
    </picture>
    <div class="still-questions__info">
        <h2 class="still-questions__title"><?php echo translate("payments_question_title"); ?></h2>
        <p class="still-questions__description">
            <?php
                echo translate(
                    "payments_question_description",
                    ["[[EMAIL]]" => "<a href=\"mailto:" . config('partnership_email') ."\">" . config('partnership_email') ."</a>"]
                );
            ?>
        </p>
        <a
            class="still-questions__link btn btn-primary fancybox.ajax fancyboxValidateModal"
            data-wrap-class="fancybox-contact-us"
            data-title="Contact us"
            href="<?php echo __SITE_URL?>contact/popup_forms/contact_us"
            <?php echo addQaUniqueIdentifier("questions__contact-us")?>
        >
            <?php echo translate("payments_question_contact"); ?>
        </a>
    </div>
</section>

<section class="what-is-ep">
    <picture class="what-is-ep__image">
        <source
            srcset="<?php echo getLazyImage(510, 440)?>"
            data-srcset="<?php echo asset("public/build/images/landings/payments/what-is-ep-m.jpg")?>"
            media="(max-width: 425px)"
        >
        <source
            srcset="<?php echo getLazyImage(920, 400)?>"
            data-srcset="<?php echo asset("public/build/images/landings/payments/what-is-ep-xm.jpg")?>"
            media="(max-width: 767px)"
        >
        <source
            srcset="<?php echo getLazyImage(909, 300)?>"
            data-srcset="<?php echo asset("public/build/images/landings/payments/what-is-ep-t.jpg")?>"
            media="(max-width: 1199px)"
        >
        <img
            class="js-lazy"
            src="<?php echo getLazyImage(810, 300)?>"
            data-src="<?php echo asset("public/build/images/landings/payments/what-is-ep.jpg")?>"
            alt="<?php echo translate("payments_what_is_ep_title")?>"
        >
    </picture>
    <div class="container-center-sm">
        <h2 class="what-is-ep__title"><?php echo translate("payments_what_is_ep_title"); ?></h2>
        <p class="what-is-ep__description"><?php echo translate("payments_what_is_ep_description"); ?></p>
        <a class="what-is-ep__link btn btn-primary" href="<?php echo __SITE_URL?>learn_more"><?php echo translate("payments_what_is_ep_link"); ?></a>
    </div>
</section>

<?php
    encoreEntryScriptTags("payments_page");
?>
