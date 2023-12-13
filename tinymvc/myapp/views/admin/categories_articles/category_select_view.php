<?php  if(isset($categories) && is_array($categories) && count($categories) > 0){ ?>
<div class="subcategories " level="<?php echo $level?>" >
    <select name="parent" data-title="Categories" class="categ1 dt_filter pull-left w-200 mt-5" level="<?php echo $level?>" id="<?php echo $cat?>">
        <option data-default="true" value="<?php echo $cat?>">Select category</option>
    	<?php foreach($categories as $category){?>
        	<option  value="<?php echo $category['category_id']?>">
        		<?php if($category['id_article']){?>*<?php }?>
        		<?php echo $category['name']?>
        	</option>
    	<?php } ?>
    </select>
</div>
<?php } ?>
