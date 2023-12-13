<div class="event-advertizing__modal-row">
    <a class="event-advertizing__modal-item" href="<?php echo __SITE_URL?>ep_events/time/upcoming">
        <span class="event-advertizing__modal-icon">
            <?php echo widgetGetSvgIcon("clock", 40, 40); ?>
        </span>
        <span><?php echo translate('event_feature_advertizing_popup_upcoming_events'); ?></span>
    </a>
    <a class="event-advertizing__modal-item" href="<?php echo __SITE_URL?>ep_events/label/attended">
        <span class="event-advertizing__modal-icon event-advertizing__modal-icon--mr-10">
            <?php echo widgetGetSvgIcon("attend", 44, 44); ?>
        </span>
        <span><?php echo translate('event_feature_advertizing_popup_attended_by_ep'); ?></span>
    </a>
    <a class="event-advertizing__modal-item" href="<?php echo __SITE_URL?>ep_events/label/recommended">
        <span class="event-advertizing__modal-icon event-advertizing__modal-icon--mb-5">
            <?php echo widgetGetSvgIcon("recommended", 45, 45); ?>
        </span>
        <span><?php echo translate('event_feature_advertizing_popup_recommended_events'); ?></span>
    </a>
    <button
        class="event-advertizing__modal-item call-action"
        data-js-action="popup:call-popup"
        data-call-type="global"
        data-popup="subscribe"
        type="button"
    >
        <span class="event-advertizing__modal-icon event-advertizing__modal-icon--mt-5">
            <?php echo widgetGetSvgIcon("bell", 44, 44) ?>
        </span>
        <span><?php echo translate('event_feature_advertizing_popup_subscribe_to_newsletter') ?></span>
    </button>
</div>
