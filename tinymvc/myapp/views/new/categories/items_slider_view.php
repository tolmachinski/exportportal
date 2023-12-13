<div class="categories-items-s">
    <div class="categories-items-s__inner">
        <div class="container-center-sm">
            <div class="categories-items-s__row">
                <div class="categories-items-s__col categories-items-s__col--left">
                    <div class="categories-products-slider-heading">
                        <h2 class="categories-products-slider-heading__title"><?php echo $title; ?></h2>
                        <p class="categories-products-slider-heading__subtitle"><?php echo $description; ?></p>
                    </div>
                    <a class="categories-items-s__btn categories-items-s__btn--lg btn btn-primary" href="<?php echo $btnUrl; ?>"><?php echo $btnTxt; ?></a>
                </div>
                <div class="categories-items-s__col categories-items-s__col--right">
                    <div
                        class="products products--slider-full <?php echo $itemsSlider; ?>"
                        data-items-count="<?php echo count($products); ?>"
                        data-slider-name="<?php echo $sliderName; ?>"
                    >
                        <?php views()->display('new/item/list_item_view', ['items' => $products, 'has_hover' => false]); ?>
                    </div>
                    <a class="categories-items-s__btn categories-items-s__btn--md btn btn-new16 btn-primary" href="<?php echo $btnUrl; ?>"><?php echo $btnTxt; ?></a>
                </div>
            </div>
        </div>
    </div>
</div>
