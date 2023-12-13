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

<div class="news-block news-block--detail" <?php echo addQaUniqueIdentifier('page__ep-news-detail__info'); ?>>
    <?php if (!empty($news_detail['main_image'])) { ?>
        <div class="news-block__thumb--detail" <?php echo addQaUniqueIdentifier('page__ep-news-detail__image-parent'); ?>>
            <img class="image" src="<?php echo $news_detail['imageUrl'] ?>" alt="<?php echo cleanOutput($news_detail['title']);?>" <?php echo addQaUniqueIdentifier('page__ep-news-detail__image'); ?>>
        </div>
    <?php } ?>

    <div class="news-block__text--detail">
        <h1 class="news-block__title news-block__title--detail" <?php echo addQaUniqueIdentifier('page__ep-news-detail__title'); ?>>
            <?php echo $news_detail['title'];?>
        </h1>
        <div class="news-block__date-row news-block__date-row--detail">
            <div class="news-block__date" <?php echo addQaUniqueIdentifier('page__ep-news-detail__date'); ?>>
                <?php echo getDateFormat($news_detail['date_time']);?>
            </div>
        </div>
        <div class="ep-tinymce-text" <?php echo addQaUniqueIdentifier('page__ep-news-detail__description'); ?>>
            <?php echo $news_detail['content'];?>
        </div>
    </div>
</div>

<?php if (!empty($comments)) {?>
    <?php widgetComments($comments['type_id'], $comments['hash_components']);?>
<?php }?>

<?php if (!empty($ep_news)) {?>
    <div id="js-other-news-wrapper">
        <div id="js-other-news-container">
            <h2 class="news-block__title news-block__title--other"><?php echo translate('news_detail_other_news_block_title');?></h2>

            <div class="row row-eq-height">
                <?php views()->display('new/ep_news/list_view');?>
            </div>
        </div>
    </div>
<?php }?>
