<?php if (isset($categoryGroups)) { ?>
    <section class="categories-group-section">
        <div class="section-header">
            <h2 class="section-header__title">
                <?php echo translate("featured_items_category_title"); ?>
            </h2>
        </div>

        <div class="categories-group-main categories-group-main--banner">
            <?php foreach ($categoryGroups as $category) { ?>
                <div class="categories-group-main__item">
                    <img
                        class="image js-lazy"
                        src="<?php echo getLazyImage(384, 222);?>"
                        data-src="<?php echo asset('public/build/images/categories-group/' . $category['img']); ?>"
                        alt="<?php echo $category['title']; ?>"
                    >
                    <a
                        class="categories-group-main__inner"
                        href="<?php echo __SITE_URL . 'categories?category=' . $category['id_group'];?>"
                        <?php echo addQaUniqueIdentifier('categories__select-category'); ?>
                    >
                        <h3 class="categories-group-main__name">
                            <?php echo implode('&<br>', explode('&', $category['title'])); ?>
                        </h3>
                    </a>
                </div>
            <?php } ?>
        </div>
    </section>
<?php } ?>
