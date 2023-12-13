<div class="mobile-links">
    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Menu" href="#about-flex-card__fixed-left">
        <i class="ep-icon ep-icon_items"></i>
        <?php echo translate('about_us_nav_menu_btn');?>
    </a>

    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Sidebar" href="#main-flex-card__fixed-right">
        <i class="ep-icon ep-icon_items"></i>
        <?php echo translate('mobile_screen_sidebar_btn');?>
    </a>
</div>

<div class="news-block news-block--detail">
    <div class="news-block__text--detail">
        <h1 class="news-block__title news-block__title--detail" <?php echo addQaUniqueIdentifier("ep-updates-detail__title"); ?>>
            <?php echo $ep_update['title'];?>
        </h1>
        <div class="news-block__date-row news-block__date-row--detail">
            <div class="news-block__date" <?php echo addQaUniqueIdentifier("ep-updates-detail__date"); ?>>
                <?php echo getDateFormat($ep_update['date_time']);?>
            </div>
        </div>
        <div class="ep-tinymce-text" <?php echo addQaUniqueIdentifier("ep-updates-detail__description"); ?>>
            <?php echo $ep_update['content'];?>
        </div>
    </div>
</div>

<?php if (!empty($comments)) {?>
    <?php widgetComments($comments['type_id'], $comments['hash_components']);?>
<?php }?>

<?php if (!empty($ep_updates)) {?>
    <div id="js-other-wrapper">
        <div id="js-other-container">
            <h2 class="news-block__title news-block__title--other"><?php echo translate('ep_updates_detail_other_updates_block_title');?></h2>

            <div class="row row-eq-height">
                <?php views()->display('new/ep_updates/list_view');?>
            </div>
        </div>
    </div>
<?php }?>
