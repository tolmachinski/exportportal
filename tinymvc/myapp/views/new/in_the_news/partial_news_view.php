<?php foreach ($news_list as $news) { ?>
    <div class="col-12 col-lg-6">
        <div class="news-block" <?php echo addQaUniqueIdentifier("global__mass-media__news"); ?>>
            <?php if (!empty($news['link_news'])) {
                $news_link = $news['link_news'];
                $news_link_target = 'target="_blank"';
            } else {
                $news_link = get_dynamic_url('mass_media/detail/'.$news['url'], __SITE_URL, true);
                $news_link_target = '';
            }?>

            <?php if (!empty($news['img_news']) || isBackstopEnabled()) { ?>
                <div
                    <?php echo addQaUniqueIdentifier("global__mass-media__news-image-parent"); ?>
                    class="news-block__thumb cur-pointer image-card3 call-function call-action"
                    data-callback="callMoveByLink"
                    data-js-action="link:move-by-link"
                    data-link="<?php echo $news_link; ?>"
                    <?php if($news_link_target != ''){?>data-target="<?php echo $news_link_target; ?>"<?php }?>
                >
                    <span class="link">
                        <img class="image" src="<?php echo $news['imageUrl']?>" alt="<?php echo $news['title_news'];?>" <?php echo addQaUniqueIdentifier("global__mass-media__news-image"); ?>>
                    </span>
                </div>
            <?php } ?>
            <div class="news-block__info">
                <div class="news-block__title">
                    <a href="<?php echo $news_link; ?>" <?php echo $news_link_target; ?> <?php echo addQaUniqueIdentifier("global__mass-media__news-title"); ?>><?php echo $news['title_news'] ?></a>
                </div>
                <p class="news-block__text" <?php echo addQaUniqueIdentifier("global__mass-media__news-description"); ?>>
                    <?php echo $news['description_news']?>
                </p>
                <div class="news-block__date-row">
                    <div class="news-block__from">
                        <a class="link news-block__name" href="<?php echo replace_dynamic_uri(strForUrl($news['title_media']) . '-' . $news['id_media'], $apply_channel_link_tpl, __SITE_URL . 'mass_media');?>" title="<?php echo $news['title_media']?>">
                            <?php if (!empty($news['logo_media']) && $src_img = $news['logoUrl']) { ?>
                                <img class="fn org epnews-list__channel-img" <?php echo addQaUniqueIdentifier("global__mass-media__news-logo"); ?> src="<?php echo $src_img;?>" alt="<?php echo $news['title_media'];?>">
                            <?php } else { ?>
                                <i class="ep-icon ep-icon_photo-gallery" <?php echo addQaUniqueIdentifier("global__mass-media__news-icon"); ?>></i>
                            <?php } ?>
                            <span class="news-block__from-name" <?php echo addQaUniqueIdentifier("global__mass-media__news-link"); ?>>
                                <?php echo $news['title_media']?>
                            </span>
                        </a>
                    </div>
                    <div class="news-block__date" <?php echo addQaUniqueIdentifier("global__mass-media__news-date"); ?>><?php echo getDateFormat($news['date_news'], null, 'j M, Y');?></div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
