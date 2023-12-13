<section class="home-section container-1420">
    <div class="section-header">
        <h2 class="section-header__title"><?php echo translate('home_header_title_shop_by_category');?></h2>
        <a class="section-header__link" href="<?php echo __SITE_URL . 'categories';?>" <?php echo addQaUniqueIdentifier("home__shop-category-link-all-categories"); ?>><?php echo translate('home_header_link_all_categories'); ?><?php echo widgetGetSvgIcon("arrowRight", 15, 15);?></a>
    </div>
    <div class="golden-categories">
        <?php foreach ($goldenCategories as $key => $goldenCategory) { ?>
            <a class="golden-category" href="<?php echo __SITE_URL . 'categories?category=' . $goldenCategory['id_group'];?>"  <?php echo addQaUniqueIdentifier("home__shop-category-link-{$key}"); ?>>
                <picture class="golden-category__picture">
                    <source srcset="<?php echo getLazyImage(344, 200);?>" data-srcset="<?php echo asset('public/build/images/golden-categories/' . $goldenCategory['images']['tablet']);?> 1x, <?php echo asset('public/build/images/golden-categories/' . $goldenCategory['images']['tablet@2x']);?> 2x" media="(max-width: 991px)">
                    <img class="golden-category__image js-lazy" src="<?php echo getLazyImage(344, 200);?>" data-src="<?php echo asset('public/build/images/golden-categories/' . $goldenCategory['images']['desktop']);?>" alt="<?php echo cleanOutput($goldenCategory['title']);?>">
                </picture>
                <strong class="golden-category__title"><?php echo $goldenCategory['title'];?></strong>
            </a>
        <?php }?>
    </div>
</section>
