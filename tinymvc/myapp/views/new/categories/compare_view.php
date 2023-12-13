<?php foreach ($categories ?: [] as $categoryId => $category) {?>
    <li class="dropdown-drop__list-item">
        <div class="dropdown-drop__title-block" category-aim="<?php echo "category-{$category['category_id']}";?>">
            <div class="dropdown-drop__trigger">
                <i class ="ep-icon <?php echo empty($category['subcats']) ? 'ep-icon_circle' : 'ep-icon_plus-stroke';?>"></i>
            </div>
            <div class="dropdown-drop__category"><?php echo $category['name'];?></div>
            <div class="dropdown-drop__count">0</div>
            <div class="dropdown-drop__close">
                <i class="ep-icon ep-icon_remove-stroke "></i>
            </div>
        </div>
        <?php if (!empty($category['subcats'])) {?>
            <ul class="dropdown-drop__list">
                <?php views('new/categories/compare_view', ['categories' => $category['subcats']]);?>
            </ul>
        <?php }?>
    </li>
<?php }?>
