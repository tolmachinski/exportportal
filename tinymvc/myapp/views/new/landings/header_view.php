<!DOCTYPE html>
<html lang="<?php echo __SITE_LANG; ?>">
    <head>
        <base href="<?php echo __SITE_URL; ?>">
        <?php app()->view->display('new/js_global_vars_view'); ?>
        <?php app()->view->display('new/js_analytics_view'); ?>

        <?php if (logged_in()) { ?>
            <meta name="csrf-token" content="<?php echo session()->csrfToken; ?>">
        <?php } ?>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">

        <?php widgetMetaHeader($meta_params ?? [], $meta_data ?? [], 'new/', $meta);?>

        <?php if (isset($webpackData)) { ?>
            <style><?php echo getPublicStyleContent('/build/css/' . $webpackData['styleCritical'] . '.critical.min.css', false); ?></style>
        <?php } else { ?>
            <link rel="stylesheet" href="<?php echo fileModificationTime('public/css/landings.css'); ?>">
        <?php } ?>
    </head>

    <body>
        <?php views()->display('new/template_views/tag_manager_body_view'); ?>

    <?php
        if (isset($webpackData)) {
            encoreEntryLinkTags($webpackData['pageConnect']);
            encoreEntryScriptTags($webpackData['pageConnect']);
            encoreEntryScriptTags('app');
            encoreEntryScriptTags('footer');
        } else {
            views()->display('new/landings/header_scripts_view');
        }
    ?>

    <?php if ($currentPage && 'advisors' === $currentPage) { ?>
        <header class="epl-page__container">
            <div class="epl-navigation">
                <a class="epl-navigation__link" href="<?php echo __SITE_URL; ?>">
                    <h1>Export portal</h1>
                </a>
                <a class="epl-navigation__logo" href="<?php echo __SITE_URL; ?>">
                    <img src="<?php echo asset('public/build/images/logo/ep-logo.png'); ?>" width="71" height="81" alt="Export Portal Logo">
                </a>
            </div>
            <section class="epl-about">
                <h2 class="epl-about__headline"><?php echo translate('landing_advisors_header_title'); ?></h2>
                <div class="epl-about__subtitle">
                    <?php echo translate('landing_advisors_header_subtitle'); ?>
                </div>

                <ul class="epl-about__list">
                    <li class="epl-about__list-item epl-about__list-item--25">
                        <div class="epl-about__block epl-about__trade">

                            <img class="epl-icon epl-icon__trade" src="<?php echo asset('public/build/images/landings/icons/about-icons-1.png'); ?>" alt="Trade">
                            <div class="epl-about__title"><?php echo translate('landing_advisors_about_list_1'); ?></div>
                        </div>
                    </li>
                    <li class="epl-about__list-item epl-about__list-item--25">
                        <div class="epl-about__block epl-about__tech">
                            <img class="epl-icon epl-icon__tech" src="<?php echo asset('public/build/images/landings/icons/about-icons-2.png'); ?>" alt="Tech">
                            <div class="epl-about__title"><?php echo translate('landing_advisors_about_list_2'); ?></div>
                        </div>
                    </li>
                    <li class="epl-about__list-item epl-about__list-item--25">
                        <div class="epl-about__block epl-about__ecommerce">
                            <img class="epl-icon js-lazy epl-icon__ecommerce" data-src="<?php echo asset('public/build/images/landings/icons/about-icons-3.png'); ?>" src="<?php echo getLazyImage(102, 86); ?>" alt="Ecommerce">
                            <div class="epl-about__title"><?php echo translate('landing_advisors_about_list_3'); ?></div>
                        </div>
                    </li>
                    <li class="epl-about__list-item epl-about__list-item--25">
                        <div class="epl-about__block epl-about__logistics">
                            <img class="epl-icon js-lazy epl-icon__logistics" data-src="<?php echo asset('public/build/images/landings/icons/about-icons-4.png'); ?>" src="<?php echo getLazyImage(89, 88); ?>" alt="Logistics">
                            <div class="epl-about__title"><?php echo translate('landing_advisors_about_list_4'); ?></div>
                        </div>
                    </li>
                    <li class="epl-about__list-item epl-about__list-item--25">
                        <div class="epl-about__block epl-about__laws">
                            <img class="epl-icon js-lazy epl-icon__laws" data-src="<?php echo asset('public/build/images/landings/icons/about-icons-6.png'); ?>" src="<?php echo getLazyImage(116, 92); ?>" alt="Laws">
                            <div class="epl-about__title"><?php echo translate('landing_advisors_about_list_5'); ?></div>
                        </div>
                    </li>
                    <li class="epl-about__list-item epl-about__list-item--25">
                        <div class="epl-about__block epl-about__sme">
                            <img class="epl-icon js-lazy epl-icon__sme" data-src="<?php echo asset('public/build/images/landings/icons/about-icons-7.png'); ?>" src="<?php echo getLazyImage(104, 96); ?>" alt="SME">
                            <div class="epl-about__title"><?php echo translate('landing_advisors_about_list_6'); ?></div>
                        </div>
                    </li>
                    <li class="epl-about__list-item epl-about__list-item--25">
                        <div class="epl-about__block epl-about__industry">
                            <img class="epl-icon js-lazy epl-icon__industry" data-src="<?php echo asset('public/build/images/landings/icons/about-icons-8.png'); ?>" src="<?php echo getLazyImage(135, 122); ?>" alt="Industry Specifics">
                            <div class="epl-about__title"><?php echo translate('landing_advisors_about_list_7'); ?></div>
                        </div>
                    </li>
                    <li class="epl-about__list-item epl-about__list-item--25">
                        <div class="epl-about__block epl-about__data">
                            <img class="epl-icon js-lazy epl-icon__data" data-src="<?php echo asset('public/build/images/landings/icons/about-icons-9.png'); ?>" src="<?php echo getLazyImage(88, 86); ?>" alt="Data and Stats">
                            <div class="epl-about__title"><?php echo translate('landing_advisors_about_list_8'); ?></div>
                        </div>
                    </li>
                </ul>

                <div class="epl-about__info"><?php echo translate('landing_advisors_about_list_info'); ?></div>
                <div class="epl-about__link">
                    <a href="https://app.smartsheet.com/b/form/59c42f8a630d4503998d17fd0442706c" target="_blank"><?php echo translate('landing_advisors_about_list_btn_text'); ?></a>
                </div>
            </section>
        </header>
    <?php } ?>

    <?php if ($currentPage && 'logistics_ambassador' === $currentPage) { ?>
        <header class="ambassador__header">
            <picture class="ambassador__header-background">
                <source media="(max-width: 414px)" srcset="<?php echo asset("public/build/images/landings/logistics_ambassador/header-mobile.jpg"); ?>">
                <source media="(min-width: 426px) and (max-width: 991px)" srcset="<?php echo asset("public/build/images/landings/logistics_ambassador/header-tablet.jpg"); ?>">
                <img class="image" src="<?php echo asset("public/build/images/landings/logistics_ambassador/header-bg.jpg"); ?>" width="1920" height="600" alt="">
            </picture>

            <div class="ambassador__header-body">
                <h1 class="ambassador__header-title">
                    <?php echo translate('landing_logistics_ambassador_header_title'); ?>
                </h1>
                <a class="ambassador__join-btn btn btn-outline-dark" href="https://app.smartsheet.com/b/form/20d4887af6be4c65a18b6cb8ddc8b814" <?php echo addQaUniqueIdentifier("logistics-ambassador__header-btn") ?>>
                    <?php echo translate('landing_government_and_association_header_join_ep_btn'); ?>
                </a>
            </div>
        </header>
    <?php } ?>

    <main>
