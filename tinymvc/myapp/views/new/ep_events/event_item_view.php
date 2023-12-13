<?php
    if ($needToShowBanners) {
        $alreadyDispalyedEvents = 0;
    }
?>
<?php foreach ($events as $key => $event) {?>
    <?php
        $startDate = DateTime::createFromFormat('Y-m-d H:i:s', $event['start_date']);
        $endDate = DateTime::createFromFormat('Y-m-d H:i:s', $event['end_date']);
        $now = new \DateTime();
    ?>
    <div class="ep-events__item <?php echo $startDate <= $now && $now <= $endDate ? ' active' : '';?>">
        <div class="ep-events__img">
            <div class="ep-events__icons-wrap">
                <?php if ($endDate > $now) {?>
                    <?php views('new/ep_events/add_calendar_btn_view', ['event' => $event]);?>
                <?php }?>
                <div class="ep-events__share-wrap ep-events__icon call-action" data-js-action="share:event" data-item="<?php echo "{$event['id']}";?>" <?php echo addQaUniqueIdentifier('ep-events__event__share_btn'); ?>>
                    <?php echo widgetGetSvgIcon('share2', 17.95); ?>
                </div>
            </div>
            <a href="<?php echo getEpEventDetailUrl($event); ?>">
                <picture class="display-b h-100pr">
                    <source
                        media="(min-width: 426px) and (max-width: 575px)"
                        <?php if ($key > 0) { ?>
                            srcset="<?php echo getLazyImage(232, 167); ?>"
                            data-srcset="<?php echo $event['thumbs']['medium']; ?>"
                        <?php } else { ?>
                            srcset="<?php echo $event['thumbs']['medium']; ?>"
                        <?php } ?>
                >
                    <img
                        <?php if ($key > 0) { ?>
                            class="js-lazy js-fs-image"
                            data-src="<?php echo $event['thumbs']['small']; ?>"
                            src="<?php echo getLazyImage(232, 167); ?>"
                        <?php } else { ?>
                            class="js-fs-image"
                            src="<?php echo $event['thumbs']['small']; ?>"
                        <?php } ?>
                        width="290"
                        height="167"
                        data-fsw="290"
                        data-fsh="167"
                        alt="<?php echo cleanOutput($event['main_image']); ?>"
                        <?php echo addQaUniqueIdentifier('ep-events__event__img'); ?>>
                </picture>

                <div class="ep-events__type-wrap" <?php echo addQaUniqueIdentifier('ep-events__event__type'); ?>>
                    <?php if ('offline' !== $event['type']['alias']) { ?>
                        <span class="ep-events__type"  <?php echo addQaUniqueIdentifier('ep-events__event__type-el'); ?>><?php echo cleanOutput($event['type']['title']); ?></span>
                    <?php } ?>
                    <?php if ($endDate < $now) {?>
                        <div class="ep-events__type ep-events__type--past" <?php echo addQaUniqueIdentifier('ep-events__event__type-el-past'); ?>><?php echo translate('ep_events_past_label'); ?></div>
                    <?php }?>
                </div>
            </a>
        </div>

        <div class="ep-events__info" <?php echo addQaUniqueIdentifier('ep-events__event__info'); ?>>
            <div class="ep-events__date" <?php echo addQaUniqueIdentifier('ep-events__event__date'); ?>>
                <span><?php echo getTimeInterval($event['start_date'], $event['end_date']); ?></span>
                <span class="ep-events__active-circle"></span>
            </div>
            <div class="ep-events__ttl">
                <a class="ep-events__link"
                    title="<?php echo cleanOutput($event['title']); ?>"
                    href="<?php echo getEpEventDetailUrl($event) ;?>"
                    <?php echo addQaUniqueIdentifier('ep-events__event__ttl'); ?>
                >
                    <?php echo cleanOutput($event['title']); ?>
                </a>
            </div>

            <?php if ('offline' === $event['type']['alias']) { ?>
                <div class="ep-events__country">
                    <img
                        class="js-lazy"
                        data-src="<?php echo getCountryFlag(cleanOutput($event['country']['name'])); ?>"
                        src="<?php echo getLazyImage(24, 24); ?>"
                        width="24"
                        height="24"
                        alt="<?php echo cleanOutput($event['country']['name']); ?>"
                        <?php echo addQaUniqueIdentifier('ep-events__event__country_img'); ?>>
                    <span <?php echo addQaUniqueIdentifier('ep-events__event__country'); ?>><?php echo cleanOutput($event['country']['name'] . ', ' . $event['state']['name']); ?></span>
                </div>
            <?php } ?>

            <?php if ('online' === $event['type']['alias']) { ?>
                <div class="ep-events__place" <?php echo addQaUniqueIdentifier('ep-events__event__place'); ?>><?php echo translate('ep_events_detail_online_label'); ?></div>
            <?php } ?>

            <?php if ('webinar' === $event['type']['alias']) { ?>
                <div class="ep-events__speaker">
                    <span class="ep-events__speaker-txt-gray"><?php echo translate('ep_events_detail_speaker_label'); ?></span> <span <?php echo addQaUniqueIdentifier('ep-events__event__speaker'); ?>><?php echo cleanOutput($event['speaker']['name']); ?></span>
                </div>
            <?php } ?>

            <div class="ep-events__desc" <?php echo addQaUniqueIdentifier('ep-events__event__desc'); ?>><?php echo cleanOutput($event['short_description']);?></div>

            <?php if ($event['is_upcoming_by_ep'] || $event['is_recommended_by_ep'] || $event['is_attended_by_ep']) { ?>
                <div class="ep-events__labels" <?php echo addQaUniqueIdentifier('ep-events__event__label-wrap'); ?>>
                    <?php if ($event['is_upcoming_by_ep']) { ?>
                        <a class="ep-events__label" href="<?php echo $upcomingLabelUrl;?>"><?php echo translate('ep_events_detail_upcoming_label'); ?></a>
                    <?php } ?>
                    <?php if ($event['is_recommended_by_ep']) { ?>
                        <a class="ep-events__label" href="<?php echo $recommendedLabelUrl;?>"><?php echo translate('ep_events_detail_recommended_label'); ?></a>
                    <?php } ?>
                    <?php if ($event['is_attended_by_ep']) { ?>
                        <a class="ep-events__label" href="<?php echo $attendedLabelUrl;?>"><?php echo translate('ep_events_detail_attended_label'); ?></a>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php
        if ($needToShowBanners) {
            $alreadyDispalyedEvents++;

            if ($firstBannerPosition === $alreadyDispalyedEvents) {
                views('new/ep_events/invite_banner_view');
            }

            if ($secondBannerPosition === $alreadyDispalyedEvents) {
                views('new/ep_events/suggest_banner_view');
            }
        }
    ?>
<?php }?>
