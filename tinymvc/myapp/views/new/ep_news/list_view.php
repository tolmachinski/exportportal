<?php foreach ($ep_news as $one_news) { ?>
    <div class="col-12 col-lg-6">
        <a class="news-block news-block--link" href="<?php echo get_dynamic_url('ep_news/detail/' . $one_news['url'], __SITE_URL, true);?>" <?php echo addQaUniqueIdentifier('global__news__item'); ?>>
            <?php if (!empty($one_news['main_image']) || isBackstopEnabled()) {?>
                <div class="news-block__thumb image-card3" <?php echo addQaUniqueIdentifier('global__news__item_image-parent'); ?>>
                    <span class="link">
                        <img class="image" src="<?php echo $one_news['imageUrl'] ?>" alt="<?php echo $one_news['title'];?>" <?php echo addQaUniqueIdentifier('global__news__item_image'); ?>>
                    </span>
                </div>
            <?php } ?>
            <div class="news-block__info">
                <span class="news-block__title" <?php echo addQaUniqueIdentifier('global__news__item_title'); ?>>
                    <?php echo $one_news['title'];?>
                </span>
                <p class="news-block__text" <?php echo addQaUniqueIdentifier('global__news__item_description'); ?>><?php echo $one_news['description'];?></p>
                <div class="news-block__date" <?php echo addQaUniqueIdentifier('global__news__item_date'); ?>><?php echo getDateFormat($one_news['date_time']);?></div>
            </div>
        </a>
    </div>
<?php } ?>
