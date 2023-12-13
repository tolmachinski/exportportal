<a class="btn btn-primary fancyboxSidebar fancybox following-button" data-title="Following" href="#timeline__right-sidebar">
    <i class="ep-icon ep-icon_items"></i>
    Following
</a>

<div class="timeline">

    <div class="timeline__left-sidebar">

        <div class="title-public pt-25">
            <h2 class="title-public__txt">Your notifications</h2>
        </div>

        <ul class="notifications-list">
            <li>
                <a class="link fancybox.ajax fancyboxMep " data-title="Notifications" href="<?php echo __SITE_URL;?>systmess/popup_forms/notification">
                    <span class="notifications-list__title txt-black">
                        <i class="ep-icon ep-icon_bell-stroke"></i>Notifications
                    </span>
                </a>
                <span class="notifications-list__count"><?php echo intval($counters_notification);?></span>
            </li>
            <li>
                <span class="notifications-list__title">
                <i class="ep-icon ep-icon_comment-stroke"></i>Comments</span>
                <span class="notifications-list__count">0</span>
            </li>
        </ul>

    </div>

    <div class="timeline__column">

        <div class="title-public pt-25 pb-20">
            <h1 class="title-public__txt fs-20 lh-22">Timeline</h1>
        </div>

        <?php if (!empty($wall_items)) { ?>
        <div id="wrapper-wall-items">
            <?php foreach ($wall_items as $wall_item) {
                echo $wall_item;
            } ?>
        </div>
        <?php } else { ?>
        <i class="ep-icon ep-icon_info-stroke"></i> Users' posts have not been found
        <?php } ?>
    </div>

    <div class="timeline__right-sidebar">
        <div id="timeline__right-sidebar">
            <div class="title-public pt-25">
                <h2 class="title-public__txt">Following</h2>
            </div>

            <?php if (!empty($companies)) {
            foreach ($companies as $company) {
                tmvc::instance()->controller->view->display('new/directory/small_list_item_view', array('item' => $company));
            }} else { ?>
                <i class="ep-icon ep-icon_info-stroke"></i> There are no users you have followed
            <?php } ?>
        </div>

    </div>
</div>



