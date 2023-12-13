<div class="container-fluid-modal">
    <div class="cat-nav-list">
        <?php
        $count_categories_per_column = ceil(count($subcats) / 3);
        $subcategory_index = 0;
        for ($i = 0; $i < 3; ++$i) { ?>
            <div class="cat-nav-list__col">
                <?php for ($j = 0; $j < $count_categories_per_column; ++$j) { ?>
                    <?php
                        $subcategory = $subcats[$subcategory_index];
                        if (empty($subcategory)) {
                            break;
                        }
                        ++$subcategory_index;
                    ?>

                    <a
                        class="cat-nav-list__item"
                        href="<?php echo __SITE_URL . 'category/' . strForURL($subcategory['name']) . '/' . $subcategory['category_id']; ?>"
                    >
                        <span class="cat-nav-list__item-name"><?php echo $subcategory['name']; ?></span>
                        <span class="cat-nav-list__item-count"><?php echo $subcategory['counter']; ?></span>
                    </a>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
