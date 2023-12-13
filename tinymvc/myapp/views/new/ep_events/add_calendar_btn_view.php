<?php
$class = 'call-action';

if (logged_in()) {
    $class = 'fancyboxMep fancybox.ajax';

    if (in_array($event['id'], $eventsInCalendar)) {
        $class = 'ep-events__calendar-icon ep-events__calendar-hover js-confirm-dialog ' . ($isDetailPage ? 'calendar-btn-success' : '');
    }
}

$title = logged_in() ? (in_array($event['id'], $eventsInCalendar) ? translate("ep_events_delete_from_calendar") : translate("ep_events_add_to_calendar")) : translate("about_why_ep_popup_login_title");
?>
<button
    class="ep-events__calendar-wrap ep-events__icon <?php echo $isDetailPage ? 'calendar-btn' : '';?> <?php echo $class;?>"
    data-fancybox-href="<?php echo __SITE_URL . "calendar/open_notifications_settings?type=ep_events&source={$event['id']}";?>"
    data-title="<?php echo $title;?>"
    data-mw="470"
    data-id="<?php echo "calendar-btn-{$event['id']}";?>"
    data-item="<?php echo "{$event['id']}";?>"
    data-js-action="<?php echo logged_in() ? 'notification:event' : 'notification:event-login';?>"
    data-message="<?php echo translate("ep_events_delete_from_calendar_message");?>"
    data-sub-title="<?php echo translate("events_ep_popup_login_content", ["[[HREF]]" => "&quot;" . __SITE_URL . "register&quot;", "[[TITLE]]" => "&quot;Go to the registration page&quot;"], true);?>"
    data-class-modificator="notification-settings"
    <?php echo addQaUniqueIdentifier('ep-events__event__calendar_' . (in_array($event['id'], $eventsInCalendar) ? 'remove' : 'add')); ?>
    type="button">

    <div class="ep-events__calendar js-not-added-calendar <?php echo in_array($event['id'], $eventsInCalendar) ? 'display-n' : '';?>">
        <?php echo widgetGetSvgIcon('calendar', 18, 18); ?> <?php echo $isDetailPage ? '<span class="calendar-btn__calendar-txt">'. translate("ep_events_calendar_icon_message") .'</span>' : '';?>
    </div>

    <div class="ep-events__calendar-success js-added-calendar <?php echo in_array($event['id'], $eventsInCalendar) ? '' : 'display-n';?>">
        <?php echo widgetGetSvgIcon('calendar-success', 18, 18); ?> <?php echo $isDetailPage ? '<span class="calendar-btn__calendar-txt">'. translate("ep_events_calendar_icon_message_success") .'</span>' : '';?>
    </div>

    <div class="ep-events__calendar-delete display-n">
        <?php echo widgetGetSvgIcon('calendar-delete', 18, 18); ?> <?php echo $isDetailPage ? '<span class="calendar-btn__calendar-txt">'. translate("ep_events_calendar_icon_message_delete") .'</span>' : '';?>
    </div>
</button>
