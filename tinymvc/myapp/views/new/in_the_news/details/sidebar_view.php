<?php views()->display('new/partial_sidebar_search_view');?>

<h3 class="minfo-sidebar-ttl">
    <span class="minfo-sidebar-ttl__txt">Last Added</span>
</h3>

<div class="minfo-sidebar-box">
    <div class="minfo-sidebar-box__desc">
        <?php foreach($news_last_added as $news_last_item) {?>
            <div class="news-block news-block--sidebar">
                <div class="news-block__info">
                    <div class="news-block__title news-block__title--sidebar">
                        <?php if (!empty($news_last_item['link_news'])) {?>
                            <a href="<?php echo $news_last_item['link_news'];?>" target="_blank" <?php echo addQaUniqueIdentifier("mass-media-detail__sidebar-title"); ?>><?php echo $news_last_item['title_news'];?></a>
                        <?php } else {?>
                            <a href="<?php echo get_dynamic_url('mass_media/detail/'.$news_last_item['url'], __SITE_URL, true);?>" <?php echo addQaUniqueIdentifier("mass-media-detail__sidebar-title"); ?>><?php echo $news_last_item['title_news'];?></a>
                        <?php }?>
                    </div>
                    <div class="news-block__date-row news-block__date-row--sidebar">
                        <div class="news-block__date" <?php echo addQaUniqueIdentifier("mass-media-detail__sidebar-date"); ?>><?php echo getDateFormat($news_last_item['date_news'], null, 'j M, Y');?></div>
                        <div class="news-block__from news-block__from--sidebar">
                            <span class="pr-5">
                                <?php if (!empty($news_last_item['logo_media']) && $img_last = $news_last_item['mediaUrl']) {?>
                                    <img class="mt-2 w-20" src="<?php echo $img_last ?>" alt="<?php echo $news_last_item['title_media'];?>" <?php echo addQaUniqueIdentifier("mass-media__link-img"); ?>>
                                <?php } else {?>
                                    <i class="ep-icon ep-icon_photo-gallery"></i>
                                <?php }?>
                            </span>
                            <a href="<?php echo get_dynamic_url('mass_media/channel/' . strForUrl($news_last_item['title_media']) . '-' . $news_last_item['id_media'], __SITE_URL, true);?>" class="news-block__name news-block__name--sidebar" title="<?php echo $news_last_item['title_media'];?>" <?php echo addQaUniqueIdentifier("mass-media-detail__sidebar-link"); ?>>
                                <?php echo $news_last_item['title_media']?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php }?>
    </div>
</div>
