<?php foreach ($blogs as $blog) { ?>
    <div class="blogs__slide" <?php echo addQaUniqueIdentifier('home__blog-item'); ?>>
        <img class="blogs__image js-lazy js-fs-image call-action" data-js-action="link:move-by-link" data-target="_blank" data-link="<?php echo getBlogUrl($blog); ?>" src="<?php echo getLazyImage(776, 337); ?>" data-src="<?php echo $blog['imagePath']; ?>" alt="<?php echo cleanOutput($blog['title']); ?>" <?php echo addQaUniqueIdentifier('home__blog-img'); ?>>
        <div class="blogs__info">
            <a class="blogs__title link" href="<?php echo getBlogUrl($blog); ?>" target="_blank" rel="noopener" <?php echo addQaUniqueIdentifier('home__blog-link'); ?>><?php echo $blog['title']; ?></a>
            <div class="blogs__about">
                by
                <?php if ('user' == $blog['author_type']) { ?>
                    <a class="blogs__author" href="<?php echo __BLOG_URL . 'author/' . strForURL($blog['fname'] . ' ' . $blog['lname'] . ' ' . $blog['id_user']); ?>" title="<?php cleanOutput("Filter by author: {$blog['fname']} {$blog['lname']}"); ?>" <?php echo addQaUniqueIdentifier('home__blog-author'); ?>><?php echo $blog['fname'] . ' ' . $blog['lname']; ?></a>
                <?php } else { ?>
                    <a class="blogs__author" href="<?php echo __BLOG_URL . 'author/export-portal'; ?>" title="Filter by author: Export Portal" <?php echo addQaUniqueIdentifier('home__blog-author'); ?>>Export Portal</a>
                <?php } ?>
                <span class="blogs__date" <?php echo addQaUniqueIdentifier('home__blog-date'); ?>><?php echo getDateFormat($blog['publish_on'], 'Y-m-d', 'j M, Y'); ?></span>
            </div>
        </div>
    </div>
<?php } ?>
