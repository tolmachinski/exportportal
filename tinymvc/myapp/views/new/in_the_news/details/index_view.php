<div class="mobile-links">
    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Menu" href="#about-flex-card__fixed-left">
        <i class="ep-icon ep-icon_items"></i>
        Menu
    </a>

    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Sidebar" href="#main-flex-card__fixed-right">
        <i class="ep-icon ep-icon_items"></i>
        Sidebar
    </a>
</div>

<div class="news-block news-block--detail">
<?php if (!empty($news)) { ?>
    <?php if (!empty($news['img_news'])) { ?>
        <div class="news-block__thumb--detail" <?php echo addQaUniqueIdentifier("mass-media-detail__img-parent"); ?>>
            <img class="image" src="<?php echo $news['imageUrl'] ?>" alt="<?php echo $news['title_news'];?>" <?php echo addQaUniqueIdentifier("mass-media-detail__img"); ?>>
        </div>
    <?php } ?>
    <div class="news-block__text--detail">
        <h1 class="news-block__title news-block__title--detail" <?php echo addQaUniqueIdentifier("mass-media-detail__title"); ?>><?php echo $news['title_news'];?></h1>
        <div class="news-block__date-row news-block__date-row--detail">
            <div class="news-block__from">
                <span class="mr-10">
                    <?php if (!empty($news['logo_media']) && $img_news = $news['mediaUrl']) {?>
                        <img class="mt-2 w-20" src="<?php echo $img_news;?>" alt="<?php echo $news['title_media'];?>">
                    <?php } else { ?>
                        <i class="ep-icon ep-icon_photo-gallery"></i>
                    <?php } ?>
                </span>
                <a class="link" href="<?php echo get_dynamic_url('mass_media/channel/' . strForUrl($news['title_media']) . '-' . $news['id_media'], __SITE_URL, true);?>" <?php echo addQaUniqueIdentifier("mass-media-detail__link"); ?>>
                    <?php echo $news['title_media'];?>
                </a>
            </div>
            <div class="news-block__date" <?php echo addQaUniqueIdentifier("mass-media-detail__date"); ?>><?php echo getDateFormat($news['date_news'], null, 'j M, Y');?></div>
        </div>
        <div class="ep-tinymce-text" <?php echo addQaUniqueIdentifier("mass-media-detail__description"); ?>>
            <?php echo $news['fulltext_news'];?>
        </div>
    </div>
<?php } ?>
</div>

<h2 class="news-block__title news-block__title--other">Other news</h2>

<div class="row row-eq-height">
    <?php foreach ($news_other as $news_other_item) {?>
        <div class="col-md-12 col-lg-6">
            <div class="news-block news-block--other" <?php echo addQaUniqueIdentifier("global__mass-media__news"); ?>>
                <?php if (!empty($news_other_item['img_news']) || isBackstopEnabled()) {?>
                    <div class="news-block__thumb image-card3 news-block__thumb--other" <?php echo addQaUniqueIdentifier("global__mass-media__news-image-parent"); ?>>
                        <span class="link">
                            <img class="image" src="<?php echo $news_other_item['imageUrl'] ?>" alt="<?php echo $news_other_item['title_news'];?>" <?php echo addQaUniqueIdentifier("global__mass-media__news-image"); ?>>
                        </span>
                    </div>
                <?php }?>
                <div class="news-block__info news-block__info--other">
                    <div class="news-block__title">
                        <?php if (!empty($news_other_item['link_news'])) { ?>
                            <a href="<?php echo $news_other_item['link_news'];?>" target="_blank" <?php echo addQaUniqueIdentifier("global__mass-media__news-title"); ?>><?php echo $news_other_item['title_news'];?></a>
                        <?php } else {?>
                            <a href="<?php echo get_dynamic_url('mass_media/detail/' . $news_other_item['url'], __SITE_URL, true);?>" <?php echo addQaUniqueIdentifier("global__mass-media__news-title"); ?>><?php echo $news_other_item['title_news'];?></a>
                        <?php }?>
                    </div>
                    <div class="news-block__date-row news-block__date-row--other">
                        <div class="news-block__date" <?php echo addQaUniqueIdentifier("global__mass-media__news-date"); ?>><?php echo getDateFormat($news_other_item['date_news'], null, 'j M, Y');?></div>
                        <div class="news-block__from news-block__from--other">
                            <span class="mr-10">
                                <?php if (!empty($news_other_item['logo_media']) && $img_news_last = $news_other_item['mediaUrl']) {?>
                                    <img class="mt-2 w-20" src="<?php echo $img_news_last;?>" alt="<?php echo $news_other_item['title_media'];?>" <?php echo addQaUniqueIdentifier("mass-media__news-link-img"); ?>>
                                <?php } else {?>
                                    <i class="ep-icon ep-icon_photo-gallery"></i>
                                <?php }?>
                            </span>
                            <a class="link news-block__name news-block__name--other" href="<?php echo get_dynamic_url('mass_media/channel/' . strForUrl($news_other_item['title_media']) . '-' . $news_other_item['id_media'], __SITE_URL, true) ?>" title="<?php echo $news_other_item['title_media'];?>" <?php echo addQaUniqueIdentifier("global__mass-media__news-link"); ?>>
                                <?php echo $news_other_item['title_media'];?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php }?>
</div>
