<?php
    $session = session();
    $cookie = cookies();
    $isRestrictedAccount = userStatus() === \App\Common\Contracts\User\UserStatus::RESTRICTED();
    $account_link = getMyProfileLink();
?>

<div class="mep-header-user">
    <div class="mep-header-user__top">
        <div class="mep-header-user__top-left">

            <?php
                $userButtonGroup = $isRestrictedAccount ? sprintf(
                    <<<'RESTRICTED'
                        <button
                            class="mep-header-user__group txt-red call-action"
                            data-js-action="popup:call-popup"
                            data-popup="account_restricted"
                            data-call-type="global"
                        >
                            %s
                            <i
                                class="mep-header-user__restricted-info-btn btn"
                            >
                                %s
                            </i>
                        </button>
                    RESTRICTED,
                    translate('account_restricted_status'),
                    widgetGetSvgIcon('info', 12, 12)
                ) : sprintf(
                    <<<'USER'
                        <a
                            class="mep-header-user__group %s"
                            href="%s"
                        >
                            %s
                        </a>
                    USER,
                    userGroupNameColor(group_name_session()),
                    $account_link,
                    groupNameWithSuffix()
                );
            ?>

            <?php if (false !== $account_link) { ?>
                <div
                    class="mep-header-user__item"
                >
                    <a
                        class="mep-header-user__img"
                        href="<?php echo $account_link; ?>"
                    >
                        <img
                            class="js-replace-file-avatar js-lazy image"
                            data-src="<?php echo getDisplayImageLink(['{ID}' => id_session(), '{FILE_NAME}' => photo_session()], 'users.main', ['thumb_size' => 0, 'no_image_group' => group_session()]); ?>"
                            src="<?php echo getLazyImage(50, 50); ?>"
                            alt="<?php echo user_name_session(); ?>"
                            <?php echo addQaUniqueIdentifier('global__user-info_avatar'); ?>
                        />
                    </a>
                    <?php if ($isRestrictedAccount) { ?>
                        <span class="mep-header-user__restricted">
                                <?php echo widgetGetSvgIcon('restricted', 11, 11); ?>
                        </span>
                    <?php } ?>

                    <div class="mep-header-user__info">
                        <a
                            class="mep-header-user__name"
                            href="<?php echo $account_link; ?>"
                            <?php echo addQaUniqueIdentifier('global__user-info_name'); ?>
                        >
                            <?php echo user_name_session(); ?>
                        </a>

                        <?php echo $userButtonGroup; ?>
                    </div>
                </div>
            <?php } else {?>
                <div class="mep-header-user__item">
                    <div class="mep-header-user__img">
                        <img
                            class="js-replace-file-avatar js-lazy image"
                            data-src="<?php echo getDisplayImageLink(['{ID}' => id_session(), '{FILE_NAME}' => photo_session()], 'users.main', ['thumb_size' => 0, 'no_image_group' => group_session()]); ?>"
                            src="<?php echo getLazyImage(50, 50); ?>"
                            alt="<?php echo user_name_session(); ?>"
                            <?php echo addQaUniqueIdentifier('global__user-info_avatar'); ?>
                        />
                    </div>
                    <?php if ($isRestrictedAccount) { ?>
                        <span class="mep-header-user__restricted">
                            <?php echo widgetGetSvgIcon('restricted', 11, 11); ?>
                        </span>
                    <?php } ?>
                    <div class="mep-header-user__info">
                        <div class="mep-header-user__name" <?php echo addQaUniqueIdentifier('global__user-info_name'); ?>>
                            <?php echo user_name_session(); ?>
                        </div>

                        <?php echo $userButtonGroup; ?>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="mep-header-user__top-right">
            <?php if (checkRightSwitchGroup()) { ?>
                <button
                    class="ep-icon ep-icon_users fs-20 mr-25 call-action"
                    data-js-action="dashboard:switch-account-mobile"
                    type="button"
                    <?php echo addQaUniqueIdentifier('global__mep-dashboard__switch-account-btn'); ?>
                ></button>
            <?php } ?>

            <?php if ('shipper' === $session->user_type) { ?>
                <a
                    class="btn btn-primary mep-header-user__epl-btn"
                    href="<?php echo __SHIPPER_URL; ?>"
                >
                    <?php echo translate('header_go_to_epl_btn'); ?>
                </a>
            <?php } ?>

            <a
                class="ep-icon ep-icon_logout call-action"
                data-js-action="dashboard:logout"
                href="<?php echo __SITE_URL . 'authenticate/logout'; ?>"
            ></a>
        </div>
    </div>

    <div class="js-mep-header-user-menu-wr">
        <div class="mep-header-user__menu<?php if (user_type("shipper")) { ?> mep-header-user__menu--bdn<?php } ?>">
            <?php if (!user_type("shipper")) { ?>
            <div class="mep-header-user__menu-l">
                <button
                    class="mep-header-user__menu-item mep-header-user__menu-item--text fancybox.ajax fancyboxMep"
                    data-w="99%"
                    data-mw="800"
                    data-title="<?php echo translate('dashboard_favorites_word', null, true); ?>"
                    data-fancybox-href="<?php echo getUrlForGroup('saved/popup_forms/saved'); ?>"
                    type="button"
                >
                    <i class="ep-icon ep-icon_favorite-empty"></i>
                    <span class="item-text"><?php echo translate('header_navigation_link_favorites'); ?></span>
                </button>
            <?php } else { ?>
                <div class="mep-header-user__menu-l mep-header-user__menu-l--jc-s">
            <?php } ?>

            <?php if (config('env.SHOW_COMPARE_FUNCTIONALITY')) { ?>
                <a class="mep-header-user__menu-item dn-md_i dynamic-status-compare" href="<?php echo __SITE_URL . 'compare'; ?>">
                    <i class="ep-icon ep-icon_balance"></i>
                </a>
            <?php } ?>

                <button
                    class="mep-header-user__menu-item mep-header-user__menu-item--text mep-header-user__menu-item--ticket call-action"
                    data-js-action="zoho-ticket:open"
                    type="button"
                >
                    <i class="ep-icon ep-icon_ticket2"></i>
                    <span class="item-text"><?php echo translate('header_navigation_link_add_ticket'); ?></span>
                </button>

                <button
                    class="mep-header-user__menu-item mep-header-user__menu-item--text mep-header-user__menu-item--chat js-btn-call-main-chat call-action"
                    data-js-action="zoho-chat:show"
                    title="<?php echo translate('header_navigation_link_support', null, true); ?>"
                    type="button"
                >
                    <i class="ep-icon ep-icon_support5"></i>
                    <span class="item-text"><?php echo translate('header_navigation_link_support'); ?></span>
                </button>
                <button
                    class="mep-header-user__menu-item mep-header-user__menu-item--text js-order-call-btn call-action"
                    data-js-action="click-to-call:open-callback-popup"
                    data-href="click_to_call/view_form"
                    data-popup-bg="<?php echo asset("public/build/images/popups/click-to-call.jpg"); ?>"
                    title="<?php echo translate('header_navigation_link_support', null, true); ?>"
                    type="button"
                >
                    <?php echo widgetGetSvgIcon('phone', 18, 18); ?>
                    <span class="item-text"><?php echo translate('header_navigation_link_order_call'); ?></span>
                </button>
            </div>

            <div class="mep-header-user__menu-r">
                <button
                    class="mep-header-user__preferences call-action notranslate"
                    data-js-action="navbar:open-popup-preferences"
                    title="<?php echo translate("header_popup_preferences_title", null, true); ?>"
                    type="button"
                    <?php echo addQaUniqueIdentifier('global__mep-dashboard__user-preferences-btn'); ?>
                >
                    <span><?php echo cookies()->getCookieParam('_ulang'); ?></span>
                    <span class="mep-header-user__preferences-delimiter">|</span>
                    <?php echo cookies()->getCookieParam('currency_key'); ?>
                </button>

                <button
                    class="mep-header-user__menu-item mr-0 ep-icon ep-icon_share-stroke2 share-social fancybox fancyboxLang"
                    data-fancybox-href="#share-social"
                    data-title="<?php echo translate('home_share_link_title', null, true); ?>"
                >
                </button>
            </div>
        </div>

        <?php if (!user_type("shipper")) { ?>
            <div id="js-mep-user-content" class="mep-header-user__content">
                <?php $nav_tabs = $session->menu_full; ?>
                <?php if (group_expired_session()) { ?>
                    <div class="warning-alert-b mt-15">
                        <i class="ep-icon ep-icon_warning-circle-stroke"></i>
                        <span><?php echo translate('system_info_expired_group_package', ['[UPGRADE_PACKAGE_LINK]' => __SITE_URL . 'upgrade']); ?></span>
                    </div>
                <?php } ?>

                <div id="js-mep-user-nav-quick" class="mep-user-nav">
                    <?php if ($complete_profile['total_completed'] < 100) { ?>
                        <div class="complete-profile-warning">
                            <div class="complete-profile-warning__info">
                                <i class="ep-icon ep-icon_warning-circle-stroke"></i> <span><?php echo translate('header_complete_profile'); ?> <button class="complete-profile-warning__button call-action" data-js-action="account:open-completion-popup">Here</button></span>
                            </div>
                            <span><?php echo "{$complete_profile['countCompleteOptions']}/{$complete_profile['countOptions']}"?></span>
                        </div>
                    <?php } ?>
                    <div class="display-n show-767">
                        <?php if (!empty($dashboardBanner)) { ?>
                            <?php views('new/banners/dashboard_banner_view'); ?>
                        <?php } ?>
                    </div>
                    <div class="mep-user-nav__item">
                        <ul class="mep-user-nav__links">
                            <?php
                            $count_completed_option = 0;
                            $count_options = count($complete_profile['options']);
                            foreach ($complete_profile['options'] as $item) {
                                if ($item['option_completed']) {
                                    ++$count_completed_option;
                                }
                            }
                            ?>
                            <li class="mep-user-nav__links-item col1-cell1"></li>
                            <li class="mep-user-nav__links-item col1-cell2"></li>
                            <li class="mep-user-nav__links-item col1-cell3"></li>
                            <li class="mep-user-nav__links-item col1-cell4"></li>
                            <li class="mep-user-nav__links-item col1-cell5"></li>
                            <li class="mep-user-nav__links-item col1-cell6"></li>
                            <li class="mep-user-nav__links-item col1-cell7"></li>
                        </ul>

                        <ul class="mep-user-nav__links">
                            <li class="mep-user-nav__links-item col2-cell1"></li>
                            <li class="mep-user-nav__links-item col2-cell2"></li>
                            <li class="mep-user-nav__links-item col2-cell3"></li>
                            <li class="mep-user-nav__links-item col2-cell4"></li>
                            <li class="mep-user-nav__links-item col2-cell5"></li>
                            <li class="mep-user-nav__links-item col2-cell6"></li>
                            <li class="mep-user-nav__links-item col2-cell7"></li>
                        </ul>

                        <ul class="mep-user-nav__links">
                            <li class="mep-user-nav__links-item col3-cell1"></li>
                            <li class="mep-user-nav__links-item col3-cell2"></li>
                            <li class="mep-user-nav__links-item col3-cell3"></li>
                            <li class="mep-user-nav__links-item col3-cell4"></li>
                            <li class="mep-user-nav__links-item col3-cell5"></li>
                            <li class="mep-user-nav__links-item col3-cell6"></li>
                            <li class="mep-user-nav__links-item col3-cell7"></li>
                        </ul>
                    </div>

                    <div class="hide-767">
                        <?php if (!empty($dashboardBanner)) { ?>
                            <?php views('new/banners/dashboard_banner_view'); ?>
                        <?php } ?>
                    </div>
                </div>

                <div id="js-mep-user-nav-full" class="mep-user-nav mep-user-nav--full" style="display: none;">
                    <?php foreach ($nav_tabs as $key_tab => $nav_tab_item) { ?>
                        <?php
                            if (!empty($nav_tab_item['params']['right']) && !have_right_or($nav_tab_item['params']['right'])) {
                                continue;
                            }
                        ?>
                        <div class="mep-user-nav__item">
                            <div>
                                <div class="mep-user-nav__ttl">
                                    <div class="name"><?php echo $nav_tab_item['params']['title']; ?></div>
                                </div>

                                <ul class="mep-user-nav__links">
                                <?php foreach ($nav_tab_item['items'] as $nav_item_key => $nav_item) { ?>
                                    <?php if (!empty($nav_item['right']) && !have_right_or($nav_item['right'])) {
                                continue;
                            } ?>
                                    <li class="mep-user-nav__links-item"
                                        data-name="<?php echo $nav_item_key; ?>">
                                        <a class="link <?php echo (isset($nav_item['popup'])) ? 'fancybox.ajax fancyboxValidateModal' : ''; ?>" <?php echo (isset($nav_item['popup'])) ? 'data-title="' . $nav_item['popup'] . '"' : ''; ?>
                                        href="<?php echo $nav_item['external_link'] ? $nav_item['external_link'] : $nav_item['link']; ?>"
                                        target="<?php echo $nav_item['external_link'] ? '_blank' : '_self'; ?>"
                                        data-name="<?php echo $nav_item_key; ?>"
                                        data-tab="<?php echo $key_tab; ?>">
                                            <i class="ep-icon ep-icon_<?php echo $nav_item['icon']; ?>"></i>
                                            <div>
                                                <?php echo $nav_item['title']; ?>
                                                <?php if ($nav_item['new']) { ?>
                                                    <span class="dashboard-nav__item-new">NEW</span>
                                                <?php } ?>
                                            </div>
                                        </a>
                                    </li>
                                <?php } ?>
                                </ul>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="mep-header-user__selector">
                <button
                    class="link active call-action"
                    data-js-action="dashboard:user-menu-mobile"
                    data-type="quick"
                    data-hide="full"
                    type="button"
                ><i class="ep-icon ep-icon_arrow-left"></i><?php echo translate('header_navigation_quick_menu'); ?></button>
                <button
                    class="link call-action"
                    data-js-action="dashboard:user-menu-mobile"
                    data-type="full"
                    data-hide="quick"
                    type="button"
                    <?php echo addQaUniqueIdentifier('global__mep-dashboard-menu__full-menu-btn'); ?>
                ><?php echo translate('header_navigation_full_menu'); ?><i class="ep-icon ep-icon_arrow-right"></i></button>
            </div>
        <?php } ?>
    </div>

    <?php if (checkRightSwitchGroup()) { ?>
        <div class="js-mep-header-switch-account-wr mep-header-switch-account">
            <div class="mep-header-switch-account__ttl"><?php echo translate('login_switch_account'); ?></div>
            <div class="mep-header-switch-account__content">
                <?php views()->display('new/authenticate/choose_list_view', ['class_select_account' => 'select-account-list--mobile']); ?>
            </div>
        </div>

        <?php views()->display('new/authenticate/clean_session_view', ['choose_another_account' => true]); ?>
    <?php } ?>
</div>

<?php echo dispatchDynamicFragment(
    'dashboard:menu',
    [json_decode($session->menu, true)]
); ?>
