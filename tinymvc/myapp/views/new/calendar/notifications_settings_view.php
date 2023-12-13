<form
    id="js-notifications-settings-form"
    class="notifications-settings-form validateModal"
    data-url="<?php echo $formAction; ?>"
    data-js-action="calendar-notifications:form-submit"
    data-body = ".js-notifications-settings-form--body"
    data-notification-btn = ".js-add-notification-settings-btn"
    data-select = ".js-notifications-settings-select"
    data-input = ".js-notifications-settings-input"
    data-event-id="<?php echo $sourceId ?? '';?>"
>
    <p class="notifications-settings-form__text">
        <?php if ($remainingDaysBeforeStart > 0) {?>
            <?php echo translate('calendar_notifications_settings_info_upcoming_event', null, true);?>
        <?php } elseif ($remainingDaysBeforeStart === 0) {?>
            <?php echo translate('calendar_notifications_settings_info_event_starts_soon', null, true);?>
        <?php } else {?>
            <?php echo translate('calendar_notifications_settings_info_active_event', null, true);?>
        <?php }?>
    </p>

    <div class="notifications-settings-form__body js-notifications-settings-form--body">
        <?php foreach ($calendar['notifications'] ?: [] as $notification) {?>
            <div class="notifications-settings-form__row">
                <select
                    name="types[]"
                    class="validate[required] ep-select ep-select--popup notifications-settings-form__select js-notifications-settings-select"
                    <?php echo addQaUniqueIdentifier('form__notifications-settings__select'); ?>
                >
                    <option selected disabled>
                        <?php echo translate('ep_events_calendar_choose_type'); ?>
                    </option>
                    <option value="system" <?php echo selected('system', (string) $notification['notification_type']); ?>>
                        <?php echo translate('ep_events_calendar_notification'); ?>
                    </option>
                    <option value="email" <?php echo selected('email', (string) $notification['notification_type']); ?>>
                        <?php echo translate('ep_events_calendar_email'); ?>
                    </option>
                </select>
                <div class="notifications-settings-form__row-input">
                    <input
                        class="ep-input ep-input--popup notifications-settings-form__input js-notifications-settings-input validate[required,max[<?php echo $validationRules['maxDays']; ?>], min[0]]"
                        type="number"
                        name="notifications[]"
                        value="<?php echo $notification['count_days']; ?>"
                    >
                    <span class="notifications-settings-form__row-text">
                    <?php echo translate('ep_events_calendar_days_before'); ?>
                </span>
                    <button class="notifications-settings-form__remove-btn call-action" type="button" data-js-action="calendar-notifications:remove-notification">
                        <?php echo widgetGetSvgIcon('remove', 14.061, 14.063); ?>
                    </button>
                </div>
            </div>
        <?php } ?>
    </div>
    <button
        class="notifications-settings-form__add-btn call-action js-add-notification-settings-btn <?php echo $remainingDaysBeforeStart < 1 ? 'notifications-settings-form__disable' : '';?>"
        type="button"
        data-js-action="calendar-notifications:add-notification"
        <?php echo addQaUniqueIdentifier('ep-events__popup__notification_settings__add_notification_btn'); ?>
    >
        <?php echo widgetGetSvgIcon('bell-simple', 15, 19); ?>
        <span><?php echo translate('ep_events_calendar_add_notification'); ?></span>
    </button>
    <div class="notifications-settings-form__actions">
        <?php if (!empty($calendar['id'])) { ?>
            <button
                class="btn btn-new16 btn-primary notifications-settings-form__actions-btn"
                type="submit"
                <?php echo addQaUniqueIdentifier('ep-events__popup__notification_settings__save_btn'); ?>
            >
                <?php echo translate('ep_events_calendar_save_settings'); ?>
            </button>
        <?php } else { ?>
            <button
                class="btn btn-new16 btn-primary notifications-settings-form__actions-btn"
                type="submit"
                <?php echo addQaUniqueIdentifier('ep-events__popup__notification_settings__save_btn'); ?>
            >
                <?php echo translate('ep_events_calendar_save_and_add'); ?>
            </button>
        <?php } ?>
        <button
            class="btn btn-new16 btn-light notifications-settings-form__actions-btn call-action"
            type="button"
            data-js-action="calendar-notifications:form-close"
            <?php echo addQaUniqueIdentifier('ep-events__popup__notification_settings__cancel_btn'); ?>
        >
            <?php echo translate('ep_events_calendar_cancel'); ?>
        </button>
    </div>


    <?php if (!empty($calendar['id'])) { ?>
        <input type="hidden" name="calendar" value="<?php echo $calendar['id']; ?>">
    <?php } else { ?>
        <input type="hidden" name="type" value="<?php echo $eventType; ?>">
        <input type="hidden" name="source" value="<?php echo $sourceId; ?>">
    <?php } ?>
</form>

<?php echo dispatchDynamicFragment(
    'notifications-settings:popup',
    [
        'form'           => '#js-notifications-settings-form',
        'maxDays'        => $validationRules['maxDays'],
    ],
    true,
);
    ?>
