<?php
    $setLazyLoad = true;
    if (!empty($lazyLoadDisabled)) {
        $setLazyLoad = false;
    }
?>

<?php foreach ($blogs as $nrKey => $blog) { ?>
	<a
        class="js-mblog-recommended-list-item mblog-recommended__list-item"
        href="<?php echo getBlogUrl($blog);?>"
        <?php echo addQaUniqueIdentifier('page__blog-detail__recommended_item'); ?>
    >
		<picture
            class="mblog-recommended__list-picture"
        >
            <source
                media="(max-width: 360px)"
                data-fsw="360"
                data-fsh="156"
                <?php if ($setLazyLoad) {?>
                    data-srcset="<?php echo $blog['photoSrc']; ?>"
                    srcset="<?php echo getLazyImage(360, 156); ?>"
                <?php } else { ?>
                    srcset="<?php echo $blog['photoSrc']; ?>"
                <?php } ?>
            >
            <source
                media="(min-width: 360px) and (max-width: 580px)"
                data-fsw="550"
                data-fsh="239"
                <?php if ($setLazyLoad) {?>
                    data-srcset="<?php echo $blog['photoMainSrc']; ?>"
                    srcset="<?php echo getLazyImage(360, 156); ?>"
                <?php } else { ?>
                    srcset="<?php echo $blog['photoSrc']; ?>"
                <?php } ?>
            >
            <img
                class="mblog-recommended__list-image js-fs-image<?php if ($setLazyLoad) {?> js-lazy<?php } ?>"
                width="360"
                height="156"
                data-fsw="360"
                data-fsh="156"
                <?php if ($setLazyLoad) {?>
                    data-src="<?php echo $blog['photoSrc']; ?>"
                    src="<?php echo getLazyImage(360, 156); ?>"
                <?php } else { ?>
                    src="<?php echo $blog['photoSrc']; ?>"
                <?php } ?>
                alt="<?php echo $blog['title']; ?>"
                <?php echo addQaUniqueIdentifier('page__blog-detail__recommended_image'); ?>
            >
		</picture>

		<div
            class="mblog-recommended__list-ttl"
            <?php echo addQaUniqueIdentifier('page__blog-detail__recommended_title'); ?>
        >
			<?php echo $blog['title']; ?>
		</div>
	</a>
<?php } ?>
