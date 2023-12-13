<script type="application/ld+json">
	{
		"@context": "http://schema.org",
		"@type": "BlogPosting",
		"mainEntityOfPage": {
			"@type": "WebPage",
			"@id": "<?php echo __CURRENT_URL; ?>"
		},
		"headline": "<?php echo $blog['title']; ?>",
		"image": {
			"@type": "ImageObject",
			"url": "<?php echo $blog['photo']['url']; ?>",
			"height": <?php echo $blog['photo']['height']; ?>,
			"width": <?php echo $blog['photo']['width']; ?>
		},
		"datePublished": "<?php echo $blog['date']; ?>",
		"dateModified": "<?php echo $blog['date']; ?>",
		"author": {
			"@type": "Person",
			"name": "ExportPortal"
		},
		"publisher": {
			"@type": "Organization",
			"name": "ExportPortal",
			"logo": {
				"@type": "ImageObject",
				"url": "<?php echo __IMG_URL; ?>public/img/ep-logo/ico-security-buyer-seller.png",
				"width": 51,
				"height": 56
			}
		},
		"description": "<?php echo $blog['title']; ?>"
	}
</script>

<div class="container-blog-detail">
	<article class="mblog-detail">
		<header>
			<div class="mblog-detail__category">
				<a
					class="mblog-detail__category-link"
					href="<?php echo $categoryUrl; ?>"
					title="<?php echo translate('blog_filter_by_category_title', null, true) . $blog['category_name']; ?>"
					<?php echo addQaUniqueIdentifier('page__blog-detail__category'); ?>
				>
					<?php echo $blog['category_name']; ?>
				</a>
			</div>

			<h1
                class="mblog-detail__ttl"
                <?php echo addQaUniqueIdentifier('page__blog-detail__title'); ?>
            ><?php echo $blog['title']; ?></h1>

            <p
                class="mblog-detail__short-description"
                <?php echo addQaUniqueIdentifier('page__blog-detail__short-description'); ?>
            ><?php echo $blog['description']; ?></p>

            <time
                class="mblog-detail__date"
                <?php echo addQaUniqueIdentifier('page__blog-detail__date'); ?>
                datetime="<?php echo $blog['publish_on']; ?>"
            ><?php echo getDateFormat($blog['publish_on'], 'Y-m-d', 'j M, Y'); ?></time>

            <div class="mblog-detail__socials">
                <?php views('new/share_on_socials_view', ['title' => $og['title'] ?: $seo['title'], 'img' => $blog['photo']['url']]);?>
			</div>

            <?php if (!empty($blog['photo'])) { ?>
				<figure class="mblog-detail__picture">
					<img
                        class="mblog-detail__image js-fs-image"
                        width="<?php echo $blog['photo']['width'] ?? 980; ?>"
                        height="<?php echo $blog['photo']['height'] ?? 426; ?>"
                        data-fsw="980"
                        data-fsh="426"
                        src="<?php echo $blog['photo']['url']; ?>"
                        alt="<?php echo $blog['title']; ?>"
                        <?php echo addQaUniqueIdentifier('page__blog-detail__main-image'); ?>
                    />

                    <?php if (!empty($blog['photo_caption'])) { ?>
                        <figcaption
                            class="mblog-detail__image-caption"
                            <?php echo addQaUniqueIdentifier('page__blog-detail__main-image-caption'); ?>
                        ><?php echo $blog['photo_caption']; ?></figcaption>
                    <?php } ?>
				</figure>
			<?php } ?>
		</header>

        <?php encoreLinks(); ?>

		<section
            class="mblog-detail__content js-fs-image-wrapper"
            <?php echo addQaUniqueIdentifier('page__blog-detail__content'); ?>
        >
            <?php
                $sliderContent = !empty($last_items) ? views()->fetch('new/blog/list_items_view', ['last_items' => $last_items]) : "";
                echo str_replace(
                    "[[EXPORT_PORTAL_PRODUCT_ADS_SLIDER]]",
                    "<div class=\"mblog-products-list\">{$sliderContent}</div>",
                    $blog['content']
                );
            ?>
		</section>

		<?php if (!empty($blog['tags'])) { ?>
			<ul
                class="mblog-detail__tags"
                <?php echo addQaUniqueIdentifier('page__blog-detail__tags'); ?>
            >
				<?php
                    $blogsTags = explode(',', $blog['tags']);

                    foreach ($blogsTags as $oneTag) {
                        if (empty($tagStr = strForURL($oneTag, '_'))) {
                            continue;
                        }
                ?>
                    <li
                        class="mblog-detail__tags-item"
                    >
                        <a
                            class="mblog-detail__tags-link"
                            href="<?php echo get_dynamic_url("{$blog_uri_components['tags']}/{$tagStr}", __BLOG_URL);?>"
                            rel="tag"
                            title="<?php echo $oneTag; ?>"
                            <?php echo addQaUniqueIdentifier('page__blog-detail__tags_item'); ?>
                        >#<?php echo $oneTag;?></a>
                    </li>
                <?php
                    }
                ?>
			</ul>
		<?php } ?>
	</article>

	<?php
        if (!empty($comments)) {
		    widgetComments($comments['type_id'], $comments['hash_components']);
	    }
    ?>

	<?php if (!empty($blogs)) { ?>
        <div class="mblog-recommended">
            <h2 class="mblog-title"><?php echo translate('blog_detail_recommended_header'); ?></h2>

            <div class="js-mblog-recommended-list mblog-recommended__list">
                <?php views()->display('new/blog/blog_recommended_list_view'); ?>
            </div>

            <?php if ($blogs_count > (int) config('blogs_recommended_list_per_page')) { ?>
                <div class="mblog-recommended__more">
                    <button
                        class="mblog-recommended__more-btn btn btn-light btn-new16 call-action"
                        data-js-action="blog:load-more-recommended"
                        data-user="<?php echo $blog['id_user']; ?>"
                        type="button"
                    >
                        <?php echo translate('blog_btn_see_more'); ?>
                    </button>
                </div>
            <?php } ?>
        </div>
	<?php } ?>
</div>

<?php
    encoreEntryLinkTags('blog_detail');
    encoreEntryScriptTags('blog_detail');
?>
