<div class="notify-popover" data-notify="<?php echo $lastNotificationId;?>">
    <?php if ((int) $countNotifications['count_new'] > 0) {?>
        <?php if ((int) $countNotifications['count_warning'] > 0) {?>
            <div class="notify-popover__main">
                <a class="notify-popover__txt-blue fancybox.ajax fancyboxMep js-fancybox js-popover-link" data-title="<?php echo translate('header_navigation_link_notifications_title', null, true); ?>" data-w="99%" data-mw="800" data-type="ajax" href="<?php echo __CURRENT_SUB_DOMAIN_URL . 'systmess/popup_forms/notification?type=warning'; ?>" data-original-title title> <span id="js-popover-nav-count-important"><?php echo (int) $countNotifications['count_warning'];?></span>
                <?php echo translate('popover_important_notification', ['{{SOME_HTML}}' => '</a>']); ?>
            </div>
            <div class="notify-popover__additional notify-popover__additional--indent">
                <?php echo translate('popover_total_unread', ['{{START_HTML}}' => '<a class="notify-popover__black-link fancybox.ajax fancyboxMep js-fancybox js-popover-link" data-title="Notifications" data-w="99%" data-type="ajax" data-mw="800" href="' . __CURRENT_SUB_DOMAIN_URL . 'systmess/popup_forms/notification"><span id="js-popover-nav-count-new">', '{{COUNT_NEW}}' => (int) $countNotifications['count_new'], '{{END_HTML}}' => '</span>']); ?></a>
            </div>
        <?php } else {?>
            <div class="notify-popover__additional">
                <span class="notify-popover__txt-medium"><span id="js-popover-nav-count-new"><?php echo (int) $countNotifications['count_new'];?></span> <?php echo translate('popover_unread_notification'); ?></span>
            </div>
        <?php }?>
    <?php } else {?>
        <div class="notify-popover__additional">
            <?php echo translate('popover_total_notification', ['{{START_HTML}}' => '<span id="js-popover-nav-count-total">', '{{COUNT_TOTAl}}' => (int) $countNotifications['count_all'], '{{END_HTML}}' => '</span>']); ?>
        </div>
    <?php }?>
</div>
