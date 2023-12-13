<?php
    $session = session();
    $navState = container()->get(\App\DataProvider\NavigationBarStateProvider::class)->getState((int) privileged_user_id());
?>
<div
    id="js-mep-header-bottom"
    class="mep-header-bottom"
>
    <div
        id="js-mep-header-bottom-toggle"
        class="mep-header-bottom-toggle"
    >
        <div id="js-mep-header-dashboard" class="mep-header-bottom-toggle__item"></div>
    </div>

    <div class="mep-header-bottom-nav">
        <button
            class="mep-header-bottom-nav__link mep-header-bottom-nav__link--categories call-action"
            data-js-action="categories:show-side-categories"
            aria-label="Show categories"
            type="button"
            <?php echo addQaUniqueIdentifier('global__mep-header__categories-btn'); ?>
        >
            <?php echo widgetGetSvgIcon('categories', 20, 20); ?>
        </button>

        <?php if (!logged_in()) { ?>
            <button
                class="mep-header-bottom-nav__link call-action"
                data-js-action="zoho-ticket:open"
                aria-label="Add ticket"
                type="button"
            >
                <?php echo widgetGetSvgIcon('ticket', 20, 20); ?>
            </button>
        <?php } else { ?>
            <?php if ('shipper' !== $session->user_type) { ?>
                <button
                    class="mep-header-bottom-nav__link js-popover-mep"
                    data-title="<?php echo translate('header_navigation_link_notifications_title', null, true); ?>"
                    data-fancybox-href="<?php echo __CURRENT_SUB_DOMAIN_URL . 'systmess/popup_forms/notification'; ?>"
                    data-w="99%"
                    data-mw="800"
                >
                    <div class="js-icon-circle-notification mep-header-bottom-nav__relative">
                        <?php if ($navState['countNotifications']['count_new']) { ?>
                            <span class="epuser-line__circle-sign bg-blue2<?php echo !isBackstopEnabled() ? ' pulse-shadow-animation' : ''; ?>"></span>
                        <?php } ?>

                        <?php echo widgetGetSvgIcon('bell-stroke', 22, 22); ?>
                    </div>
                </button>
            <?php } ?>

            <?php if (!matrixChatEnabled() || matrixChatHiddenForCurrentUser()) { ?>
                <?php list($dataDialogType, $dataMessage, $dataTitle) = getMatrixDialogData(); ?>
                <button
                    class="mep-header-bottom-nav__link mep-header-bottom-nav__link--messages call-action"
                    data-js-action="chat:open-access-denied-popup"
                    data-title="<?php echo $dataTitle; ?>"
                    data-message="<?php echo $dataMessage; ?>"
                    data-type="<?php echo cleanOutput($dataDialogType); ?>"
                    type="button"
                >
                    <div class="mep-header-bottom-nav__relative">
                        <span class="js-icon-circle-messages epuser-line__circle-sign bg-green pulse-shadow-animation--green display-n_i"></span>
                        <?php echo widgetGetSvgIcon('envelope-stroke', 23, 22); ?>
                    </div>
                </button>
            <?php } else { ?>
                <a
                    class="<?php echo empty($chatApp['openIframe']) || 'page' !== $chatApp['openIframe'] ? 'call-action disabled ' : ''; ?>mep-header-bottom-nav__link mep-header-bottom-nav__link--messages js-open-chat"
                    data-title="Open chat popup"
                    data-js-action="chat:open-chat-popup"
                    href="<?php echo __SITE_URL . 'chats'; ?>"
                >
                    <div class="mep-header-bottom-nav__relative">
                        <span class="js-icon-circle-messages epuser-line__circle-sign bg-green pulse-shadow-animation--green display-n_i"></span>
                        <?php echo widgetGetSvgIcon('envelope-stroke', 23, 22); ?>
                    </div>
                </a>
            <?php } ?>

            <?php if (have_right('sell_item')) { ?>
                <a
                    class="mep-header-bottom-nav__link"
                    href="<?php echo __SITE_URL . 'items/choose_category'; ?>"
                >
                    <?php echo widgetGetSvgIcon('plus-circle', 22, 22); ?>
                </a>
            <?php } elseif (have_right('buy_item')) { ?>
                <a
                    class="mep-header-bottom-nav__link mep-header-bottom-nav__link--basket"
                    href="<?php echo __SITE_URL . 'basket/my'; ?>"
                >
                    <div class="mep-header-bottom-nav__relative">
                        <?php if ($session->basket) { ?>
                            <span class="epuser-line__circle-sign bg-orange"></span>
                        <?php } ?>
                        <?php echo widgetGetSvgIcon('basket', 26, 24); ?>
                    </div>
                </a>
            <?php } ?>
        <?php } ?>

        <?php
            $dashboardMobileMenuType = "";

            if(!logged_in()) {
                $dashboardMobileMenuType = "notLogged";
            }elseif($session->user_type === "shipper"){
                $dashboardMobileMenuType = "shipper";
            }elseif(have_right('manage_personal_items')){
                $dashboardMobileMenuType = "seller";
            }
        ?>

        <button
            class="mep-header-bottom-nav__link call-action <?php if (logged_in()) { ?>js-mep-user-actions<?php } else { ?>js-mep-header-mobile-login<?php } ?>"
            data-js-action="navbar:toggle-dashboard-mobile-menu"
            data-type="<?php echo $dashboardMobileMenuType; ?>"
            data-chat="false"
            aria-label="Open mobile user dasboard"
            type="button"
            <?php echo addQaUniqueIdentifier('global__mep-header__navbar-toggler'); ?>
        >
            <div class="js-mep-user-actions-inner mep-header-bottom-nav__relative">
                <?php if (logged_in()) { ?>
                    <span class="mep-header-bottom__user-img">
                        <img
                            class="js-replace-file-avatar image"
                            src="<?php echo getDisplayImageLink(['{ID}' => $session->id, '{FILE_NAME}' => $session->user_photo], 'users.main', ['thumb_size' => 0, 'no_image_group' => group_session()]); ?>"
                            alt="<?php echo $session->fname; ?>"
                            <?php echo addQaUniqueIdentifier('global__user-info_avatar'); ?>
                        />
                    </span>

                    <?php if ('restricted' === $session->status) { ?>
                        <span class="mep-header-bottom__restricted">
                            <?php echo widgetGetSvgIcon('restricted', 10, 10); ?>
                        </span>
                    <?php } elseif ($navState['completeProfile']['total_completed'] < 100) { ?>
                        <span class="epuser-line__circle-sign bg-red"></span>
                    <?php } ?>

                <?php } else {
                    echo widgetGetSvgIcon('user', 20, 22);
                } ?>
            </div>
        </button>

        <?php if (!logged_in()) { ?>
            <button
                class="mep-header-bottom-nav__link js-order-call-btn call-action"
                data-js-action="click-to-call:open-callback-popup"
                data-href="click_to_call/view_form"
                data-popup-bg="<?php echo asset("public/build/images/popups/click-to-call.jpg"); ?>"
                aria-label="Click to call"
                type="button"
            >
                <?php echo widgetGetSvgIcon('phone', 20, 20); ?>
            </button>

            <button
                class="mep-header-bottom-nav__link mep-header-bottom-nav__link--chat js-btn-call-main-chat call-action"
                data-js-action="zoho-chat:show"
                title="<?php echo translate('header_navigation_link_chat_title', null, true); ?>"
                type="button"
            >
                <?php echo widgetGetSvgIcon('support-chat', 22, 20, 'js-svg-icon-chat'); ?>
                <?php echo widgetGetSvgIcon('updates', 24, 24, 'js-svg-icon-updates display-n'); ?>
            </button>
        <?php }?>
    </div>
</div>
