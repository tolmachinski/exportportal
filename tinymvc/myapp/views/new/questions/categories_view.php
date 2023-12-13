<?php $main_categories = array_filter($quest_cats, function($el){ return $el['on_main_page'] == 1; });
    array_multisort(array_column($main_categories, "order_number"), SORT_ASC, $main_categories);
?>

<section class="categories">
    <div class="community-container">
        <div class="categories__inner">
        <?php foreach($main_categories as $category){ ?>
            <a class="categories__item" href="<?php echo replace_dynamic_uri($category['url'], $links_tpl[$questions_uri_components['category']], __COMMUNITY_ALL_URL);?>">
                <div class="categories__icon"><i class="<?php echo $category['icon']; ?>"></i></div>
                <div class="categories__title">
                    <?php echo $category['title_cat']; ?>
                </div>
            </a>
        <?php } ?>
        </div>
    </div>
</section>
<?php encoreEntryLinkTags('community_index'); ?>
<?php encoreLinks(); ?>
