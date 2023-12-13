<?php
$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $event['start_date']);
$endDate = DateTime::createFromFormat('Y-m-d H:i:s', $event['end_date']);
$now = new \DateTime();
?>
<div class="container-1420">
    <div class="event-detail__header-wrap">
        <div class="event-detail__header" data-start-date="<?php echo $startDate > $now && 7 > (int) $startDate->diff(new DateTime()) ? $startDate->format(DATE_ATOM) : null;?>">
            <div class="event-detail__header-info">
                <div class="event-detail__header-top">
                    <?php if ('offline' !== $event['type']['alias']) { ?>
                        <div class="event-detail__label"><?php echo cleanOutput($event['type']['title']); ?></div>
                    <?php } ?>

                    <?php if ($startDate > $now) { ?>
                        <div class="event-detail__label"><?php echo translate('ep_events_upcoming_label'); ?></div>
                    <?php } elseif ($startDate <= $now && $now <= $endDate) { ?>
                        <div class="event-detail__label event-detail__label--active"><?php echo translate('ep_events_active_label'); ?></div>
                    <?php } elseif ($endDate < $now) { ?>
                        <div class="event-detail__label event-detail__label--past"><?php echo translate('ep_events_past_label'); ?></div>
                    <?php } ?>

                    <div class="event-detail__part">
                        <div class="event-detail__date <?php echo $startDate <= $now && $now <= $endDate ? 'event-detail__date--active' : ($endDate < $now ? 'event-detail__date--past' : ''); ?>">
                            <?php echo getTimeInterval($event['start_date'], $event['end_date']); ?>
                        </div>
                        <div class="event-detail__views">
                            <i class="ep-icon ep-icon_views"></i>
                            <span <?php echo addQaUniqueIdentifier('page__events-detail__views'); ?>><?php echo $event['views']; ?></span>
                            <span class="hide-767"><?php echo translate('ep_events_detail_views'); ?></span>
                        </div>
                    </div>
                </div>
                <h1 class="event-detail__header-ttl"><?php echo cleanOutput($event['title']); ?></h1>
            </div>
        </div>

        <?php if ($isDetailPage) { ?>
            <div class="events-detail-sidebar js-detail-sidebar">
                <div class="events-detail-sidebar__wrap">
                    <?php if ($startDate > $now && 7 > (int) $startDate->diff(new DateTime())->format('%a')) { ?>
                        <div id="js-event-countdown" class="event-countdown">
                            <div class="event-countdown__item">
                                <div id="js-event-countdown-days-left" class="event-countdown__numbers">00</div>
                                <div id="js-event-countdown-days-txt" class="event-countdown__text"><?php echo translate('countdown_days_txt'); ?></div>
                            </div>
                            <div class="event-countdown__item">
                                <div id="js-event-countdown-hours-left" class="event-countdown__numbers">00</div>
                                <div class="event-countdown__text"><?php echo translate('countdown_hours_txt'); ?></div>
                            </div>
                            <div class="event-countdown__item">
                                <div id="js-event-countdown-minutes-left" class="event-countdown__numbers">00</div>
                                <div class="event-countdown__text"><?php echo translate('countdown_minutes_txt'); ?></div>
                            </div>
                            <div class="event-countdown__item">
                                <div id="js-event-countdown-seconds-left" class="event-countdown__numbers">00</div>
                                <div class="event-countdown__text"><?php echo translate('countdown_seconds_txt'); ?></div>
                            </div>
                        </div>
                    <?php } ?>
                    <?php views()->display('new/ep_events/events_contact_btns_view');?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
