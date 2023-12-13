<div class="event-detail__btns">
    <?php
        $linkContactUs = __SITE_URL . 'contact/popup_forms/contact_us';
        if (isset($webpackData)) {
            $linkContactUs .= '/webpack';
        }
    ?>
    <button
        class="btn btn-primary btn-new16 event-detail__contact-btn fancybox.ajax fancyboxValidateModal"
        data-fancybox-href="<?php echo $linkContactUs;?>"
        data-mw="800"
        data-title="<?php echo translate('ep_events_contact_us_title'); ?>"
        type="button"
        <?php echo addQaUniqueIdentifier('ep-events__event_detail__calendar_btn'); ?>
    >
        <?php echo 'webinar' === $event['type']['alias'] ? translate('ep_events_register_btn') : translate('ep_events_contact_us_title'); ?>
    </button>

    <?php if (\DateTime::createFromFormat('Y-m-d H:i:s', $event['end_date']) > new \DateTime()) {?>
        <?php views('new/ep_events/add_calendar_btn_view'); ?>
    <?php }?>

    <button class="btn btn-light btn-new16 event-detail__share-btn call-action"
        data-js-action="share:event"
        type="button"
        data-item="<?php echo $event['id'];?>"
        <?php echo addQaUniqueIdentifier('ep-events__event_detail__share_btn'); ?>
    >
        <?php echo widgetGetSvgIcon('share2', 17); ?> <span><?php echo translate('ep_events_share_title'); ?></span>
    </button>
</div>
