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

<div class="title-public">
    <h2 class="title-public__txt title-public__txt--26"><?php if(isset($keywords))echo $keywords?></h2>
    <span class="minfo-title__total tar">Found <?php echo $count_news_list; ?> News</span>
</div>
<?php if (!empty($news_list)) { ?>
    <div class="row row-eq-height">
    <?php foreach($news_list as $news_other_item){ ?>
        <div class="col-md-12 col-lg-6">
            <div class="news-block news-block--other">
                <?php if (!empty($news_other_item['img_news'])) { ?>
                    <div class="news-block__thumb image-card3 news-block__thumb--other">
                        <span class="link">
                            <img class="image" src="<?php echo $news_other_item['imageUrl'] ?>" alt="<?php echo $news_other_item['title_news'] ?>">
                        </span>
                    </div>
                <?php } ?>
                <div class="news-block__info">
                    <div class="news-block__title">
                        <?php if (!empty($news_other_item['link_news'])) { ?>
                            <a href="<?php echo $news_other_item['link_news'] ?>" target="_blank"><?php echo $news_other_item['title_news'] ?></a>
                        <?php } else { ?>
                            <a href="<?php echo get_dynamic_url('mass_media/detail/'.$news_other_item['url'], __SITE_URL, true)?>"><?php echo $news_other_item['title_news'] ?></a>
                        <?php } ?>
                    </div>
                    <div class="news-block__date-row news-block__date-row--other">
                        <div class="news-block__date"><?php echo formatDate($news_other_item['date_news'])?></div>
                        <div class="news-block__from news-block__from--other">
                            <span>
                                from
                                <?php if (!empty($news_other_item['logo_media']) && $img_news_last = $news_other_item['logoUrl']) {?>
                                    <img class="mt-2 w-20" src="<?php echo __IMG_URL.$img_news_last;?>" alt="<?php echo $news_other_item['title_media'] ?>">
                                <?php } else { ?>
                                    <i class="ep-icon ep-icon_photo-gallery"></i>
                                <?php } ?>
                            </span>
                            <a class="link news-block__name news-block__name--other" href="<?php echo get_dynamic_url('mass_media/channel/'.strForUrl($news_other_item['title_media']).'-'.$news_other_item['id_media'], __SITE_URL, true) ?>" title="<?php echo $news_other_item['title_media']?>">
                                <?php echo $news_other_item['title_media']?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    </div>
<?php } else { ?>
    <?php views()->display('new/search/cheerup_view');?>
<?php } ?>
