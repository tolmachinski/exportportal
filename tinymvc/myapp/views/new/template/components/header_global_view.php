<?php
    $session = session();
    $navState = container()->get(\App\DataProvider\NavigationBarStateProvider::class)->getState((int) privileged_user_id());

    views()->display("new/template_views/autologout_view");

    // For classes to ep-header to fix jumping of content
    $checkBannerBecomeCertified = verifyNeedCertifyUpgrade() && !cookies()->exist_cookie('showTopBannerBecomeCertified');
    $checkMaintenance = 'on' === config('env.MAINTENANCE_MODE') && validateDate(config('env.MAINTENANCE_START'), DATE_ATOM) && !isExpiredDate(DateTime::createFromFormat(DATE_ATOM, config('env.MAINTENANCE_START'), new DateTimeZone('UTC')));

    echo file_get_contents(substr(asset('public/build/images/svg_icons.svg'), 1));
?>

<header
    id="js-ep-header"
    class="ep-header<?php echo $checkBannerBecomeCertified ? ' ep-header--banner' : ''; ?><?php echo $checkMaintenance ? ' ep-header--maintenance' : ''; ?>"
>
<!-- #region desktop header -->
    <div
        id="js-ep-header-fixed-top"
        class="ep-header-fixed-top"
    >
        <?php if ($checkMaintenance) { ?>
            <div id="js-maintenance-banner-container" class="maintenance-banner-container animate">
                <style><?php echo getPublicStyleContent('/css/maintenance_mode.min.css') ?: getPublicStyleContent('/css/maintenance_mode.css'); ?></style>
                <?php
                    $timeMaintenanceStart = DateTime::createFromFormat(DATE_ATOM, config('env.MAINTENANCE_START'), new DateTimeZone('UTC'));
                    $date = DateTime::createFromFormat(DATE_ATOM, $_ENV['MAINTENANCE_START'], new DateTimeZone('UTC'));
                    $diff = $date->diff(new DateTimeImmutable());
                    $days = $timeMaintenanceStart->diff(new DateTime())->format('%a');
                ?>
                <div class="maintenance-mode" id="js-maintenance-banner">
                    <div class="container-center maintenance-mode__inner">
                        <div class="maintenance-mode__content">
                            <svg class="maintenance-mode__icon" version="1.1" viewBox="0 0 50 50" width="22" height="22" xmlns="http://www.w3.org/2000/svg"><path d="m22.757 49.903c-7.6927-0.6831-14.763-5.0102-18.92-11.579-0.70254-1.1103-1.8864-3.5185-2.3472-4.775-1.7226-4.6969-1.9541-10.032-0.65133-15.014 0.77222-2.953 2.3221-6.1227 4.2216-8.6336 1.7536-2.3182 4.1345-4.5058 6.6056-6.0695 1.1102-0.7025 3.5185-1.8863 4.7749-2.3471 4.697-1.7227 10.032-1.9541 15.014-0.6514 2.953 0.7723 6.1227 2.3221 8.6336 4.2216 9.6919 7.3318 12.705 20.653 7.1063 31.421-1.4298 2.7503-3.0705 4.8852-5.3868 7.0091-5.1094 4.685-12.089 7.0364-19.051 6.4182zm3.5882-10.788c1.3458-0.615 2.0637-1.8897 1.9454-3.4544-0.10062-1.3304-0.74997-2.2905-1.9204-2.8395-0.47101-0.221-0.66996-0.2542-1.5193-0.2536-0.79317 5e-4 -1.0634 0.04-1.4343 0.2112-0.64431 0.2969-1.2671 0.9063-1.602 1.5678-0.2628 0.519-0.28656 0.651-0.28656 1.5921 0 0.9079 0.0297 1.0882 0.25781 1.5637 0.81176 1.6926 2.8257 2.4049 4.5594 1.6127zm-0.60646-9.4423c0.37082-0.1122 0.76526-0.5116 0.95416-0.9663 0.19649-0.4729 1.9745-16.06 1.8855-16.53-0.0903-0.4769-0.42842-0.9583-0.84479-1.2029-0.3196-0.1877-0.49679-0.2007-2.7362-0.2007-2.1103 0-2.4322 0.021-2.7125 0.1737-0.40426 0.2209-0.78052 0.7891-0.85578 1.2923-0.0648 0.4336 1.631 15.549 1.825 16.266 0.15778 0.5833 0.71748 1.1365 1.2719 1.2572 0.42843 0.093 0.66943 0.076 1.2127-0.089z" stroke-width=".097732"/></svg>
                            <span class="maintenance-mode__txt">
                                The site will be under construction <span id="js-maintenance-starte-date-client-text">today</span>. Please keep this in mind when performing
                                time-sensitive actions.
                            </span
                            >
                            <span class="maintenance-mode__txt-tablet">Maintenance anouncement!</span>
                            <span class="maintenance-mode__txt-mobile">Maintenance!</span>
                        </div>
                        <div id="js-getting-started" class="maintenance-mode__timer">
                            <span id="js-days-left" class="<?php echo 0 === $diff->days ? 'display-n' : ''; ?>">
                                <?php echo $diff->days; ?>
                            </span>
                            <span class="maintenance-mode__days <?php echo 0 === $diff->days ? 'display-n' : ''; ?>">
                                Days
                            </span>
                            <span id="js-hours-left">
                                <?php echo $diff->format('%H') ?: '00'; ?>
                            </span>
                            <span class="txt-gray pl-5 pr-5">:</span>
                            <span id="js-minutes-left">
                                <?php echo $diff->format('%I') ?: '00'; ?>
                            </span>
                            <span class="txt-gray pl-5 pr-5">:</span>
                            <span id="js-seconds-left">
                                <?php echo $diff->format('%S') ?: '00'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <?php
                    $dateMaintenance = $timeMaintenanceStart->format(DATE_ATOM);

                    echo dispatchDynamicFragment(
                        'maintenance-mode:boot',
                        [$dateMaintenance],
                        true
                    );
                ?>
            </div>
        <?php }?>

        <?php
            if ($checkBannerBecomeCertified) {
                views()->display('new/upgrade/banner_top_view');
            }
        ?>

    <!-- #region first line -->
        <div
            id="js-ep-header-top"
            class="ep-header-top js-main-user-line"
        >
            <div class="ep-header-top__content container-center">
                <nav class="ep-header-top__menu">
                    <ul class="ep-header-top__menu-list">
                        <li class="ep-header-top__menu-item">
                            <a class="ep-header-top__menu-link" href="<?php echo __SITE_URL . 'buying'; ?>">
                                <?php echo translate('header_navigation_link_buying'); ?>
                            </a>
                        </li>
                        <li class="ep-header-top__menu-item">
                            <a class="ep-header-top__menu-link" href="<?php echo __SITE_URL . 'selling'; ?>">
                                <?php echo translate('header_navigation_link_selling'); ?>
                            </a>
                        </li>
                        <li class="ep-header-top__menu-item">
                            <a class="ep-header-top__menu-link" href="<?php echo __SITE_URL . 'about'; ?>">
                                <?php echo translate('header_navigation_link_about_us'); ?>
                            </a>
                        </li>
                        <li class="ep-header-top__menu-item">
                            <a class="ep-header-top__menu-link" href="<?php echo __BLOG_URL; ?>">
                                <?php echo translate('header_main_menu_link_blog'); ?>
                            </a>
                        </li>
                        <li class="ep-header-top__menu-item">
                            <a class="ep-header-top__menu-link" href="<?php echo __SITE_URL . 'b2b'; ?>">
                                <?php echo translate('header_navigation_link_b2b'); ?>
                            </a>
                        </li>
                        <li class="ep-header-top__menu-item">
                            <a class="ep-header-top__menu-link" href="<?php echo __SITE_URL . 'faq'; ?>">
                                <?php echo translate('header_navigation_link_faq'); ?>
                            </a>
                        </li>
                        <li class="ep-header-top__menu-item ep-header-top__menu-item--mobile">
                            <a class="ep-header-top__menu-link" href="<?php echo __SITE_URL . 'help'; ?>">
                                <?php echo translate('header_navigation_link_help'); ?>
                            </a>
                        </li>
                        <li class="ep-header-top__menu-item">
                            <div class="ep-header-top__menu-dropdown dropdown dropdown--select">
                                <button
                                    class="dropdown-toggle dropdown-toggle--center"
                                    id="headerMenuMore"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                    type="button"
                                >
                                    <?php echo translate('header_menu_dropdown_more_btn'); ?>
                                    <?php echo getEpIconSvg('arrow-down', [9, 9]);?>
                                </button>

                                <div class="dropdown-menu  dropdown-menu-right" aria-labelledby="headerMenuMore">
                                    <a class="dropdown-item" href="<?php echo __SITE_URL . 'export_import'; ?>"><?php echo translate('header_navigation_link_export_import'); ?></a>
                                    <a class="dropdown-item" href="<?php echo __SITE_URL . 'about/features'; ?>"><?php echo translate('header_navigation_link_ep_feature'); ?></a>
                                    <a class="dropdown-item" href="<?php echo __SITE_URL . 'ep_events'; ?>"><?php echo translate('header_navigation_link_events'); ?></a>
                                    <a class="dropdown-item" href="<?php echo __SITE_URL . 'trade_news'; ?>"><?php echo translate('header_navigation_link_library'); ?></a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </nav>

                <div class="ep-header-top__actions">
                    <button
                        class="ep-header-top__actions-preferences-link call-action notranslate"
                        data-js-action="navbar:open-popup-preferences"
                        title="<?php echo translate("header_popup_preferences_title", null, true); ?>"
                        type="button"
                        <?php echo addQaUniqueIdentifier('global__header__user-preferences-btn'); ?>
                    >
                        <span><?php echo cookies()->getCookieParam('_ulang'); ?></span>
                        <span class="ep-header-top__actions-delimiter">|</span>
                        <?php echo cookies()->getCookieParam('currency_key'); ?>
                    </button>
                    <a
                        class="ep-header-top__actions-link"
                        href="<?php echo __SITE_URL . 'help';?>"
                    >
                        <?php echo widgetGetSvgIcon('question-circle', 16, 16, 'ep-header-top__actions-icon'); ?>
                        <?php echo translate('header_navigation_link_help');?>
                    </a>
                </div>
            </div>
        </div>
    <!-- #endregion first line -->

    <!-- #region second line -->
    <?php if (!isset($hideGlobalHeaderBottom)) {?>
        <div
            id="js-ep-header-bottom"
            class="ep-header-bottom inputs-40"
        >
            <div
                class="ep-header-bottom__content container-center"
                <?php echo addQaUniqueIdentifier('global__header__header-bottom-content'); ?>
            >
                <div class="ep-header-bottom__content-left">
                    <a class="ep-header-bottom__logo" href="<?php echo __SITE_URL; ?>" itemprop="url">
                        <?php
                            $logoImage = 'logo.png';
                                if (filter_var(config('new_year_theme'), FILTER_VALIDATE_BOOLEAN) && !isBackstopEnabled()) {
                                    $logoImage = 'logo-ny.png';
                                }
                        ?>
                        <img
                            id="js-ep-logo"
                            class="image"
                            width="45"
                            height="54"
                            src="<?php echo asset('public/build/images/logo/' . $logoImage); ?>"
                            alt="Export Portal Logo"
                        >
                        <span class="ep-header-bottom__logo-txt notranslate">EXPORT PORTAL</span>
                    </a>

                    <div class="ep-header-bottom__mobile-actions">
                        <button
                            class="js-ep-header-mobile-link-search ep-header-bottom__mobile-link call-action"
                            data-js-action="navbar:show-header-mobile-search-form"
                            data-item="mep-header-search"
                            aria-label="Toggle mobile search"
                            type="button"
                            <?php echo addQaUniqueIdentifier('global__header__navbar-search-form_toggle-btn'); ?>
                        >
                            <?php echo widgetGetSvgIcon('magnifier', 20, 20); ?>
                        </button>

                        <button
                            id="js-mep-header-burger-btn"
                            class="ep-header-bottom__mobile-burger call-action"
                            data-js-action="navbar:toggle-mobile-sidebar-menu"
                            aria-label="Open mobile menu"
                            type="button"
                        >
                            <span class="menu-burger"><span></span></span>
                        </button>
                    </div>
                </div>

                <div
                    id="js-ep-header-content-search"
                    class="ep-header-bottom__content-center"
                >
                    <button
                        class="ep-header-bottom__categories call-action"
                        data-js-action="categories:show-side-categories"
                        <?php echo addQaUniqueIdentifier('global__header__categories-btn'); ?>
                        type="button"
                    >
                        <?php echo widgetGetSvgIcon('categories', 16, 16, 'ep-header-bottom__categories-icon'); ?>
                        <span class="ep-header-bottom__categories-txt"><?php echo translate('header_categories_btn'); ?></span>
                    </button>

                    <?php views('new/template_views/search_block_view', ['connectWidgetAutocomplete' => true]); ?>
                </div>

                <div class="ep-header-bottom__content-right">
                    <?php if (!logged_in()) { ?>
                        <button
                            class="js-sign-in ep-header-bottom__link fancybox.ajax js-fancybox-validate-modal call-action"
                            data-fancybox-href="<?php echo get_static_url('login/index', __SITE_URL); ?>"
                            data-js-action="lazy-loading:login"
                            data-mw="400"
                            data-title="<?php echo translate('header_navigation_link_login', null, true); ?>"
                            type="button"
                            <?php echo addQaUniqueIdentifier('global__header-login-btn'); ?>
                        >
                            <?php echo translate('header_sign_in_btn'); ?>
                        </button>

                        <a
                            class="ep-header-bottom__btn btn btn-outline-primary"
                            href="<?php echo get_static_url('register/index'); ?>"
                            <?php echo addQaUniqueIdentifier('global__header-registration-btn'); ?>
                        >
                            <?php echo translate('register_button_text'); ?>
                        </a>
                    <?php } else { ?>
                        <?php
                            $isRestrictedAccount = 'restricted' === $session->status;
                        ?>
                        <div class="js-epuser-line epuser-line">
                            <div class="epuser-line__bl<?php echo 'shipper' === $session->user_type ? ' epuser-line__bl--shipper' : ''; ?>">
                                <?php if ('shipper' !== $session->user_type) { ?>
                                    <div
                                        class="js-block-wrapper-navbar-toggle epuser-line__item epuser-line__user"
                                    >
                                        <?php if ($isRestrictedAccount) { ?>
                                            <span class="epuser-line__restricted">
                                                <?php echo widgetGetSvgIcon('restricted', 10, 10); ?>
                                            </span>
                                        <?php } elseif ($navState['completeProfile']['total_completed'] < 100) { ?>
                                            <span class="epuser-line__circle-sign bg-red"></span>
                                        <?php } ?>

                                        <button
                                            class="epuser-line__user-img call-action"
                                            data-js-action="navbar:toggle-dashboard-menu"
                                            type="button"
                                        >
                                            <img
                                                class="js-replace-file-avatar image"
                                                src="<?php echo getDisplayImageLink(['{ID}' => $session->id, '{FILE_NAME}' => $session->user_photo], 'users.main', ['thumb_size' => 0, 'no_image_group' => group_session()]); ?>"
                                                alt="<?php echo $session->fname; ?>"
                                                <?php echo addQaUniqueIdentifier('global__user-info_avatar'); ?>
                                            />
                                        </button>

                                        <div class="epuser-line__user-info">
                                            <button
                                                class="epuser-line__user-name call-action"
                                                data-js-action="navbar:toggle-dashboard-menu"
                                                type="button"
                                                <?php echo addQaUniqueIdentifier('global__header__navbar-toggler'); ?>
                                            >
                                                <span class="epuser-line__user-name-txt" <?php echo addQaUniqueIdentifier('global__user-info_name'); ?>>
                                                    <?php echo $session->fname; ?>
                                                </span>
                                                <i class="ep-icon ep-icon_arrow-down"></i>
                                            </button>

                                            <?php if ($isRestrictedAccount) { ?>
                                                <button
                                                    class="epuser-line__user-group txt-red call-action"
                                                    data-js-action="popup:call-popup"
                                                    data-popup="account_restricted"
                                                    data-call-type="global"
                                                    <?php echo addQaUniqueIdentifier('global__header_restricted-account-info-btn'); ?>
                                                >
                                                    <?php echo translate('account_restricted_status'); ?>
                                                    <i
                                                        class="epuser-line__restricted-info-btn btn call-action"
                                                    >
                                                        <?php echo widgetGetSvgIcon('info', 12, 12); ?>
                                                    </i>
                                                </button>
                                            <?php } else { ?>
                                                <button
                                                    class="epuser-line__user-group <?php echo userGroupNameColor($session->group_name); ?> call-action"
                                                    data-js-action="navbar:toggle-dashboard-menu"
                                                    type="button"
                                                >
                                                    <?php echo groupNameWithSuffix(); ?>
                                                </button>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <div class="epuser-line__icons">
                                        <a
                                            class="js-popover-nav epuser-line__icons-item fancybox.ajax fancyboxMep"
                                            data-title="<?php echo translate('header_navigation_link_notifications_title', null, true); ?>"
                                            data-w="99%"
                                            data-mw="800"
                                            href="<?php echo __CURRENT_SUB_DOMAIN_URL . 'systmess/popup_forms/notification'; ?>"
                                        >
                                            <div class="js-icon-circle-notification mep-header-bottom-nav__relative">
                                                <?php if ($navState['countNotifications']['count_new']) { ?>
                                                    <span class="epuser-line__circle-sign bg-blue2<?php echo !isBackstopEnabled() ? ' pulse-shadow-animation' : ''; ?>"></span>
                                                <?php } ?>

                                                <?php echo widgetGetSvgIcon('bell-stroke', 22, 22); ?>
                                            </div>
                                        </a>

                                        <div id="js-popover-nav-hidden" class="display-n">
                                            <?php widgetCountNotifyPopover(); ?>
                                        </div>

                                        <?php if (!matrixChatEnabled() || matrixChatHiddenForCurrentUser()) { ?>
                                            <?php list($dataDialogType, $dataMessage, $dataTitle) = getMatrixDialogData(); ?>
                                            <a
                                                class="epuser-line__icons-item epuser-line__icons-item--messages call-action"
                                                data-js-action="chat:open-access-denied-popup"
                                                data-title="<?php echo $dataTitle; ?>"
                                                data-message="<?php echo $dataMessage; ?>"
                                                data-type="<?php echo cleanOutput($dataDialogType); ?>"
                                            >
                                        <?php } else { ?>
                                            <a
                                                class="<?php echo empty($chatApp['openIframe']) || 'page' !== $chatApp['openIframe'] ? 'call-action disabled ' : ''; ?>js-popover-messages epuser-line__icons-item epuser-line__icons-item--messages"
                                                data-title="<?php echo translate('header_navigation_link_messages_title', null, true); ?>"
                                                title="Go to chat page"
                                                data-js-action="chat:open-chat-popup"
                                                href="<?php echo getUrlForGroup('chats'); ?>"
                                            >
                                        <?php } ?>
                                            <div class="mep-header-bottom-nav__relative">
                                                <span class="js-icon-circle-messages epuser-line__circle-sign bg-green pulse-shadow-animation--green display-n_i"></span>

                                                <?php echo widgetGetSvgIcon('envelope-stroke', 23, 22); ?>
                                            </div>
                                        </a>

                                        <div id="js-popover-messages-hidden" class="display-n">
                                            <div class='notify-popover'>
                                                <div class='notify-popover__additional'>
                                                    <span class='txt-medium'><span id='js-popover-messages-count-new'>0</span>
                                                        <?php echo translate('header_unread_messages'); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <a
                                            class="epuser-line__icons-item fancybox.ajax fancyboxMep"
                                            data-w="99%"
                                            data-mw="800"
                                            data-title="<?php echo translate('header_navigation_link_saved_title', null, true); ?>"
                                            href="<?php echo __CURRENT_SUB_DOMAIN_URL . 'saved/popup_forms/saved'; ?>"
                                        >
                                            <?php echo widgetGetSvgIcon('favorites', 28, 24); ?>
                                        </a>

                                        <?php if (have_right('buy_item')) { ?>
                                            <a
                                                class="epuser-line__icons-item epuser-line__icons-item--basket fancybox.ajax fancyboxMep js-header-basket-link"
                                                data-title="<?php echo translate('header_navigation_popup_basket_header', null, true); ?>"
                                                data-mw="450"
                                                href="<?php echo __CURRENT_SUB_DOMAIN_URL . 'basket/popup_forms/show_basket_list'; ?>"
                                            >
                                                <?php if ($session->basket) { ?>
                                                    <span class="epuser-line__circle-sign bg-orange js-epuser-line-circle-sign"></span>
                                                <?php } ?>
                                                <?php echo widgetGetSvgIcon('basket', 26, 24); ?>
                                            </a>
                                        <?php } ?>

                                        <?php if (have_right('manage_personal_items')) { ?>
                                            <a class="epuser-line__icons-item" href="<?php echo __SITE_URL; ?>items/choose_category">
                                                <?php echo widgetGetSvgIcon('plus-circle', 22, 22); ?>
                                            </a>
                                        <?php } ?>
                                    </div>
                                <?php } else { ?>
                                    <div class="epuser-line__item epuser-line__user">
                                        <span class="epuser-line__user-img">
                                            <img
                                                class="js-replace-file-avatar image"
                                                src="<?php echo getDisplayImageLink(['{ID}' => $session->id, '{FILE_NAME}' => $session->user_photo], 'users.main', ['thumb_size' => 0, 'no_image_group' => group_session()]); ?>"
                                                alt="<?php echo $session->fname; ?>"
                                                <?php echo addQaUniqueIdentifier('global__user-info_avatar'); ?>
                                            />
                                        </span>
                                        <div class="epuser-line__user-info">
                                            <div class="epuser-line__user-name">
                                                <span
                                                    class="epuser-line__user-name-txt"
                                                    <?php echo addQaUniqueIdentifier('global__user-info_name'); ?>
                                                >
                                                    <?php echo $session->fname; ?>
                                                </span>
                                            </div>

                                            <?php if ($isRestrictedAccount) { ?>
                                                <button
                                                    class="epuser-line__user-group txt-red call-action"
                                                    data-js-action="popup:call-popup"
                                                    data-popup="account_restricted"
                                                    data-call-type="global"
                                                    <?php echo addQaUniqueIdentifier('global__header_restricted-account-info-btn'); ?>
                                                >
                                                    <?php echo translate('account_restricted_status'); ?>
                                                    <i
                                                        class="epuser-line__restricted-info-btn btn"
                                                    >
                                                        <?php echo widgetGetSvgIcon('info', 12, 12); ?>
                                                    </i>
                                                </button>
                                            <?php } else { ?>
                                                <div class="epuser-line__user-group <?php echo userGroupNameColor($session->group_name); ?>">
                                                    <?php echo groupNameWithSuffix(); ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php } ?>

                                <?php if ('shipper' === $session->user_type) { ?>
                                    <a
                                        class="btn btn-primary epuser-line__epl-btn"
                                        href="<?php echo __SHIPPER_URL; ?>"
                                        <?php echo addQaUniqueIdentifier('global__header-go-to-epl-btn'); ?>
                                    >
                                        <?php echo translate('header_go_to_epl_btn'); ?>
                                    </a>

                                    <a
                                        class="btn btn-link epuser-line__epl-btn-logout call-action"
                                        data-js-action="dashboard:logout"
                                        href="<?php echo __SHIPPER_URL . 'authenticate/logout' ?>"
                                        <?php echo addQaUniqueIdentifier("global__navigation-logout-btn")?>
                                    >
                                        <i class="wr-ep-icon-svg wr-ep-icon-svg--h26"><?php echo getEpIconSvg('logout', [16, 16]);?></i>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>

                        <div id="js-epuser-subline" class="epuser-subline display-n">
                            <div id="js-epuser-dashboard" class="epuser-subline__inner"></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>
    <!-- #endregion second line -->
    </div>
<!-- #endregion desktop header -->

<!-- #region mobile header -->
    <?php views()->display('new/template_views/mep_header_bottom_view'); ?>
<!-- #endregion mobile header -->

    <?php if (logged_in()) { ?>
        <div id="js-shadow-header-top" class="shadow-header-top call-action" data-js-action="navbar:hide-dashboard-menu"></div>
    <?php }?>

    <?php if (!empty($breadcrumbs) && !isset($hide_global_header_breadcrumbs)) {?>
        <div class="container-center-sm <?php if(!empty($newTemplate)){ ?>container-1420<?php } ?>">
            <?php views('new/breadcrumbs_view', ['breadcrumbs' => $breadcrumbs ?: []]); ?>
        </div>
    <?php } ?>

    <?php
        if (!empty($headerContent)) {
            views($headerContent);
        }
    ?>

    <?php
        encoreEntryScriptTags('navigation');
        encoreEntryLinkTags('navigation');
        echo dispatchDynamicFragment('navbar:notification', [logged_in()], true);
    ?>
</header>

<link crossorigin="anonymous" rel="stylesheet" href="<?php echo asset("public/build/styles_user_pages_general.css");?>" />



