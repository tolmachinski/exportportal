<?php views()->display('new/partial_sidebar_search_view', $partial_search_params);?>

<?php if (!empty($ep_updates_last)) { ?>
    <h3 class="minfo-sidebar-ttl">
        <span class="minfo-sidebar-ttl__txt"><?php echo translate('ep_updates_detail_last_added_block_title');?></span>
    </h3>

    <div class="minfo-sidebar-box">
        <div class="minfo-sidebar-box__desc">
            <?php foreach ($ep_updates_last as $ep_update_item) {?>
                <div class="news-block news-block--sidebar">
                    <div class="news-block__info">
                        <div class="news-block__title news-block__title--sidebar">
                            <a href="<?php echo get_dynamic_url('ep_updates/detail/' . $ep_update_item['url'], __SITE_URL, true)?>" <?php echo addQaUniqueIdentifier("ep-updates-detail__sidebar-title"); ?>><?php echo $ep_update_item['title'] ?></a>
                        </div>
                        <div class="news-block__date-row news-block__date-row--sidebar">
                            <div class="news-block__date" <?php echo addQaUniqueIdentifier("ep-updates-detail__sidebar-date"); ?>><?php echo getDateFormat($ep_update_item['date_time']);?></div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>
