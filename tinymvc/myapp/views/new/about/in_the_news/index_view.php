<div class="mobile-links">
    <a class="btn btn-primary btn-panel-left fancyboxSidebar fancybox" data-title="Menu" href="#about-flex-card__fixed-left">
        <i class="ep-icon ep-icon_items"></i> <?php echo translate('about_us_nav_menu_btn');?>
    </a>
</div>

<div class="news news--newsletter-archive">
    <div class="nav--inline">
        <ul class="nav nav-tabs nav--borders nav--new" role="tablist">
            <li class="nav-item">
                <a class="nav-link js-in-the-news-tab<?php echo $pageHash === 'press_releases' || !isset($pageHash) ? ' active' : ''; ?>"
                    href="#press_releases"
                    aria-controls="title"
                    role="tab"
                    data-toggle="tab"
                    data-img="press_releases_header.jpg"
                    data-title="<?php echo translate('about_us_in_the_news_press_releases_header_title', null, true);?>"
                    <?php echo addQaUniqueIdentifier("page__news_and_media__press-releases_tab"); ?>
                >
                        <?php echo translate('about_us_in_the_news_press_releases_tab_title');?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link js-in-the-news-tab<?php echo $pageHash === 'ep_news' ? ' active' : ''; ?>"
                    href="#ep_news"
                    aria-controls="title"
                    role="tab"
                    data-toggle="tab"
                    data-img="in_the_news_header3.jpg"
                    data-title="<?php echo translate('about_us_in_the_news_news_header_title', null, true);?>"
                    <?php echo addQaUniqueIdentifier('page__news-and-media__tab_news-btn'); ?>
                >
                        <?php echo translate('about_us_in_the_news_news_tab_title');?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link js-in-the-news-tab<?php echo $pageHash === 'ep_updates' ? ' active' : ''; ?>"
                    href="#ep_updates"
                    aria-controls="title"
                    role="tab"
                    data-toggle="tab"
                    data-img="updates_header.jpg"
                    data-title="<?php echo translate('about_us_in_the_news_updates_header_title', null, true);?>"
                    <?php echo addQaUniqueIdentifier('page__news-and-media__tab_updates-btn') ?>>
                    <?php echo translate('about_us_in_the_news_updates_tab_title');?>
                </a>
            </li>
            <?php if (!empty($newsletter_archive)) { ?>
            <li class="nav-item">
                <a class="nav-link js-in-the-news-tab<?php echo $pageHash === 'newsletter_archive' ? ' active' : ''; ?>"
                    href="#newsletter_archive"
                    aria-controls="title"
                    role="tab"
                    data-toggle="tab"
                    data-img="newsletter_archive_header.jpg"
                    data-title="<?php echo translate('about_us_in_the_news_newsletter_archive_header_title', null, true);?>"
                    <?php echo addQaUniqueIdentifier('page__news-and-media__tab_newsletter-archive-btn') ?>>
                    <?php echo translate('about_us_in_the_news_newsletter_archive_tab_title');?>
                </a>
            </li>
            <?php }?>
        </ul>
    </div>

    <div class="tab-content tab-content--borders tab-content--archive">
        <div role="tabpanel" class="tab-pane fade<?php echo $pageHash === 'press_releases' || !isset($pageHash) ? ' show active' : ''; ?>" id="press_releases">
            <?php if (!empty($news_list)) { ?>
                <div class="row row-eq-height pb-50">
                    <?php app()->view->display('new/in_the_news/partial_news_view');?>
                </div>
                <div class="col-md-12">
                    <a class="btn btn-outline-dark news-block__more-button" href="<?php echo get_dynamic_url('mass_media', __SITE_URL, true)?>"><?php echo translate('about_us_in_the_news_press_releases_tab_view_more_btn');?></a>
                </div>
            <?php } else { ?>
                <?php echo translate('about_us_in_the_news_press_releases_tab_not_found_releases');?>
            <?php } ?>
        </div>
        <div role="tabpanel" class="tab-pane fade<?php echo $pageHash === 'ep_news' ? ' show active' : ''; ?>" id="ep_news">
            <?php if (!empty($ep_news)) { ?>
                <div class="row row-eq-height pb-50">
                    <?php app()->view->display('new/ep_news/list_view');?>
                </div>
                <div class="col-md-12">
                    <a class="btn btn-outline-dark news-block__more-button" href="<?php echo get_dynamic_url( "ep_news", __SITE_URL, true);?>"><?php echo translate('about_us_in_the_news_news_tab_view_more_btn');?></a>
                </div>
            <?php } else { ?>
                <?php echo translate('about_us_in_the_news_news_tab_not_found_news');?>
            <?php } ?>
        </div>
        <div role="tabpanel" class="tab-pane fade<?php echo $pageHash === 'ep_updates' ? ' show active' : ''; ?>" id="ep_updates">
            <?php if (!empty($ep_updates)) { ?>
                <div class="row row-eq-height pb-50">
                    <?php app()->view->display('new/ep_updates/list_view');?>
                </div>
                <div class="col-md-12">
                    <a class="btn btn-outline-dark news-block__more-button" href="<?php echo get_dynamic_url("ep_updates", __SITE_URL, true)?>"><?php echo translate('about_us_in_the_news_updates_tab_view_more_btn');?></a>
                </div>
            <?php } else { ?>
                <?php echo translate('about_us_in_the_news_updates_tab_not_found_updates');?>
            <?php } ?>
        </div>
        <?php if (!empty($newsletter_archive)) { ?>
        <div role="tabpanel" class="tab-pane fade<?php echo $pageHash === 'newsletter_archive' ? ' show active' : ''; ?>" id="newsletter_archive">
            <div class="row row-eq-height pb-50">
                <?php app()->view->display('new/newsletter/list_view');?>
            </div>
            <div class="col-md-12">
                <a class="btn btn-outline-dark news-block__more-button" href="<?php echo get_dynamic_url("newsletter/all", __SITE_URL, true)?>"><?php echo translate('about_us_in_the_news_updates_tab_view_more_btn');?></a>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<script>
    $('.js-in-the-news-tab').on('shown.bs.tab', function (e) {
        var $this = $(e.target);
        var imgUrl = __img_url + "public/img/headers-info-pages/" + $this.data('img');
        var headerBlock = $("#js-public-heading");
        var image = headerBlock.find(".image");

        if (image[0].style.backgroundImage) {
            image[0].style.backgroundImage = "url('" + imgUrl +"')";
        }

        image.attr("src", imgUrl);
        image.attr("alt", $this.data('title'));
        headerBlock.find(".public-heading__ttl").html($this.data('title'));

        // Replace hash
        const queryParams = new URLSearchParams(window.location.search);
        queryParams.set("hash", $this.attr("href").replace("#", ""));
        history.pushState(null, document.title, window.location.href.split("?")[0] + "?" + queryParams.toString());
    })
</script>

<?php if ($pageHash) { ?>
    <script>
        setTimeout(() => {
            const hash = "#<?php echo $pageHash; ?>";
            $('html,body').animate({scrollTop: $(hash).offset().top - 160});
        }, 500);
    </script>
<?php } ?>
