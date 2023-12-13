<div class="container-1420">
    <div class="event-detail txt-break-word">
        <div class="event-detail__info">
            <div class="event-detail__info-item">
                <div class="event-detail__info-label"><?php echo translate('ep_events_detail_when_label'); ?></div>
                <div class="event-detail__info-desc">
                    <?php echo getDateTimeInterval($event['start_date'], $event['end_date'], 'Y-m-d H:i:s', ' to '); ?>
                </div>
            </div>

            <?php if (!empty($event['type']) && 'webinar' === $event['type']['alias']) { ?>
                <div class="event-detail__info-item">
                    <div class="event-detail__info-label"><?php echo translate('ep_events_detail_virtual_host_label'); ?></div>
                    <div class="event-detail__info-desc">
                        <a href="<?php echo cleanOutput($event['url']); ?>" target="blank"><?php echo cleanOutput($event['url']); ?></a>
                    </div>
                </div>
            <?php } ?>

            <?php if (!empty($event['speaker'])) { ?>
                <div class="event-detail__info-item">
                    <div class="event-detail__info-label"><?php echo translate('ep_events_detail_speaker_label'); ?></div>
                    <div class="event-detail__info-desc">
                        <div class="event-detail__speaker">
                            <div class="event-detail__speaker-img">
                                <img
                                    class="js-fs-image"
                                    data-fsw="42"
                                    data-fsh="42"
                                    src="<?php echo $event['speaker']['photo']; ?>"
                                    alt="<?php echo cleanOutput($event['speaker']['name']); ?>"
                                    <?php echo addQaUniqueIdentifier('page__events-detail__img-user'); ?>>
                            </div>
                            <div class="event-detail__speaker-name"><?php echo cleanOutput($event['speaker']['name']); ?>
                                <div class="event-detail__speaker-position"><?php echo cleanOutput($event['speaker']['position']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php if ('webinar' !== $event['type']['alias']) { ?>
                <div class="event-detail__info-item">
                    <div class="event-detail__info-label"><?php echo translate('ep_events_detail_where_label'); ?></div>
                    <div class="event-detail__info-desc">
                        <?php if ('online' !== $event['type']['alias']) { ?>
                            <img
                                width="24"
                                height="18"
                                src="<?php echo getCountryFlag($event['country']['name']); ?>"
                                alt="<?php echo cleanOutput($event['country']['name']); ?>"
                            >
                            <span class="ml-5 ml-sm-0">
                            <?php echo cleanOutput("{$event['country']['name']}, {$event['state']['name']}, {$event['address']}, {$event['city']['name']}"); ?>
                        </span>
                        <?php } else { ?>
                            <span><?php echo translate('ep_events_detail_online_label'); ?></span>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>

            <?php if (null !== $event['ticket_price']) { ?>
                <div class="event-detail__info-item">
                    <div class="event-detail__info-label"><?php echo translate('ep_events_detail_price_label'); ?></div>
                    <div class="event-detail__info-desc"><?php echo 0 === (int) $event['ticket_price'] ? translate('ep_events_free_price') : get_price($event['ticket_price']) . translate('ep_events_per_ticket_txt'); ?></div>
                </div>
            <?php } ?>

            <?php if (!empty($event['nr_of_participants'])) { ?>
                <div class="event-detail__info-item">
                    <div class="event-detail__info-label"><?php echo translate('ep_events_detail_participants_label'); ?></div>
                    <div class="event-detail__info-desc"><?php echo $event['nr_of_participants']; ?> <?php echo translate('ep_events_detail_persons_label'); ?></div>
                </div>
            <?php } ?>

            <?php if (!empty($event['url']) && 'webinar' !== $event['type']['alias']) { ?>
                <div class="event-detail__info-item lh-26">
                    <div class="event-detail__info-label"><?php echo translate('ep_events_detail_link_label'); ?></div>
                    <div class="event-detail__info-desc">
                        <a href="<?php echo cleanOutput($event['url']); ?>" target="blank"><?php echo cleanOutput($event['url']); ?></a>
                    </div>
                </div>
            <?php } ?>

            <div class="event-detail__info-item">
                <div class="event-detail__info-label"><?php echo translate('ep_events_detail_category_label'); ?></div>
                <div class="event-detail__info-desc"><?php echo cleanOutput($event['category']['name']); ?></div>
            </div>

            <div class="event-detail__info-item">
                <div class="event-detail__info-label"><?php echo translate('ep_events_detail_type_label'); ?></div>
                <div class="event-detail__info-desc"><?php echo cleanOutput($event['type']['title']); ?></div>
            </div>

            <?php if ($event['is_upcoming_by_ep'] || $event['is_recommended_by_ep'] || $event['is_attended_by_ep']) { ?>
                <div class="event-detail__info-item">
                    <div class="event-detail__info-label"><?php echo translate('ep_events_detail_label'); ?></div>
                    <div class="event-detail__info-desc">
                        <div class="ep-events__labels">
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
                    </div>
                </div>
            <?php } ?>
        </div>

        <picture class="event-detail__img">
            <source media="(max-width: 425px)" srcset="<?php echo $event['thumbs']['small']; ?>">
            <source media="(min-width: 426px) and (max-width: 575px)" srcset="<?php echo $event['thumbs']['medium']; ?>">
            <img class="image js-fs-image"
                 data-fsw="858"
                 data-fsh="373"
                 src="<?php echo $event['main_image']; ?>"
                 alt="<?php echo cleanOutput($event['title']); ?>"
                <?php echo addQaUniqueIdentifier('page__events-detail__img'); ?>>
        </picture>

        <div class="event-detail__desc"><?php echo $event['description']; ?></div>

        <?php if (!empty($event['tags'])) { ?>
            <div class="ep-tags_new">
                <?php foreach ($event['tags'] as $tag) {
                    $tag = str_replace('#', '', $tag);
                    ?>
                    <span class="ep-tags__item_new" title="<?php echo capitalWord($tag); ?>">
                    # <?php echo capitalWord($tag); ?>
                </span>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if (!empty($event['agenda'])) { ?>
            <div class="event-detail__heading"><?php echo translate('ep_events_detail_agenda_heading'); ?></div>

            <div class="event-detail__agenda">
                <?php foreach ($event['agenda'] as $agendaItem) { ?>
                    <div class="event-detail__agenda-item">
                        <div class="event-detail__agenda-start"><?php echo getDateFormat($agendaItem['startDate'], 'm/d/Y H:i', 'g:iA'); ?></div>
                        <div><?php echo $agendaItem['description']; ?></div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if (!empty($event['partners'])) { ?>
            <div class="event-detail__heading"><?php echo translate('ep_events_detail_partners_heading'); ?></div>

            <div class="event-detail__partners">
                <?php foreach ($event['partners'] as $partner) { ?>
                    <div class="event-detail__partners-item-wrap">
                        <div class="event-detail__partners-item">
                            <img class="event-detail__partners-logo js-lazy js-fs-image"
                                 data-fsw="206"
                                 data-fsh="113"
                                 data-src="<?php echo $partner['image']; ?>"
                                 src="<?php echo getLazyImage(206, 113); ?>"
                                 alt="<?php echo cleanOutput($partner['name']); ?>"
                            >
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if ($event['gallery']) { ?>
            <div class="event-detail__heading"><?php echo translate('ep_events_detail_gallery_heading'); ?></div>

            <div class="event-detail__gallery">
                <?php foreach ($event['gallery'] as $galleryItem) { ?>
                    <div class="event-detail__gallery-item-wrap">
                        <a class="link fancyboxGallery event-detail__gallery-img"
                           data-image-index="<?php echo $key; ?>"
                           data-title="<?php echo cleanOutput($event['title']); ?>"
                           rel="galleryItem"
                           href="<?php echo $galleryItem['image']; ?>"
                        >
                            <img class="image js-lazy js-fs-image"
                                 data-src="<?php echo $galleryItem['image']; ?>"
                                 src="<?php echo getLazyImage(206, 155); ?>"
                                 alt="<?php echo cleanOutput($event['title']); ?>"
                                 data-fsw="206"
                                 data-fsh="155"
                                 width="206"
                                 height="155"
                                <?php echo addQaUniqueIdentifier('page__events-detail__img-gallery'); ?>
                            >
                        </a>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if (!empty($event['why_attend'])) { ?>
            <div class="event-detail__heading"><?php echo translate('ep_events_detail_why_attend_heading'); ?></div>
            <div class="event-detail__attend"><?php echo $event['why_attend']; ?></div>
        <?php } ?>

        <?php if (!empty($comments)) { ?>
            <?php widgetComments($comments['type_id'], $comments['hash_components']); ?>
        <?php } ?>

        <?php views('new/ep_events/invite_banner_view');?>

        <?php if(!empty($relatedEvents)) { ?>
            <div class="event-detail__heading"><?php echo translate('ep_events_detail_related_heading'); ?></div>
            <div class="ep-events__list">
                <?php views('new/ep_events/event_item_view', ['events' => $relatedEvents]); ?>
            </div>
        <?php } ?>

        <?php if (!empty($highlightedEvent)) {?>
            <?php views('new/ep_events/highlighted_event_view'); ?>
        <?php }?>

        <?php views('new/ep_events/suggest_banner_view');?>
    </div>
</div>

<?php
    encoreEntryLinkTags('ep_event_page');
    encoreEntryScriptTags('ep_event_page');
    encoreLinks();
?>
