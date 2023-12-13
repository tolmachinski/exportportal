<?php foreach ($ep_updates as $update) { ?>
    <div class="col-12 col-lg-6">
        <a class="news-block news-block--link" href="<?php echo get_dynamic_url('ep_updates/detail/' . $update["url"], __SITE_URL, true)?>" <?php echo addQaUniqueIdentifier("ep-updates__item"); ?>>
            <div class="news-block__info">
                <div class="news-block__title" <?php echo addQaUniqueIdentifier("ep-updates__title"); ?>>
                    <?php echo $update['title']?>
                </div>
                <div class="news-block__date-row">
                    <div class="news-block__date" <?php echo addQaUniqueIdentifier("ep-updates__date"); ?>><?php echo getDateFormat($update['date_time']);?></div>
                </div>
                <p class="news-block__text" <?php echo addQaUniqueIdentifier("ep-updates__description"); ?>><?php echo $update['description']?></p>
            </div>
        </a>
    </div>
<?php } ?>
