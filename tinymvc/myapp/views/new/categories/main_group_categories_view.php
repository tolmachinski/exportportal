<?php
    views()->display('new/categories/items_slider_view', [
        'title'         => translate('categories_page_featured_items_slider_ttl'),
        'description'   => translate('categories_page_featured_items_slider_desc'),
        'btnTxt'        => translate('categories_page_featured_items_slider_btn'),
        'products'      => $featuredItems,
        'itemsSlider'   => 'js-featured-items-categories-slider',
        'btnUrl'        => __SITE_URL . 'items/featured',
        'sliderName'    => 'featured-items',
    ]);
?>

<?php views()->display('new/categories/popular_items_view'); ?>

<?php
    views()->display('new/categories/items_slider_view', [
        'title'         => translate('categories_page_latest_items_slider_ttl', [
            '{{START_TAG}}' => '<span class="hide-txt-sm">',
            '{{END_TAG}}'   => '</span>',
        ]),
        'description'   => translate('categories_page_latest_items_slider_desc'),
        'btnTxt'        => translate('categories_page_latest_items_slider_btn'),
        'products'      => $latestItems,
        'btnUrl'        => __SITE_URL . 'items/latest',
        'itemsSlider'   => 'js-latest-items-categories-slider',
        'sliderName'    => 'latest-items',
    ]);
?>

<div class="container-center-sm">
    <?php
        if (logged_in() && have_right('manage_personal_items') || !logged_in()) {
            views()->display("new/banners/categories_banner_view");
        }
    ?>
</div>

<script type="text/template" id="js-template-categories-group-list">
    <ul class="categories-group-list" data-list-category="{{ID}}">
        {{ITEM}}
    </ul>
</script>

<script type="text/template" id="js-template-categories-group-list-item">
    <li class="categories-group-list__item">
        <div class="categories-group-list__top" <?php echo addQaUniqueIdentifier('categories__select-property'); ?>>
            {{ICON}}
            {{LINK}}
            <div class="categories-group-list__counter" <?php echo addQaUniqueIdentifier('categories__counter'); ?>>
                ({{COUNT}})
            </div>
        </div>
    </li>
</script>

<script type="text/template" id="js-template-categories-group-list-item-toggle">
    <li class="categories-group-list__item">
        <div class="categories-group-list__top">
            {{ICON}}
            {{LINK}}
            <div class="categories-group-list__counter" <?php echo addQaUniqueIdentifier('categories__counter'); ?>>
                ({{COUNT}})
            </div>
        </div>
        {{LIST}}
    </li>
</script>

<script type="text/template" id="js-template-categories-group-list-item-simple">
    <li class="categories-group-list__item" <?php echo addQaUniqueIdentifier('categories__select-subcategory'); ?>>
        {{LINK}}
    </li>
</script>

<?php if(!isset($webpackData)) { ?>
    <script src="<?php echo fileModificationTime('public/plug/js/categories/open-age-verification.js'); ?>"></script>
<?php } ?>
