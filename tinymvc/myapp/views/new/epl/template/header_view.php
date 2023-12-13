<?php
    if (isset($webpackData['pageConnect'])) {
        encoreEntryScriptTags($webpackData['pageConnect']);
    }
    encoreEntryScriptTags('epl_app');
    $session = session();
    $cookie = cookies();
    $completeProfile = session()->__get('completeProfile');
    $notifications = widgetCounterUserNotifications();
?>

<?php
    views()->display('new/template_views/tag_manager_body_view');
?>

<?php if (isset($webpackData['dashboardOldPage']) && $webpackData['dashboardOldPage']) { ?>
    <?php if (DEBUG_MODE) { ?>
        <script src="<?php echo fileModificationTime('public/plug/core-js-3-6-5/bundle.js'); ?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/js/lang_new.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/jquery-1-12-0/jquery-1.12.0.min.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/jquery-mousewheel-3-1-12/jquery.mousewheel.min.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/popper-1-11-0/popper.min.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/bootstrap-4-1-1/js/src/util.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/bootstrap-4-1-1/js/src/tooltip.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/bootstrap-4-1-1/js/src/popover.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/bootstrap-4-1-1/js/src/modal.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/bootstrap-4-1-1/js/src/tab.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/bootstrap-4-1-1/js/src/carousel.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/ofi/ofi.min.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/jquery-fancybox-2-1-7/js/jquery.fancybox.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/jquery-validation-engine-2-6-2/js/jquery.validationEngine.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/textcounter-0-3-6/textcounter.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/jquery-jscrollpane-2-0-20/jquery.jscrollpane.min-mod.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/js/js.cookie.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/select2-4-0-3/js/select2.min.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/bootstrap-dialog-1-35-4/js/bootstrap-dialog.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/bootstrap-rating-1-3-1/bootstrap-rating.min.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/jquery-ui-1-12-1-custom/jquery-ui.min.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/resizestop-master/jquery.resizestop.min.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/lazyloading/index.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/js/scripts_general.js');?>"></script>
        <script src="<?php echo fileModificationTime('public/plug/js/scripts_new.js');?>"></script>
    <?php } else { ?>
        <script src="<?php echo fileModificationTime('public/plug/core-js-3-6-5/bundle.min.js'); ?>"></script>
        <script src="<?php echo fileModificationTime('public/plug_compiled/all-epl-dashboard-min.js');?>"></script>
    <?php } ?>
    <script src="<?php echo fileModificationTime('public/plug/js/maintenance-mode/index.js');?>"></script>
<?php }?>

<?php views()->display("new/template_views/autologout_view");?>

<header
    id="js-epl-header"
    class="epl-header"
>
    <div
        id="js-epl-header-mobile-line"
        class="epl-header-mobile-line"
    >
        <div class="epl-header-mobile-line__inner container-center container-center--header">
            <button
                class="epl-header-mobile-line__btn call-action"
                type="button"
                data-js-action="navbar:toggle-dashboard-mobile-menu"
                <?php echo addQaUniqueIdentifier("epl-header-mobile-line__btn-menu")?>
            >
                <?php echo widgetGetSvgIconEpl("menu", 25, 19);?>
            </button>

            <a
                class="link"
                href="<?php echo __SHIPPER_URL; ?>"
            ><img width="116" height="33" src="<?php echo asset("public/build/images/epl/logo.svg");?>" alt="EPL"></a>

            <?php if (!logged_in()) { ?>
                <button
                    class="epl-header-mobile-line__btn call-action js-fancybox js-open-sign-in-modal-btn"
                    type="button"
                    data-js-action="lazy-loading:epl-login"
                    data-type="ajax"
                    data-src="<?php echo __SHIPPER_URL . "login"; ?>"
                    data-title="Sign in"
                    data-mw="430"
                    <?php echo addQaUniqueIdentifier("epl-header-mobile-line__btn-user")?>
                >
                    <?php echo widgetGetSvgIconEpl("user", 20, 24);?>
                </button>
            <?php } else { ?>
                <button
                    class="epl-header-mobile-line__user-img call-action js-navbar-toggle-btn"
                    type="button"
                    data-js-action="navbar:toggle-dashboard-menu"
                    <?php echo addQaUniqueIdentifier('global__epl-header-mobile-line_user-avatar'); ?>
                >
                    <img
                        class="js-replace-file-avatar image"
                        src="<?php echo getDisplayImageLink(['{ID}' => $session->id, '{FILE_NAME}' => $session->user_photo], 'users.main', ['thumb_size' => 0, 'no_image_group' => group_session()]); ?>"
                        alt="<?php echo $session->fname; ?>" />
                </button>
            <?php } ?>
        </div>
    </div>

    <div
        id="js-epl-header-line"
        class="epl-header-line <?php echo isset($isRegisterPage) ? 'epl-header-line--hide-lg' : ''; ?>"
    >
        <div class="epl-header-line__inner container-center container-center--header">
            <div class="epl-header-line__logo">
                <a class="link" href="<?php echo __SHIPPER_URL; ?>"><img width="140" height="40" src="<?php echo asset("public/build/images/epl/logo.svg");?>" alt="EPL"></a>
            </div>
            <nav class="epl-header-nav">
                <?php if (isset($isHomePage)) { ?>
                    <button
                        class="epl-header-nav__item call-action"
                        data-js-action="navbar:scroll-to"
                        data-anchor="js-epl-about-b"
                        <?php echo addQaUniqueIdentifier("epl-header-nav__btn-about")?>
                    ><?php echo translate('epl_general_nav_link_about'); ?></button>
                    <button
                        class="epl-header-nav__item call-action"
                        data-js-action="navbar:scroll-to"
                        data-anchor="js-epl-tools-b"
                        <?php echo addQaUniqueIdentifier("epl-header-nav__btn-tools")?>
                    ><?php echo translate('epl_general_nav_link_tools'); ?></button>
                    <button
                        class="epl-header-nav__item call-action"
                        data-js-action="navbar:scroll-to"
                        data-anchor="js-epl-faq-b"
                        <?php echo addQaUniqueIdentifier("epl-header-nav__btn-faq")?>
                    ><?php echo translate('epl_general_nav_link_faq'); ?></button>
                    <button
                        class="epl-header-nav__item call-action"
                        data-js-action="navbar:scroll-to"
                        data-anchor="js-epl-testimonials-b"
                        <?php echo addQaUniqueIdentifier("epl-header-nav__btn-testimonials")?>
                    ><?php echo translate('epl_general_nav_link_testimonials'); ?></button>
                    <button
                        class="epl-header-nav__item call-action"
                        data-js-action="navbar:scroll-to"
                        data-anchor="js-epl-partners-b"
                        <?php echo addQaUniqueIdentifier("epl-header-nav__btn-partners")?>
                    ><?php echo translate('epl_general_nav_link_partners'); ?></button>
                <?php } else { ?>
                    <a
                        class="epl-header-nav__item"
                        href="<?php echo __SHIPPER_URL .'#about'; ?>"
                        <?php echo addQaUniqueIdentifier("epl-header-nav__btn-about"); ?>
                    >
                        <?php echo translate('epl_general_nav_link_about'); ?>
                    </a>
                    <a
                        class="epl-header-nav__item"
                        href="<?php echo __SHIPPER_URL .'#tools'; ?>"
                        <?php echo addQaUniqueIdentifier("epl-header-nav__btn-tools"); ?>
                    >
                        <?php echo translate('epl_general_nav_link_tools'); ?>
                    </a>
                    <a
                        class="epl-header-nav__item"
                        href="<?php echo __SHIPPER_URL .'#faq'; ?>"
                        <?php echo addQaUniqueIdentifier("epl-header-nav__btn-faq"); ?>
                    >
                        <?php echo translate('epl_general_nav_link_faq'); ?>
                    </a>
                    <a
                        class="epl-header-nav__item"
                        href="<?php echo __SHIPPER_URL .'#testimonials'; ?>"
                        <?php echo addQaUniqueIdentifier("epl-header-nav__btn-testimonials"); ?>
                    >
                        <?php echo translate('epl_general_nav_link_testimonials'); ?>
                    </a>
                    <a
                        class="epl-header-nav__item"
                        href="<?php echo __SHIPPER_URL .'#partners'; ?>"
                        <?php echo addQaUniqueIdentifier("epl-header-nav__btn-partners"); ?>
                    >
                        <?php echo translate('epl_general_nav_link_partners'); ?>
                    </a>
                <?php } ?>
                <a
                    class="epl-header-nav__item"
                    href="<?php echo __SHIPPER_URL .'resources'; ?>"
                    <?php echo addQaUniqueIdentifier("epl-header-nav__resources-btn"); ?>
                >
                    <?php echo translate('epl_general_nav_link_resources'); ?>
                </a>
            </nav>

            <div class="epl-header-line__zoho">
                <button
                    class="epl-header-line__zoho-btn call-action"
                    type="button"
                    title="<?php echo translate('header_navigation_link_chat_title', null, true); ?>"
                    data-js-action="zoho-chat:show"
                    <?php echo addQaUniqueIdentifier('epl-menu__popup-chat'); ?>
                >
                    <i class="ep-icon ep-icon_support5"></i> Support
                </button>

                <button
                    class="epl-header-line__zoho-btn call-action"
                    type="button"
                    data-js-action="zoho-ticket:open"
                    <?php echo addQaUniqueIdentifier('epl-menu__popup-ticket'); ?>
                >
                    <i class="ep-icon ep-icon_ticket2"></i> Add a ticket
                </button>
                <button
                    class="epl-header-line__zoho-btn js-order-call-btn call-action"
                    type="button"
                    data-href="click_to_call/view_form"
                    data-js-action="click-to-call:open-callback-popup"
                    data-popup-bg="<?php echo asset('public/build/images/popups/click-to-call.jpg'); ?>"
                    <?php echo addQaUniqueIdentifier('epl-menu__popup-order-call'); ?>
                >
                    <?php echo widgetGetSvgIcon('phone', 18, 18); ?> Order Call
                </button>
            </div>

            <?php if (!logged_in()) { ?>
                <div class="epl-header-line__actions">
                    <button
                        class="btn btn-sm epl-header-line__signin call-action js-fancybox js-open-sign-in-modal-btn"
                        type="button"
                        data-js-action="lazy-loading:epl-login"
                        data-type="ajax"
                        data-src="<?php echo __SHIPPER_URL . "login"; ?>"
                        data-title="<?php echo translate('epl_login_form_ttl', null, true); ?>"
                        data-mw="430"
                        <?php echo addQaUniqueIdentifier('global__header_epl-login-btn'); ?>
                    >
                        <?php echo translate('epl_general_action_link_signin'); ?>
                    </button>
                    <span class="epl-header-line__or"><?php echo translate('epl_general_action_text_or'); ?></span>
                    <a
                        class="btn btn-outline-primary btn-sm epl-header-line__register"
                        href="<?php echo __SHIPPER_URL . 'register/ff'; ?>"
                        <?php echo addQaUniqueIdentifier('global__header_epl-register-btn'); ?>
                    >
                        <?php echo translate('epl_general_action_link_register'); ?>
                    </a>
                </div>
            <?php } else { ?>
                <div class="js-epuser-line epuser-line">
                    <div class="epuser-line__bl">
                        <button
                            class="epuser-line__item epuser-line__user call-action js-navbar-toggle-btn"
                            type="button"
                            data-js-action="navbar:toggle-dashboard-menu"
                            <?php echo addQaUniqueIdentifier('global__header_epl-user-menu-btn'); ?>
                        >
                            <?php if ($completeProfile['total_completed'] < 100) { ?>
                                <span class="epuser-line__circle-sign epuser-line__circle-sign--bg-red"></span>
                            <?php } ?>

                            <span class="epuser-line__user-img">
                                <img
                                    class="js-replace-file-avatar image"
                                    src="<?php echo getDisplayImageLink(['{ID}' => $session->id, '{FILE_NAME}' => $session->user_photo], 'users.main', ['thumb_size' => 0, 'no_image_group' => group_session()]); ?>"
                                    alt="<?php echo $session->fname; ?>"
                                    <?php echo addQaUniqueIdentifier('global__header_user-avatar'); ?>
                                />
                            </span>
                            <div class="epuser-line__user-info">
                                <div class="epuser-line__user-name">
                                    <span class="epuser-line__user-name-txt">
                                        <?php echo $session->fname; ?>
                                    </span>
                                    <i class="js-btn-user-dashboard ep-icon ep-icon_arrow-down"></i>
                                </div>
                                <div class="epuser-line__user-group">
                                    <?php echo $session->group_name . ($session->group_name_suffix ?? ''); ?>
                                </div>
                            </div>

                        </button>

                        <div class="epuser-line__icons">
                            <button
                                id="js-popover-notifications"
                                class="epuser-line__icons-item js-fancybox js-popover"
                                data-type="ajax"
                                data-title="<?php echo translate('header_navigation_link_notifications_title', null, true); ?>"
                                data-mw="800"
                                data-src="<?php echo getUrlForGroup('/systmess/popup_forms/notification'); ?>"
                                type="button"
                            >
                                <span class="js-icon-circle-notification">
                                    <?php if ($notifications['count_new']) { ?>
                                        <span class="epuser-line__circle-sign epuser-line__circle-sign--notifications pulse-shadow-animation"></span>
                                    <?php } ?>

                                    <?php echo widgetGetSvgIconEpl("bell-stroke", 22, 22);?>
                                </span>
                            </button>

                            <div id="js-tooltip-notifications" class="tooltip">
                                <div class="tooltip__arrow-wrap">
                                    <div class="tooltip__arrow" data-popper-arrow></div>
                                </div>
                                <div class="tooltip__body">
                                    <?php echo widgetCountNotifyPopover(); ?>
                                </div>
                            </div>

                            <?php if (!matrixChatEnabled() || matrixChatHiddenForCurrentUser()) { ?>
                                <?php list($dataDialogType, $dataMessage) = getMatrixDialogData(); ?>
                                <a
                                    class="js-info-dialog epuser-line__icons-item epuser-line__icons-item--messages"
                                    data-message="<?php echo $dataMessage; ?>"
                                    data-type="<?php echo cleanOutput($dataDialogType); ?>"
                                >
                            <?php } else { ?>
                                <a
                                    id="js-popover-messages"
                                    class="<?php echo empty($chatApp['openIframe']) || $chatApp['openIframe'] !== "page"?"call-action disabled ":"";?> epuser-line__icons-item epuser-line__icons-item--messages"
                                    data-title="<?php echo translate('header_navigation_link_messages_title', null, true); ?>"
                                    title="Go to chat page"
                                    data-js-action="chat:open-chat-popup"
                                    href="<?php echo getUrlForGroup('chats'); ?>"
                                >
                                <div id="js-tooltip-messages" class="tooltip tooltip-messages">
                                    <div class="tooltip__arrow tooltip-messages__arrow" data-popper-arrow></div>
                                    <div class="tooltip__body">
                                        <div class='notify-popover'>
                                            <div class="notify-popover__additional">
                                                <span class="notify-popover__txt-medium">
                                                    <span id='js-popover-messages-count-new'>0</span>
                                                    <?php echo translate('header_unread_messages_text'); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                                <div class="relative-b">
                                    <span class="js-icon-circle-messages epuser-line__circle-sign epuser-line__circle-sign--messages pulse-shadow-animation--green display-n_i"></span>

                                    <?php echo widgetGetSvgIconEpl('envelope-stroke', 22, 21) ?>
                                </div>
                            </a>

                            <a
                                class="epuser-line__icons-item js-fancybox"
                                data-type="ajax"
                                data-mw="800"
                                data-title="<?php echo translate('header_navigation_link_saved_title', null, true); ?>"
                                href="<?php echo getUrlForGroup('saved/popup_forms/saved'); ?>"
                            >
                                <?php echo widgetGetSvgIconEpl("favorites", 24, 20);?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php if (logged_in()) {?>
        <div id="js-epuser-subline" class="epuser-subline display-n">
            <div class="epuser-subline__inner" id="js-epuser-dashboard"></div>
        </div>
    <?php } ?>

    <?php
        if (logged_in()) {
            views()->display('new/epl/template/mep_header_bottom_view');
        }
    ?>

    <div
        id="js-epl-header-line-background"
        class="epl-header-line__background call-action"
        data-js-action="navbar:toggle-dashboard-mobile-menu"
        <?php echo addQaUniqueIdentifier("epl-header-nav__background")?>
    ></div>
</header>

<?php
    encoreEntryScriptTags('epl_navigation');
?>
