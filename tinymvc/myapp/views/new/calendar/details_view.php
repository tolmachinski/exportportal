<button class="calendar-info-popup__close call-action" data-js-action="calendar-info:close" type="button">
    <?php echo widgetGetSvgIcon('remove', 15, 15); ?>
</button>
<?php if ($calendar['isUnpublishedEvent']) {?>
    <div class="calendar-info-popup__canceled"><?php echo translate('calendar_popup_event_canceled'); ?></div>
<?php } else {?>
    <div class="calendar-info-popup__date"><?php echo $calendar['duration']; ?></div>
<?php }?>
<h4 class="calendar-info-popup__title"><?php echo $calendar['title']; ?></h4>
<p class="calendar-info-popup__body"><?php echo $calendar['short_description']; ?></p>
<?php if (!empty($calendar['location']['country']['name'])) {?>
    <div class="calendar-info-popup__country">
        <img class="calendar-info-popup__country-flag" src="<?php echo getCountryFlag($calendar['location']['country']['name']); ?>" alt="<?php echo implode(', ', [$calendar['location']['country']['name'], $calendar['location']['city']['name'] ?? '']); ?>" />
        <span class="calendar-info-popup__country-name">
            <?php echo implode(', ', [$calendar['location']['country']['name'], $calendar['location']['city']['name'] ?? '']); ?>
        </span>
    </div>
<?php }?>

<div class="calendar-info-popup__actions">
    <?php if (!$calendar['isUnpublishedEvent']) {?>
        <a
            href="<?php echo $calendar['previewUrl']; ?>"
            class="calendar-info-popup__btn calendar-info-popup__btn--today btn btn-light btn-new16"
            <?php echo addQaUniqueIdentifier('ep-events-calendar__popup__preview_btn'); ?>
        >
            <?php echo widgetGetSvgIcon('eye', 17, 17); ?>
            <span><?php echo translate('calendar_popup_preview'); ?></span>
        </a>
        <?php if (((int) (new DateTimeImmutable())->diff($calendar['start_date'])->format('%r%a')) > 0) {?>
            <button
                class="calendar-info-popup__btn calendar-info-popup__btn--notification btn btn-light btn-new16 fancybox.ajax fancyboxValidateModal js-calendar-info-notifications"
                data-fancybox-href="<?php echo __SITE_URL . "calendar/open_notifications_settings?calendar={$calendar['id']}"; ?>"
                data-title="<?php echo translate('calendar_popup_notification_settings'); ?>"
                data-wrap-class="fancybox-notifications-settings-form"
                data-mw="470"
                data-js-action="calendar-info:1"
                data-id="<?php echo $calendar['id']; ?>"
                <?php echo addQaUniqueIdentifier('ep-events-calendar__popup__notification_btn'); ?>
                type="button"
            >
                <?php echo widgetGetSvgIcon('bell-simple', 14, 19); ?>
                <span><?php echo translate('calendar_popup_notification'); ?></span>
            </button>
        <?php }?>
    <?php } ?>

    <button
        class="calendar-info-popup__btn btn btn-light btn-new16 call-action"
        data-js-action="calendar-info:delete"
        data-id="<?php echo $calendar['id']; ?>"
        data-type="<?php echo $calendar['event_type']; ?>"
        data-source-id="<?php echo $calendar['source_id']; ?>"
        <?php echo addQaUniqueIdentifier('ep-events-calendar__popup__delete_btn'); ?>
        type="button"
    >
        <?php echo widgetGetSvgIcon('basket-remove', 14, 15); ?>
        <span><?php echo translate('calendar_popup_delete'); ?></span>
    </button>
</div>
