<?php foreach ($newsletter_archive as $archive) { ?>
    <div class="col-12 col-md-6">
        <div class="news-block news-block--newsletter-archive" <?php echo addQaUniqueIdentifier('global__news__item'); ?>>
            <div class="news-block__info news-block__archive">
                <div class="news-block__image" <?php echo addQaUniqueIdentifier('global__news__item_image-parent'); ?>>
                    <img class="image"
                        <?php echo addQaUniqueIdentifier('global__news__item_image'); ?>
                         src="<?php echo __IMG_URL . getImage('public/newsletter_archive/' . $archive['id_archive'] . "/main-image.jpg", 'public/img/no_image/group/noimage-other.svg'); ?>"
                         alt="<?php echo $archive['title']?>"
                        <?php echo addQaUniqueIdentifier('page__ep-newsletter__item_image'); ?>/>
                </div>
                <div class="news-block__date-row">
                    <div class="news-block__date" <?php echo addQaUniqueIdentifier('global__news__item_date'); ?>><?php echo getDateFormat($archive['published_on'], 'Y-m-d H:i:s', 'd M, Y')?></div>
                </div>
                <a class="news-block__title"
                   target="_blank"
                   href="<?php echo get_dynamic_url("newsletter/archive/".$archive["id_archive"], __SITE_URL, true)?>"
                   <?php echo addQaUniqueIdentifier('global__news__item_title'); ?>>
                    <?php echo $archive['title']?>
                </a>
                <p class="news-block__text" <?php echo addQaUniqueIdentifier('global__news__item_description'); ?>>
                    <?php echo $archive['description']?>
                </p>
            </div>
        </div>
    </div>
<?php } ?>
