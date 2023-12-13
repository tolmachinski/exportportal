<div class="container-1420">
    <?php
        if ($page > 1 || isset($search_params)) {
            $headerParams = [
                'mainTitle'         => translate('blog_main_title_1'),
                'descriptionTitle'  => translate('blog_main_title_description_1'),
            ];

            if ($currentCategoryPage) {
                $headerParams = [
                    'mainTitle'         => translate('blog_main_title_2'),
                    'descriptionTitle'  => translate('blog_main_title_description_2', ['{{CATEGORY_NAME}}' => strtolower($categoryName)]),
                ];
            }

            views()->display('new/blog/header_simple_view', $headerParams);
        } else {
            views()->display('new/blog/header_view');
        }

        encoreLinks();
    ?>

    <div class="site-content site-content--sidebar-right">
        <div class="site-main-content">
            <div class="filter-control-bar filter-control-bar--empty">
                <button
                    class="filter-btn call-action"
                    data-js-action="sidebar:toggle-visibility"
                    type="button"
                >
                    <?php echo widgetGetSvgIcon('filter', 17, 17, 'filter-btn__icon'); ?>
                    <span class="filter-btn__txt"><?php
                        echo translate('general_dt_filters_button_text');
                        echo !empty($search_params) ? '<span class="filter-btn__counter">' . count($search_params) . '</span>' : '';
                    ?></span>
                </button>
            </div>

            <?php if (!empty($blogs)) { ?>
                <div class="mblog-list">
                    <?php foreach ($blogs as $nrKey => $blog) {?>
                        <article
                            class="mblog-list__item <?php echo 0 === $nrKey && $page === 1 && !isset($search_params) ? "mblog-list__item--first" : ""; ?>"
                        >
                            <a
                                class="mblog-list__img"
                                href="<?php echo getBlogUrl($blog); ?>"
                            >
                                <img
                                    class="mblog-list__img-item js-lazy js-fs-image"
                                    src="<?php echo getLazyImage(800, 348);?>"
                                    width="800"
                                    height="348"
                                    data-src="<?php echo $blog['photoSrc']; ?>"
                                    alt="<?php echo $blog['title']; ?>"
                                    <?php echo addQaUniqueIdentifier('page__blog__card_image'); ?>
                                >
                            </a>
                            <div class="mblog-list__detail">
                                <div class="mblog-list__category">
                                    <a
                                        class="mblog-list__category-name"
                                        href="<?php echo $blogsCategories[$blog['id_category']]['link']; ?>"
                                        title="<?php echo translate('blog_filter_by_category_title', null, true) . $blog['category_name']; ?>"
                                        <?php echo addQaUniqueIdentifier('page__blog__card_category'); ?>
                                    >
                                        <?php echo $blog['category_name']; ?>
                                    </a>
                                </div>
                                <h2 class="mblog-list__ttl">
                                    <a
                                        class="mblog-list__ttl-link"
                                        href="<?php echo getBlogUrl($blog); ?>"
                                        <?php echo addQaUniqueIdentifier('page__blog__card_title'); ?>
                                    >
                                        <?php echo $blog['title']; ?>
                                    </a>
                                </h2>
                                <time
                                    class="mblog-list__date"
                                    <?php echo addQaUniqueIdentifier('page__blog__card_date'); ?>
                                    datetime="<?php echo $blog['publish_on']; ?>"
                                >
                                    <?php echo getDateFormat($blog['publish_on'], 'Y-m-d', 'j M, Y'); ?>
                                </time>
                                <div
                                    class="mblog-list__short-description"
                                    <?php echo addQaUniqueIdentifier('page__blog__card_short-description'); ?>
                                >
                                    <?php echo $blog['description']; ?>
                                </div>
                                <a
                                    class="mblog-list__more"
                                    href="<?php echo getBlogUrl($blog); ?>"
                                ><?php echo translate('blog_read_more'); ?></a>
                            </div>
                        </article>

                        <?php if ($latestItemsPosition === $nrKey && !empty($last_items)) { ?>
                            <div class="mblog-products-list">
                                <?php views('new/blog/list_items_view', ['last_items' => $last_items, 'has_hover' => false]); ?>
                            </div>
                        <?php } ?>

                        <?php if ($smeSpotlightPosition === $nrKey && !empty($smeSpotlight)) { ?>
                            <ul class="mblog-sme-spotlight">
                                <?php foreach ($smeSpotlight as $blog) { ?>
                                    <li class="mblog-sme-spotlight__item">
                                        <a
                                            class="mblog-sme-spotlight__badge"
                                            href="<?php echo __BLOG_URL . "sme_spotlight"; ?>"
                                        ><?php echo translate('blog_sme_spotlight_title'); ?></a>
                                        <a
                                            class="mblog-sme-spotlight__item-link"
                                            href="<?php echo getBlogUrl($blog); ?>"
                                        >
                                            <div class="mblog-sme-spotlight__image-wr">
                                                <img
                                                    class="mblog-sme-spotlight__image js-lazy js-fs-image"
                                                    src="<?php echo getLazyImage(300, 147);?>"
                                                    width="300"
                                                    height="147"
                                                    data-src="<?php echo $blog['photoUrl']; ?>"
                                                    alt="<?php echo $blog['title']; ?>"
                                                    <?php echo addQaUniqueIdentifier('page__blog__card_image'); ?>
                                                >
                                            </div>
                                            <div class="mblog-sme-spotlight__detail">
                                                <h3
                                                    class="mblog-sme-spotlight__ttl"
                                                    <?php echo addQaUniqueIdentifier('page__blog__card_title'); ?>
                                                >
                                                    <?php echo $blog['title']; ?>
                                                </h3>
                                                <div
                                                    class="mblog-sme-spotlight__date"
                                                    <?php echo addQaUniqueIdentifier('page__blog__card_date'); ?>
                                                >
                                                    <?php echo getDateFormat($blog['publish_on'], 'Y-m-d', 'j M, Y'); ?>
                                                </div>
                                                <div class="mblog-sme-spotlight__more">
                                                    <?php echo translate('blog_read_more'); ?>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php }?>
                    <?php } ?>
                </div>

                <?php if ($olderPosts || $latestPosts) { ?>
                    <div class="pagination-wr">
                        <?php views()->display("new/paginator_view"); ?>
                    </div>
                <?php } ?>

            <?php } else { ?>
                <?php views()->display('new/search/not_found_view', [
                    'message' => translate('general_no_results_card_subtitle_2')
                ]); ?>
            <?php } ?>

            <?php if (!logged_in()) {?>
                <div class="mblog-write-for-us">
                    <div class="mblog-write-for-us__detail">
                        <h2 class="mblog-write-for-us__ttl"><?php echo translate('blog_write_for_us_ttl'); ?></h2>
                        <div class="mblog-write-for-us__desc"><?php echo translate('blog_write_for_us_desc'); ?></div>
                        <a
                            class="mblog-write-for-us__more"
                            href="<?php echo __SITE_URL . 'landing/content_ambassador'; ?>"
                        ><?php echo translate('blog_btn_become_guest_writer'); ?> <?php echo widgetGetSvgIcon('arrowRight', 16, 17);?></a>
                    </div>
                    <picture class="mblog-write-for-us__picture">
                        <source
                            media="(max-width: 480px)"
                            srcset="<?php echo asset("public/build/images/blog/write-for-us-360.jpg");?> 1x, <?php echo asset("public/build/images/blog/write-for-us-360@2x.jpg"); ?> 2x">
                        <source
                            media="(max-width: 1300px)"
                            srcset="<?php echo asset("public/build/images/blog/write-for-us-768.jpg");?> 1x, <?php echo asset("public/build/images/blog/write-for-us-768@2x.jpg"); ?> 2x">
                        <img
                            class="mblog-write-for-us__img js-lazy"
                            width="550"
                            height="150"
                            src="<?php echo getLazyImage(550, 150);?>"
                            data-src="<?php echo asset("public/build/images/blog/write-for-us-1420.jpg"); ?>"
                            data-srcset="<?php echo asset("public/build/images/blog/write-for-us-1420.jpg"); ?> x1, <?php echo asset("public/build/images/blog/write-for-us-1420@2x.jpg"); ?> x2"
                            alt="<?php echo translate('blog_write_for_us_ttl', null, true); ?>">
                    </picture>
                </div>
            <?php }?>


            <?php if (!$currentCategoryPage && !empty($newsList)) {?>
                <div class="mblog-news-and-media">
                    <h2 class="mblog-title"><?php echo translate('blog_sidebar_media_header'); ?></h2>

                    <ul class="mblog-news-and-media__list">
                        <?php foreach ($newsList as $newsItem) {?>
                            <li
                                class="mblog-news-and-media__list-item"
                                <?php echo addQaUniqueIdentifier('page__blog__news-and-media_item'); ?>
                            >
                                <div
                                    class="mblog-news-and-media__date"
                                    <?php echo addQaUniqueIdentifier('page__blog__news-and-media_date'); ?>
                                >
                                    <?php echo getDateFormat($newsItem['date_news'], 'Y-m-d H:i:s', 'j M, Y'); ?>
                                </div>
                                <h3 class="mblog-news-and-media__title">
                                    <a
                                        class="mblog-news-and-media__link"
                                        href="<?php echo !empty($newsItem['link_news']) ? $newsItem['link_news'] : get_dynamic_url('mass_media/detail/' . $newsItem['url'], __SITE_URL, true) ?>"
                                        target="_blank"
                                        <?php echo addQaUniqueIdentifier('page__blog__news-and-media_title'); ?>
                                    >
                                        <?php echo $newsItem['title_news']; ?>
                                    </a>
                                </h3>
                                <div class="mblog-news-and-media__source">
                                    <div class="mblog-news-and-media__source-ttl"><?php echo translate('blog_news_and_media_title_source'); ?>:</div>
                                    <div
                                        class="mblog-news-and-media__source-name"
                                        <?php echo addQaUniqueIdentifier('page__blog__news-and-media_source'); ?>
                                    ><?php echo !empty($newsItem['link_news']) ? $newsItem['link_news'] : __HTTP_HOST_ORIGIN; ?></div>
                                </div>
                            </li>
                        <?php }?>
                    </ul>

                    <div class="mblog-news-and-media__more-wr">
                        <a
                            class="mblog-news-and-media__more btn btn-light btn-new16"
                            href="<?php echo __SITE_URL . 'mass_media'; ?>"
                        ><?php echo translate('blog_view_more_btn'); ?></a>
                    </div>
                </div>
            <?php }?>

            <?php if (!$currentCategoryPage) {?>
                <div class="mblog-what-we-do">
                    <h2 class="mblog-title"><?php echo translate('blog_sidebar_what_we_do_header'); ?></h2>

                    <picture class="mblog-what-we-do__picture">
                        <source
                            media="(max-width: 480px)"
                            srcset="<?php echo asset("public/build/images/blog/what-we-do-360.jpg");?> 1x, <?php echo asset("public/build/images/blog/what-we-do-360@2x.jpg"); ?> 2x">
                        <source
                            media="(max-width: 768px)"
                            srcset="<?php echo asset("public/build/images/blog/what-we-do-768.jpg");?> 1x, <?php echo asset("public/build/images/blog/what-we-do-768@2x.jpg"); ?> 2x">
                        <img
                            class="mblog-what-we-do__image js-lazy"
                            width="1050"
                            height="150"
                            src="<?php echo getLazyImage(1050, 150);?>"
                            data-srcset="<?php echo asset("public/build/images/blog/what-we-do-1420.jpg");?> 1x, <?php echo asset("public/build/images/blog/what-we-do-1420@2x.jpg");?> 2x"
                            data-src="<?php echo asset("public/build/images/blog/what-we-do-1420.jpg"); ?>"
                            alt="<?php echo translate('blog_sidebar_what_we_do_header'); ?>"
                        >
                    </picture>
                    <div class="mblog-what-we-do__txt">
                        <p class="mblog-what-we-do__txt-paragraph"><?php echo translate('blog_sidebar_what_we_do_text'); ?></p>
                        <p class="mblog-what-we-do__txt-paragraph"><?php echo translate('blog_sidebar_what_we_do_text2'); ?></p>
                        <p class="mblog-what-we-do__txt-paragraph"><?php echo translate('blog_sidebar_about_us_text');?></p>
                    </div>
                    <div class="mblog-what-we-do__sign">
                        <picture class="mblog-what-we-do__sign-picture">
                            <source
                                media="(max-width: 480px)"
                                srcset="<?php echo asset("public/build/images/blog/sign-320.jpg");?> 1x, <?php echo asset("public/build/images/blog/sign-320@2x.jpg"); ?> 2x">
                            <source
                                media="(max-width: 768px)"
                                srcset="<?php echo asset("public/build/images/blog/sign-768.jpg");?> 1x, <?php echo asset("public/build/images/blog/sign-768@2x.jpg"); ?> 2x">
                            <img
                                class="mblog-what-we-do__sign-image js-lazy"
                                width="80"
                                height="47"
                                data-srcset="<?php echo asset("public/build/images/blog/sign-1420.jpg");?> 1x, <?php echo asset("public/build/images/blog/sign-1420@2x.jpg");?> 2x"
                                data-src="<?php echo asset("public/build/images/blog/sign-1420.jpg");?>"
                                src="<?php echo getLazyImage(80, 47);?>"
                                alt="<?php echo translate('blog_sidebar_founder_name');?>"
                            >
                        </picture>
                        <div class="mblog-what-we-do__sign-text">
                            <?php echo translate('blog_sidebar_founder_name');?>
                        </div>
                    </div>
                </div>
            <?php }?>
        </div>

        <div id="js-ep-sidebar" class="sidebar sidebar--right">
            <div class="sidebar__inner">
                <div class="sidebar__heading">
                    <button
                        class="sidebar__close-btn call-action"
                        data-js-action="sidebar:toggle-visibility"
                        type="button"
                    >
                        <i class="ep-icon ep-icon_arrow-left"></i> <?php echo translate('general_sidebar_button_hide');?>
                    </button>
                </div>
                <div class="sidebar__content">
                    <?php views($sidebarContent); ?>
                </div>
            </div>

            <div class="sidebar__bg call-action" data-js-action="sidebar:toggle-visibility"></div>
        </div>

    </div>
</div>

<?php
    encoreEntryLinkTags('blog');
    encoreEntryScriptTags('blog');
?>
