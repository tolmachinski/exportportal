<?php $have_right_sell_item = have_right('sell_item');?>

<?php foreach ($categories as $category) {?>
    <div class="all-categories__search-item">
        <a class="link" href="<?php echo __SITE_URL . 'category/'.strForURL($category['parent_for_link'].' '.$category['name']).'/'.$category['category_id']?>" target="_blank">
            <?php echo $category['parent_for_link'].' '.$category['name']?>
        </a>
        <div class="all-categories__wrapper">
            <?php echo implode(' / ', $category['breadcrumbs']);?>
        </div>

        <?php if ($have_right_sell_item) {?>
            <a class="btn-add-item btn btn-primary call-action" data-js-action="categories:submit-add-product" data-category="<?php echo $category['category_id']; ?>" title="Add product in this category"><i class="ep-icon ep-icon_plus-stroke"></i></a>
        <?php }?>
    </div>
<?php }?>
