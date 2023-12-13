<?php foreach($categories as $category){?>
    <?php
        $item_breadcrumbs = json_decode('['.$category['breadcrumbs'].']', true);
        if(!empty($item_breadcrumbs)){
            foreach($item_breadcrumbs as $category_parent){
                foreach($category_parent as $cat_id => $cat_title)
                    $cat_parents[]= $cat_id;
            }
        }
    ?>
	<option data-value-text="<?php echo capitalWord($category['name']); ?>" data-categories="<?php echo implode(',',$cat_parents);?>" value="<?php echo $category['category_id']?>"><?php echo $level; echo capitalWord($category['name']); ?> (<?php echo $category['counter']?>)</option>
	<?php if(!empty($category['subcats'])){
			recursive_ctegories_product($category['subcats'], $level);
		}?>
<?php }?>
