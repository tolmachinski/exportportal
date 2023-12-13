<div class="filter-options">
    <?php
        if (!empty($search_params)) {
            views()->display('new/blog/search_params_view');
        }
    ?>

    <div class="filter-block">
        <h2 class="filter-block__ttl"><?php echo translate('blog_sidebar_search_header'); ?></h2>

        <form
            class="js-on-validate-before-submit validengine filter-search-form"
            method="get"
            action="<?php echo $search_form_link; ?>"
        >
            <input
                class="js-on-validate-before-submit__input filter-search-form__field ep-input validate[ minSize[2]]"
                type="text"
                name="keywords"
                maxlength="50"
                placeholder="<?php echo translate('blog_form_search_keywords_placeholder'); ?>"
                value="<?php echo $keywords; ?>"
                <?php echo addQaUniqueIdentifier('page__blog__search_input_keywords'); ?>
            />

            <button
                class="filter-search-form__btn btn btn-new16 btn-light btn-block"
                type="submit"
                <?php echo addQaUniqueIdentifier('page__blog__search_button_submit'); ?>
            ><?php echo translate('blog_form_search_button_submit'); ?></button>
        </form>
    </div>

    <?php if (!empty($blogsCategories)) {?>
        <div class="filter-block">
            <h2 class="filter-block__ttl"><?php echo translate('blog_sidebar_categories_header'); ?></h2>

            <ul class="filter-options-list js-hide-max-list">
                <?php foreach ($blogsCategories as $category) {?>
                    <li class="filter-options-list__item">
                        <div class="filter-options-list__inner">
                            <a
                                class="filter-options-list__link"
                                href="<?php echo $category['link']; ?>"
                                <?php echo addQaUniqueIdentifier('global__sidebar-category'); ?>
                            >
                                <?php echo $category['name']; ?>
                            </a>
                            <span
                                class="filter-options-list__counter"
                                <?php echo addQaUniqueIdentifier('global__sidebar-counter'); ?>
                            ><?php echo $category['counter']; ?></span>
                        </div>
                    </li>
                <?php }?>
            </ul>
        </div>
    <?php }?>

    <?php if (!empty($blogsArchived)) {?>
        <div class="filter-block">
            <h2 class="filter-block__ttl"><?php echo translate('blog_sidebar_archives_header'); ?></h2>

            <ul class="filter-options-list js-hide-max-list">
                <?php foreach ($blogsArchived as $blogArchived) {?>
                    <li class="filter-options-list__item">
                        <div class="filter-options-list__inner">
                            <a
                                class="filter-options-list__link"
                                href="<?php echo  __BLOG_URL . "{$blog_uri_components['archived']}/{$blogArchived['blog_month']}-{$blogArchived['blog_year']}"; ?>"
                                <?php echo addQaUniqueIdentifier('global__sidebar-archive'); ?>
                            >
                                <?php echo $blogArchived['month_name'] . ' ' . $blogArchived['blog_year']; ?>
                            </a>
                            <span
                                class="filter-options-list__counter"
                                <?php echo addQaUniqueIdentifier('global__sidebar-counter'); ?>
                            ><?php echo $blogArchived['counter']; ?></span>
                        </div>
                    </li>
                <?php }?>
            </ul>
        </div>
    <?php }?>

    <?php views()->display('new/subscribe/sidebar_view'); ?>

    <?php echo widgetShowBanner('blogs_sidebar', 'promo-banner-wr--blogs'); ?>
</div>

