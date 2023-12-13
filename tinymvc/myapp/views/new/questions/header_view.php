<?php
    $checkMaintenance = config('env.MAINTENANCE_MODE') === 'on' && validateDate(config('env.MAINTENANCE_START'), DATE_ATOM) && !isExpiredDate(DateTime::createFromFormat(DATE_ATOM, config('env.MAINTENANCE_START'), new DateTimeZone('UTC')));
?>
<header
    id="js-ep-header"
    class="community-header <?php echo $checkMaintenance ? "community-header--maintenance" : "";?> <?php echo (in_array($current_page, ["all", "question_detail"])) ? "community-header--index":"";?>"
>
    <div
        id="js-ep-header-fixed-top"
        class="ep-header-fixed-top"
    >
        <?php views()->display('new/maintenance/block_view');?>

        <div
            id="js-ep-header-top"
            class="ep-header-top js-main-user-line"
        >
            <div class="ep-header-top__content">
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
                        <li class="ep-header-top__menu-item ep-header-top__menu-item">
                            <a class="ep-header-top__menu-link" href="<?php echo __SITE_URL . 'export_import'; ?>">
                                <?php echo translate('header_navigation_link_export_import'); ?>
                            </a>
                        </li>
                        <li class="ep-header-top__menu-item ep-header-top__menu-item">
                            <a class="ep-header-top__menu-link" href="<?php echo __SITE_URL . 'about/features'; ?>">
                                <?php echo translate('header_navigation_link_ep_feature'); ?>
                            </a>
                        </li>
                        <li class="ep-header-top__menu-item ep-header-top__menu-item">
                            <a class="ep-header-top__menu-link" href="<?php echo __SITE_URL . 'ep_events'; ?>">
                                <?php echo translate('header_navigation_link_events'); ?>
                            </a>
                        </li>
                        <li class="ep-header-top__menu-item ep-header-top__menu-item">
                            <a class="ep-header-top__menu-link" href="<?php echo __SITE_URL . 'trade_news'; ?>">
                                <?php echo translate('header_navigation_link_library'); ?>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <?php
            $session = tmvc::instance()->controller->session;
            $cookie = tmvc::instance()->controller->cookies;
            $isRestrictedAccount = $session->status === "restricted";
        ?>
        <div class="js-main-user-line main-user-line <?php if('questions' === $current_page && !logged_in()){ ?>main-user-line--hidden<?php } ?>">
            <div class="container-center-sm">
                <div class="main-user-line__inner">
                    <div class="nav-logo">
                        <a href="<?php echo __SITE_URL;?>">
                            <img
                                class="nav-logo__image"
                                src="<?php echo asset("public/build/images/logo/ep_logo.png"); ?>"
                                alt="Export Portal">
                        </a>
                        <div class="nav-logo__divider nav-logo__divider--gray"></div>
                        <a class="nav-logo__title txt-black-light" href="<?php echo __COMMUNITY_URL;?>"><?php echo translate('community_help_header_title'); ?></a>
                    </div>

                    <div class="nav-ask">
                        <?php if(logged_in()){?>
                            <div class="js-epuser-line epuser-line">
                                <div class="epuser-line__bl">
                                    <div
                                        class="js-block-wrapper-navbar-toggle epuser-line__item epuser-line__user"
                                    >
                                        <span class="epuser-line__item epuser-line__user">
                                            <?php if ($isRestrictedAccount) { ?>
                                                <span class="epuser-line__restricted">
                                                    <?php echo widgetGetSvgIcon('restricted', 10, 10); ?>
                                                </span>
                                            <?php } elseif ($complete_profile['total_completed'] < 100) { ?>
                                                <span class="epuser-line__circle-sign bg-red"></span>
                                            <?php } ?>
                                            <button
                                                class="epuser-line__user-img call-action"
                                                data-js-action="navbar:toggle-dashboard-menu"
                                                type="button"
                                            >
                                                <img
                                                    class="js-replace-file-avatar image"
                                                    src="<?php echo getDisplayImageLink(array('{ID}' => $session->id, '{FILE_NAME}' => $session->user_photo), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => group_session() )); ?>"
                                                    alt="<?php echo $session->fname; ?>"
                                                />
                                            </button>

                                            <div class="epuser-line__user-info">
                                                <button
                                                    class="epuser-line__user-name call-action"
                                                    data-js-action="navbar:toggle-dashboard-menu"
                                                    type="button"
                                                >
                                                    <span class="epuser-line__user-name-txt"><?php echo $session->fname; ?></span> <i class="ep-icon ep-icon_arrow-down"></i>
                                                </button>

                                                <?php if ($isRestrictedAccount) { ?>
                                                    <button
                                                        class="epuser-line__user-group txt-red call-action"
                                                        data-js-action="popup:call-popup"
                                                        data-popup="account_restricted"
                                                        data-call-type="global"
                                                        <?php echo addQaUniqueIdentifier("global__header_restricted-account-info-btn"); ?>
                                                    >
                                                        <?php echo translate('account_restricted_status'); ?>
                                                        <i
                                                            class="epuser-line__restricted-info-btn btn"
                                                        >
                                                            <?php echo widgetGetSvgIcon('info', 12, 12); ?>
                                                        </i>
                                                    </button>
                                                <?php } else {?>
                                                    <button
                                                        class="epuser-line__user-group <?php echo userGroupNameColor($session->group_name); ?> call-action"
                                                        data-js-action="navbar:toggle-dashboard-menu"
                                                        type="button"
                                                    >
                                                        <?php echo groupNameWithSuffix();?>
                                                    </button>
                                                <?php }?>
                                            </div>
                                        </span>
                                    </div>

                                    <a
                                        class="js-popover-nav epuser-line__item m-0 p-0 fancybox.ajax fancyboxMep"
                                        data-title="<?php echo translate('header_navigation_link_notifications_title', null, true);?>"
                                        data-w="99%"
                                        data-mw="800"
                                        href="<?php echo getUrlForGroup('/systmess/popup_forms/notification');?>"
                                    >
                                        <div class="js-icon-circle-notification mep-header-bottom-nav__relative">
                                            <?php if ($count_notifications['count_new']) { ?>
                                                <span class="epuser-line__circle-sign bg-blue2<?php echo !isBackstopEnabled() ? ' pulse-shadow-animation' : ''; ?>"></span>
                                            <?php } ?>

                                            <?php echo widgetGetSvgIcon('bell-stroke', 22, 22) ?>
                                        </div>
                                    </a>

                                    <div id="js-popover-nav-hidden" class="display-n">
                                        <?php widgetCountNotifyPopover();?>
                                    </div>

                                    <?php if (!matrixChatEnabled() || matrixChatHiddenForCurrentUser()) { ?>
                                        <?php list($dataDialogType, $dataMessage, $dataTitle) = getMatrixDialogData(); ?>
                                        <a
                                            class="epuser-line__item epuser-line__item--messages m-0 pl-20 call-action"
                                            data-js-action="chat:open-access-denied-popup"
                                            data-title="<?php echo $dataTitle; ?>"
                                            data-message="<?php echo $dataMessage; ?>"
                                            data-type="<?php echo cleanOutput($dataDialogType); ?>"
                                        >
                                    <?php } else { ?>
                                        <a
                                            class="<?php echo empty($chatApp['openIframe']) || $chatApp['openIframe'] !== "page"?"call-action disabled ":"";?>js-popover-messages epuser-line__item epuser-line__item--messages m-0 pl-20"
                                            title="Go to chat page"
                                            data-js-action="chat:open-chat-popup"
                                            href="<?php echo __SITE_URL . 'chats';?>"
                                        >
                                    <?php } ?>
                                        <div class="mep-header-bottom-nav__relative">
                                            <span class="js-icon-circle-messages epuser-line__circle-sign bg-green pulse-shadow-animation--green display-n_i"></span>

                                            <?php echo widgetGetSvgIcon('envelope-stroke', 23, 22) ?>
                                        </div>
                                    </a>

                                    <div id="js-popover-messages-hidden" class="display-n">
                                        <div class='notify-popover'>
                                            <div class="notify-popover__additional">
                                                <span class='txt-medium'><span id='js-popover-messages-count-new'>0</span>
                                                    <?php echo translate('header_unread_messages_text'); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="js-epuser-subline" class="epuser-subline display-n">
                                <div class="epuser-subline__inner" id="js-epuser-dashboard"></div>
                            </div>
                        <?php }?>

                        <button
                            class="nav-ask__icon call-action"
                            data-js-action="form:show_search_form"
                            type="button"
                            <?php echo addQaUniqueIdentifier('page__community__header_search-question-btn'); ?>
                        >
                            <?php echo widgetGetSvgIcon('magnifier', 18, 18); ?>
                        </button>

                        <button
                            id="js-mep-header-burger-btn"
                            class="main-user-line__burger call-action"
                            data-js-action="navbar:toggle-mobile-sidebar-menu"
                            aria-label="Open mobile menu"
                            type="button"
                        >
                            <span class="menu-burger"><span></span></span>
                        </button>

                        <a
                            class="btn btn-dark mnw-155 fancybox.ajax fancyboxValidateModal call-action"
                            <?php echo addQaUniqueIdentifier('page__community__header_ask-question-btn'); ?>
                        <?php if(logged_in()){?>
                            data-title="<?php echo translate('community_ask_a_question_text', null, true); ?>"
                            data-mw="535"
                            href="<?php echo __COMMUNITY_URL . 'community_questions/popup_forms/add_question'; ?>"
                        <?php } else { ?>
                            data-js-action="lazy-loading:login"
                            data-title="<?php echo translate('header_navigation_link_login', null, true); ?>"
                            data-mw="400"
                            href="<?php echo __SITE_URL . 'login'; ?>"
                        <?php } ?>
                        >
                            <span class="nav-ask__button--desktop"><?php echo translate('community_ask_a_question_text'); ?></span>
                            <span class="nav-ask__button--mobile"><?php echo translate('community_ask_a_question_mobile_text'); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php encoreEntryScriptTags('navigation'); ?>
    </div>

    <div class="js-community-search-header<?php echo empty($search_params)?' display-n':'';?>">
        <?php
            if(!empty($search_params)){
                views()->display('new/questions/search_form_view', array('search_params' => $search_params));
            }
        ?>
    </div>

    <?php if ($current_page === 'questions') { views()->display('new/questions/main_header_view'); } ?>

    <?php if(!isset($hide_global_header_breadcrumbs)){?>
		<div class="container-center-sm">
            <?php views('new/breadcrumbs_view', ['breadcrumbs' => $breadcrumbs ?: []]);?>
		</div>
    <?php } ?>

    <?php views()->display('new/template_views/mep_header_bottom_view'); ?>

    <?php if(logged_in()){ ?>
        <div id="js-shadow-header-top" class="shadow-header-top call-action" data-js-action="navbar:hide-dashboard-menu"></div>
    <?php }?>
</header>
