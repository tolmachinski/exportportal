<div class="container-1420">
    <div class="category-head-img">
        <div class="category-head-img__inner">
            <div class="category-head-img__content">
                <h1 class="category-head-img__ttl"><?php echo $category['h1']; ?></h1>
            </div>

            <?php $categoryImgName = strForUrl($category['name']);?>
            <picture class="category-head-img__picture">
                <source
                    media="(max-width: 330px)"
                    srcset="<?php echo asset("public/build/images/category_header/{$categoryImgName}_330_1x.jpg");?> 1x, <?php echo asset("public/build/images/category_header/{$categoryImgName}_330_2x.jpg");?> 2x">
                <source
                    media="(max-width: 540px)"
                    srcset="<?php echo asset("public/build/images/category_header/{$categoryImgName}_540_1x.jpg");?> 1x, <?php echo asset("public/build/images/category_header/{$categoryImgName}_540_2x.jpg");?> 2x">
                <source
                    media="(max-width: 768px)"
                    srcset="<?php echo asset("public/build/images/category_header/{$categoryImgName}_738_1x.jpg");?> 1x, <?php echo asset("public/build/images/category_header/{$categoryImgName}_738_2x.jpg");?> 2x">
                <source
                    media="(max-width: 1024px)"
                    srcset="<?php echo asset("public/build/images/category_header/{$categoryImgName}_1024_1x.jpg");?> 1x, <?php echo asset("public/build/images/category_header/{$categoryImgName}_1024_2x.jpg");?> 2x">
                <img
                    class="category-head-img__image"
                    width="1420"
                    height="400"
                    src="<?php echo asset("public/build/images/category_header/{$categoryImgName}_1420_1x.jpg");?>"
                    srcset="<?php echo asset("public/build/images/category_header/{$categoryImgName}_1420_1x.jpg");?> 1x, <?php echo asset("public/build/images/category_header/{$categoryImgName}_1420_2x.jpg");?> 2x"
                    alt="<?php echo $category['name']; ?>"
                    <?php echo addQaUniqueIdentifier('page__category__header_image'); ?>
                >
            </picture>
        </div>

        <?php if ($category['is_restricted']) { ?>
            <div class="default-alert-b age-verification-disclaimer">
                <i class="ep-icon ep-icon_warning-circle-stroke"></i>
                <span>The international trade of the items in this category is strictly regulated. Export Portal releases liability and requires our user to know and obey the laws of their region and the region they are importing to.</span>
            </div>
        <?php } ?>

        <?php if (!empty($subcats)) {?>
            <div class="category-head-nav">
                <div class="category-head-nav__list">
                    <?php foreach ($subcats as $key => $row) {?>
                        <a
                            class="category-head-nav__list-item"
                            href="<?php echo __SITE_URL . 'category/' . strForURL($row['name']) . '/' . $row['category_id']; ?>"
                        >
                            <span
                                <?php echo addQaUniqueIdentifier('page__category__header_nav_item-name'); ?>
                            ><?php echo $row['name']; ?></span>
                            <span
                                class="category-head-nav__list-count"
                                <?php echo addQaUniqueIdentifier('page__category__header_nav_item-count'); ?>
                            ><?php echo $row['counter']; ?></span>
                        </a>
                    <?php } ?>
                </div>

                <a
                    class="category-head-nav__btn-all call-action"
                    data-js-action="category:open-modal"
                    href="<?php echo __SITE_URL;?>categories/popup_forms/preview_subcategories/<?php echo $category['category_id'];?>"
                    data-title="<?php echo htmlspecialchars('<div class="fancybox-title-additional"><div class="fancybox-title-additional__txt">'.$category['name'].'</div><div class="fancybox-title-additional__info">' . ($categoryItemsCount ? $categoryItemsCount : "") . ' items</div></div>');?>"
                >
                    <span class="category-head-nav__btn-text">Show all</span>
                    <?php echo getEpIconSvg('arrow-line-right', [16, 16], "category-head-nav__btn-arrow");?>
                </a>
            </div>
        <?php }?>
    </div>
</div>
