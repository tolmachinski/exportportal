
<div class="trade-news-detail">
    <h1 class="trade-news-detail__title" <?php echo addQaUniqueIdentifier("trade-news__title")?>>
        <?php echo $trade_news_detail['title'];?>
    </h1>
    <div class="trade-news-detail__date"  <?php echo addQaUniqueIdentifier("trade-news__date")?>>
        <?php echo formatDate($trade_news_detail['date'], 'm/d/Y');?>
    </div>
    <picture class="trade-news-detail__image"  <?php echo addQaUniqueIdentifier("trade-news__picture")?>>
        <source
            media="(max-width: 574px)"
            data-srcset="<?php echo getDisplayImageLink(array('{ID}' => $trade_news_detail['id_trade_news'], '{FILE_NAME}' => $trade_news_detail['photo']), 'trade_news.main', array( 'thumb_size' => 5 ));?>">
        <img
            class="image js-lazy"
            data-src="<?php echo getDisplayImageLink(array('{ID}' => $trade_news_detail['id_trade_news'], '{FILE_NAME}' => $trade_news_detail['photo']), 'trade_news.main');?>"
            src="<?php echo getLazyImage(200, 200); ?>"
            alt="<?php echo $trade_news_detail['title'];?>"
        >
    </picture>

    <div class="trade-news-detail__description ep-tinymce-text mb-50" <?php echo addQaUniqueIdentifier("trade-news__description")?>><?php echo $trade_news_detail['content'];?></div>
</div>

<?php if (!empty($comments)) {?>
    <?php widgetComments($comments['type_id'], $comments['hash_components']);?>
<?php }?>
