<?php
$level = 2;

foreach($product_categories['list'] as $product_categories_group_key => $product_categories_group_item){?>
	<div class="form-categories__toggle">
        <div
            class="form-categories__selected<?php echo ($product_categories_group_key != $product_categories['last'])?' active':'';?>"
        >
			<i class="ep-icon ep-icon_arrow-line-left call-function" data-callback="showFormCategories"></i>
			<div class="form-categories__item-name">
                <?php echo ($product_categories_group_key != $product_categories['last'])?$product_categories_group_item[$product_categories_group_key]['name']:'';?>
            </div>
		</div>

        <ul
            class="form-categories__list<?php echo ($product_categories_group_key != $product_categories['last'])?' display-n':'';?>"
            data-level="<?php echo $level?>"
        >
            <?php
                $product_cat_params_temp = array(
                    'class_next' => ' current',
                    'class_last' => ' last',
                    'icon_next' => '<i class="ep-icon ep-icon_arrow-line-right"></i>',
                    'icon_last' => '<i class="ep-icon ep-icon_ok-stroke"></i>',
                );

                foreach($product_categories_group_item as $product_categories_group_item_key => $product_categories_group_item_sub) { ?>
                <?php
                    $product_cat_params = array(
                        'class' => '',
                        'icon' => '',
                    );

                    if(
                        $product_categories_group_key == $product_categories_group_item_key
                    ){
                        $product_cat_params = array(
                            'class' => $product_cat_params_temp['class_next'],
                            'icon' => $product_cat_params_temp['icon_next'],
                        );

                        if(
                            $product_categories['last'] == $product_categories_group_item_key
                        ){

                            $product_cat_params['class'] = $product_cat_params_temp['class_last'];
                            $product_cat_params['icon'] = $product_cat_params_temp['icon_last'];
                        }
                    }
                ?>

                <li
                    class="form-categories__item<?php echo $product_cat_params['class'];?>"
                    data-id="<?php echo $product_categories_group_item_key; ?>"
                >
                    <?php echo $product_categories_group_item_sub['name']; ?>
                    <?php echo $product_cat_params['icon'];?>
                </li>

			<?php } ?>
		</ul>
	</div>
	<?php $level++;?>
<?php }?>
