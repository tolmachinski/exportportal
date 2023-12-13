<?php
    $session = session();
    $notifications = widgetCounterUserNotifications();
?>
<div id="js-epl-mobile-header-bottom" class="epl-mobile-header-bottom">
    <div class="epl-mobile-header-bottom-nav">
        <button
            id="js-popover-notifications-mep"
            class="epl-mobile-header-bottom-nav__link"
            data-type="ajax"
            data-title="<?php echo translate('header_navigation_link_notifications_title', null, true); ?>"
            data-mw="800"
            data-src="<?php echo getUrlForGroup('systmess/popup_forms/notification'); ?>"
        >
            <span class="js-icon-circle-notification epl-mobile-header-bottom-nav__relative">
                <?php if ($notifications['count_new']) { ?>
                    <span class="epuser-line__circle-sign epuser-line__circle-sign--notifications pulse-shadow-animation"></span>
                <?php } ?>

                <?php echo widgetGetSvgIconEpl('bell-stroke', 22, 22); ?>
            </span>
        </button>
        <div id="js-tooltip-notifications-mep" class="tooltip">
            <div class="tooltip__arrow tooltip__arrow--mob" data-popper-arrow></div>
            <div class="tooltip__body">
                <?php echo widgetCountNotifyPopover(); ?>
            </div>
        </div>

        <?php if (!matrixChatEnabled() || matrixChatHiddenForCurrentUser()) { ?>
            <?php list($dataDialogType, $dataMessage) = getMatrixDialogData(); ?>
            <a
                class="js-info-dialog epl-mobile-header-bottom-nav__link epl-mobile-header-bottom-nav__link--messages"
                data-message="<?php echo $dataMessage; ?>"
                data-type="<?php echo cleanOutput($dataDialogType); ?>"
            >
        <?php } else { ?>
            <a
                class="<?php echo empty($chatApp['openIframe']) || $chatApp['openIframe'] !== "page"?"call-action disabled ":"";?>epl-mobile-header-bottom-nav__link epl-mobile-header-bottom-nav__link--messages js-open-chat"
                data-title="Open chat popup"
                data-js-action="chat:open-chat-popup"
                href="<?php echo __SITE_URL . 'chats'; ?>"
            >
        <?php } ?>
            <div class="relative-b">
                <span class="js-icon-circle-messages epuser-line__circle-sign epuser-line__circle-sign--messages pulse-shadow-animation--green display-n_i"></span>
                <?php echo widgetGetSvgIconEpl('envelope-stroke', 23, 22); ?>
            </div>
        </a>

        <a
            class="epl-mobile-header-bottom-nav__link js-fancybox"
            data-type="ajax"
            data-mw="800"
            data-title="<?php echo translate('header_navigation_link_saved_title', null, true); ?>"
            href="<?php echo getUrlForGroup('saved/popup_forms/saved'); ?>"
        >
            <?php echo widgetGetSvgIconEpl("favorites", 24, 20); ?>
        </a>
    </div>
</div>
