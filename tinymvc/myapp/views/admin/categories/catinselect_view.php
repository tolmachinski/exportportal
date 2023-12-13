<?php  if(isset($categories) && is_array($categories) && count($categories) > 0){ ?>
<div class="subcategories" level="<?php echo $level?>" >
    <select name="<?php echo isset($select_name) ? $select_name :  'parent'; ?>" data-title="Category" class="<?php echo $class?> <?php echo isset($not_filter) ?: 'dt_filter'; ?> pull-left mr-5 mt-5" level="<?php echo $level?>" id="<?php echo $cat?>">
        <option data-default="true" value="<?php echo $cat?>">Select category</option>
    <?php foreach($categories as $category){?>
        <option  value="<?php echo $category['category_id']?>"><?php echo $category['name']?></option>
    <?php } ?>
    </select>
</div>
<?php } ?>
