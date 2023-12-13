<!-- Benefits block -->
<section class="epu-section benefits-of-epu">
    <div class="epu-section__heading benefits-of-epu__heading">
        <h2 class="epu-section__title benefits-of-epu__title">
            <?php echo translate("university_benefits_is_epu_title"); ?>
        </h2>
        <p class="epu-section__text benefits-of-epu__subtitle">
            <?php echo translate("university_benefits_is_epu_subtitle"); ?>
        </p>
    </div>
    <div class="benefits-of-epu__row">
        <div class="benefits-of-epu__image">
            <picture>
                <source srcset="<?php echo getLazyImage(425, 250) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/benefit-1-xs.jpg") ?>" media="(max-width: 426px)">
                <source srcset="<?php echo getLazyImage(592, 390) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/benefit-1-t.jpg") ?>" media="(max-width: 992px)">
                <img class="js-lazy" src="<?php echo getLazyImage(960, 390) ?>" data-src="<?php echo asset("public/build/images/landings/university/benefit-1.jpg") ?>" alt="<?php echo translate("university_benefits_is_epu_info_title_one"); ?>">
            </picture>
        </div>
        <div class="benefits-of-epu__info">
            <h3 class="benefits-of-epu__info-title">
                <?php echo translate("university_benefits_is_epu_info_title_one"); ?>
            </h3>
            <ul class="benefits-of-epu__list">
                <li>
                    <?php echo widgetGetSvgIcon("ok-circle", 20, 20); ?>
                    <?php echo translate("university_benefits_is_epu_info_list_one"); ?>
                </li>
                <li>
                    <?php echo widgetGetSvgIcon("ok-circle", 20, 20); ?>
                    <?php echo translate("university_benefits_is_epu_info_list_two"); ?>
                </li>
                <li>
                    <?php echo widgetGetSvgIcon("ok-circle", 20, 20); ?>
                    <?php echo translate("university_benefits_is_epu_info_list_three"); ?>
                </li>
            </ul>
        </div>
    </div>
    <div class="benefits-of-epu__row benefits-of-epu__row--reverse">
        <div class="benefits-of-epu__image">
            <picture>
                <source srcset="<?php echo getLazyImage(375, 250) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/benefit-2-xs.jpg") ?>" media="(max-width: 376px)">
                <source srcset="<?php echo getLazyImage(425, 250) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/benefit-2-m.jpg") ?>" media="(max-width: 426px)">
                <source srcset="<?php echo getLazyImage(495, 390) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/benefit-2-t.jpg") ?>" media="(max-width: 992px)">
                <img class="js-lazy" src="<?php echo getLazyImage(960, 390) ?>" data-src="<?php echo asset("public/build/images/landings/university/benefit-2.jpg") ?>" alt="<?php echo translate("university_benefits_is_epu_info_title_two"); ?>">
            </picture>
        </div>
        <div class="benefits-of-epu__info">
            <h3 class="benefits-of-epu__info-title">
                <?php echo translate("university_benefits_is_epu_info_title_two"); ?>
            </h3>
            <ul class="benefits-of-epu__list">
                <li>
                    <?php echo widgetGetSvgIcon("ok-circle", 20, 20); ?>
                    <?php echo translate("university_benefits_is_epu_info_list_four"); ?>
                </li>
                <li>
                    <?php echo widgetGetSvgIcon("ok-circle", 20, 20); ?>
                    <?php echo translate("university_benefits_is_epu_info_list_five"); ?>
                </li>
                <li>
                    <?php echo widgetGetSvgIcon("ok-circle", 20, 20); ?>
                    <?php echo translate("university_benefits_is_epu_info_list_six"); ?>
                </li>
            </ul>
        </div>
    </div>
    <div class="benefits-of-epu__row">
        <div class="benefits-of-epu__image">
            <picture>
                <source srcset="<?php echo getLazyImage(375, 250) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/benefit-3-xs.jpg") ?>" media="(max-width: 376px)">
                <source srcset="<?php echo getLazyImage(425, 250) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/benefit-3-m.jpg") ?>" media="(max-width: 426px)">
                <source srcset="<?php echo getLazyImage(495, 390) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/benefit-3-t.jpg") ?>" media="(max-width: 992px)">
                <img class="js-lazy" src="<?php echo getLazyImage(960, 390) ?>" data-src="<?php echo asset("public/build/images/landings/university/benefit-3.jpg") ?>" alt="<?php echo translate("university_benefits_is_epu_info_title_three"); ?>">
            </picture>
        </div>
        <div class="benefits-of-epu__info">
            <h3 class="benefits-of-epu__info-title">
                <?php echo translate("university_benefits_is_epu_info_title_three"); ?>
            </h3>
            <ul class="benefits-of-epu__list">
                <li>
                    <?php echo widgetGetSvgIcon("ok-circle", 20, 20); ?>
                    <?php echo translate("university_benefits_is_epu_info_list_seven"); ?>
                </li>
                <li>
                    <?php echo widgetGetSvgIcon("ok-circle", 20, 20); ?>
                    <?php echo translate("university_benefits_is_epu_info_list_eight"); ?>
                </li>
                <li>
                    <?php echo widgetGetSvgIcon("ok-circle", 20, 20); ?>
                    <?php echo translate("university_benefits_is_epu_info_list_nine"); ?>
                </li>
            </ul>
        </div>
    </div>
</section>

<!-- Webinar block -->
<section class="epu-section ep-webinar">
    <div class="epu-section__row">
        <div class="epu-section__info">
            <h2 class="epu-section__title">
                <?php echo translate("university_ep_webinar_title"); ?>
            </h2>
            <p class="epu-section__text">
                <?php echo translate("university_ep_webinar_subtitle"); ?>
            </p>
            <a class="epu-section__link btn btn-primary" href="https://app.smartsheet.com/b/form/8a105d3cecb74fb7956c56015bcfe117">
                <?php echo translate("university_ep_webinar_link"); ?>
            </a>
        </div>
        <div class="epu-section__image">
            <picture>
                <source srcset="<?php echo getLazyImage(426, 333) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/webinar-xs.jpg") ?>" media="(max-width: 426px)">
                <source srcset="<?php echo getLazyImage(570, 327) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/webinar-m.jpg") ?>" media="(max-width: 570px)">
                <source srcset="<?php echo getLazyImage(400, 409) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/webinar-t.jpg") ?>" media="(max-width: 992px)">
                <source srcset="<?php echo getLazyImage(555, 409) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/webinar-l.jpg") ?>" media="(max-width: 1366px)">
                <img class="js-lazy" src="<?php echo getLazyImage(805, 362) ?>" data-src="<?php echo asset("public/build/images/landings/university/webinar.jpg") ?>" alt="<?php echo translate("university_ep_webinar_title"); ?>">
            </picture>
        </div>
    </div>
</section>

<!-- Why join EPU block -->
<section class="epu-section why-join-epu">
    <div class="epu-section__heading why-join-epu__heading">
        <h2 class="epu-section__title why-join-epu__title">
            <?php echo translate("university_why_join_epu_title"); ?>
        </h2>
        <p class="epu-section__text why-join-epu__subtitle">
            <?php echo translate("university_why_join_epu_subtitle"); ?>
        </p>
    </div>
    <div class="why-join-epu__row">
        <div class="why-join-epu__image">
            <picture>
                <source srcset="<?php echo getLazyImage(320, 250) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/why-join-epu-xs.jpg") ?>" media="(max-width: 426px)">
                <source srcset="<?php echo getLazyImage(320, 250) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/why-join-epu-m.jpg") ?>" media="(max-width: 767px)">
                <source srcset="<?php echo getLazyImage(372, 908) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/why-join-epu-t.jpg") ?>" media="(max-width: 991px)">
                <source srcset="<?php echo getLazyImage(495, 908) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/why-join-epu-l.jpg") ?>" media="(max-width: 1300px)">
                <img class="js-lazy" src="<?php echo getLazyImage(1035, 908) ?>" data-src="<?php echo asset("public/build/images/landings/university/why-join-epu.jpg") ?>" alt="<?php echo translate("university_why_join_epu_title"); ?>">
            </picture>
        </div>
        <div class="why-join-epu__info">
            <div class="why-join-epu__item">
                <div class="why-join-epu__item-icon"><?php echo $icons['webinar']; ?></div>
                <h3 class="why-join-epu__item-title">
                    <?php echo translate("university_why_join_epu_item_title_one"); ?>
                </h3>
                <p class="why-join-epu__item-text epu-section__text">
                    <?php echo translate("university_why_join_epu_item_text_one"); ?>
                </p>
            </div>
            <div class="why-join-epu__item">
                <div class="why-join-epu__item-icon"><?php echo $icons['education']; ?></div>
                <h3 class="why-join-epu__item-title">
                    <?php echo translate("university_why_join_epu_item_title_two"); ?>
                </h3>
                <p class="why-join-epu__item-text epu-section__text">
                    <?php echo translate("university_why_join_epu_item_text_two"); ?>
                </p>
            </div>
            <div class="why-join-epu__item">
                <div class="why-join-epu__item-icon"><?php echo $icons['content']; ?></div>
                <h3 class="why-join-epu__item-title">
                    <?php echo translate("university_why_join_epu_item_title_three"); ?>
                </h3>
                <p class="why-join-epu__item-text epu-section__text">
                    <?php echo translate("university_why_join_epu_item_text_three"); ?>
                </p>
            </div>
            <div class="why-join-epu__item">
                <div class="why-join-epu__item-icon"><?php echo $icons['articles']; ?></div>
                <h3 class="why-join-epu__item-title">
                    <?php echo translate("university_why_join_epu_item_title_four"); ?>
                </h3>
                <p class="why-join-epu__item-text epu-section__text">
                    <?php echo translate("university_why_join_epu_item_text_four"); ?>
                </p>
            </div>
            <div class="why-join-epu__item">
                <div class="why-join-epu__item-icon"><?php echo $icons['community']; ?></div>
                <h3 class="why-join-epu__item-title">
                    <?php echo translate("university_why_join_epu_item_title_five"); ?>
                </h3>
                <p class="why-join-epu__item-text epu-section__text">
                    <?php echo translate("university_why_join_epu_item_text_five"); ?>
                </p>
            </div>
            <div class="why-join-epu__item">
                <div class="why-join-epu__item-icon"><?php echo $icons['support']; ?></div>
                <h3 class="why-join-epu__item-title">
                    <?php echo translate("university_why_join_epu_item_title_six"); ?>
                </h3>
                <p class="why-join-epu__item-text epu-section__text">
                    <?php echo translate("university_why_join_epu_item_text_six"); ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Seminar block -->
<section class="epu-section ep-seminar">
    <div class="epu-section__row">
        <div class="epu-section__info ep-seminar__info">
            <h2 class="epu-section__title">
                <?php echo translate("university_ep_seminar_title"); ?>
            </h2>
            <p class="epu-section__text">
                <?php echo translate("university_ep_seminar_subtitle"); ?>
            </p>
            <a class="epu-section__link btn btn-primary" href="https://app.smartsheet.com/b/form/2a5351d0f9ee4a419ab19d48de12e809">
                <?php echo translate("university_ep_seminar_link"); ?>
            </a>
        </div>
        <div class="epu-section__image">
            <picture>
                <source srcset="<?php echo getLazyImage(426, 333) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/seminar-xs.jpg") ?>" media="(max-width: 426px)">
                <source srcset="<?php echo getLazyImage(570, 327) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/seminar-m.jpg") ?>" media="(max-width: 570px)">
                <source srcset="<?php echo getLazyImage(495, 365) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/seminar-t.jpg") ?>" media="(max-width: 991px)">
                <source srcset="<?php echo getLazyImage(495, 365) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/seminar-l.jpg") ?>" media="(max-width: 1365px)">
                <img class="js-lazy" src="<?php echo getLazyImage(805, 362) ?>" data-src="<?php echo asset("public/build/images/landings/university/seminar.jpg") ?>" alt="<?php echo translate("university_ep_seminar_title"); ?>">
            </picture>
        </div>
    </div>
</section>

<!-- What-looking-for -->
<section class="epu-section what-looking-for">
    <div class="epu-section__row what-looking-for__row">
        <div class="what-looking-for__image">
            <picture>
                <source srcset="<?php echo getLazyImage(426, 333) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/what-looking-for-xs.jpg") ?>" media="(max-width: 426px)">
                <source srcset="<?php echo getLazyImage(570, 327) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/what-looking-for-m.jpg") ?>" media="(max-width: 570px)">
                <source srcset="<?php echo getLazyImage(485, 428) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/what-looking-for-t.jpg") ?>" media="(max-width: 991px)">
                <source srcset="<?php echo getLazyImage(712, 636) ?>" data-srcset="<?php echo asset("public/build/images/landings/university/what-looking-for-l.jpg") ?>" media="(max-width: 1365px)">
                <img class="js-lazy" src="<?php echo getLazyImage(805, 362) ?>" data-src="<?php echo asset("public/build/images/landings/university/what-looking-for.jpg") ?>" alt="<?php echo translate("university_what_looking_for_title"); ?>">
            </picture>
        </div>
        <div class="epu-section__info what-looking-for__info">
            <h2 class="epu-section__title">
                <?php echo translate("university_what_looking_for_title"); ?>
            </h2>
            <p class="epu-section__text">
                <?php echo translate("university_what_looking_for_subtitle"); ?>
            </p>
            <a class="epu-section__link btn btn-primary" href="https://app.smartsheet.com/b/form/9849d38ec11949caa92697d34363fe92">
                <?php echo translate("university_what_looking_for_link"); ?>
            </a>
        </div>
    </div>
</section>

<!-- What is EP -->
<section class="what-is-ep epu-section footer-connect">
    <div class="what-is-ep__content">
        <h2 class="what-is-ep__title epu-section__title">
            <?php echo translate("university_what_is_ep_title"); ?>
        </h2>
        <h3 class="what-is-ep__description epu-section__text">
            <?php echo translate("university_what_is_ep_subtitle"); ?>
        </h3>
        <a class="what-is-ep__link epu-section__link btn btn-primary" href="https://www.exportportal.com/learn_more">
            <?php echo translate("university_what_is_ep_link"); ?>
        </a>
    </div>
    <div class="what-is-ep__background">
        <picture>
            <source srcset="<?php echo getLazyImage(575, 503);?>" data-srcset="<?php echo asset("public/build/images/landings/university/what-is-ep-m.jpg") ?>" media="(max-width: 575px)">
            <source srcset="<?php echo getLazyImage(768, 470);?>" data-srcset="<?php echo asset("public/build/images/landings/university/what-is-ep-t.jpg") ?>" media="(max-width: 768px)">
            <source srcset="<?php echo getLazyImage(991, 470);?>" data-srcset="<?php echo asset("public/build/images/landings/university/what-is-ep-l.jpg") ?>" media="(max-width: 991px)">
            <img class="js-lazy" src="<?php echo getLazyImage(1920, 750);?>" data-src="<?php echo asset("public/build/images/landings/university/what-is-ep.jpg") ?>" alt="<?php echo translate("university_what_is_ep_title"); ?>">
        </picture>
    </div>
</section>
