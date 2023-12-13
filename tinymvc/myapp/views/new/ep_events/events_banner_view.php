<div class="event-banner" <?php echo addQaUniqueIdentifier('page__events__event-banner'); ?>>
    <div class="event-banner__container">
        <a href="<?php echo getEpEventDetailUrl($eventPromotion);?>">
            <div class="event-banner__img">
                <picture>
                    <img
                        class="js-lazy"
                        src="<?php echo $eventPromotion['images']['desktop']; ?>"
                        alt="<?php echo cleanOutput($eventPromotion['title']); ?>"
                    >
                </picture>
            </div>
        </a>
        <div class="event-banner__info">
                <div class="event-banner__title-date" <?php echo addQaUniqueIdentifier('page__events__event-banner_suptitle'); ?>>
                    <?php echo translate('ep_events_countdown_banner_title', [
                        '{{TAG}}' => '<span>' . cleanOutput($eventPromotion['countdown']['text']) . '</span>',
                    ]); ?>
                </div>
                <div class="event-banner__desc" <?php echo addQaUniqueIdentifier('page__events__event-banner_title'); ?>>
                     <a href="<?php echo getEpEventDetailUrl($eventPromotion);?>" class="ep-events__link event-banner__title"><?php echo cleanOutput($eventPromotion['title']); ?></a>
                </div>
            </div>
    </div>
    <div class="event-banner__countdown">
        <div id="js-event-countdown" class="event-banner__countdown-row">
            <div class="event-banner__countdown-item">
                <div id="js-event-countdown-days-left" class="event-banner__countdown-numbers"><?php echo cleanOutput($eventPromotion['countdown']['days']); ?></div>
                <div id="js-event-countdown-days-txt" class="event-banner__countdown-text"><?php echo translate('countdown_days_txt'); ?></div>
            </div>
            <div class="event-banner__countdown-item">
                <div id="js-event-countdown-hours-left" class="event-banner__countdown-numbers"><?php echo cleanOutput($eventPromotion['countdown']['hours']); ?></div>
                <div class="event-banner__countdown-text"><?php echo translate('countdown_hours_txt'); ?></div>
            </div>
            <div class="event-banner__countdown-item">
                <div id="js-event-countdown-minutes-left" class="event-banner__countdown-numbers"><?php echo cleanOutput($eventPromotion['countdown']['minutes']); ?></div>
                <div class="event-banner__countdown-text"><?php echo translate('countdown_minutes_txt'); ?></div>
            </div>
            <div class="event-banner__countdown-item">
                <div id="js-event-countdown-seconds-left" class="event-banner__countdown-numbers"><?php echo cleanOutput($eventPromotion['countdown']['seconds']); ?></div>
                <div class="event-banner__countdown-text"><?php echo translate('countdown_seconds_txt'); ?></div>
            </div>
        </div>
    </div>
</div>

<?php echo dispatchDynamicFragmentInCompatMode(
    'event:countown',
    asset('public/plug/js/ep_events/events.js', 'legacy'),
    sprintf(
        "function () {
            if (!('EventsModule' in window)) {
                if (__debug_mode) {
                    console.error(new SyntaxError(\"'EventsModule' must be defined\"))
                }

                return;
            }

            EventsModule.default(%s);
        }",
        json_encode(
            $options = [
                'selectors' => [
                    'eventCountDown' => '#js-event-countdown',
                    'daysLeft'       => '#js-event-countdown-days-left',
                    'hoursLeft'      => '#js-event-countdown-hours-left',
                    'minutesLeft'    => '#js-event-countdown-minutes-left',
                    'secondsLeft'    => '#js-event-countdown-seconds-left',
                    'daysTxt'        => '#js-event-countdown-days-txt',
                ],
                'startDate' => $eventPromotion['countdown']['end_date']->format(DATE_ATOM),
            ]
        )
    ),
    [$options],
    true
); ?>
