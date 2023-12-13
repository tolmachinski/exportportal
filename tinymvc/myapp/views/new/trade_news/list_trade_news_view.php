<?php if(!empty($trade_news_list)){?>
    <ul class="trade-news-list">
        <?php foreach($trade_news_list as $trade_news_item){?>
        <li class="trade-news-list__item">
            <a
                class="trade-news-list__inner"
                href="<?php echo __SITE_URL;?>trade_news/detail/<?php echo $trade_news_item['title_slug'].'-'.$trade_news_item['id_trade_news'];?>"
            >
                <picture class="trade-news-list__img" <?php echo addQaUniqueIdentifier("trade-news__picture")?>>
                    <source
                        media="(max-width: 574px)"
                        data-srcset="<?php echo getDisplayImageLink(array('{ID}' => $trade_news_item['id_trade_news'], '{FILE_NAME}' => $trade_news_item['photo']), 'trade_news.main', array( 'thumb_size' => 5 ));?>">
                    <img
                        class="image js-lazy"
                        data-src="<?php echo getDisplayImageLink(array('{ID}' => $trade_news_item['id_trade_news'], '{FILE_NAME}' => $trade_news_item['photo']), 'trade_news.main', array( 'thumb_size' => 8 ));?>"
                        src="<?php echo getLazyImage(200, 200); ?>"
                        alt="<?php echo $trade_news_item['title'];?>"
                    >
                </picture>
                <h3 class="trade-news-list__title" <?php echo addQaUniqueIdentifier("trade-news__title")?>><?php echo $trade_news_item['title'];?></h3>
                <p class="trade-news-list__description" <?php echo addQaUniqueIdentifier("trade-news__text")?>><?php echo $trade_news_item['short_description'];?></p>
                <div class="trade-news-list__date" <?php echo addQaUniqueIdentifier("trade-news__date")?>><?php echo formatDate($trade_news_item['date'], 'm/d/Y');?></div>
            </a>
        </li>
        <?php }?>
    </ul>
<?php }else{?>
    <div class="info-alert-b"><i class="ep-icon ep-icon_info-stroke mb-15"></i> <span>There are no news to display.</span></div>
<?php }?>
