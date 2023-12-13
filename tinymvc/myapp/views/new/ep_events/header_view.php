<section class="ep-events__header container-1420">
    <div class="ep-events__inner">
    <h1 class="ep-events__title">
        <?php echo $headerTitle; ?>
    </h1>
    <picture class="ep-events__picture">
        <source
            media="(max-width: 425px)"
            srcset="<?php echo asset("public/build/images/ep_events/header-m.jpg"); ?> 1x,
            <?php echo asset("public/build/images/ep_events/header-m@2x.jpg"); ?> 2x"
            >
        <source
            media="(max-width: 991px)"
            srcset="<?php echo asset("public/build/images/ep_events/header-t.jpg"); ?> 1x,
            <?php echo asset("public/build/images/ep_events/header-t@2x.jpg"); ?> 2x"
        >
        <img
            class="ep-events__image"
            src="<?php echo asset("public/build/images/ep_events/header.jpg"); ?>"
            srcset="<?php echo asset("public/build/images/ep_events/header.jpg"); ?> 1x,
            <?php echo asset("public/build/images/ep_events/header@2x.jpg"); ?> 2x"
            alt="<?php echo translate('ep_events_header_title', null, true); ?>"
        >
    </picture>
    </div>
</section>
