<div class="ep-events-recommended">
    <div class="ep-events-recommended__ttl-wrap">
        <div class="ep-events-recommended__ttl">Recommended by Export Portal</div>
    </div>
    <div class="ep-events__item ">
        <div class="ep-events__img">
            <div class="ep-events__icons-wrap">
                <?php views('new/ep_events/add_calendar_btn_view', ['event' => $highlightedEvent]);?>
                <div class="ep-events__share-wrap ep-events__icon call-action" data-js-action="share:event" data-item="<?php echo $highlightedEvent['id'];?>">
                    <?php echo widgetGetSvgIcon('share2', 17.95); ?>
                </div>
            </div>
            <a href="<?php echo getEpEventDetailUrl($highlightedEvent);?>">
                <picture class="display-b h-100pr">
                    <source media="(min-width: 426px) and (max-width: 575px)" srcset="<?php echo cleanOutput($highlightedEvent['image']); ?>">
                    <img class="js-lazy js-fs-image"
                         src="<?php echo cleanOutput($highlightedEvent['image']); ?>"
                         width="290"
                         height="167"
                         data-fsw="290"
                         data-fsh="167"
                         alt="<?php echo cleanOutput($highlightedEvent['title']);?>" <?php echo addQaUniqueIdentifier('ep-events__event__img'); ?>>
                </picture>

                <div class="ep-events__type-wrap" <?php echo addQaUniqueIdentifier('ep-events__event__type'); ?>>
                    <?php if ('offline' !== $event['type']['alias']) { ?>
                        <span class="ep-events__type" <?php echo addQaUniqueIdentifier('ep-events__event__type-el'); ?>><?php echo cleanOutput($highlightedEvent['type']['title']);?></span>
                    <?php } ?>
                    <?php if ((new DateTime())->createFromFormat('Y-m-d H:i:s', $highlightedEvent['end_date']) < (new DateTime())) {?>
                        <div class="ep-events__type ep-events__type--past" <?php echo addQaUniqueIdentifier('ep-events__event__type-el'); ?>><?php echo translate('ep_events_past_label'); ?></div>
                    <?php }?>
                </div>
            </a>
        </div>

        <div class="ep-events__info" <?php echo addQaUniqueIdentifier('ep-events__event__info'); ?>>
            <div class="ep-events__date" <?php echo addQaUniqueIdentifier('ep-events__event__date'); ?>>
                <span><?php echo getTimeInterval($highlightedEvent['start_date'], $highlightedEvent['end_date']);?></span>
                <span class="ep-events__active-circle"></span>
            </div>
            <div class="ep-events__ttl">
                <a class="ep-events__link" title="<?php echo cleanInput($highlightedEvent['title']);?>" href="<?php echo getEpEventDetailUrl($highlightedEvent);?>" <?php echo addQaUniqueIdentifier('ep-events__event__ttl'); ?>><?php echo cleanInput($highlightedEvent['title']);?></a>
            </div>

            <?php if ('online' === $highlightedEvent['type']['alias']) { ?>
                <div class="ep-events__place" <?php echo addQaUniqueIdentifier('ep-events__event__place'); ?>><?php echo translate('ep_events_detail_online_label'); ?></div>
            <?php } elseif ('offline' === $highlightedEvent['type']['alias']) {?>
                <div class="ep-events__country">
                    <img
                        class="js-lazy"
                        data-src="<?php echo getCountryFlag($highlightedEvent['country']['name']); ?>"
                        src="<?php echo getLazyImage(24, 24); ?>"
                        width="24"
                        height="24"
                        alt="<?php echo cleanOutput($highlightedEvent['country']['name']); ?>"
                        <?php echo addQaUniqueIdentifier('ep-events__event__country_img'); ?>>
                    <span <?php echo addQaUniqueIdentifier('ep-events__event__country'); ?>><?php echo cleanOutput($highlightedEvent['country']['name'] . ', ' . $highlightedEvent['state']['name']); ?></span>
                </div>
            <?php } else {?>
                <div class="ep-events__speaker">
                    <span class="ep-events__speaker-txt-gray"><?php echo translate('ep_events_detail_speaker_label'); ?></span> <span <?php echo addQaUniqueIdentifier('ep-events__event__speaker'); ?>><?php echo cleanOutput($highlightedEvent['speaker']['name']); ?></span>
                </div>
            <?php } ?>

            <div class="ep-events__desc" <?php echo addQaUniqueIdentifier('ep-events__event__desc'); ?>><?php echo cleanOutput($highlightedEvent['title']);?></div>
        </div>
    </div>
</div>
