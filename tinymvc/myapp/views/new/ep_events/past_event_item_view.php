<?php if ($pastEvents) {?>
    <?php foreach ($pastEvents as $pastEvent) {?>
        <div class="ep-events-past__item" <?php echo addQaUniqueIdentifier('ep-events__event__info'); ?>>
            <div class="ep-events-past__top" <?php echo addQaUniqueIdentifier('ep-events__event__type'); ?>>
                <?php if ('offline' !== $pastEvent['type']['alias']) {?>
                    <span class="ep-events-past__type"><?php echo $pastEvent['type']['title'];?></span>
                <?php }?>
                <span class="ep-events-past__date" <?php echo addQaUniqueIdentifier('ep-events__past_event__date'); ?>><?php echo getTimeInterval($pastEvent['start_date'], $pastEvent['end_date']); ?></span>
            </div>

            <a class="ep-events-past__ttl"
                title="<?php echo cleanOutput($pastEvent['title']);?>"
                href="<?php echo getEpEventDetailUrl($pastEvent) ;?>"
                <?php echo addQaUniqueIdentifier('ep-events__event__ttl'); ?>>
                <?php echo cleanOutput($pastEvent['title']);?>
            </a>

            <?php if ('online' === $pastEvent['type']['alias']) {?>
                <div class="ep-events-past__place" <?php echo addQaUniqueIdentifier('ep-events__event__place'); ?>><?php echo translate('ep_events_detail_online_label'); ?></div>
            <?php } elseif ('webinar' === $pastEvent['type']['alias']) {?>
                <div class="ep-events-past__speaker">
                    <span class="ep-events__speaker-txt-gray"><?php echo translate('ep_events_detail_speaker_label'); ?></span> <span <?php echo addQaUniqueIdentifier('ep-events__event__speaker'); ?>><?php echo $pastEvent['speaker']['name'];?></span></div>
            <?php } else {?>
                <div class="ep-events-past__country">
                    <img class="js-lazy"
                        data-src="<?php echo getCountryFlag($pastEvent['country']['name']);?>"
                        src="<?php echo getLazyImage(24, 24); ?>"
                        width="24"
                        height="24"
                        alt="<?php echo cleanOutput($pastEvent['country']['name']);?>"
                        <?php echo addQaUniqueIdentifier('ep-events__event__country_img'); ?>>
                    <span <?php echo addQaUniqueIdentifier('ep-events__event__country'); ?>><?php echo implode(', ', array_filter([$pastEvent['country']['name'] ?? null, $pastEvent['state']['name'] ?? null]));?></span>
                </div>
            <?php }?>
        </div>
    <?php }?>
<?php }?>
