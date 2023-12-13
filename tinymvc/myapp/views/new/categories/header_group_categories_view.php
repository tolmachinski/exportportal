<div id="js-categories-group-wr" class="container-center-sm relative-b">
    <?php
    if (!empty($_GET['keywords'])) {
        echo tmvc::instance()->controller->view->display('new/categories/search_categories_block_view');
    }
    ?>

    <h1 class="categories-main-title"><?php echo translate('categories_page_ttl'); ?></h1>

    <div id="js-categories-group-breadcrumbs" class="categories-group-breadcrumbs">
        <div data-step="1" class="categories-group-breadcrumbs__item display-n_i">
            <a class="link call-action" data-js-action="categories:select-category-breadcrumb" href="#">
                <i class="ep-icon ep-icon_arrow-line-left mr-5"></i> <?php echo translate('all_categories_link_text'); ?>
            </a>
        </div>
        <div data-step="2" class="categories-group-breadcrumbs__item display-n_i">
            <a class="link call-action" data-js-action="categories:select-category-breadcrumb" href="#"></a>
        </div>
        <div data-step="3" class="categories-group-breadcrumbs__item display-n_i">
            <a class="link call-action" data-js-action="categories:select-category-breadcrumb" href="#"></a>
        </div>
        <div data-step="4" class="categories-group-breadcrumbs__item display-n_i">
            <span class="link"></span>
        </div>
    </div>

    <div class="js-wr-category-group categories-group-main" data-step="1">
        <?php foreach ($category_groups as $category_groups_item) {
            $title_category_groups = explode('&', $category_groups_item['title']); ?>
            <div class="categories-group-main__item call-action js-category-<?php echo  $category_groups_item['id_group']; ?>" data-js-action="categories:golden-category-select" data-category="<?php echo $category_groups_item['id_group']; ?>">
                <?php if ($category_groups_item['id_group'] < 10) { ?>
                    <img class="image" src="<?php echo __IMG_URL . getFileExits('public/img/categories_group/' . $category_groups_item['img'], 'public/img/no_image/group/noimage-other.svg'); ?>" alt="<?php echo $category_groups_item['title']; ?>">
                <?php } else { ?>
                    <img class="image js-lazy" src="<?php echo getLazyImage(384, 222); ?>" data-src="<?php echo __IMG_URL . getFileExits('public/img/categories_group/' . $category_groups_item['img'], 'public/img/no_image/group/noimage-other.svg'); ?>" alt="<?php echo $category_groups_item['title']; ?>">
                <?php } ?>
                <div class="categories-group-main__inner" data-group="<?php echo  $category_groups_item['id_group']; ?>" <?php echo addQaUniqueIdentifier('categories__select-category'); ?>>
                    <h2 class="categories-group-main__name"><?php echo implode('&<br>', $title_category_groups); ?></h2>
                </div>
            </div>
        <?php } ?>
    </div>

    <div id="js-categories-group-selected" class="categories-group-selected">
        <h3 id="js-categories-group-selected-title" class="categories-group-selected__title display-n_i"></h3>

        <div class="categories-group-selected__row ">
            <div id="js-first-category-list" class="js-wr-category-group categories-group-selected__col categories-group-selected__col--first display-n_i" data-step="2"></div>

            <div id="js-center-category-list" class="js-wr-category-group categories-group-selected__col categories-group-selected__col--center display-n_i" data-step="3"></div>

            <div id="js-last-category-list" class="js-wr-category-group categories-group-selected__col categories-group-selected__col--last display-n_i" data-step="4"></div>
        </div>

    </div>
</div>

<div class="container-center-sm categories-items-s">
    <section class="categories-handcraft-banner">
        <picture class="categories-handcraft-banner__picture">
            <source
                srcset="<?php echo getLazyImage(400, 200); ?>"
                data-srcset="<?php echo asset('public/build/images/categories/discover-mobile.jpg'); ?> 1x, <?php echo asset('public/build/images/categories/discover-mobile@2x.jpg'); ?> 2x"
                media="(max-width: 575px)"
            >
            <source
                srcset="<?php echo getLazyImage(275, 100); ?>"
                data-srcset="<?php echo asset('public/build/images/categories/discover-tablet.jpg'); ?> 1x, <?php echo asset('public/build/images/categories/discover-tablet@2x.jpg'); ?> 2x"
                media="(max-width: 991px)"
            >
            <img
                class="categories-handcraft-banner__image js-lazy"
                src="<?php echo getLazyImage(556, 100); ?>"
                data-src="<?php echo asset('public/build/images/categories/discover.jpg'); ?>"
                data-srcset="<?php echo asset('public/build/images/categories/discover.jpg'); ?> 1x, <?php echo asset('public/build/images/categories/discover@2x.jpg'); ?> 2x" alt="Explore the Top 50 Products"
            >
        </picture>
        <div class="categories-handcraft-banner__info">
            <h3 class="categories-handcraft-banner__title">
                <?php echo translate('categories_page_handcraft_banner_title') ?>
            </h3>
            <a
                class="categories-handcraft-banner__btn btn btn-primary btn-new18"
                <?php echo addQaUniqueIdentifier("page__cagetories__handcraft-banner_view-more-btn"); ?>
                href="<?php echo __SITE_URL . 'landing/handmade' ;?>"
            >
                <?php echo translate('categories_page_handcraft_banner_view_more_button') ?>
            </a>
        </div>
    </section>
</div>
