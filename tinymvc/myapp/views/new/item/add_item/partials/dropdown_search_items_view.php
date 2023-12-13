<?php foreach ($categories as $category) {
        $breadcrumbs = json_decode("[" . $category['breadcrumbs'] . "]", true);
?>
    <div class="category-fast-search__result-item">
        <div
            class="category-fast-search__result-item-inner call-function"
            <?php if($category['is_restricted']) { ?>
            data-callback="openAdultItemsTerms"
            <?php } else { ?>
            data-callback="submitAddProduct"
            <?php } ?>
            data-category="<?php echo $category['category_id']; ?>"
            title="Add product in this category"
        >
            <div class="category-fast-search__result-name"><?php echo $category['parent_for_link'].' '.$category['name']?></div>
            <div class="category-fast-search__result-category">in <?php echo array_values($breadcrumbs[0])[0]; ?></div>
        </div>
    </div>
<?php }?>
